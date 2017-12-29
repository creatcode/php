<?php

/**
 * 锁
 * Class ControllerYunWeiLock
 */
class ControllerYunWeiLock extends Controller
{

    /**
     * 批量获取锁信息
     */
    public function index() {
        $this->load->library("sys_model/bicycle");
        $this->load->library("sys_model/lock");

        if(!$this->request->post('mac') && $this->request->post('lock_sn')){
            $lock_sn_str = $this->request->post('lock_sn');
            $lock_list = $this->sys_model_lock->getLockList(array('lock_sn' => array('IN',$lock_sn_str)));
            if(!empty($lock_list)){
                foreach($lock_list as $item){
                    if($item['mac_address']){
                        $mac_str = isset($mac_str) ? $mac_str.','.$item['mac_address'] : $item['mac_address'];
                    }
                }
            }
            $macs = isset($mac_str) ? $mac_str : '';
        }else{
            $macs = $this->request->post('mac');
        }

        $macArr = explode(',', $macs);
        // 初始化锁信息
        $lockData = array();
        foreach ($macArr as &$mac) {
            $mac = str_replace(':', '', $mac);
            $lockData[$mac] = array(
                'mac' => $mac,
                'is_company' => 0
            );
            $mac = strtoupper(substr(chunk_split($mac, 2, ':'), 0, 17));
        }
        // 获取已入库的锁信息
        $condition = array(
            'mac_address' => array('in', $macArr)
        );
        $order = '';
        $limit = '';
        $field = 'l.*, bicycle.full_bicycle_sn';
        $join = array(
            'bicycle' => 'bicycle.lock_sn=l.lock_sn'
        );
        $lockArr = $this->sys_model_lock->getLockList($condition, $order, $limit, $field, $join);
        if (!empty($lockArr) && is_array($lockArr)) {
            foreach ($lockArr as $lock) {
                $mac = str_replace(':', '', $lock['mac_address']);
                $lockData[$mac] = array(
                    'mac' => $mac,
                    'is_company' => 1,
                    'lock_sn' => $lock['lock_sn'],
                    'lock_type' => $lock['lock_type'],
                    'bicycle_sn' => $lock['full_bicycle_sn'],
                );
            }
        }
        $this->response->showSuccessResult(array_values($lockData), '数据获取成功');
    }

    public function getLocation(){
        $post = $this->request->post(array('lock_sn'));
        if(!$post['lock_sn']){
            $this->response->showJsonResult('锁编号参数错误',0,array(),101);
        }
        $send_data = array(
            'deviceid' => $post['lock_sn'],
            'ptype' => 'location',
            'serialnum' => time()
        );

        $client = new \Swoole\Client(SWOOLE_SOCK_TCP);
        if ($client->connect('120.76.98.150', 5200, 3)) { //连接到锁平台服务器，超时3秒
            $client->send(json_encode($send_data));
            $response = $client->recv();
            $response = json_decode($response,true);
            $client->close();
            if($response){
                $this->response->showJsonResult('成功',1,$response);
            }
        }else{
            $this->response->showJsonResult('链接超时',0,array(),102);
        }

    }
}