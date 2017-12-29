<?php

/**
 * Class ControllerPaymentAliPay
 * 支付宝失败订单重新回调
 */
class ControllerPaymentAliPayutil extends Controller {
	public function refund_check(){
		set_time_limit(300);//最多运行五分钟
        $this->load->library('sys_model/deposit', true);
        require_once(__DIR__.'/util.class.php');
        $where  = array(
            'pdc_add_time'          => array(array('egt',strtotime('2017-08-02 00:00:00')),array('lt',time())),
            //'pdc_payment_state'     => array('in','0,2'),
            //'pdc_type'              => 1,//押金退款
            'trace_no' => '2017050821001004620227001297',
            //'pdc_batch_no'          =>'',
			'pdc_payment_code'		=>'alipay'
            );
         $pdc_list = $this->sys_model_deposit->getDepositCashList($where);//var_dump($this->db->getLastSql());die;
         foreach ($pdc_list as $k => $v) {
             if(empty($v['pdc_batch_no'])){
                if(empty($v['trace_no'])){
                    file_put_contents(DIR_LOGS.'/alipay_refund_check_err.log', date('Y-m-d H:i:s').'[Empty trace_no]'.json_encode($v,JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND);
                    throw new \Exception("Empty trace_no.".json_encode($v,JSON_UNESCAPED_UNICODE), 1);
                }
                $batch_no   = \Util::get_batch_no($v['trace_no']);
                $log_info   = \Util::get_log_info($v['trace_no']);//var_dump($log_info);die;
                if(empty($batch_no)){//找不到记录，记录log，执行下一次循环
                    file_put_contents(DIR_LOGS.'/alipay_refund_check_err.log', date('Y-m-d H:i:s').'[batch_no not fond]'.json_encode($v,JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND);
                    continue;
                }
                $res = $this->sys_model_deposit->updateDepositCash(array('pdc_id'=>$v['pdc_id']),array('pdc_batch_no'=>$batch_no,'pdc_payment_time'=>strtotime($log_info['notify_time'])));
                if(!$res){
                    file_put_contents(DIR_LOGS.'/alipay_refund_check_err.log', date('Y-m-d H:i:s').'[update DB fail:'.$res['err_msg'].']'.json_encode($v,JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND);
                    throw new \Exception("update DB fail.sql:".$this->db->getLastSql());
                }
                $tmp    = $v;
                $tmp['pdc_batch_no']         = $batch_no;
                $tmp['pdc_payment_time']     = strtotime($log_info['notify_time']);
                file_put_contents(DIR_LOGS.'/alipay_refund_check_info.log', date('Y-m-d H:i:s').'[update pdc_batch_no]'.json_encode($tmp,JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND);//exit();
             }

             $res = \Util::renotify($v['trace_no']);
             if($res['status'] != 1){
                file_put_contents(DIR_LOGS.'/alipay_refund_check_err.log', date('Y-m-d H:i:s').'[renotify fail:'.$res['err_msg'].']'.json_encode($v,JSON_UNESCAPED_UNICODE), FILE_APPEND);
             }
             usleep(50000);//避免调用速度过快导致服务瘫痪
         }
    }
}
