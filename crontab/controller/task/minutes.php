<?php
class ControllerTaskMinutes extends Controller {
    public function index() {
//        $this->_order_timeout_cancel();
        $this->cancel_under_riding_order_timeout();
        $this->_dealThreeDaysOrders();
        //$this->_changeOrderState1To3();
        //$this->_autoRefundRecharge();
    }

    /**
     * 预约超过15（变量）分钟，系统自动取消
     */
    private function _order_timeout_cancel() {
        $_break = false;
        $this->load->library('sys_model/orders');
        $condition = array();
        $condition['order_state'] = '0';
        $condition['add_time'] = array('lt', time() - BOOK_EFFECT_TIME);
        //分批，每批处理100个订单，最多处理5W个订单
        for ($i = 0; $i < 500; $i++) {
            if ($_break) break;
            $order_list = $this->sys_model_orders->getOrdersList($condition, '', 100);
            if (empty($order_list)) break;
            foreach ($order_list as $order_info) {
                $update = $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), array('order_state' => '-1'));
                if (!$update) {
                    //更新失败写入日志
                }
            }
        }
    }

    /**
     * 15分钟后取消等待开锁订单
     */
    private function cancel_under_riding_order_timeout() {
        $_break = false;
        $this->load->library('sys_model/orders');
        $condition = array();
        $condition['order_state'] = -2;
        $condition['add_time'] = array('lt', time() - $this->config->get('config_cancel_under_riding_time') + 0);
        //分批处理，每100个订单，最多5w个订单
        $count = 0;
        for ($i = 0; $i < 500; $i++) {
            if ($_break) break;
            $order_list = $this->sys_model_orders->getOrdersList($condition, 'add_time ASC', 100);
            $count += count($order_list);
            if (empty($order_list)) break;
            foreach ($order_list as $order_info) {              
                $order_data = array(
                    'order_state' => -1,
                    'end_time' => time(),
                    'recharge_sn' => '0',
                );

                if ($order_info['recharge_sn']) {
                    $this->db->table('temp_recharge')->where(array('recharge_sn' => $order_info['recharge_sn']))->update(array('used' => 0));
                }

                $update = $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), $order_data);
            }
        }
        #file_put_contents('crontab_test.log', date('Y-m-d H:i:s') . ' count:' . $count . ' cancel_under_riding_order' . "\n" , 8);
    }

    /**
     * 更新三天之前的订单，每次只执行一条，每分钟执行一次
     */
    private function _dealThreeDaysOrders() {
        $_break = false;
        $this->load->library('sys_model/orders', true);
        $this->load->library('logic/orders', true);
//        $where = array(
//            'order_state' => 3,
//            'start_time' => array('lt', time() - 3 * 24 * 3600)
//        );

        $where = "(order_state=3 OR order_state=1) AND start_time <" . (time() - 1 * 24 * 3600);

        $str = '';
        //每次只执行1条，1分钟执行
        for ($i = 0; $i < 1; $i++) {
            if ($_break) break;
            $order_list = $this->sys_model_orders->getOrdersList($where, 'order_id ASC', 50);
//            print_r($this->db->getLastSql());exit;
            if (empty($order_list)) break;
            foreach ($order_list as $order_info) {
                $order_line = $this->db->table('orders_line')->where(array('order_id' => $order_info['order_id']))->order('add_time DESC')->limit(1)->find();
                if ($order_info['device_time'] == 0) {
                    $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), array('device_time' => $order_line['add_time']));
                }
                $data = array(
                    'order_id' => $order_info['order_id'],
                    'is_cron_close' => 1,
                    'finish_time' => $order_info['device_time'] ? $order_info['device_time'] : $order_line['add_time'],
                    'lat' => isset($order_line['lat']) ? $order_line['lat'] : '',
                    'lng' => isset($order_line['lng']) ? $order_line['lng'] : '',
                );
                $result = $this->logic_orders->closeOrder($data);
                $str .= date('Y-m-d H:i:s')  . json_encode($result) . "\n";
            }
        }
        if ($str) {
            file_put_contents('crontab_order.log', $str, 8);
        }
    }

    private function _dealThreeHoursOrders() {
        $this->load->library('sys_model/orders', true);
        $this->load->library('logic/orders', true);
        $where = "(order_state=3 OR order_state=1) AND start_time < " . (time() - 3 * 24 * 3600);
        $str = '';
        for ($i = 0; $i < 1; $i++) {
            $order_list = $this->sys_model_orders->getOrdersList($where, 'order_id ASC', 10);
            if (empty($order_list)) break;
            foreach ($order_list as $order) {
                $data = array(
                    'order_id' => $order['order_id'],
                    'finish_time' => time(),
                );
                $result = $this->logic_orders->closeOrder($data);
            }
        }
    }

    private function _changeOrderState1To3() {
        $this->load->library('sys_model/orders', true);
        $where = "order_state=1 AND add_time < " . (time() - 1 * 10 * 3600);
        for ($i = 0; $i < 5; $i++) {
            $order_list = $this->sys_model_orders->getOrdersList($where, 'order_id', 100);
            if (empty($order_list)) break;
            foreach ($order_list as $order_info) {
				if (!in_array($order_info['lock_type'], array(2,4,5))) {
					//非蓝牙锁continue掉
					continue;
				}
                $order_state = array('order_state' => 3);
                $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), $order_state);
            }
        }
    }

    /**
     * 优惠券过期处理
     */
    private function _coupon_timeout_expire() {

    }

    private function _autoRefundRecharge() {
        //SELECT rrr.*,rdr.* FROM `rich_rides_recharge` rrr LEFT JOIN rich_deposit_recharge rdr ON rrr.recharge_sn=rdr.pdr_sn LEFT JOIN rich_orders ror ON rrr.order_sn=ror.order_sn WHERE rrr.state=0 AND ror.order_state=2
        $where = array('state' => 0, 'add_time' => array('gt', time() - 5 * 3600));
        $list = $this->db->table('rides_recharge')->where($where)->select();
        $this->load->library('sys_model/deposit');
        $this->load->library('sys_model/orders');

        foreach ($list as $rides_recharge) {
            //申请退款表里有记录
            $cashInfo = $this->sys_model_deposit->getDepositCashInfo(array('pdr_sn' => $rides_recharge['recharge_sn']));
            if ($cashInfo) {
                $this->_changeRidesRechargeStatus($rides_recharge['recharge_sn']);
                continue;
            }

            $order_info = $this->sys_model_orders->getOrderInfo(array('order_sn' => $rides_recharge['order_sn']));
            if (!$order_info) {
                $this->_changeRidesRechargeStatus($rides_recharge['recharge_sn']);
                continue;
            }

            if ($order_info['state'] != 2) {
                $this->_changeRidesRechargeStatus($rides_recharge['recharge_sn']);
                continue;
            }

            $recharge_info = $this->sys_model_deposit->getRechargeInfo(array('pdr_sn' => $rides_recharge['recharge_sn']));

            if ($recharge_info) {
                if ($recharge_info['pdc_payment_state'] != 1) {
                    continue;
                }

                if ($order_info['pay_amount'] >= $recharge_info['pdr_amount']) {
                    $this->db->table('rides_recharge')->where(array('recharge_sn' => $rides_recharge['recharge_sn']))->update(array('state' => 1));
                    continue;
                }
                $amount = $recharge_info['pdr_amount'] - $order_info['pay_amount'];
                //部分退款反映到充值表

                $recharge_info['cash_amount'] = $amount;
                $recharge_info['admin_id'] = 0;
                $recharge_info['admin_name'] = 'system';

                $result = $this->sys_model_deposit->cashApply($recharge_info);
                if ($result['state']) {
                    $pdc_info = array(
                        'pdc_id' => $result['data']['pdc_id'],
                        'pdc_sn' => $result['data']['pdc_sn'],
                        'pdc_user_id' => $recharge_info['pdr_user_id'],
                        'pdc_user_name' => $recharge_info['pdr_user_name'],
                        'pdc_payment_name' => $recharge_info['pdr_payment_name'],
                        'pdc_payment_code' => $recharge_info['pdr_payment_code'],
                        'pdc_payment_type' => $recharge_info['pdr_payment_type'],
                        'pdc_payment_state' => '0',
                        'pdr_amount' => $recharge_info['pdr_amount'],
                        'has_cash_amount' => 0,
                        'cash_amount' => $recharge_info['cash_amount'],
                        'pdr_sn' => $recharge_info['pdr_sn'],
                        'trace_no' => $recharge_info['trace_no'],
                        'admin_id' => $recharge_info['admin_id'],
                        'admin_name' => $recharge_info['admin_name'],
                        'pdc_type' => $recharge_info['pdr_type'],
                    );

                    $ssl_cert_path =  DIR_SYSTEM . 'library/payment/cert/' . $this->config->get('config_wxpay_ssl_cert_path') . '/' . $recharge_info['pdr_payment_type'] . '/apiclient_cert.pem';
                    $ssl_key_path = DIR_SYSTEM . 'library/payment/cert/' . $this->config->get('config_wxpay_ssl_cert_path') . '/' . $recharge_info['pdr_payment_type'] . '/apiclient_key.pem';
                    define('WX_SSLCERT_PATH', $ssl_cert_path);
                    define('WX_SSLKEY_PATH', $ssl_key_path);
                    $result = $this->sys_model_deposit->wxPayRefund($pdc_info);
                    if ($result['state'] == true) {
                        $this->db->table('rides_recharge')->where(array('recharge_sn' => $rides_recharge['recharge_sn']))->update(array('state' => 1));
                    }
                }
            }
        }
    }

    private function _changeRidesRechargeStatus($recharge_sn) {
        $this->db->table('rides_recharge')->where(array('recharge_sn' => $recharge_sn))->update(array('state' => 1));
    }
}
