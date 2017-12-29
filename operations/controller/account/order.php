<?php
class ControllerAccountOrder extends Controller {
    //使开锁状态改变
    public function effectOrderByBT(){
        $post = $this->request->post(array('order_id', 'lat', 'lng', 'trade_no', 'voltage'));
        if (!$post['order_id'] || !$post['lat'] || !$post['lng']) {
            $this->response->showErrorResult('参数错误');
        }

        $this->load->library('sys_model/open_lock', true);
        $this->load->library('sys_model/lock', true);
        $order_info = $this->sys_model_open_lock->getOrdersInfo(array('open_sn_id' => $post['order_id']));
        if (empty($order_info)) {
            $this->response->showErrorResult('不存在此订单');
        }

        $lock_info = $this->sys_model_lock->getLockInfo(array('lock_sn' => $order_info['lock_sn']));
        if (empty($lock_info)) {
            $this->response->showErrorResult('系统不存在此锁信息');
        }

        $lock_data = array(
            'system_time' => time(),
            'lat' => $post['lat'],
            'lng' => $post['lng'],
            'amap_lat' => $post['lat'],
            'amap_lng' => $post['lng'],
            'open_nums' => array('exp', 'open_nums+1'),
            'lock_status' => 1,
        );

        if ($this->voltage2Power($post['voltage'])) {
            $lock_data['battery'] = $this->voltage2Power($post['voltage']);
        }

        $this->sys_model_lock->updateLock(array('lock_id' => $lock_info['lock_id']), $lock_data);

        //更新单车使用状态
        $this->load->library('sys_model/bicycle', true);
        $this->sys_model_bicycle->updateBicycle(array('bicycle_id' => $order_info['bicycle_id']), array('is_using' => 1));
        $order_data = array(
            'open_state' => 1,
            'start_time' => time(),
            'start_lat' => $post['lat'],
            'start_lng' => $post['lng']
        );

        $order_data['trade_no'] = $post['trade_no'] ? $post['trade_no'] : 0;

        $update = $this->sys_model_open_lock->updateOpenStatus(array('open_sn_id' => $order_info['open_sn_id']), $order_data);

        $update ? $this->response->showSuccessResult(array('order_id' => $order_info['open_sn_id'])) : $this->response->showErrorResult();
    }

    //因为电池电压受多种影响
    private function voltage2Power($voltage) {
        if ($voltage >= 4.2) {
            return 100;
        } elseif ($voltage < 4.2 && $voltage >= 4.06) {
            return 100;
        } elseif ($voltage < 4.06 && $voltage >= 3.98) {
            return 95;
        } elseif ($voltage < 3.98 && $voltage >= 3.92) {
            return 90;
        } elseif ($voltage < 3.92 && $voltage >= 3.87) {
            return 85;
        } elseif ($voltage < 3.87 && $voltage >= 3.82) {
            return 70;
        } elseif ($voltage < 3.82 && $voltage >= 3.79) {
            return 60;
        } elseif ($voltage < 3.79 && $voltage >= 3.77) {
            return 50;
        } elseif ($voltage < 3.77 && $voltage >= 3.74) {
            return 45;
        } elseif ($voltage < 3.74 && $voltage >= 3.68) {
            return 40;
        } elseif ($voltage < 3.68 && $voltage >= 3.45) {
            return 30;
        } elseif ($voltage < 3.45 && $voltage >= 3) {
            return 10;
        } else {
            return 0;
        }
    }

    public function closeOrderByBT() {
        $post = $this->request->post(array('order_id', 'lat', 'lng', 'is_pre', 'finish_time'));
        if (!$post['order_id'] || !$post['lat'] || !$post['lng']) {
            $this->response->showErrorResult('参数错误');
        }

        $this->load->library('sys_model/open_lock', true);
        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/lock', true);

        $order_info = $this->sys_model_open_lock->getOrdersInfo(array('open_sn_id' => $post['order_id']));
        if (empty($order_info)) {
            $this->response->showErrorResult('参数有误');
        }

        if ($order_info['open_state'] == 2) {
            $this->response->showErrorResult('行程已结束，无需重复提交');
        }
        //-3冻结的订单暂不考虑处理
        if ($order_info['open_state'] != 1) {
            $this->response->showErrorResult('非进行中的订单，无需结束');
        }

        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo(array('bicycle_id' => $order_info['bicycle_id']), 'lock_sn,bicycle_id,bicycle_sn');
        if (empty($bicycle_info)) {
            $this->response->showErrorResult('不存在此编号的单车');
        }

        $lock_info = $this->sys_model_lock->getLockInfo(array('lock_sn' => $bicycle_info['lock_sn']));

        $update = false;

        $order_data = array(
            'end_time' => time(),
            'open_state' => 2,
            'end_lat' => $post['lat'],
            'end_lng' => $post['lng']
        );
        if ($post['is_pre'] == 1) {
            $order_data['open_state'] = 3;
        }

        //蓝牙锁(包括二合一锁)
        if (in_array($lock_info['lock_type'], array(2, 4, 5))) {
            $update = $this->sys_model_open_lock->updateOpenStatus(array('open_sn_id' => $order_info['open_sn_id']), $order_data);
        } else if ($lock_info['lock_type'] == 3) { //机械锁

        }

        //更新锁的信息
        $lock_data = array(
            'lock_status' => 0,
            'lat' => $post['lat'],
            'lng' => $post['lng'],
            'amap_lat' => $post['lat'],
            'amap_lng' => $post['lng'],
            'system_time' => time(),
            'device_time' => $post['finish_time'] ? $post['finish_time'] : time()
        );

        $bicycle_data = array(
            'is_using' => 0
        );

        $this->sys_model_bicycle->updateBicycle(array('bicycle_id' => $bicycle_info['bicycle_id']), $bicycle_data);
        $this->sys_model_lock->updateLock(array('lock_id' => $lock_info['lock_id']), $lock_data);

        $order_info = $this->sys_model_open_lock->getOrdersInfo(array('open_sn_id' => $post['order_id']));
        $update ? $this->response->showSuccessResult($order_info, '成功结束订单') : $this->response->showErrorResult('操作失败');
    }

    public function checkPreFinishedRide() {
        $post = $this->request->post(array('trade_no', 'phone', 'finish_time', 'lat', 'lng'));
        if (!$post['trade_no']) {
            $this->response->showErrorResult('trade no不能为空', 12301);
        }
        if (!$post['finish_time']) {
            $this->response->showErrorResult('finish time不能为空', 12302);
        }

        $this->load->library('sys_model/orders', true);
        $order_info = $this->sys_model_orders->getOrdersInfo(array('trade_no' => $post['trade_no']));
        if (!empty($order_info)) {
            if ($order_info['order_state'] == 1 || $order_info['order_state'] == 3) {
                $this->load->library('logic/orders', true);
                $update_data = array(
                    'order_id' => $order_info['order_id'],
                    'finish_time' => $post['finish_time'],
                    'lat' => $post['lat'],
                    'lng' => $post['lng']
                );
                $callback = $this->logic_orders->closeMCHOrder($update_data);
            }
        }

        $this->load->library('sys_model/open_lock', true);
        $order_info = $this->sys_model_open_lock->getOrdersInfo(array('trade_no' => $post['trade_no']));
        if (empty($order_info)) {
            $this->response->showErrorResult('不存在此订单');
        }
        if ($order_info['open_state'] == 2) {
            $this->response->showSuccessResult(array(), '订单已结束');
        }

        //订单未结束，仍在计费的情况
        if ($order_info['open_state'] == 1) {
            $this->response->showErrorResult('当前进行中的订单');
//            if ($order_info['user_id'] == $this->startup_user->userId()) $this->response->showErrorResult('当前进行中的订单');
//            $update_data = array(
//                'finish_time' => $post['finish_time'],
//                'order_id' => $order_info['order_id'],
//                'lat' => $post['lat'],
//                'lng' => $post['lng']
//            );
//            $callback = $this->logic_orders->closeMCHOrder($update_data);
        } elseif ($order_info['open_state'] == 3) {
            $update_data = array(
                'end_time' => $post['finish_time'],
                'open_state' => 2,
                'end_lat' => $post['lat'],
                'end_lng' => $post['lng']
            );
            #file_put_contents('trade_no_finish_time.txt', date('Y-m-d H:i:s') . json_encode($post) . "\n", FILE_APPEND);
            $this->sys_model_open_lock->updateOpenStatus(array('open_sn_id' => $order_info['open_sn_id']), $update_data);
            $callback = callback(true);
        } else {
            $callback = array('state' => false, '订单状态不对');
        }

        if ($callback['state']) {
            $this->response->showSuccessResult(array('order_info' => $order_info), '成功结束订单');
        } else {
            $this->response->showErrorResult($callback['msg']);
        }
    }
}