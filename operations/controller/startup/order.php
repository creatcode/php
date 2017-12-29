<?php
class ControllerStartupOrder extends Controller {
    public function index() {
        $route = isset($this->request->get['route']) ? $this->request->get['route'] : '';
        $route = strtolower($route);
        //针对开锁，和预约的情况
        $in = array(
            'operator/operator/openlock',
            'account/order/book'
        );
        //创建订单
        if (in_array($route, $in)) {
            if (!isset($this->request->post['device_id']) || empty($this->request->post['device_id'])) {
                $this->response->showErrorResult('锁编码不能为空', 126);
            }

            $this->load->library('sys_model/lock');

            $device_id = $this->request->post['device_id'];
            $lock_info = $this->sys_model_lock->getLockInfo(array('lock_sn' => $device_id));
            if (empty($lock_info)) {
                $this->response->showErrorResult('不存在此锁', 127);
            }

            if ($lock_info['battery'] <= 15) {
                $this->response->showErrorResult('锁电量不足，请稍后再试', 128);
            }

            if ($lock_info['lock_status'] == 1 || $lock_info['lock_status'] == 2) {
                //$this->response->showErrorResult('开锁状态中扫码无效');
            }
            if ($lock_info['lock_status'] == 0 && $lock_info['gx'] < 500) {
                //$this->response->showErrorResult('锁电压过低');
            }

            $user_id = $this->startup_user->userId();
            $user_info = $this->startup_user->getUserInfo();

            $this->load->library('logic/orders', true);

            //判断是否有进行中的订单
            $where = array('lock_sn' => $device_id, 'order_state' => 1);
            $exits = $this->logic_orders->existsOrder($where);
            if ($exits) {
                if ($exits['user_id'] != $user_id) {
                    $this->response->showErrorResult('此单车已被他人使用，请预约其他单车', 129);
                } else {
                    $this->response->showErrorResult('您还在骑行中', 130);
                }
            }

            $where = array(
                'user_id' => $user_id,
                'add_time' => array('EGT', time() - BOOK_EFFECT_TIME),
                'order_state' => '0'
            );

            //是否存在预约期内的订单
            $exits = $this->logic_orders->existsOrder($where);

            if ($exits) {
                if ($exits['lock_sn'] != $device_id) {
                    if ($route == 'operator/operator/openlock') {
                        $this->response->showErrorResult('您已经预约了其他单车，开锁失败', 131);
                    } elseif ($route == 'account/order/book') {
                        $this->response->showErrorResult('您已经预约了其他单车，不能再预约此单车', 132);
                    }
                } else {
                    if ($route == 'operator/operator/openlock') {
                        $obj = new stdClass();
                        $obj->result = true;
                        $obj->order_sn = $exits['order_sn'];
                        $this->registry->set('order_result', $obj);

                        $this->config->set('order_add_time', $exits['add_time']);
                    } elseif ($route == 'account/order/book') {
                        $this->response->showErrorResult('您已经预约了此单车，无需再预约',133);
                    }
                }
            } else {
                $data = array(
                    'user_id' => $user_id,
                    'user_name' => $user_info['mobile'],
                    'lock_sn' => $device_id,
                    'bicycle_id' => '0',
                    'bicycle_sn' => '0',
                    'keep_time' => BOOK_EFFECT_TIME
                );

                $where = array(
                    'lock_sn' => $device_id,
                    'add_time' => array('EGT', time() - BOOK_EFFECT_TIME),
                    'order_state' => '0'
                );
                $exits = $this->logic_orders->existsOrder($where);
                if ($exits) {
                    $this->response->showErrorResult('此单车已被他人预约', 134);
                }

                if ($route == 'account/order/book') {
                    $result = $this->logic_orders->addOrders($data);
                    $result['state'] ? $this->response->showSuccessResult($result['data'], '预约成功') : $this->response->showErrorResult('数据库操作失败',4);
                } elseif ($route == 'operator/operator/openlock') {
                    $result = $this->logic_orders->addOrders($data);
                    $obj = new stdClass();
                    $obj->result = true;
                    $obj->order_sn = $result['data']['order_sn'];
                    if ($result['state'] == true) {
                        $this->config->set('order_add_time', $result['data']['add_time']);
                        $this->registry->set('order_result', $obj);
                    } else {
                        $this->response->showErrorResult('数据库操作失败',4);
                    }
                }
            }

        }
    }
}