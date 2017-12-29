<?php
/**
 * 东莞市亦强软件有限公司
 * Author: 罗剑波
 * Time: 2017/3/4 13:40
 */
class ControllerSystemTest extends Controller {

    public function index() {
//        $device_id = '063072624246'; //西班牙客户
        $device_id = '063072619956'; //我桌面上的锁
//        $device_id = '063072656941';

        $this->load->library('instructions/instructions', true);
//        $re = $this->instructions_instructions->selectLocks($device_id);
//        var_dump($re);
        $this->instructions_instructions->setGapTime($device_id, 60);
        $this->instructions_instructions->setGapTime2($device_id, 600);
    }

}