<?php

class ControllerStartupOrder extends Controller
{
    const BLUE_TOOTH_LOCK   = 2; //蓝牙锁
    const MACHINE_LOCK      = 3; //机械锁
    const GPRS_BLUE_LOCK    = 4; //二合一锁
    const BLUE_TOOTH_E_STRONGER = 5; //自己的纯蓝牙锁

    public function index()
    {
        $route = isset($this->request->get['route']) ? $this->request->get['route'] : '';
        $route = strtolower($route);
        // 关闭预约接口
        if ($route == 'account/order/book') {
            $this->response->showErrorResult('预约功能已经关闭！');
        }
        //针对开锁，和预约的情况
        $in = array(
            'operator/operator/openlock',
            'account/order/book'
        );

        if($route=='operator/operator/openlock' && isset($this->request->post['device_id']) && (
                $this->request->post['device_id']=='063072632892' || $this->request->post['device_id']=='063072619956' || $this->request->post['device_id']=='063072649805')){
            file_put_contents('/data/wwwroot/default/bike/locktest.log', date('Y-m-d H:i:s') . 'openLock:' . print_r($this->request->post, true) , FILE_APPEND);
        }

        //创建订单
        if (in_array($route, $in)) {
            if (!isset($this->request->post['device_id']) || empty($this->request->post['device_id'])) {
                $this->response->showErrorResult('锁编码不能为空', 126);
            }

            $this->load->library('sys_model/lock');
            $this->load->library('sys_model/bicycle');
            $this->load->library('logic/orders', true);

            $device_id = $this->request->post['device_id'];
            $lock_info = $this->sys_model_lock->getLockInfo(array('lock_sn' => $device_id));
            $bike_info = $this->sys_model_bicycle->getBicycleInfo(array('lock_sn' => $device_id));
            if (empty($lock_info)) {
                $this->response->showErrorResult('不存在此锁', 127);
            }

            //离线直接提示
            if ($lock_info['lock_type'] == 1 && time() - $lock_info['system_time'] > 3600 * 48) {
                //$this->response->showErrorResult('锁故障需要维护，请扫描其他单车，给您带来的不便请见谅');
            }

            //判断版本
            if ($lock_info['lock_type'] == self::BLUE_TOOTH_LOCK || $lock_info['lock_type'] == self::BLUE_TOOTH_E_STRONGER) {
                if ($this->request->get_request_header('client') == 'wechat') {
                    $this->response->showSuccessResult(array('lock_type' => $lock_info['lock_type']), '微信目前不支持开此锁');
                }

                $gets = $this->request->get(array('fromApi', 'version'));
                if ($gets['fromApi'] == 'android' && $gets['version'] < 34) {
                    $this->response->showErrorResult('请更新APP版本，该版本不支持开此锁', 1024);
                } elseif ($gets['fromApi'] == 'ios' && $gets['version'] < 60) {
                    $this->response->showErrorResult('请到AppStore更新版本，该版本不支持开此锁', 1024);
                }
            }
            
            //开锁的距离限制
            if (strlen($this->request->post['bicycle_sn']) == 6) {
                $lat = $this->request->post['lat'];
                $lng = $this->request->post['lng'];
                $this->load->library('tool/distance');
                $distance = $this->tool_distance->getDistance($lng, $lock_info['lng'], $lat, $lock_info['lat']);
                if ($distance > $this->config->get('config_open_lock_distance')) {
                    $this->response->showErrorResult('超过开锁距离，请在' . ($this->config->get('config_open_lock_distance') + 0) * 1000 . '米内开锁', 521);
                }
            }

            if ($lock_info['lock_type'] == 1 && abs($lock_info['battery']) <= 15) {
                $this->response->showErrorResult('锁电量不足，请稍后再试', 128);
            }

            if ($lock_info['lock_status'] == 1 || $lock_info['lock_status'] == 2) {
                //$this->response->showErrorResult('开锁状态中扫码无效');
            }
            
            $user_id = $this->startup_user->userId();
            $user_info = $this->startup_user->getUserInfo();
            $t = time() - BOOK_EFFECT_TIME;
            $count = $this->db->table('orders')->where(array('user_id' => $user_id, 'order_state' => 3))->count('order_id');
            if ($count > 1) $this->response->showErrorResult('不能超过两个待计费');            

            //判断是否有进行中的订单或者预约的订单，用户是否有进行中或者预约中的订单
            $where = "(lock_sn='$device_id' AND order_state=1)";
            $where .= " OR (lock_sn='$device_id' AND order_state=0 AND add_time > {$t})";
            $where .= " OR (user_id='$user_id' AND order_state=1)";
            $where .= " OR (user_id='$user_id' AND order_state=0 AND add_time > {$t})";
            $where .= " OR (user_id='$user_id' AND order_state=-2 AND add_time > " . (time() - $this->config->get('config_cancel_under_riding_time') + 0) . ")";

            $exits = $this->logic_orders->existsOrder($where);

            if ($exits) {
                if ($exits['user_id'] != $user_id) {
                    if ($exits['order_state'] == 1) {
						if ($exits['lock_type'] == 1) {
							$this->response->showErrorResult('此单车已被他人使用', 129);
						} else {
							$this->load->library('sys_model/orders', true);
							$this->sys_model_orders->updateOrders(array('order_id' => $exits['order_id']), array('order_state' => 3, 'before_state' => 3));
                            $exits = array();
						}                      
                    } else {
                        $this->response->showErrorResult('此单车已人被预约', 132);
                    }
                } else {
                    if ($exits['order_state'] == 1) {
                        $this->response->showErrorResult('您还在骑行中', 130);
                    } else if ($exits['order_state'] == 0 && $route == 'operator/operator/openlock') {
                        $this->load->library('sys_model/orders', true);
                        //更新订单为等待开锁
                        $this->sys_model_orders->updateOrders(array('order_id' => $exits['order_id']), array('order_state' => '-2', 'add_time' => time()));
                        $obj = new stdClass();
                        $obj->result = true;
                        $obj->order_sn = $exits['order_sn'];
                        $this->registry->set('order_result', $obj);

                        $this->config->set('order_add_time', $exits['add_time']);
                    } else if ($exits['order_state'] == 0 && $route == 'account/order/book') {
                        $this->response->showErrorResult('您已经预约了此单车，无需再预约', 133);
                    } elseif ($exits['order_state'] == -2) {
                        $lock_info = $this->sys_model_lock->getLockInfo(array('lock_sn' => $exits['lock_sn']));
                        $data = array(
                            'lock_type' => intval($lock_info['lock_type']),
                            'mac_address' => $lock_info['mac_address'],
                            'encrypt_key' => $lock_info['encrypt_key'],
                            'password' => $lock_info['password'],
                        );

                        $result = array(
                            'order_id' => intval($exits['order_id']),
                            'order_sn' => $exits['order_sn'],
                            'add_time' => intval($exits['add_time']),
                            'bicycle_sn' => $exits['bicycle_sn'],
                            'lock_sn' => $exits['lock_sn'],
                            'is_limit_free' => $exits['is_limit_free'],
                            'is_month_card' => $exits['is_month_card'],
                            'is_scenic' => $bike_info['is_scenic'],
                            'keep_time' => (!empty($data['keep_time'])) ? $data['keep_time'] : BOOK_EFFECT_TIME
                        );

                        $data = array_merge($data, $result);

                        $this->response->showSuccessResult($data, '开锁中请稍后...');
                    }
                }
            }

            $data = array(
                'user_id' => $user_id,
                'user_name' => $user_info['mobile'],
                'lock_sn' => $device_id,
                'bicycle_id' => '0',
                'bicycle_sn' => '0',
                'keep_time' => BOOK_EFFECT_TIME,
		        'lock_type' => $lock_info['lock_type'],
            );

            if ($user_info['card_expired_time'] >= time()) {
                $data['is_month_card'] = 1;
            }

            if ($bike_info['is_scenic'] && $this->request->get_request_header('sing') == 'BBC') {
                $data['is_scenic'] = 1;
                $data['recharge_sn'] = $this->request->post['recharge_sn'];
		$this->db->table('temp_recharge')->where(array('recharge_sn' => $data['recharge_sn']))->update(array('used' => 1));
            }

            //订单的客户端信息
            $client_version = isset($this->request->get['version']) ? $this->request->get['version'] : 0;
            $from_client = isset($this->request->get['fromApi']) ? $this->request->get['fromApi'] : ($this->request->get_request_header('client') && $this->request->get_request_header('client') == 'miniapp' ? 'mini_app' : 'wechat');
            $data['from_client'] = $from_client;
            $data['client_version'] = $client_version;

            //机械锁扫码
            if ($lock_info['lock_type'] == self::MACHINE_LOCK) {
                $data['order_state'] = 1;
            }

            if ($route == 'account/order/book') {
                $data['order_state'] = 0;
                $result = $this->logic_orders->addOrders($data);
                $lock_temp = $this->db->table('mac_temp')->where(array('lock_sn' => $device_id))->field('uuid')->find();
                $result['data']['uuid'] = isset($lock_temp['uuid']) ? $lock_temp['uuid'] : '';
                $result['state'] ? $this->response->showSuccessResult($result['data'], '预约成功') : $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
            }
            elseif ($route == 'operator/operator/openlock') {
                $data['order_state'] = -2;
                if (empty($exits)) {
                    $result = $this->logic_orders->addOrders($data);
                    $result['data']['is_month_card'] = isset($data['is_month_card']) ? '1' : '0';
                    $lock_temp = $this->db->table('mac_temp')->where(array('lock_sn' => $device_id))->field('uuid')->find();
                    $result['data']['uuid'] = isset($lock_temp['uuid']) ? $lock_temp['uuid'] : '';
                }
                else {
                    $result = array(
                        'data' => array(
                            'order_sn' => $exits['order_sn'],
                            'add_time' => $exits['add_time'],
                            'is_limit_free' => $exits['is_limit_free'],
                            'is_month_card' => $exits['is_month_card'],
                            'is_scenic' => $bike_info['is_scenic'],
                        ),
                        'state' => true
                    );
                    $lock_temp = $this->db->table('mac_temp')->where(array('lock_sn' => $device_id))->field('uuid')->find();
                    $result['data']['uuid'] = isset($lock_temp['uuid']) ? $lock_temp['uuid'] : '';
                }

                if ($result['state'] == true) {
                    $obj = new stdClass();
                    $obj->result = true;
                    $obj->order_sn = $result['data']['order_sn'];
                    $obj->lock_type = intval($lock_info['lock_type']);
                    $obj->is_limit_free = $result['data']['is_limit_free'];
                    $obj->is_month_card = $result['data']['is_month_card'];
                    $obj->is_scenic = $bike_info['is_scenic'];

                    $data = array(
                        'lock_type' => intval($lock_info['lock_type']),
                        'mac_address' => $lock_info['mac_address'],
                        'encrypt_key' => $lock_info['encrypt_key'],
                        'password' => $lock_info['password'],
                        'is_scenic' => $bike_info['is_scenic']
                    );

                    //蓝牙锁
                    if (in_array($lock_info['lock_type'], array(self::BLUE_TOOTH_LOCK, self::GPRS_BLUE_LOCK, self::BLUE_TOOTH_E_STRONGER))) {
                        if (!isset($this->request->get['fromApi']) && $this->request->get_request_header('client') == 'wechat') {
                            $this->response->showErrorResult($this->language->get('error_bluetooth_version'), 1024);
                        }
                        else if (isset($this->request->get['fromApi']) && $this->request->get['fromApi'] == 'android' && $this->request->get['version'] > 31) {
                            $data = array_merge($data, $result['data']);
                            $this->response->showSuccessResult($data);
                        } elseif ($lock_info['lock_type'] == 2 || $lock_info['lock_type'] == self::BLUE_TOOTH_E_STRONGER) {
                            $data = array_merge($data, $result['data']);
                            $this->response->showSuccessResult($data);
                        }
                    } elseif (in_array($lock_info['lock_type'], array(1))) {
                        $obj->mac_address = $data['mac_address'];
                        $obj->encrypt_key = $data['encrypt_key'];
                        $obj->password    = $data['password'];
                    }

                    //机械锁
                    if ($lock_info['lock_type'] == self::MACHINE_LOCK) {
                        $data = array_merge($data, $result['data']);
                        $this->response->showSuccessResult($data);
                    }

                    $this->config->set('order_add_time', $result['data']['add_time']);
                    $this->registry->set('order_result', $obj);		  
                } else {
                    $this->response->showErrorResult($this->language->get($result['msg']), 4);
                }
            }
        }
    }

    private function _dealBlueTooth($device_info) {
        $this->_dealCommon($device_info);
    }

    private function _dealMachine($device_info) {
        $this->_dealCommon($device_info);
    }

    private function _dealCommon($device_info) {
        $lat = isset($this->request->post['lat']) ? $this->request->post['lat'] : '';
        $lng = isset($this->request->post['lng']) ? $this->request->post['lng'] : '';

        $user_id = $this->startup_user->userId();
        $user_info = $this->startup_user->getUserInfo();
        $order_info = $this->logic_orders->existsOrder(array('order_state' => 1, 'lock_sn' => $device_info['lock_sn']));
        if ($order_info) {
            if ($order_info['user_id'] == $user_id) {
                $this->response->showErrorResult('您的订单尚未结束');
            } else {
                $this->response->showErrorResult('此单车已被他人使用');
            }
        }

        $data = array(
            'user_id' => $user_id,
            'user_name' => $user_info['mobile'],
            'lock_sn' => $device_info['lock_sn'],
            'bicycle_id' => $this->request->post['bicycle_id'],
            'bicycle_sn' => $this->request->post['bicycle_sn'],
            'keep_time' => '0', //蓝牙和机械锁预约时间为0
            'region_id' => $device_info['region_id'],
            'region_name' => $device_info['region_name'],
            'start_time' => time(),
            'start_lng' => $lng,
            'start_lat' => $lat,
            'lng' => $lng,
            'lat' => $lat,
            'order_state' => $device_info['lock_type'] == self::MACHINE_LOCK ? 1 : 0,
        );
        $order_id = $this->logic_orders->addOrder($data);
        //创建订单成功，更改开锁状态为1（已开锁）
        if ($order_id) {
            $data['lock_status'] = 1;
            $this->sys_model_lock->updateLock(array('lock_sn' => $device_info['lock_sn']), $data);
        }
        //$this->response->showSuccessResult(array('order_id' => $order_id), '操作成功');
    }
}
