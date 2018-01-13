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

        $this->cur_url = $this->url->link($this->request->get['route']);
        $this->page_rows = $this->config->get('config_limit_admin');
        $this->load->library('sys_model/comment', true);
        $this->load->library('sys_model/present', true);

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

        $time_arr = explode(' 至 ', $fiter['pdr_payment_time']);
        if ($fiter['time_type']==1) {
            if($fiter['pdr_payment_time']){
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0].'-01-01')." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1].'-12-31'));

            }else{
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime(date('Y-01-01'))." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime(date('Y-12-31')));
            }
        }else if($fiter['time_type']==2){
            if($fiter['pdr_payment_time']){
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0])." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1].'+1 month -1 day'));
            }else{
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime(date('Y-m'))." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime(date('Y-m-t')));
            }
        }else if($fiter['time_type']==3){
            if($fiter['pdr_payment_time']){
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0])." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1]));
            }else{
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime(date('Y-m-d'))." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime(date('Y-m-d')));
            }
        }else{
            if($fiter['pdr_payment_time']){
                $time_arr = explode('至', $fiter['pdr_payment_time']);
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
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($fiter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $this->page_rows)) ? $total : ($offset + $this->page_rows), $total, ceil($total / $this->page_rows));

        $this->assign('filter_regions', $filter_regions);
        $this->assign('time_type',get_time_type());
        $this->assign('pagination', $pagination);
        $this->assign('results', $results);
        $this->assign('action',$this->url->link('user/recharge_give'));
        $this->assign('data_rows',$rechargeLists);
        $this->set_column(array('区域','城市','用户名','充值金额','赠送金额','支付时间','订单编号'));
        $this->assign('data_columns',$this->data_columns);
        $this->assign('filter', $fiter);
        $this->assign('deposit_recharge_chart_action', $this->url->link('user/recharge_give/chart'));
        $this->assign('setting_action', $this->url->link('user/recharge_give/setting'));
        $this->assign('export_action', $this->url->link('user/recharge_give/export'). '&amp;'.str_replace('&', '&amp', http_build_query($fiter)));
        $this->response->setOutPut($this->load->view('user/recharge_give_list', $this->output));
    }


    public function set_column($data){
        foreach($data as $item){
            $this->setDataColumn($item);
        }
    }

    public function chart(){
        $this->load->library('sys_model/data_sum', true);
        $fiter = $this->request->get(array('city_id','pdr_payment_time','region_id','time_type'));
        $where = " deposit_recharge.pdr_present_amount > 0 AND pdr_payment_state > 0 ";
        if(is_numeric($fiter['city_id'])){
            $where .= " AND user.city_id = ".$fiter['city_id'];
        }
        if(is_numeric($fiter['region_id'])){
            $where .= " AND user.region_id = ".$fiter['region_id'];
        }
        $time_arr = explode(' 至 ', $fiter['pdr_payment_time']);
        if ($fiter['time_type']==1) {
            if(!empty($fiter['pdr_payment_time'])){
                $firstday = strtotime($time_arr[0].'-01-01');
                $lastday  = bcadd(86399,strtotime($time_arr[1].'-12-31'));
                $where .= " AND deposit_recharge.pdr_payment_time >= '$firstday' AND deposit_recharge.pdr_payment_time <= '$lastday'";
                }else{
                    $firstday = strtotime(date('Y-01-01'));
                    $lastday  = bcadd(86399,strtotime(date('Y-12-31')));
                    $where .= " AND deposit_recharge.pdr_payment_time >= '$firstday' AND deposit_recharge.pdr_payment_time <= '$lastday'";
                }
            }else if($fiter['time_type']==2){
                if(!empty($fiter['pdr_payment_time'])){
                    $firstday = strtotime($time_arr[0]);
                    $lastday  = bcadd(86399, strtotime($time_arr[1].'+1 month -1 day'));
                    $where .= " AND deposit_recharge.pdr_payment_time  >= '$firstday' AND deposit_recharge.pdr_payment_time <= '$lastday'";
                        
                }else{
                    $firstday = strtotime(date('Y-m'));
                    $lastday  = bcadd(86399, strtotime(date('Y-m-t')));
                    $where .= " AND deposit_recharge.pdr_payment_time >= '$firstday' AND deposit_recharge.pdr_payment_time <= '$lastday'";
                }
            }else if($fiter['time_type']==3){
                if(!empty($fiter['pdr_payment_time'])){
                    $firstday = strtotime($time_arr[0]);
                    $lastday  = bcadd(86399, strtotime($time_arr[1]));
                    $where .= " AND deposit_recharge.pdr_payment_time  >= '$firstday' AND deposit_recharge.pdr_payment_time <= '$lastday'";
                        
                }else{
                    $firstday = strtotime(date('Y-m-d'));
                    $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
                    $where .= " AND deposit_recharge.pdr_payment_time >= '$firstday' AND deposit_recharge.pdr_payment_time <= '$lastday'";
                }
            }else{
                if(!empty($fiter['pdr_payment_time'])){
                    $firstday = strtotime($time_arr[0]);
                    $lastday  = bcadd(86399, strtotime($time_arr[1]));
                    $where .= " AND deposit_recharge.pdr_payment_time  >= '$firstday' AND deposit_recharge.pdr_payment_time <= '$lastday'";
                        
                }else{
                    $firstday = strtotime(date('Y-m-01'));
                    $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
                    $where .= " AND deposit_recharge.pdr_payment_time >= '$firstday' AND deposit_recharge.pdr_payment_time <= '$lastday'";
                }
            }


        if($fiter['time_type']==1){
            $filed = "FROM_UNIXTIME(deposit_recharge.pdr_payment_time,'%Y') as date,sum(deposit_recharge.pdr_amount) as amount,sum(deposit_recharge.pdr_present_amount) as refund";
        }else if($fiter['time_type']==2){
            $filed = "FROM_UNIXTIME(deposit_recharge.pdr_payment_time,'%Y-%m') as date,sum(deposit_recharge.pdr_amount) as amount,sum(deposit_recharge.pdr_present_amount) as refund";
        }else{
            $filed = "FROM_UNIXTIME(deposit_recharge.pdr_payment_time,'%Y-%m-%d') as date,sum(deposit_recharge.pdr_amount) as amount,sum(deposit_recharge.pdr_present_amount) as refund";
        
        }
        
        // 初始化订单统计数据
        $chart_arr = array();
        if($fiter['time_type']==1){
             while ($firstday <= $lastday) {
            $tempDay = date('Y', $firstday);
            $chart_arr[$tempDay] = array(
                'date' => $tempDay,
                'amount' => 0,
                'refund' => 0,
            );
            $firstday = strtotime('+1 year', $firstday);
        }
        }else if($fiter['time_type']==2){
            while ($firstday <= $lastday) {
            $tempDay = date('Y-m', $firstday);
            $chart_arr[$tempDay] = array(
                'date' => $tempDay,
                'amount' => 0,
                'refund' => 0,
            );
            $firstday = strtotime('+1 month', $firstday);
        }
        }else{
            while ($firstday <= $lastday) {
            $tempDay = date('Y-m-d', $firstday);
            $chart_arr[$tempDay] = array(
                'date' => $tempDay,
                'amount' => 0,
                'refund' => 0,
            );
            $firstday = strtotime('+1 day', $firstday);
        }
        }

       $rechargeLists = $this->sys_model_comment->getDepositRecharge2($where,$filed);
        //整理输出结果；
        foreach($rechargeLists as $v){
            $chart_arr[$v['date']] = array(
                'date'   => $v['date'],
                'amount' => $v['amount'],
                'refund' => $v['refund'],
            );
        }
         $orderAmountTotal = $refundAmountTotal  = 0;
        $total = $this->sys_model_comment->getDepositRechargeLong($where);
       
        $orderAmountTotal = sprintf('%0.2f', $orderAmountTotal);
        $refundAmountTotal = sprintf('%0.2f', $refundAmountTotal);

        $total['orderAmountTotal']+=$orderAmountTotal;
        $total['refundAmountTotal']+=$refundAmountTotal;

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
        $this->assign('setting_action', $this->url->link('user/recharge_give/setting'));
        $this->response->setOutPut($this->load->view('user/user_recharge_give_chart',$this->output));

    }

    public function setting(){
        $filter  = $this->request->get(array('city_id','pdr_payment_time','region_id','time_type','pre_type'));
        $page   = isset($this->request->get['page']) ? $this->request->get['page'] : 1 ;
        $offset = $this->page_rows*($page - 1);
        $limit  = $this->page_rows*($page - 1).",".$this->page_rows;
        $where  = " 1=1 ";
        if($filter['city_id']){
            $where .= " AND present_city_id = ".$filter['city_id'];
        }
        if($filter['region_id']){
            $where .= " AND present_region_id = ".$filter['region_id'];
        }
        if(is_numeric($filter['pre_type'])){
            $where .= " AND state = ".$filter['pre_type'];
        }

        // $time_arr = explode(' 至 ', $fiter['pdr_payment_time']);
        // if ($fiter['time_type']==1) {
        //     if($fiter['pdr_payment_time']){
        //         $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0].'-01-01')." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1].'-12-31'));

        //     }else{
        //         $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime(date('Y-01-01'))." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime(date('Y-12-31')));
        //     }
        // }else if($fiter['time_type']==2){
        //     if($fiter['pdr_payment_time']){
        //         $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0])." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1].'+1 month -1 day'));
        //     }else{
        //         $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime(date('Y-m'))." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime(date('Y-m-t')));
        //     }
        // }else if($fiter['time_type']==3){
        //     if($fiter['pdr_payment_time']){
        //         $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0])." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1]));
        //     }else{
        //         $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime(date('Y-m-d'))." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime(date('Y-m-d')));
        //     }
        // }else{
        //     if($fiter['pdr_payment_time']){
        //         $time_arr = explode('至', $fiter['pdr_payment_time']);
        //             $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0])." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1]));
        //     }
        // }
      
        $filed = "present_recharge.*,city.city_name,region.region_name";
        $rechargeLists = $this->sys_model_comment->getPrensentRecharge($where,$filed,$limit);
        $total = $this->sys_model_comment->getPrensentRechargeToals($where);
        
        if (is_array($rechargeLists) && !empty($rechargeLists)) {
            foreach ($rechargeLists as &$item) {
                $item['edit_action'] = $this->url->link('user/recharge_give/edit', 'prc_id='.$item['prc_id']);
                $item['delete_action'] = $this->url->link('user/recharge_give/del', 'prc_id='.$item['prc_id']);
            }
        }
        
        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }


        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $this->page_rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $this->page_rows)) ? $total : ($offset + $this->page_rows), $total, ceil($total / $this->page_rows));

        $this->assign('filter_regions', $filter_regions);
        $this->assign('time_type',get_time_type());
        $this->assign('pre_type',get_present_type());
        $this->assign('pagination', $pagination);
        $this->assign('results', $results);
        $this->assign('action',$this->url->link('user/recharge_give/setting'));
        $this->assign('data_rows',$rechargeLists);
        $this->set_column(array('区域','城市','充值金额','赠送金额','状态'));
        $this->assign('data_columns',$this->data_columns);
        $this->assign('filter', $filter);
        $this->assign('deposit_recharge_chart_action', $this->url->link('user/recharge_give/chart'));
        $this->assign('user_rating_action',$this->url->link('user/recharge_give'));
        $this->assign('add_action',$this->url->link('user/recharge_give/add'));
        $this->response->setOutPut($this->load->view('user/recharge_give_setting', $this->output));
    }

    /**
     * @license  充值优惠添加
     */
    public function add(){
            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('region_id', 'city_id', 'recharge_amount', 'present_amount','state'));
            $data = array(
                'present_region_id' => $input['region_id'],
                'present_city_id' => $input['city_id'],
                'present_amount' => $input['present_amount'],
                'recharge_amount' => $input['recharge_amount'],
                'state' => $input['state']=='1' ? 1 : 0
                
            );

            $prc_id = $this->sys_model_present->addPresent($data);
            
            
            $this->session->data['success'] = '添加充值优惠成功！！！';
            

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '添加充值优惠：' . $prc_id,
                'log_ip' => $this->request->ip_address(),
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

            $this->load->controller('common/base/redirect', $this->url->link('user/recharge_give/setting', '', true));
            
        }

        $this->assign('title', '添加充值优惠');
        $this->getForm();
        
    }

    /**
     * @Author   obj
     * @DateTime 2018-01-11
     * @license  充值优惠编辑
     * @return   [type]     [description]
     */
    public function edit(){
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $input = $this->request->post(array('region_id', 'city_id', 'recharge_amount', 'present_amount','state'));
            $prc_id = $this->request->get['prc_id'];
            $data = array(
                'present_region_id' => $input['region_id'],
                'present_city_id' => $input['city_id'],
                'present_amount' => $input['present_amount'],
                'recharge_amount' => $input['recharge_amount'],
                'state' => $input['state']
            );

            $where = array('prc_id' => $prc_id);

            $this->sys_model_present->updatePresent($where, $data);

            $this->session->data['success'] = '编辑充值优惠成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '编辑充值优惠：' . $prc_id,
                'log_ip' => $this->request->ip_address(),
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);
            
            $this->load->controller('common/base/redirect', $this->url->link('user/recharge_give/setting', '', true));
            
            
        }

        $this->assign('title', '编辑充值优惠');
        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }
        $this->assign('filter_regions', $filter_regions);
        $this->getForm();
    }

    /**
         * @Author   obj
         * @DateTime 2018-01-11
         * @license  充值优惠删除
         * @return   [type]     [description]
         */
    public function del() {
        if (isset($this->request->get['prc_id'])&& $this->validateDelete() ) {
            $condition = array(
                'prc_id' => $this->request->get['prc_id']
            );
            $this->sys_model_present->deletePresent($condition);

            $this->session->data['success'] = '删除充值优惠成功！';

            // 添加管理员日志
            $this->load->controller('common/base/adminLog', '删除充值优惠：' . $this->request->get['prc_id']);
        }
        $filter = array();
        $this->load->controller('common/base/redirect', $this->url->link('user/recharge_give/setting', '', true));
    }

    private function getForm() {
        $info = $this->request->post(array('region_id', 'city_id', 'recharge_amount','present_amount','state'));
        $gets = $this->request->get(array('prc_id'));
        $region_id=0;
        $city_id=0;
        if (isset($this->request->get['prc_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'prc_id' => $this->request->get['prc_id']
            );
            $info = $this->sys_model_present->getPresentInfo($condition);
            if (!empty($info)) {
                $region_id = $info['present_region_id'];
                $city_id = $info['present_city_id'];
            }
        }
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $region_id = $this->request->post['region_id'];
        }
         

        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }
        $region_list = $this->sys_model_region->getRegionList();
        $output_region = array();
        foreach ($region_list as $region) {
            $output_region[$region['region_id']] = $region['region_name'];
        }
        
        $this->assign('data', $info);
        $this->assign('region_activity_options', $output_region);

        $this->assign('region_id', $region_id);
        $this->assign('filter_regions', $filter_regions);
        $this->assign('action', $this->cur_url . '&prc_id=' . $gets['prc_id']);
        $this->assign('error', $this->error);
        $this->assign('return_action', $this->url->link('user/recharge_give/setting'));

        $this->response->setOutput($this->load->view('user/recharge_give_form', $this->output));
    }

        

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $info = $this->request->post(array('region_id', 'city_id', 'recharge_amount','present_amount'));

        foreach ($info as $k => $v) {
            
                if (empty($v)) {
                    $this->error[$k] = '请完善此项！';
                }
            }


       if (empty($info['recharge_amount'])) {
           $this->error['warning'] = '警告: 存在错误，请检查！';
       }
        return !$this->error;
    }

     /**
     * 验证删除条件
     */
    private function validateDelete() {
        return !$this->error;
    }

    /**
     * 导出
     */
    public function export() {

        $fiter = $this->request->get(array('city_id','pdr_payment_time','region_id','time_type'));
        $where = " deposit_recharge.pdr_present_amount > 0 AND pdr_payment_state > 0 ";
        if($fiter['city_id']){
            $where .= " AND user.city_id = ".$fiter['city_id'];
        }
        if($fiter['region_id']){
            $where .= " AND user.region_id = ".$fiter['region_id'];
        }
        $time_arr = explode(' 至 ', $fiter['pdr_payment_time']);
        if ($fiter['time_type']==1) {
            if($fiter['pdr_payment_time']){
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0].'-01-01')." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1].'-12-31'));

            }else{
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime(date('Y-01-01'))." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime(date('Y-12-31')));
            }
        }else if($fiter['time_type']==2){
            if($fiter['pdr_payment_time']){
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0])." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1].'+1 month -1 day'));
            }else{
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime(date('Y-m'))." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime(date('Y-m-t')));
            }
        }else if($fiter['time_type']==3){
            if($fiter['pdr_payment_time']){
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0])." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1]));
            }else{
                $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime(date('Y-m-d'))." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime(date('Y-m-d')));
            }
        }else{
            if($fiter['pdr_payment_time']){
                $time_arr = explode('至', $fiter['pdr_payment_time']);
                    $where .= " AND deposit_recharge.pdr_payment_time >= ".strtotime($time_arr[0])." AND deposit_recharge.pdr_payment_time <= ". bcadd(86399, strtotime($time_arr[1]));
            }
        }

        $filed = "deposit_recharge.*,user.*,city.*,region.*";
        $rechargeLists = $this->sys_model_comment->getDepositRecharge($where,$filed);
        $total  = $this->sys_model_comment->getDepositRechargeToals($where);
        $result = array();
        foreach($rechargeLists as $v){
            $result[] = array(
                'region_name'           => $v['region_name'],
                'city_name'             => $v['city_name'],
                // 'user_id'               => $v['pdr_user_id'],
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
                'region_name'        => '区域',
                'city_name'          => '城市',
                // 'user_id'            => '用户ID',
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