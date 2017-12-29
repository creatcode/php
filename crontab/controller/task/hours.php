<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2017/7/13
 * Time: 16:11
 */
class ControllerTaskHours extends Controller {
    public function index() {
        //$this->_dealThreeDaysOrders();
        //print_r('fuck');exit;
	$this->_autoRefundRecharge();
    }

    private function _dealThreeDaysOrders() {
        $_break = false;
        $this->load->library('sys_model/orders', true);
        $this->load->library('logic/orders', true);
        $where = array(
            'order_state' => 3,
            'start_time' => array('lt', time() - 3 * 24 * 3600)
        );

        //每次只执行1条，1分钟执行
        for ($i = 0; $i < 1; $i++) {
            if ($_break) break;
            $order_list = $this->sys_model_orders->getOrdersList($where, 'order_id ASC', 1);
            //print_r($order_list);exit;
            if (empty($order_list)) break;
            foreach ($order_list as $order_info) {
                //$order_line = $this->db->table('orders_line')->where(array('order_id' => $order_info['order_id']))->order('add_time DESC')->limit(1)->find();
                $data = array(
                    'order_id' => $order_info['order_id'],
                    'is_cron_close' => 1,
                    'finish_time' => $order_info['device_time'] ? $order_info['device_time'] : time(),
                    'lat' => isset($order_line['lat']) ? $order_line['lat'] : '',
                    'lng' => isset($order_line['lng']) ? $order_line['lng'] : '',
                );
                $result = $this->logic_orders->closeOrder($data);
            }
        }
        print_r($result);exit;
        file_put_contents('crontab_order.log', date('Y-m-d H:i:s')  . json_encode($result) . "\n" , 8);
    }

    private function _autoRefundRecharge() {
        //SELECT rrr.*,rdr.* FROM `rich_rides_recharge` rrr LEFT JOIN rich_deposit_recharge rdr ON rrr.recharge_sn=rdr.pdr_sn LEFT JOIN rich_orders ror ON rrr.order_sn=ror.order_sn WHERE rrr.state=0 AND ror.order_state=2
        $where = array('state' => 0, 'add_time' => array('lt', time() - 5 * 3600));
        $list = $this->db->table('rides_recharge')->where($where)->select();
        $this->load->library('sys_model/deposit');
        $this->load->library('sys_model/orders');
        //print_r($this->db->getLastSql());
	//print_r($list);exit;
        foreach ($list as $rides_recharge) {
            //申请退款表里有记录
            $cashInfo = $this->sys_model_deposit->getDepositCashInfo(array('pdr_sn' => $rides_recharge['recharge_sn']));
            if ($cashInfo) {
                //$this->_changeRidesRechargeStatus($rides_recharge['recharge_sn']);
                //continue;
            }
            //print_r($cashInfo);exit;
            $order_info = $this->sys_model_orders->getOrdersInfo(array('order_sn' => $rides_recharge['order_sn']));
            if (!$order_info) {
                //$this->_changeRidesRechargeStatus($rides_recharge['recharge_sn']);
                //continue;
            }
            //print_r($order_info);exit;
            if ($order_info['order_state'] != 2) {             
                //$this->_changeRidesRechargeStatus($rides_recharge['recharge_sn']);
               // continue;
            }

            $recharge_info = $this->sys_model_deposit->getRechargeInfo(array('pdr_sn' => $rides_recharge['recharge_sn']));
            //print_r($recharge_info);exit;
            if ($recharge_info) {
                if ($recharge_info['pdr_payment_state'] != 1) {
                    continue;
                }

                if ($order_info['pay_amount'] >= $recharge_info['pdr_amount']) {
                    $this->_changeRidesRechargeStatus($rides_recharge['recharge_sn']);
                    continue;
                }

                $amount = $recharge_info['pdr_amount'] - $order_info['pay_amount'];
                //部分退款反映到充值表
		//print_r($amount);exit;
                $recharge_info['cash_amount'] = $amount;
                $recharge_info['admin_id'] = 0;
                $recharge_info['admin_name'] = 'system';

                $result = $this->sys_model_deposit->cashApply($recharge_info);
                //$result['state'] = true;
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
                    //print_r($pdc_info);exit;
                    $ssl_cert_path =  DIR_SYSTEM . 'library/payment/cert/' . $this->config->get('config_wxpay_ssl_cert_path') . '/' . $recharge_info['pdr_payment_type'] . '/apiclient_cert.pem';
                    $ssl_key_path = DIR_SYSTEM . 'library/payment/cert/' . $this->config->get('config_wxpay_ssl_cert_path') . '/' . $recharge_info['pdr_payment_type'] . '/apiclient_key.pem';
                    //print_r($ssl_cert_path);
                    //print_r($ssl_key_path);exit;
                    define('WX_SSLCERT_PATH', $ssl_cert_path);
                    define('WX_SSLKEY_PATH', $ssl_key_path);
                    $result = $this->sys_model_deposit->wxPayRefund($pdc_info);
                    if ($result['state'] == true) {
                        $this->_changeRidesRechargeStatus($rides_recharge['recharge_sn']);
                        $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), array('refund_state' => 1));
                    }
		    echo 'success', "\n";
                }
            }
        }
    }

    private function _changeRidesRechargeStatus($recharge_sn) {
        $this->db->table('rides_recharge')->where(array('recharge_sn' => $recharge_sn))->update(array('state' => 1));
    }
}
