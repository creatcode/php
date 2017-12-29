<?php
/**
 * 充值优惠.
 * User: LJW
 * Date: 2017/7/26 0026
 * Time: 17:06
 */
error_reporting(E_ALL); //E_ALL
function cache_shutdown_error() {
    $_error = error_get_last();
    if ($_error && in_array($_error['type'], array(1, 4, 16, 64, 256, 4096, E_ALL))) {
        echo '<font color=red>你的代码出错了：</font></br>';
        echo '致命错误:' . $_error['message'] . '</br>';
        echo '文件:' . $_error['file'] . '</br>';
        echo '在第' . $_error['line'] . '行</br>';
    }
}
register_shutdown_function("cache_shutdown_error");
use Tool\ArrayUtil;
class ControllerUserRechargeGive extends Controller
{
    private $error;
    private $cur_url;
    private $page_rows;

    public function __construct($registry){
        parent::__construct($registry);

        $this->cur_url = $this->request->get['route'];
        $this->page_rows = $this->config->get('config_limit_admin');
        $this->load->library('sys_model/comment', true);
    }

    public function index(){
        $fiter  = $this->request->get(array('city_id','pdr_payment_time','region_id','time_type'));
        $page   = isset($this->request->get['page']) ? $this->request->get['page'] : 1 ;
        $offset = $this->page_rows*($page - 1);
        $limit  = $this->page_rows*($page - 1).",".$this->page_rows;
        $where  = " deposit_recharge.pdr_present_amount > 0 AND pdr_payment_state > 0 ";
        if($fiter['city_id']){
            $where .= " AND user.city_id = ".$fiter['city_id'];
        }
        if($fiter['region_id']){
            $where .= " AND user.region_id = ".$fiter['region_id'];
        }
        if($fiter['pdr_payment_time']){
            $time_arr = explode('至', $fiter['pdr_payment_time']);
            if($time_arr[0] && $time_arr[1]){
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0])." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1]));
            }
        }
        $filed = "deposit_recharge.*,user.*,city.*,region.*";
        $rechargeLists = $this->sys_model_comment->getDepositRecharge($where,$filed,$limit);
        $total = $this->sys_model_comment->getDepositRechargeToals($where);
        
        
        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }


        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $this->page_rows;
        $pagination->url = $this->url->link($this->cur_url) . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($fiter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $this->page_rows)) ? $total : ($offset + $this->page_rows), $total, ceil($total / $this->page_rows));

        $this->assign('filter_regions', $filter_regions);
        $this->assign('time_type',get_time_type());
        $this->assign('pagination', $pagination);
        $this->assign('results', $results);
        $this->assign('action',$this->url->link('user/recharge_give'));
        $this->assign('data_rows',$rechargeLists);
        $this->set_column(array('区域','城市','用户ID','手机号码','充值金额','赠送金额','支付时间','订单编号'));
        $this->assign('data_columns',$this->data_columns);
        $this->assign('filter', $fiter);
        $this->assign('deposit_recharge_chart_action', $this->url->link('user/recharge_give/chart'));
        $this->assign('export_action', $this->url->link('user/recharge_give/export'). '&amp;'.str_replace('&', '&amp', http_build_query($fiter)));
        $this->response->setOutPut($this->load->view('user/recharge_give_list', $this->output));
    }


    public function set_column($data){
        foreach($data as $item){
            $this->setDataColumn($item);
        }
    }

    public function chart(){

        $fiter = $this->request->get(array('city_id','pdr_payment_time','region_id','time_type'));
        $where = " deposit_recharge.pdr_present_amount > 0 AND pdr_payment_state > 0 ";
        if($fiter['city_id']){
            $where .= " AND user.city_id = ".$fiter['city_id'];
        }
        if($fiter['region_id']){
            $where .= " AND user.region_id = ".$fiter['region_id'];
        }
        $firstday = strtotime(date('Y-m-01'));
        $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
        if($fiter['pdr_payment_time']){
            $time_arr = explode('至', $fiter['pdr_payment_time']);
            if($time_arr[0] && $time_arr[1]){
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0])." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1]));
                $firstday = strtotime($time_arr[0]);
                $lastday  = bcadd(86399, strtotime($time_arr[1]));
            }
        }else{
            $where .= " AND deposit_recharge.pdr_payment_time >= ".$firstday." AND deposit_recharge.pdr_payment_time <= ". $lastday;
        }
        $filed = "deposit_recharge.*,user.mobile,FROM_UNIXTIME(deposit_recharge.pdr_payment_time,'%Y-%m-%d') as date,deposit_recharge.pdr_amount as amount,deposit_recharge.pdr_present_amount as refund";
        
        $rechargeLists = $this->sys_model_comment->getDepositRecharge($where,$filed);
        // 初始化订单统计数据
        $chart_arr = array();
        while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $chart_arr[$tempDay] = array(
                'date' => $tempDay,
                'amount' => 0,
                'refund' => 0,
            );
            $firstday = strtotime('+1 day', $firstday);
        }
        //整理输出结果；
        foreach($rechargeLists as $v){
            $chart_arr[$v['date']] = array(
                'date'   => $v['date'],
                'amount' => $v['amount'],
                'refund' => $v['refund'],
            );
        }
        //SUM(deposit_recharge.pdr_amount) as orderAmountTotal, SUM(deposit_recharge.pdr_present_amount) as refundAmountTotal
        $total = $this->sys_model_comment->getDepositRechargeLong($where);
        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }


        $this->assign('filter_regions', $filter_regions);
        $this->assign('time_type',get_time_type());
        $this->assign('action',$this->url->link('user/recharge_give/chart'));
        $this->assign('recharge_give_action',$this->url->link('user/recharge_give'));
        $show_result = array_values($chart_arr);
        $this->assign('data', json_encode($show_result));
        $this->assign('filter', $fiter);
        $this->assign('orderAmountTotal', $total['orderAmountTotal']);
        $this->assign('refundAmountTotal', $total['refundAmountTotal']);
        $this->assign('user_rating_action',$this->url->link('user/recharge_give'));
        $this->response->setOutPut($this->load->view('user/user_recharge_give_chart',$this->output));

    }


    /**
     * 导出
     */
    public function export() {

        $fiter = $this->request->get(array('cooperator_id','pdr_payment_time'));
        $where = " deposit_recharge.pdr_present_amount > 0 ";
        if($fiter['cooperator_id']){
            $where .= " AND user.cooperator_id = ".$fiter['cooperator_id'];
        }
        if($fiter['pdr_payment_time']){
            $time_arr = explode('至', $fiter['pdr_payment_time']);
            if($time_arr[0] && $time_arr[1]){
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0])." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1]));
            }
        }

        $filed = "deposit_recharge.*,user.mobile";
        $rechargeLists = $this->sys_model_comment->getDepositRecharge($where,$filed);
        $total  = $this->sys_model_comment->getDepositRechargeToals($where);
        $result = array();
        foreach($rechargeLists as $v){
            $result[] = array(
                'user_id'               => $v['pdr_user_id'],
                'mobile'                => $v['mobile'],
                'pdr_amount'            => $v['pdr_amount'],
                'pdr_present_amount'    => $v['pdr_present_amount'],
                'pdr_payment_time'      => date('Y-m-d',$v['pdr_payment_time']),
                'pdr_sn'                => $v['pdr_sn'],
            );
        }

        $data = array(
            'title'     => '充值优惠列表',
            'list'      => $result,
            'header'    => array(
                'user_id'            => '用户ID',
                'mobile'             => '手机号码',
                'pdr_amount'         => '充值金额',
                'pdr_present_amount' => '赠送金额',
                'pdr_payment_time'   => '支付时间',
                'pdr_sn'             => '订单编号',
            )
        );
        unset($result);
        $this->load->controller('common/base/exportExcel', $data);
    }



}