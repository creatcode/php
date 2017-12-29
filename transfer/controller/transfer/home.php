<?php

/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2016/12/8
 * Time: 13:19
 */
class ControllerTransferHome extends Controller
{
    public function receiptData()
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $post = file_get_contents("php://input");
#            file_put_contents('/data/wwwroot/default/bike/transfer/controller/transfer/receive.log', date('Y-m-d H:i:s ') . $post . "\n", FILE_APPEND);
            $post = json_decode($post, true);

            if (isset($post['time'])) {
                $post['time'] = strtotime($post['time']);
            }

            $data = array(
                'cmd' => strtolower($post['cmd']),
                'device_id' => $post['deviceid'],
                'battery' => $post['battery'],
                'location_type' => $post['bike'],
                'lock_status' => $post['lockstatus'],
                'lng' => $post['lng'],
                'lat' => $post['lat'],
                'gx' => $post['gx'],
                'gy' => $post['gy'],
                'gz' => $post['gz'],
                'time' => $post['time'],
                'serialnum' => $post['serialnum'],
            );

            $lock_data = array(
                'battery' => $data['battery'],
                'lng' => $data['lng'],
                'lat' => $data['lat'],
                'gx' => $data['gx'],
                'gy' => $data['gy'],
                'gz' => $data['gz'],
                'lock_status' => $data['lock_status'],
                'system_time' => time(),
                'device_time' => $data['time'],
                'serialnum' => $post['serialnum'] < 64 ? $post['serialnum'] : 1,
            );

            if ($post['cmd'] == 'open') {
                $lock_data['open_nums'] = array('exp', 'open_nums+1');
            }

            $line_data = array(
                'lat' => $data['lat'],
                'lng' => $data['lng'],
            );

            $this->load->library('logic/orders', true);
            $this->load->library('sys_model/lock', true);
            $this->load->library('sys_model/orders', true);
            $this->load->library('sys_model/bicycle', true);
            $this->load->library('sys_model/location_records', true);

            $price_unit = $this->config->get('config_price_unit') ? $this->config->get('config_price_unit') : 0;
            $time_recharge_unit = $this->config->get('config_time_charge_unit') ? $this->config->get('config_time_charge_unit') : 30 * 60;
            define('PRICE_UNIT', $price_unit); //价格单元
            define('TIME_CHARGE_UNIT', $time_recharge_unit);//计费单位

            $order_data = array(
                'device_time' => $post['time'],
                'close_type' => 'gprs',
                'data_type' => $post['cmd'],
            );

            $lock_info = $this->sys_model_lock->getLockInfo(array('lock_sn' => $data['device_id']));

            switch ($post['cmd']) {
                case 'open' :
                    if ($data['lock_status'] == 1) { //开锁指令执行之后开锁成功
                        // 利用开锁之后锁立刻上传的定位信息来改变订单状态 先准备数据,预约15分钟内，否则会把15分钟之外的订单也生效
                        $order_info = $this->sys_model_orders->getOrdersList(array('order_state' => '-2', 'lock_sn' => $data['device_id'], 'add_time' => array('egt', time() - BOOK_EFFECT_TIME)), '`order_id` DESC', '1');
                        if (empty($order_info)) {
                            break;
                        }
                        $data['result'] = 'ok';
                        $data['cmd'] = 'open';
                        $data['serialnum'] = $order_info[0]['add_time'];

                        $result = $this->logic_orders->effectOrders($data);
                        if ($result['state'] == true) {
                            $arr = $this->response->_error['success'];
                            $arr['data'] = $result['data'];
                            $this->load->library('JPush/JPush', true);
                            $send_result = $this->JPush_JPush->message($result['data']['user_id'], json_encode($arr));
                        } else {
                            file_put_contents('open_order_error.log', json_encode($result) . "\n", 8);
                        }
                    } else { // 开锁指令之后开锁失败

                    }
                    break;
                case 'close':
                    $order_info = $this->sys_model_orders->getOrdersList("lock_sn='{$data['device_id']}' AND (order_state=1 OR order_state=-3)");
                    if (empty($order_info)) {
                        break;
                    }

                    $order_data['before_state'] = $order_info[0]['order_state'];

                    if ($order_info[0]['order_state'] == -3) {
                        $this->sys_model_orders->updateOrders(array('order_id' => $order_info[0]['order_id']), $order_data);
                        $this->logic_orders->finishOrders($data);
                        break;//无需推送
                    } else {
                        if ($post['time'] < $lock_info['device_time']) {
                            break;
                        }

                        if ($order_info[0]['add_time'] > $post['time']) {
                            break;
                        } else {
                            $this->sys_model_orders->updateOrders(array('order_id' => $order_info[0]['order_id']), $order_data);
                            $result = $this->logic_orders->finishOrders($data);
                        }
                    }

                    if ($result['state'] == true) {
                        $arr = $this->response->_error['success'];
                        $arr['data'] = $result['data'];
                        $this->load->library('JPush/JPush', true);
                        $this->JPush_JPush->message($result['data']['user_id'], json_encode($arr));
                    }
                    break;
                case 'normal':
                    $order_info = $this->sys_model_orders->getOrdersList("lock_sn='{$data['device_id']}' AND (order_state=1 OR order_state=-3)");
                    if (empty($order_info)) {
                        break;
                    }

                    $order_data['before_state'] = $order_info[0]['order_state'];

                    if ($order_info[0]['order_state'] == -3 && $data['lock_status'] == 0) {
                        $this->sys_model_orders->updateOrders(array('order_id' => $order_info[0]['order_id']), $order_data);
                        $this->logic_orders->finishOrders($data);
                    } elseif ($order_info[0]['order_state'] == 1 && $data['lock_status'] == 0) {
                        if ($post['time'] < $lock_info['device_time']) {
                            break;
                        }
                        $line_data['order_id'] = $order_info[0]['order_id'];
                        $line_data['user_id'] = $order_info[0]['user_id'];
                        $line_data['add_time'] = time();
                        $line_data['status'] = 1;
                        $this->logic_orders->recordLine($line_data);

                        $this->sys_model_orders->updateOrders(array('order_id' => $order_info[0]['order_id']), $order_data);
                        $result = $this->logic_orders->finishOrders($data);
                        if ($result['state'] == true) {
                            $arr = $this->response->_error['success'];
                            $arr['data'] = $result['data'];
                            $this->load->library('JPush/JPush', true);
                            $this->JPush_JPush->message($order_info[0]['user_id'], json_encode($arr));
                        }
                    }
                    break;
            }

            //更新锁的相关信息

            if ($lock_info) {
                //GPS信号太弱，丢弃
                if (($lock_data['lock_status'] == 0 && $lock_info['gz'] % 100 > 0 && $lock_data['gz'] % 100 == 0)) {
                    unset($lock_data['lat']);
                    unset($lock_data['lng']);
                } elseif (($lock_data['lock_status'] == 0 && $lock_data['gz'] % 100 == 0 && $lock_info['from_platform'] == 1)) {
                    $lock_data['lat'] = $lock_info['amap_lat'];
                    $lock_data['lng'] = $lock_info['amap_lng'];
                }
                $this->sys_model_lock->updateLock(array('lock_sn' => $data['device_id']), $lock_data);
            }
            //
            if ($data['lock_status'] == 0) {
                $this->load->library('sys_model/open_lock', true);
                $open_lock = $this->sys_model_open_lock->getOrdersInfo(array('lock_sn' => $data['device_id'], 'open_state' => 1));
                if ($open_lock) {
                    $this->sys_model_open_lock->updateOpenStatus(array('lock_sn' => $data['device_id'], 'open_state' => 1), array('open_state' => 2));
                }
            }


            if ($lock_info) {
                library('queue/queue_client');
                library('queue/queue_db');
                library('queue/queue_server');
                if ($this->config->get('config_start_queue')) {
                    $result = \Queue\Queue_Client::push('addLocation', $data, $this->registry);
                } else {
                    $result = $this->sys_model_location_records->addLogs($data);
                }

                if (!$result) {
                    $this->response->showErrorResult('数据写入失败');
                }
                $this->response->showSuccessResult();
            }
            $this->response->showErrorResult('不存在此锁');
        } else {
            $this->response->showErrorResult('Request Method Error');
        }
    }
}
