<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/12/16
 * Time: 15:32
 */
class ControllerTransferProcess extends Controller{

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->library('sys_model/orders');
        $this->load->library('logic/order');
        $this->load->library('sys_model/lock');
    }


    /**
     * 数据
     * array (
            'cmd' => 'opened',
            'lock_id' => '80000c52',
            'error' => 0,
            'msg' => 'OK',
            'data' =>array (),
            'timestamp' => 1513419299,
            'notify_serial' => '1513419299851581001',
            'notify_time' => '2017-12-16 18:14:59',
            'sign' => '96D0A99538F274E103D48C40DCB40093',
            )
     * 程序发送完开锁指令后 锁服返回开锁状态 表示锁开 用户已经开始骑行
     */
    public function opened(){
        file_put_contents('../system/storage/logs/notify.post.txt',"---opened---\n",FILE_APPEND);
        $data = $_POST;
        //关键参数
        if ($data['error'] == 0) {
            $data['lock_sn'] = $data['lock_id'];
            $this->logic_order->open($data);
        } else { // 开锁指令之后开锁失败

        }
        

    }

    /**
     * 'cmd' => 'closed',
        'lock_id' => '80000c52',
        'error' => 0,
        'msg' => 'OK',
        'data' =>
            array (
                'battery_42' => 91,
                'battery_36' => 0,
                'location' => 0,
                'status_battery_42' => 0,
                'status_battery_36' => 0,
                'status_bike' => 0,
                'status_bike_lock_open' => 1,
                'status_battery_lock_open' => 1,
                'status_assistance' => 0,
                'status_battery_lock' => 0,
                'status_bike_lock' => 0,
                'status_gps' => 0,
                'status_gprs' => 0,
                'status_controller' => 0,
                'lat' => 0,
                'lng' => 0,
                'altitude' => 0,
                'speed' => 0,
            ),
        'timestamp' => 1513676247,
        'notify_serial' => '1513676247941201001',
        'notify_time' => '2017-12-19 17:37:27',
        'sign' => '027EC9A930C2EB2C0226843C3FCD3220',

     * 结束订单
     */
    public function closed(){
        file_put_contents('../system/storage/logs/notify.post.txt',"---closed---\n",FILE_APPEND);
        $data = $_POST;
        $data['lock_sn'] = $_POST['lock_id'];
        $data['lat'] = $data['data']['lat'];
        $data['lng'] = $data['data']['lng'];

        $this->logic_order->close($data);
    }


    /**
     *array (
        'cmd' => 'heartbeat',
        'lock_id' => '80000c52',
        'error' => 0,
        'msg' => 'OK',
        'data' =>
            array (
                'battery_42' => 90,                    4.2v电压值
                'battery_36' => 0,                     36v电压值
                'location' => 0,                       1 定位有效 0 定位无效
                'status_battery_42' => 0,              4.2v电量状态
                'status_battery_36' => 0,              36v电量状态
                'status_bike' => 0,                    车辆状态
                'status_bike_lock_open' => 1,          车辆锁开启状态  0:开启 1:关闭
                'status_battery_lock_open' => 1,       电池锁开启状态  0：开启 1：关闭
                'status_assistance' => 0,              助力状态
                'status_battery_lock' => 0,            电池锁状态
                'status_bike_lock' => 0,               车辆锁状态
                'status_gps' => 0,                     gps状态
                'status_gprs' => 0,                    gprs状态
                'status_controller' => 0,              车辆控制器状态
                'lat' => 0,
                'lng' => 0,
                'altitude' => 0,                       海拔
                'speed' => 0,                          速度
            ),
        'timestamp' => 1513675356,
        'notify_serial' => '1513675356007581001',
        'notify_time' => '2017-12-19 17:22:36',
        'sign' => 'B887D99B60A10EC21563DD9D9644605B',
     *
     * heartbeat 不一定是正在骑行 关锁后也会有心跳数据
     */
    public function heartbeat(){
        file_put_contents('../system/storage/logs/notify.post.txt',"---heartbeat---\n",FILE_APPEND);
        $data = $_POST;
        $data['lock_sn'] = $_POST['lock_id'];
        $data['lat'] = $data['data']['lat'];
        $data['lng'] = $data['data']['lng'];
        $data['status_bike_lock_open'] = $data['data']['status_bike_lock_open'];

        
        //更新锁位置
        $this->sys_model_lock->updateLock(['lock_sn'=>$_POST['lock_id']], ['lng'=>$data['data']['lng'],'lat'=>$data['data']['lat']]);
        
        //判断当前锁的状态 如果锁是开着的
        //锁关闭
        if($data['status_bike_lock_open'] == 1){
            //更新锁的状态 比如 电量等等  主要用于报警

        }else{
            //锁开着 更新骑行状态
            $this->logic_order->riding($data);
        }

    }

}