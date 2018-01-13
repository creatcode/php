<?php
class ControllerOperationCoupon extends Controller {
    private $cur_url = null;
    private $error = null;
    
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = isset($this->request->get['route']) ? $this->url->link($this->request->get['route']) : '';

        // 加载coupon Model
        $this->load->library('sys_model/coupon', true);
        $this->load->library('sys_model/user', true);
    }

    /**
     * 优惠券列表
     */
    public function index() {
        $filter = array();
        $condition = array();
        
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_coupon->getCouponList($condition, $order, $limit);
        $total = $this->sys_model_coupon->getTotalCoupons($condition);
        $this->load->library("sys_model/admin");
        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $coupon_name = '';
                switch ($item['coupon_type']) {
                    case '1' :
                        $coupon_name = sprintf('%d分钟用车券', $item['number']);
                        break;
                    case '2' :
                        $coupon_name = '单次体验券';
                        break;
                    case '3' :
                        $coupon_name = sprintf('%d元代金券', $item['number']);
                        break;
                }

                $admin_name = '平台';
                if($item['add_admin_id']){
                    $admin_info = $this->sys_model_admin->getAdminInfo(array('admin_id' => $item['add_admin_id']));
                    if(!empty($admin_info)){
                        $admin_name = $admin_info['admin_name'];
                    }
                }

                $item['coupon_name'] = $coupon_name;
                $item['admin_name'] = $admin_name;
                $item['used'] = $item['used'] == 1 ? '已使用' : '未使用';
                $item['used'] = $item['used'] == 1 ? '已使用' : '未使用';
                $item['effective_time'] = !empty($item['effective_time']) ? date('Y-m-d', $item['effective_time']) : '';
                $item['failure_time'] = !empty($item['failure_time']) ? date('Y-m-d', $item['failure_time']) : '';

                $item['edit_action'] = $this->url->link('operation/coupon/edit', 'coupon_id='.$item['coupon_id']);
                $item['delete_action'] = $this->url->link('operation/coupon/delete', 'coupon_id='.$item['coupon_id']);
                $item['info_action'] = $this->url->link('operation/coupon/info', 'coupon_id='.$item['coupon_id']);
            }
        }

        
         $this->load->library('sys_model/region');
        $regionList = $this->sys_model_region->getRegionList();

        if(empty($regionList)){
            $this->load->controller('common/base/redirect', $this->url->link('operation/coupon', $filter, true));
        }
        $this->assign('regionList', $regionList);
        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('data_rows', $result);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('operation/coupon/add'));

        if (isset($this->session->data['success'])) {
            $this->assign('success', $this->session->data['success']);
            unset($this->session->data['success']);
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->page_size = $rows;
        $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
        $pagination = $pagination->render();
        $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

        $this->assign('pagination', $pagination);
        $this->assign('results', $results);

        $this->assign('export_action', $this->url->link('operation/violation/export'));
        $this->assign('chart_action', $this->url->link('operation/coupon/chart'));
        $this->assign('cooperation_action', $this->url->link('operation/coupon/cooperation'));
        $this->assign('region_action', $this->url->link('operation/coupon/region'));
        $this->assign('time_type',get_time_type());
        $this->response->setOutput($this->load->view('operation/coupon_list', $this->output));
    }

    public function region(){

        $filter = $this->request->get(array('date', 'region_id'));
        $this->load->library('sys_model/data_sum', true);

        $firstday = strtotime(date('Y-m-01'));
        $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
        if (!empty($filter['date'])) {
            $date = explode(' 至 ', $filter['date']);
            if(count($date)==2){
                $firstday = strtotime($date[0]);
                $lastday  = bcadd(86399, strtotime($date[1]));
            }
        }
        $firstday = max($firstday, strtotime('2016-01-01')); //限制开始时间，2016-01-01之前无数据
        $lastday = min($lastday, time()); //限制结束时间，截止今天

        #全部合伙人
        $this->load->library('sys_model/region');
        $regionList = $this->sys_model_region->getRegionList();

        if(empty($regionList)){
            $this->load->controller('common/base/redirect', $this->url->link('operation/coupon', $filter, true));
        }

        if(isset($filter['region_id']) && $filter['region_id']){
            $w['region_id'] = $filter['region_id'];
        }else{
            $w['region_id'] = $regionList[0]['region_id'];
        }

        $where1 = "c.used_time>={$firstday} AND c.used_time <= {$lastday}";
        $where2 = "c.add_time>={$firstday} AND c.add_time <= {$lastday}";
        $where3 = "failure_time>={$firstday} AND failure_time <= {$lastday}";

        $couponSum   = $this->sys_model_data_sum->getCouponSumAndr($w);
        $res         = $this->sys_model_data_sum->getCouponSumByDayAndr($where1, $where2, $w['region_id']);
        //类型
        $countByType = $this->sys_model_data_sum->getCouponSumByTypeAndr($where1, $where2, $where3, $w['region_id']);
        $total_count_arr = $countByType['total_count'];
        $used_count  = $countByType['used_count'];
        $fail_count  = $countByType['fail_count'];
        foreach($total_count_arr as $k => &$v ){
            $v['used_count'] = isset($used_count[$k]['used_count']) ? $used_count[$k]['used_count'] : 0;
            $v['fail_count'] = isset($fail_count[$k]['fail_count']) ? $fail_count[$k]['fail_count'] : 0;
        }
        $countByType = $total_count_arr;
        //来源
        $countByObtain = $this->sys_model_data_sum->getCouponSumByObtainAndr($where1, $where2, $where3, $w['region_id']);
        $total_count_arr = $countByObtain['total_count'];
        $used_count  = $countByObtain['used_count'];
        $fail_count  = $countByObtain['fail_count'];
        foreach($total_count_arr as $k => &$v ){
            $v['used_count'] = isset($used_count[$k]['used_count']) ? $used_count[$k]['used_count'] : 0;
            $v['fail_count'] = isset($fail_count[$k]['fail_count']) ? $fail_count[$k]['fail_count'] : 0;
        }
        $countByObtain = $total_count_arr;

        //数据处理$res1
        $countByDay = array();
        $currUsed = current($res['usedCount']);
        $currTotal = current($res['totalCount']);
        $usedCountSum = 0;
        $totalCountSum = 0;
        for($time = $firstday; $time <= $lastday; $time += 86400) {
            $date = date('Y-m-d', $time);
            $item = array(
                'used_count' => 0,
                'total_count' => 0,
                'date' => $date,
            );
            if (!empty($currUsed) && $currUsed['date'] == $date) {
                $item['used_count'] = $currUsed['used_count'];
                $usedCountSum += $currUsed['used_count'];
                $currUsed = next($res['usedCount']);
            }
            if (!empty($currTotal) && $currTotal['date'] == $date) {
                $item['total_count'] = $currTotal['total_count'];
                $totalCountSum += $currTotal['total_count'];
                $currTotal = next($res['totalCount']);
            }
            $countByDay[] = $item;
        }

        //数据处理$countByType
        $couponTypes = get_coupon_type();
        $countByTypeSum = array();
        foreach ($countByType AS &$r) {
            $countByTypeSum[$r['coupon_type']]['number_sum'] = 0;
            $countByTypeSum[$r['coupon_type']]['number_sum'] += $r['number_sum'];
            $r['coupon_type'] = !empty($couponTypes[$r['coupon_type']]) ? $couponTypes[$r['coupon_type']] : '未知类型';
            $r['available_count'] = $r['total_count'] - $r['used_count'] - $r['fail_count'];

        }
        unset($r);

        //数据处理$countByObtain
        $couponObtain = get_coupon_obtain();
        $countByObtainSum = array();
        foreach ($countByObtain AS &$r) {
            $countByObtainSum[$r['obtain']]['number_sum'] = 0;
            $countByObtainSum[$r['obtain']]['number_sum'] += $r['number_sum'];
            $r['obtain'] = !empty($couponObtain[$r['obtain']]) ? $couponObtain[$r['obtain']] : '未知来源';
            $r['available_count'] = $r['total_count'] - $r['used_count'] - $r['fail_count'];
        }
        unset($r);

        $this->assign('couponSum', $couponSum);

        $this->assign('countByDay', $countByDay);
        $this->assign('usedCountSum', $usedCountSum);
        $this->assign('totalCountSum', $totalCountSum);

        $this->assign('countByType', $countByType);
        $this->assign('countByTypeSum', $countByTypeSum);

        $this->assign('countByObtainSum', $countByObtainSum);
        $this->assign('countByObtain', $countByObtain);

        $this->assign('filter', $filter);
        $this->assign('list_action', $this->url->link('operation/coupon'));
        $this->assign('action', $this->url->link('operation/coupon/region'));
        $this->assign('time_type',get_time_type());
        unset($res, $countByDay, $couponSum);
        $this->assign('regionList',$regionList);
        $this->assign('region_id',$w['region_id']);
        $this->assign('chart_action', $this->url->link('operation/coupon/chart'));
        $this->assign('cooperation_action', $this->url->link('operation/coupon/cooperation'));
        $this->assign('region_action', $this->url->link('operation/coupon/region'));
        $this->response->setOutput($this->load->view('operation/coupon_region', $this->output));
    }

    public function cooperation(){

        $filter = $this->request->get(array('date', 'cooperator_id'));
        $this->load->library('sys_model/data_sum', true);

        $firstday = strtotime(date('Y-m-01'));
        $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
        if (!empty($filter['date'])) {
            $date = explode(' 至 ', $filter['date']);
            if(count($date)==2){
                $firstday = strtotime($date[0]);
                $lastday  = bcadd(86399, strtotime($date[1]));
            }
        }
        $firstday = max($firstday, strtotime('2016-01-01')); //限制开始时间，2016-01-01之前无数据
        $lastday = min($lastday, time()); //限制结束时间，截止今天

        #全部合伙人
        $this->load->library('sys_model/cooperator');
        $cooperatorList = $this->sys_model_cooperator->getCooperatorList();
        if(empty($cooperatorList)){
            $this->load->controller('common/base/redirect', $this->url->link('operation/coupon', $filter, true));
        }

        if(isset($filter['cooperator_id']) && $filter['cooperator_id']){
            $w['cooperator_id'] = $filter['cooperator_id'];
        }else{
            $w['cooperator_id'] = $cooperatorList[0]['cooperator_id'];
        }

        $where1 = "c.used_time>={$firstday} AND c.used_time <= {$lastday}";
        $where2 = "c.add_time>={$firstday} AND c.add_time <= {$lastday}";
        $where3 = "failure_time>={$firstday} AND failure_time <= {$lastday}";

        $couponSum = $this->sys_model_data_sum->getCouponSumandw($w,$where1);
        $res = $this->sys_model_data_sum->getCouponSumByDayAndw($where1, $where2, $w['cooperator_id']);
        //类型
        $countByType = $this->sys_model_data_sum->getCouponSumByTypeAndw($where1, $where2, $where3, $w['cooperator_id']);
        $total_count_arr = $countByType['total_count'];
        $used_count  = $countByType['used_count'];
        $fail_count  = $countByType['fail_count'];
        foreach($total_count_arr as $k => &$v ){
            $v['used_count'] = isset($used_count[$k]['used_count']) ? $used_count[$k]['used_count'] : 0;
            $v['fail_count'] = isset($fail_count[$k]['fail_count']) ? $fail_count[$k]['fail_count'] : 0;
        }
        $countByType = $total_count_arr;
        //来源
        $countByObtain = $this->sys_model_data_sum->getCouponSumByObtainAndw($where1, $where2, $where3, $w['cooperator_id']);
        $total_count_arr = $countByObtain['total_count'];
        $used_count  = $countByObtain['used_count'];
        $fail_count  = $countByObtain['fail_count'];
        foreach($total_count_arr as $k => &$v ){
            $v['used_count'] = isset($used_count[$k]['used_count']) ? $used_count[$k]['used_count'] : 0;
            $v['fail_count'] = isset($fail_count[$k]['fail_count']) ? $fail_count[$k]['fail_count'] : 0;
        }
        $countByObtain = $total_count_arr;

        //数据处理$res1
        $countByDay = array();
        $currUsed = current($res['usedCount']);
        $currTotal = current($res['totalCount']);
        $usedCountSum = 0;
        $totalCountSum = 0;
        for($time = $firstday; $time <= $lastday; $time += 86400) {
            $date = date('Y-m-d', $time);
            $item = array(
                'used_count' => 0,
                'total_count' => 0,
                'date' => $date,
            );
            if (!empty($currUsed) && $currUsed['date'] == $date) {
                $item['used_count'] = $currUsed['used_count'];
                $usedCountSum += $currUsed['used_count'];
                $currUsed = next($res['usedCount']);
            }
            if (!empty($currTotal) && $currTotal['date'] == $date) {
                $item['total_count'] = $currTotal['total_count'];
                $totalCountSum += $currTotal['total_count'];
                $currTotal = next($res['totalCount']);
            }
            $countByDay[] = $item;
        }

        //数据处理$countByType
        $couponTypes = get_coupon_type();
        $countByTypeSum = array();
        foreach ($countByType AS &$r) {
            $countByTypeSum[$r['coupon_type']]['number_sum'] = 0;
            $countByTypeSum[$r['coupon_type']]['number_sum'] += $r['number_sum'];
            $r['coupon_type'] = !empty($couponTypes[$r['coupon_type']]) ? $couponTypes[$r['coupon_type']] : '未知类型';
            $r['available_count'] = $r['total_count'] - $r['used_count'] - $r['fail_count'];

        }
        unset($r);

        //数据处理$countByObtain
        $couponObtain = get_coupon_obtain();
        $countByObtainSum = array();
        foreach ($countByObtain AS &$r) {
            $countByObtainSum[$r['obtain']]['number_sum'] = 0;
            $countByObtainSum[$r['obtain']]['number_sum'] += $r['number_sum'];
            $r['obtain'] = !empty($couponObtain[$r['obtain']]) ? $couponObtain[$r['obtain']] : '未知来源';
            $r['available_count'] = $r['total_count'] - $r['used_count'] - $r['fail_count'];
        }
        unset($r);

        $this->assign('couponSum', $couponSum);

        $this->assign('countByDay', $countByDay);
        $this->assign('usedCountSum', $usedCountSum);
        $this->assign('totalCountSum', $totalCountSum);

        $this->assign('countByType', $countByType);
        $this->assign('countByTypeSum', $countByTypeSum);

        $this->assign('countByObtainSum', $countByObtainSum);
        $this->assign('countByObtain', $countByObtain);

        $this->assign('filter', $filter);
        $this->assign('list_action', $this->url->link('operation/coupon'));
        $this->assign('action', $this->url->link('operation/coupon/cooperation'));

        unset($res, $countByDay, $couponSum);
        $this->assign('cooperList',$cooperatorList);
        $this->assign('cooperator_id',$w['cooperator_id']);
        $this->assign('chart_action', $this->url->link('operation/coupon/chart'));
        $this->assign('cooperation_action', $this->url->link('operation/coupon/cooperation'));
        $this->assign('region_action', $this->url->link('operation/coupon/region'));
        $this->response->setOutput($this->load->view('operation/coupon_cooperation', $this->output));
    }


    public function chart() {
        $filter = $this->request->get(array('date'));
        $this->load->library('sys_model/data_sum', true);
        
        $firstday = strtotime(date('Y-m-01'));
        $lastday  = bcadd(86399, strtotime(date('Y-m-d')));
        if (!empty($filter['date'])) {
            $date = explode(' 至 ', $filter['date']);
            if(count($date)==2){
                $firstday = strtotime($date[0]);
                $lastday  = bcadd(86399, strtotime($date[1]));
            }
        }
        $firstday = max($firstday, strtotime('2016-01-01')); //限制开始时间，2016-01-01之前无数据
        $lastday = min($lastday, time()); //限制结束时间，截止今天
        
        $where1 = "used_time>={$firstday} AND used_time <= {$lastday}";
        $where2 = "add_time>={$firstday} AND add_time <= {$lastday}";
        $where3 = "failure_time>={$firstday} AND failure_time <= {$lastday}";

        $couponSum = $this->sys_model_data_sum->getCouponSum();
        $res = $this->sys_model_data_sum->getCouponSumByDay($where1, $where2);
        //类型统计
        $countByType = $this->sys_model_data_sum->getCouponSumByType($where1,$where2,$where3);
        $total_count_arr = isset($countByType['total_count']) ? $countByType['total_count'] : array();
        $used_count  = isset($countByType['used_count']) ? $countByType['used_count'] : array();
        $fail_count  = isset($countByType['fail_count']) ? $countByType['fail_count'] : array();
        foreach($total_count_arr as $k => &$v ){
            $v['used_count'] = isset($used_count[$k]['used_count']) ? $used_count[$k]['used_count'] : 0;
            $v['fail_count'] = isset($fail_count[$k]['fail_count']) ? $fail_count[$k]['fail_count'] : 0;
        }
        $countByType = $total_count_arr;
        //来源
        $countByObtain = $this->sys_model_data_sum->getCouponSumByObtain($where1,$where2,$where3);
        $total_count_arr = $countByObtain['total_count'];
        $used_count  = $countByObtain['used_count'];
        $fail_count  = $countByObtain['fail_count'];
        foreach($total_count_arr as $k => &$v ){
            $v['used_count'] = isset($used_count[$k]['used_count']) ? $used_count[$k]['used_count'] : 0;
            $v['fail_count'] = isset($fail_count[$k]['fail_count']) ? $fail_count[$k]['fail_count'] : 0;
        }
        $countByObtain = $total_count_arr;


        //数据处理$res1
        $countByDay = array();
        $currUsed = current($res['usedCount']);
        $currTotal = current($res['totalCount']);
        $usedCountSum = 0;
        $totalCountSum = 0;
        for($time = $firstday; $time <= $lastday; $time += 86400) {
            $date = date('Y-m-d', $time);
            $item = array(
                'used_count' => 0,
                'total_count' => 0,
                'date' => $date,
            );
            if (!empty($currUsed) && $currUsed['date'] == $date) {
                $item['used_count'] = $currUsed['used_count'];
                $usedCountSum += $currUsed['used_count'];
                $currUsed = next($res['usedCount']);
            }
            if (!empty($currTotal) && $currTotal['date'] == $date) {
                $item['total_count'] = $currTotal['total_count'];
                $totalCountSum += $currTotal['total_count'];
                $currTotal = next($res['totalCount']);
            }
            $countByDay[] = $item;
        }
        
        //数据处理$countByType
        $couponTypes = get_coupon_type();
        $countByTypeSum = array();
        foreach ($countByType AS &$r) {
            $countByTypeSum[$r['coupon_type']]['number_sum'] = 0;
            $countByTypeSum[$r['coupon_type']]['number_sum'] += $r['number_sum'];
            $r['coupon_type'] = !empty($couponTypes[$r['coupon_type']]) ? $couponTypes[$r['coupon_type']] : '未知类型';
            $r['available_count'] = $r['total_count'] - $r['used_count'] - $r['fail_count'];
            
        }
        unset($r);
        
        //数据处理$countByObtain
        $couponObtain = get_coupon_obtain();
        $countByObtainSum = array();
        foreach ($countByObtain AS &$r) {
            $countByObtainSum[$r['obtain']]['number_sum'] = 0;
            $countByObtainSum[$r['obtain']]['number_sum'] += $r['number_sum'];
            $r['obtain'] = !empty($couponObtain[$r['obtain']]) ? $couponObtain[$r['obtain']] : '未知来源';
            $r['available_count'] = $r['total_count'] - $r['used_count'] - $r['fail_count'];
        }
        unset($r);

        $this->assign('couponSum', $couponSum);
        
        $this->assign('countByDay', $countByDay);
        $this->assign('usedCountSum', $usedCountSum);
        $this->assign('totalCountSum', $totalCountSum);
        
        $this->assign('countByType', $countByType);
        $this->assign('countByTypeSum', $countByTypeSum);
        
        $this->assign('countByObtainSum', $countByObtainSum);
        $this->assign('countByObtain', $countByObtain);

        $this->assign('filter', $filter);
        $this->assign('list_action', $this->url->link('operation/coupon'));
        $this->assign('action', $this->url->link('operation/coupon/chart'));
        $this->assign('cooperation_action', $this->url->link('operation/coupon/cooperation'));
        $this->assign('region_action', $this->url->link('operation/coupon/region'));

        unset($res, $countByDay, $couponSum);
        $this->response->setOutput($this->load->view('operation/coupon_chart', $this->output));
    }
    
    /**
     * 表格字段
     * @return mixed
     */
    protected function getDataColumns() {
        $this->setDataColumn('用户名称');
        //$this->setDataColumn('优惠券号码');
        $this->setDataColumn('优惠券名称');
        $this->setDataColumn('是否使用');
        $this->setDataColumn('发券人(账号)'); 
        $this->setDataColumn('发券途径');
        $this->setDataColumn('生效时间');
        $this->setDataColumn('失效时间');
        return $this->data_columns;
    }

    /**
     * 添加优惠券
     */
    public function add() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $input = $this->request->post(array('coupon_type', 'number', 'piece', 'valid_time', 'mobiles'));
            $now = time();

            $data = array(
                'used' => 0,
                'add_time' => $now,
                'obtain' => 0,
            );
            // 有效时间
            $valid_time = explode(' 至 ', $input['valid_time']);
            if (is_array($valid_time) && !empty($valid_time)) {
                $data['effective_time'] = strtotime($valid_time[0] . ' 00:00:00');
                $data['failure_time'] = strtotime($valid_time[1] . ' 23:59:59');
            }

            $data['coupon_type'] = $input['coupon_type'];
            $data['number'] = $input['number'];
            if ($input['coupon_type'] == 2) {
                $data['number'] = 1;
            }

            // 优惠券类型
            if ($data['coupon_type'] == 1) {
                $data['left_time'] = $data['number'];
            } else {
                $data['left_time'] = 1;
            }
            //管理员添加
            $data['add_admin_id'] = $this->logic_admin->getId();
            //description
            if($input['coupon_type'] == 1) {
                $data['description'] = '免时优惠券，免' . $data['number'] . '分钟';
            }else if($input['coupon_type'] == 3){
                $data['description'] = '代金券，面额' . $data['number'] . '元';
            }else if($input['coupon_type'] == 4){
                $data['description'] = '折扣券，折扣' . $data['number'] . '折';
            }else{
                $data['description'] = '优惠券';
            }
            // 派发用户
            $mobiles = explode(PHP_EOL, $input['mobiles']);
            foreach ($mobiles as $i => $mobile){
                $mobiles[$i] = trim($mobile);
            }
            if (is_array($mobiles) && !empty($mobiles)) {
                foreach ($mobiles as $mobile) {
                    $condition = array(
                        'mobile' => $mobile
                    );
                    $user = $this->sys_model_user->getUserInfo($condition, 'user_id');
                    if ($user) {
                        $data['user_id'] = $user['user_id'];
                        $data['coupon_code'] = $this->buildCouponCode();
                        // 发放张数
                        for ($i = 1; $i <= $input['piece']; $i++) {
                            $this->sys_model_coupon->addCoupon($data);
                        }
                    }
                }
            }

            $this->session->data['success'] = '添加优惠券成功！';

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
                'log_description' => '添加优惠券：',
                'log_ip' => $this->request->ip_address(),
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);
            
            $filter = array();

            $this->load->controller('common/base/redirect', $this->url->link('operation/coupon', $filter, true));
        }

        $this->assign('title', '优惠券添加');
        $this->getForm();
    }

//    /**
//     * 导出
//     */
//    public function export() {
//        $ids = $this->request->post("selected");
//
//        $condition = array(
//            'coupon_id' => array('in', $ids)
//        );
//        $order = 'add_time DESC';
//        $limit = '';
//
//        $result = $this->sys_model_coupon->getCouponList($condition, $order, $limit);
//        $list = array();
//        if (is_array($result) && !empty($result)) {
//            foreach ($result as $v) {
//                $list[] = array(
//                    'user_name' => $v['user_name'],
//                    'coupon_code' => $v['coupon_code'],
//                    'description' => $v['description'],
//                    'effective_time' => date("Y-m-d",$v['effective_time']),
//                    'failure_time' => date("Y-m-d",$v['failure_time']),
//                );
//            }
//        }
//
//        $data = array(
//            'title' => '优惠券列表',
//            'header' => array(
//                'user_name' => '用户名称',
//                'coupon_code' => '优惠券号码',
//                'description' => '优惠券名称',
//                'effective_time' => '生效时间',
//                'failure_time' => '失效时间',
//            ),
//            'list' => $list
//        );
//        $this->load->controller('common/base/exportExcel', $data);
//    }

    private function getForm() {
        // 编辑时获取已有的数据
        $info = $this->request->post(array('coupon_type', 'number', 'piece', 'valid_time', 'mobiles'));
        $coupon_id = $this->request->get('coupon_id');
        if (isset($this->request->get['coupon_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $condition = array(
                'coupon_id' => $this->request->get['coupon_id']
            );
            $info = $this->sys_model_coupon->getCouponInfo($condition);
        }
        //判断用户是否可以修改发放的数量；
        if($this->logic_admin->hasPermission('operation/fafang')){
            $permission = true;
        }else{
            $permission = false;
        }
        // 发放数量默认为1
        $info['piece'] = empty($info['piece']) ? 1 : $info['piece'];
        $this->assign('data', $info);
        $this->assign('permission', $permission);
        $this->assign('data', $info);
        $this->assign('action', $this->cur_url . '&coupon_id=' . $coupon_id);
        $this->assign('return_action', $this->url->link('operation/coupon'));
        $this->assign('error', $this->error);

        $this->response->setOutput($this->load->view('operation/coupon_form', $this->output));
    }

    /**
     * 验证表单数据
     * @return bool
     */
    private function validateForm() {
        $input = $this->request->post(array('coupon_type', 'valid_time', 'mobiles', 'number', 'piece'));

        foreach ($input as $k => $v) {
            if (empty($v)) {
                $this->error[$k] = '请输入完整！';
            }
        }

        if($input['coupon_type'] == 4){
            if(!($input['number'] > 0 && $input['number'] < 10)){
                $this->error['number'] = '请输入正确的数量！';
            }
        }

        if ($this->error) {
            $this->error['warning'] = '警告: 存在错误，请检查！';
        }
        return !$this->error;
    }

    /**
     * 生成优惠券唯一码
     */
    private function buildCouponCode() {
        $coupon_code = token(32);
        $condition = array(
            'coupon_code' => $coupon_code,
            'used' => 0
        );
        $total = $this->sys_model_coupon->getTotalCoupons($condition);
        if ($total == 0) {
            return $coupon_code;
        } else {
            return self::buildCouponCode();
        }
    }
}
