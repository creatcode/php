<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2016/12/8
 * Time: 10:14
 */
namespace Instructions;
use Tool\Curl;

class Instructions {

    private $gap_time = 0;

    private $curl;

    const API_URL = 'http://47.90.39.93:8888?version=1';
    const NEW_API_URL = 'http://gps.dola520.com:8888?version=1';

    const TEST_LOCKS = array(
        '063072913722', 
        '063074009610', 
        '063072964022',
        '063072920222',
        '122334456789',
        '222334456789',
        '322334456789',
        '122301456789',
        '122302456789',
        '122303456789',
        '122304456789',
        '122305456789',
        '122306456789',
        '122307456789',
        '122308456789',
        '122309456789',
        '122310456789',
        '063072911015',
        '063072619956',
        '063072649805',
        '063072672997',
        '063072674845',
        '063072658285',
        '063072665884'
    );

    public function __construct($registry) {
        $this->lock = new \Sys_Model\Lock($registry);
    }

    /**
     * 下发指令，指令的类型有select,open,close,gapTime
     * @param array $data 键值为cmd和device_id
     * @return mixed
     */
    public function sendInstruct($data) {
        // 判断锁是否属于新平台
        $condition = array(
            'lock_sn' => $data['device_id']
        );
        $lockInfo = $this->lock->getLockInfo($condition);
        if (isset($lockInfo['lock_platform']) && $lockInfo['lock_platform'] == 1) {
            $this->curl = new Curl(self::NEW_API_URL);
        } else {
            $this->curl = new Curl(self::API_URL);
        }

        $type = $data['cmd'];
        $type = strtolower($type);
        $base = array(
            'userid' => USER_ID,
            'cmd' => $data['cmd'],
            'deviceid' => $data['device_id']
        );
        if (in_array($type, array('select', 'open', 'close', 'beep'))) {
            $base['serialnum'] = $this->gap_time ? $this->gap_time : $this->make_sn();
        } else {
            $base['serialnum'] = $this->gap_time ? $this->gap_time : GAP_TIME;
        }

        $base['sign'] = $this->make_md5($base);
        $base = json_encode($base);
        if(in_array($data['device_id'], self::TEST_LOCKS )) {
            $client = new \Swoole\Client(SWOOLE_SOCK_TCP);
            if ($client->connect('120.76.98.150', 5200, 3)) { //连接到锁平台服务器，超时3秒
                $client->send($base);
                $response = $client->recv();
                $client->close();
//                sleep(10);
                return $response===false ? ('{"result":"fail","info":"errorCode:' . $client->errCode . '"}') : '';//$response;
            }
        }
        $this->curl->setData($base);
        $response = $this->curl->postData();
//        file_put_contents('/data/wwwroot/default/bike/transfer/controller/transfer/logs/instruction.log', date('Y-m-d H:i:s ') . $response. "\n", FILE_APPEND);
        return $response;
    }

    public function parseLock($device_id, $cmd) {
        $data['device_id'] = $device_id;
        $data['cmd'] = $cmd;
        $response = $this->sendInstruct($data);
        return $response;
    }

    /**
     * 开锁
     * @param $device_id
     * @param $time
     * @return mixed
     */
    public function openLock($device_id, $time = 0) {
        if ($time > 0) {
            $this->gap_time = $time;
        }
        $response = $this->parseLock($device_id, 'open');
        $arr = $this->jsonToArray($response);
        if (strtolower($arr['result']) == 'ok') {
            return callback(true);
        }
        return callback(false, '发送失败', $arr);
    }

    /**
     * 关锁
     * @param $device_id
     * @return mixed
     */
    public function closeLock($device_id) {
        $response = $this->parseLock($device_id, 'close');
        return $response;
    }

    /**
     * 寻车，发送车响的警报
     * @param $device_id
     * @return string $response
     */
    public function beepLock($device_id) {
        $response = $this->parseLock($device_id, 'beep');
        return $response;
    }

    /**
     * 查询数据
     * @param $data
     * @return bool|mixed
     */
    public function selectLocks($data) {
        if (empty($data)) {
            return false;
        }
        if (is_array($data)) {
            $device_id = implode(',', $data); //以逗号隔开
        } else {
            $device_id = $data;
        }
        $response = $this->parseLock($device_id, 'select');
        return $response;
    }

    /**
     * 设置每台设备回传时间
     * @param $device_id
     * @param $time
     */
    public function setGapTime($device_id, $time) {
        $this->gap_time = $time;
        $this->parseLock($device_id, 'gapTime');
    }

    /**
     * 生成流水号，直接时间戳
     * @return int
     */
    public function make_sn() {
        return time();
    }

    /**
     * MD5签名
     * @param $data
     * @return string
     */
    public function make_md5($data) {
        $str = '';
        if (!empty($data)) {
            $str .= implode('', $data);
        }
        $str = $str . USER_KEY;
        return md5($str);
    }

    public function jsonToArray($json) {
        return json_decode($json, true);
    }
}
