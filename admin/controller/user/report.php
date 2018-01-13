<?php

/**
 * 统计报表
 * Class ControllerUserReport
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
class ControllerUserReport extends Controller {
    private $cur_url = null;
    private $error = null;

    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载bicycle Model
        $this->load->library('sys_model/orders', true);
        $this->load->library('sys_model/deposit', true);
        $this->assign('lang',$this->language->all());
    }

    /**
     * 月报表
     */
    public function index() {
        // 导出
        $operation = $this->request->get('operation');
        if ($operation == 'export') {
            $filter = $this->request->post(array('search_time', 'city_id','time_type','region_id','user_type'));
            $titles = $monthData = $dayData = $yearData = array();
            if (isset($this->request->get['page'])) {
                $page = (int)$this->request->get['page'];
            } else {
                $page = 1;
            }
            if (!empty($filter['search_time']) && strstr($filter['search_time'], ' 至 ')){
                list($startMonth, $endMonth) = explode(' 至 ', $filter['search_time']);
                //日
                $startDay = strtotime($startMonth);
                $endDay = strtotime($endMonth);
                $dayTotal = $this->calculateDays($startDay, $endDay) + 1;
                //年
                $startYear = strtotime($startMonth.'-01-01');
                $endYear = strtotime($endMonth.'-12-31');
                $yearTotal = $this->calculateYears($startYear, $endYear) + 1;
                //月
                $startMonth = strtotime($startMonth);
                $endMonth = strtotime($endMonth.'+1 month -1 day');
                $monthTotal = $this->calculateMonths($startMonth, $endMonth) + 1;
                

                if($filter['time_type']==1){
                    $total = $yearTotal;
                    $rows = 3;
                    $offset = ($page - 1) * $rows;
                    $max = $total;
                    if ($yearTotal > 1) { 
                        // 查询大于1年
                        if ($rows + $offset < $total) {
                            $max = $rows + $offset;
                        }
                        for ($i = $offset; $i < $max; $i++) {
                            $curYearTimeHorizon = strtotime(date('Y-01-01', $startYear) . '+' . $i . ' years');
                            $titles[] = date('Y年'.$this->language->get('t32'), $curYearTimeHorizon);
                            $thisYearTimeHorizon = array(
                                array('egt', $curYearTimeHorizon),
                                array('elt', bcadd(86399,strtotime(date('Y-12-31', $curYearTimeHorizon))))
                            );
                            $yearData[] = $this->getFinanceData($thisYearTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        }
                        $title = date('Y年', $startMonth) . '-' . date('Y年', $endMonth) . $this->language->get('t35');
                    }else{
                        //查询今年
                        $thisYearTimeHorizon = array(
                        array('egt', strtotime(date('Y-01-01', $startYear))),
                        array('elt', bcadd(86399, strtotime(date('Y-12-31', $startYear))))
                        );
                        $titles[] = date('Y年'.$this->language->get('t32'), $startYear);
                        $yearData[] = $this->getFinanceData($thisYearTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        $title = date('Y年m月', $startMonth) . $this->language->get('t35');
                    }
                }else if($filter['time_type']==2){
                    $total = $monthTotal;
                    $rows = 3;
                    $offset = ($page - 1) * $rows;
                    $max = $total;
                    if ($monthTotal > 1) { 
                        // 查询大于1个月
                    if ($rows + $offset < $total) {
                        $max = $rows + $offset;
                    }
                        for ($i = $offset; $i < $max; $i++) {
                            $curMonthTimeHorizon = strtotime(date('Y-m', $startMonth) . '+' . $i . ' months');
                            $titles[] = date('Y年n月'.$this->language->get('t32'), $curMonthTimeHorizon);
                            $thisMonthTimeHorizon = array(
                                array('egt', $curMonthTimeHorizon),
                                array('elt', bcadd(86399, strtotime(date('Y-m-t', $curMonthTimeHorizon))))
                            );
                            $monthData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        }
                        $title = date('Y年m月', $startMonth) . '-' . date('Y年m月', $endMonth) . $this->language->get('t35');
                    } else {      
                        //本月
                        $thisMonthTimeHorizon = array(
                            array('egt', strtotime(date('Y-m-01', $startMonth))),
                            array('elt', bcadd(86399, strtotime(date('Y-m-t', $startMonth))))
                        );
                        $titles[] = date('Y年n月'.$this->language->get('t32'), $startMonth);
                        $monthData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        $title = date('Y年m月', $startMonth) . $this->language->get('t35');
                    }
                }else if($filter['time_type']==3){
                    $total = $dayTotal;
                    $rows = 3;
                    $offset = ($page - 1) * $rows;
                    $max = $total;
                    if ($dayTotal > 1) { // 查询大于1天
                        if ($rows + $offset < $total) {
                            $max = $rows + $offset;
                        }
                        for ($i = $offset; $i < $max; $i++) {
                            $curMonthTimeHorizon = strtotime(date('Y-m-d', $startDay) . '+' . $i . ' days');
                            $titles[] = date('Y年n月j日'.$this->language->get('t32'), $curMonthTimeHorizon);
                            $thisMonthTimeHorizon = array(
                                array('egt', $curMonthTimeHorizon),
                                array('elt', bcadd(86399, $curMonthTimeHorizon))
                            );
                            $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        }
                        $title = date('Y年m月d日', $startMonth) . '-' . date('Y年m月d日', $endMonth) . $this->language->get('t35');
                    } else {   
                       // 查询1天的数据
                        $thisMonthTimeHorizon = array(
                            array('egt', strtotime(date('Y-m-d', $startDay))),
                            array('elt', bcadd(86399, strtotime(date('Y-m-d', $startDay))))
                        );
                        $titles[] = date('Y年n月j日'.$this->language->get('t32'), $startDay);
                        $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        $title = date('Y年m月d日', $startMonth) . $this->language->get('t35');
                    }
                    /////////没选择时间区间/////
                }else{
                    $total = $dayTotal;
                    $rows = 3;
                    $offset = ($page - 1) * $rows;
                    $max = $total;
                    if ($dayTotal > 1) { // 查询大于1天
                        if ($rows + $offset < $total) {
                            $max = $rows + $offset;
                        }
                        for ($i = $offset; $i < $max; $i++) {
                            $curMonthTimeHorizon = strtotime(date('Y-m-d', $startDay) . '+' . $i . ' days');
                            $titles[] = date('Y年n月j日'.$this->language->get('t32'), $curMonthTimeHorizon);
                            $thisMonthTimeHorizon = array(
                                array('egt', $curMonthTimeHorizon),
                                array('elt', bcadd(86399, $curMonthTimeHorizon))
                            );
                            $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        }
                        $title = date('Y年m月d日', $startMonth) . '-' . date('Y年m月d日', $endMonth) . $this->language->get('t35');
                    } else {   
                       // 查询1天的数据
                        $thisMonthTimeHorizon = array(
                            array('egt', strtotime(date('Y-m-d', $startDay))),
                            array('elt', bcadd(86399, strtotime(date('Y-m-d', $startDay))))
                        );
                        $titles[] = date('Y年n月j日'.$this->language->get('t32'), $startDay);
                        $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        $title = date('Y年m月d日', $startMonth) . $this->language->get('t35');
                    }
                }
            }else{
                    $total = 1;
                    $rows = 3;
                    $offset = ($page - 1) * $rows;
                    $max = $total;
                if ($filter['time_type']==1) {
                    //查询本年
                    $startYear = $endYear = strtotime(date('Y'));
                    $thisYearTimeHorizon = array(
                    array('egt', strtotime(date('Y-01-01', $startYear))),
                    array('elt', bcadd(86399, strtotime(date('Y-12-31', $startYear))))
                    );
                    $titles[] = date('Y年'.$this->language->get('t32'), $startYear);
                    $yearData[] = $this->getFinanceData($thisYearTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                    $title = date('Y年', $startMonth) . $this->language->get('t35');
                }else if($filter['time_type']==2){
                    //查询本月
                    $startMonth = $endMonth = strtotime(date('Y-m'));
                    $thisMonthTimeHorizon = array(
                            array('egt', strtotime(date('Y-m-01', $startMonth))),
                            array('elt', bcadd(86399, strtotime(date('Y-m-t', $startMonth))))
                        );
                        $titles[] = date('Y年n月'.$this->language->get('t32'), $startMonth);
                        $monthData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        $title = date('Y年m月', $startMonth) . $this->language->get('t35');
                }else if($filter['time_type']==3){
                    //查询本日
                    $startDay = $endDay = strtotime(date('Y-m-d'));
                    $thisMonthTimeHorizon = array(
                            array('egt', strtotime(date('Y-m-d', $startDay))),
                            array('elt', bcadd(86399, strtotime(date('Y-m-d', $startDay))))
                        );
                        $titles[] = date('Y年n月j日'.$this->language->get('t32'), $startDay);
                        $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        $title = date('Y年m月d日', $startMonth) . $this->language->get('t35');
                }else{
                    $total = 1;
                    $rows = 3;
                    $offset = ($page - 1) * $rows;
                    $max = $total;
                    // 本日
                    $startDay = $endDay = strtotime(date('Y-m-d'));
                    $dayTotal = 1;
                    $thisDayTimeHorizon = array(
                        array('egt', strtotime(date('Y-m-d', $startDay))),
                        array('elt', bcadd(86399, strtotime(date('Y-m-d', $startDay))))
                    );
                    $titles[] = $this->language->get('t29');
                    $dayData[] = $this->getFinanceData($thisDayTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                    //本月
                    $monthTotal = 1;
                    $startMonth = $endMonth = strtotime(date('Y-m'));
                    $thisMonthTimeHorizon = array(
                        array('egt', strtotime(date('Y-m-01', $startMonth))),
                        array('elt', bcadd(86399, strtotime(date('Y-m-t', $startMonth))))
                    );
                    
                    $titles[] = $this->language->get('t30');
                    $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                    // 本年
                    $startYear = $endYear = strtotime(date('Y'));
                    $yearTotal = 1;
                    $thisYearTimeHorizon = array(
                        array('egt', strtotime(date('Y-01-01', $startYear))),
                        array('elt', bcadd(86399,strtotime(date('Y-12-31', $startYear))))
                    );
                    
                    $titles[] = $this->language->get('t31');
                    $dayData[] = $this->getFinanceData($thisYearTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);

                    $title = $this->language->get('t37');

                }
            }

            

            $rowNames = array(
                'total' => $this->language->get('t16'),
                'deposit_net' => $this->language->get('t17'),
                'deposit_recharge' => $this->language->get('t18'),
                'deposit_refund' => $this->language->get('t19'),
                'balance_net' => $this->language->get('t20'),
                'balance_recharge' => $this->language->get('t21'),
                'balance_refund' => $this->language->get('t22'),
                'order_amount' => $this->language->get('t23'),
                'order_refund' => $this->language->get('t24'),
                'reginster_net'      => $this->language->get('t25'),
                'reginster_recharge' => $this->language->get('t26'),
                'reginster_refund'   => $this->language->get('t27'),
                'coupon_num' => $this->language->get('t28'),
            );
            $list = array();
            foreach ($rowNames as $key => $rowName) {
                if ($filter['time_type']==1) {
                    $temp = array_column($yearData, $key);
                }else if($filter['time_type']==2){
                    $temp = array_column($monthData, $key);
                }else if($filter['time_type']==3){
                    $temp = array_column($dayData, $key);
                }else{
                    $temp = array_column($dayData, $key);   
                }
                array_unshift($temp, $rowName);
                $list[] = $temp;
            }
            array_unshift($titles, $this->language->get('t15'));

            $data = array(
                'title' => $title,
                'header' => $titles,
                'list' => $list
            );
            $this->load->controller('common/base/exportExcel', $data);
        } else {
            /** 页面显示 **/
           $filter = $this->request->get(array('search_time','time_type','user_type','city_id','region_id'));
            $titles = $monthData = $dayData = $yearData = array();
            if (isset($this->request->get['page'])) {
                $page = (int)$this->request->get['page'];
            } else {
                $page = 1;
            }
            if (!empty($filter['search_time']) && strstr($filter['search_time'], ' 至 ')){
                list($startMonth, $endMonth) = explode(' 至 ', $filter['search_time']);
                //日
                $startDay = strtotime($startMonth);
                $endDay = strtotime($endMonth);
                $dayTotal = $this->calculateDays($startDay, $endDay) + 1;
                //年
                $startYear = strtotime($startMonth.'-01-01');
                $endYear = strtotime($endMonth.'-12-31');
                $yearTotal = $this->calculateYears($startYear, $endYear) + 1;
                //月
                $startMonth = strtotime($startMonth);
                $endMonth = strtotime($endMonth.'+1 month -1 day');
                $monthTotal = $this->calculateMonths($startMonth, $endMonth) + 1;
                

                if($filter['time_type']==1){
                    $total = $yearTotal;
                    $rows = 3;
                    $offset = ($page - 1) * $rows;
                    $max = $total;
                    if ($yearTotal > 1) { 
                        // 查询大于1年
                        if ($rows + $offset < $total) {
                            $max = $rows + $offset;
                        }
                        for ($i = $offset; $i < $max; $i++) {
                            $curYearTimeHorizon = strtotime(date('Y-01-01', $startYear) . '+' . $i . ' years');
                            $titles[] = date('Y年'.$this->language->get('t32'), $curYearTimeHorizon);
                            $thisYearTimeHorizon = array(
                                array('egt', $curYearTimeHorizon),
                                array('elt', bcadd(86399,strtotime(date('Y-12-31', $curYearTimeHorizon))))
                            );
                            $yearData[] = $this->getFinanceData($thisYearTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        }
                    }else{
                        //查询今年
                        $thisYearTimeHorizon = array(
                        array('egt', strtotime(date('Y-01-01', $startYear))),
                        array('elt', bcadd(86399, strtotime(date('Y-12-31', $startYear))))
                        );
                        $titles[] = date('Y年'.$this->language->get('t32'), $startYear);
                        $yearData[] = $this->getFinanceData($thisYearTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                    }
                }else if($filter['time_type']==2){
                    $total = $monthTotal;
                    $rows = 3;
                    $offset = ($page - 1) * $rows;
                    $max = $total;
                    if ($monthTotal > 1) { 
                        // 查询大于1个月
                    if ($rows + $offset < $total) {
                        $max = $rows + $offset;
                    }
                        for ($i = $offset; $i < $max; $i++) {
                            $curMonthTimeHorizon = strtotime(date('Y-m', $startMonth) . '+' . $i . ' months');
                            $titles[] = date('Y年n月'.$this->language->get('t32'), $curMonthTimeHorizon);
                            $thisMonthTimeHorizon = array(
                                array('egt', $curMonthTimeHorizon),
                                array('elt', bcadd(86399, strtotime(date('Y-m-t', $curMonthTimeHorizon))))
                            );
                            $monthData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        }
                    } else {      
                        //本月
                        $thisMonthTimeHorizon = array(
                            array('egt', strtotime(date('Y-m-01', $startMonth))),
                            array('elt', bcadd(86399, strtotime(date('Y-m-t', $startMonth))))
                        );
                        $titles[] = date('Y年n月'.$this->language->get('t32'), $startMonth);
                        $monthData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                    }
                }else if($filter['time_type']==3){
                    $total = $dayTotal;
                    $rows = 3;
                    $offset = ($page - 1) * $rows;
                    $max = $total;
                    if ($dayTotal > 1) { // 查询大于1天
                        if ($rows + $offset < $total) {
                            $max = $rows + $offset;
                        }
                        for ($i = $offset; $i < $max; $i++) {
                            $curMonthTimeHorizon = strtotime(date('Y-m-d', $startDay) . '+' . $i . ' days');
                            $titles[] = date('Y年n月j日'.$this->language->get('t32'), $curMonthTimeHorizon);
                            $thisMonthTimeHorizon = array(
                                array('egt', $curMonthTimeHorizon),
                                array('elt', bcadd(86399, $curMonthTimeHorizon))
                            );
                            $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        }
                    } else {   
                       // 查询1天的数据
                        $thisMonthTimeHorizon = array(
                            array('egt', strtotime(date('Y-m-d', $startDay))),
                            array('elt', bcadd(86399, strtotime(date('Y-m-d', $startDay))))
                        );
                        $titles[] = date('Y年n月j日'.$this->language->get('t32'), $startDay);
                        $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                    }
                }else{
                    $total = $dayTotal;
                    $rows = 3;
                    $offset = ($page - 1) * $rows;
                    $max = $total;
                    if ($dayTotal > 1) { // 查询大于1天
                        if ($rows + $offset < $total) {
                            $max = $rows + $offset;
                        }
                        for ($i = $offset; $i < $max; $i++) {
                            $curMonthTimeHorizon = strtotime(date('Y-m-d', $startDay) . '+' . $i . ' days');
                            $titles[] = date('Y年n月j日'.$this->language->get('t32'), $curMonthTimeHorizon);
                            $thisMonthTimeHorizon = array(
                                array('egt', $curMonthTimeHorizon),
                                array('elt', bcadd(86399, $curMonthTimeHorizon))
                            );
                            $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                        }
                    } else {   
                       // 查询1天的数据
                        $thisMonthTimeHorizon = array(
                            array('egt', strtotime(date('Y-m-d', $startDay))),
                            array('elt', bcadd(86399, strtotime(date('Y-m-d', $startDay))))
                        );
                        $titles[] = date('Y年n月j日'.$this->language->get('t32'), $startDay);
                        $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                    }
                }
            }else{
                    $total = 1;
                    $rows = 3;
                    $offset = ($page - 1) * $rows;
                    $max = $total;
                if ($filter['time_type']==1) {
                    //查询本年
                    $startYear = $endYear = strtotime(date('Y'));
                    $thisYearTimeHorizon = array(
                    array('egt', strtotime(date('Y-01-01', $startYear))),
                    array('elt', bcadd(86399, strtotime(date('Y-12-31', $startYear))))
                    );
                    $titles[] = date('Y年'.$this->language->get('t32'), $startYear);
                    $yearData[] = $this->getFinanceData($thisYearTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                }else if($filter['time_type']==2){
                    //查询本月
                    $startMonth = $endMonth = strtotime(date('Y-m'));
                    $thisMonthTimeHorizon = array(
                            array('egt', strtotime(date('Y-m-01', $startMonth))),
                            array('elt', bcadd(86399, strtotime(date('Y-m-t', $startMonth))))
                        );
                        $titles[] = date('Y年n月'.$this->language->get('t32'), $startMonth);
                        $monthData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                }else if($filter['time_type']==3){
                    //查询本日
                    $startDay = $endDay = strtotime(date('Y-m-d'));
                    $thisMonthTimeHorizon = array(
                            array('egt', strtotime(date('Y-m-d', $startDay))),
                            array('elt', bcadd(86399, strtotime(date('Y-m-d', $startDay))))
                        );
                        $titles[] = date('Y年n月j日'.$this->language->get('t32'), $startDay);
                        $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                }else{
                    $total = 1;
                    $rows = 3;
                    $offset = ($page - 1) * $rows;
                    $max = $total;
                    // 本日
                    $startDay = $endDay = strtotime(date('Y-m-d'));
                    $dayTotal = 1;
                    $thisDayTimeHorizon = array(
                        array('egt', strtotime(date('Y-m-d', $startDay))),
                        array('elt', bcadd(86399, strtotime(date('Y-m-d', $startDay))))
                    );
                    $titles[] = $this->language->get('t29');
                    $dayData[] = $this->getFinanceData($thisDayTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                    //本月
                    $monthTotal = 1;
                    $startMonth = $endMonth = strtotime(date('Y-m'));
                    $thisMonthTimeHorizon = array(
                        array('egt', strtotime(date('Y-m-01', $startMonth))),
                        array('elt', bcadd(86399, strtotime(date('Y-m-t', $startMonth))))
                    );
                    
                    $titles[] = $this->language->get('t30');
                    $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                    // 本年
                    $startYear = $endYear = strtotime(date('Y'));
                    $yearTotal = 1;
                    $thisYearTimeHorizon = array(
                        array('egt', strtotime(date('Y-01-01', $startYear))),
                        array('elt', bcadd(86399,strtotime(date('Y-12-31', $startYear))))
                    );
                    
                    $titles[] = $this->language->get('t31');
                    $dayData[] = $this->getFinanceData($thisYearTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);
                }
            }


            

            $rowNames = array(
                'total' => $this->language->get('t16'),
                'deposit_net' => $this->language->get('t17'),
                'deposit_recharge' => $this->language->get('t18'),
                'deposit_refund' => $this->language->get('t19'),
                'balance_net' => $this->language->get('t20'),
                'balance_recharge' => $this->language->get('t21'),
                'balance_refund' => $this->language->get('t22'),
                'order_amount' => $this->language->get('t23'),
                'order_refund' => $this->language->get('t24'),
                'reginster_net'      => $this->language->get('t25'),
                'reginster_recharge' => $this->language->get('t26'),
                'reginster_refund'   => $this->language->get('t27'),
                'coupon_num' => $this->language->get('t28'),
            );
            $list = array();
            foreach ($rowNames as $key => $rowName) {
                if ($filter['time_type']==1) {
                    $temp = array_column($yearData, $key);
                }else if($filter['time_type']==2){
                    $temp = array_column($monthData, $key);
                }else if($filter['time_type']==3){
                    $temp = array_column($dayData, $key);
                }else{
                    $temp = array_column($dayData, $key);   
                }
                
                array_unshift($temp, $rowName);
                $list[] = $temp;
            }
            
            
            // 分页
            $pagination = new Pagination();
            $pagination->total = $total;
            $pagination->page = $page;
            $pagination->page_size = $rows;
            $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
            $pagination = $pagination->render();
            $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

            // // 合伙人列表
            // $this->load->library('sys_model/cooperator', true);
            // $condition = array();
            // $order = '';
            // $limit = '';
            // $field = 'cooperator.cooperator_id, cooperator.cooperator_name';
            // $cooperators = $this->sys_model_cooperator->getCooperatorList($condition, $order, $limit, $field);

            //根据区域获取城市
            $this->load->library('sys_model/region');
            $this->load->library('sys_model/city');
            $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
            foreach ($filter_regions as $key2 => $val2) {
                $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
            }
            $user_type = array(
                '0' => $this->language->get('t6'),
                '1' => $this->language->get('t7')
            );
            $this->assign('filter_regions', $filter_regions);
            $this->assign('time_type',get_time_type());
            $this->assign('user_types', $user_type);
            $this->assign('list', $list);
            $this->assign('pagination', $pagination);
            $this->assign('results', $results);
            $this->assign('titles', $titles);
            $this->assign('filter', $filter);
            $this->assign('action', $this->cur_url);
            $this->assign('day_report_action', $this->url->link('user/report/day'));
            $this->assign('summary_report_action', $this->url->link('user/report/summary'));
            $this->assign('export_action', htmlspecialchars_decode($this->url->link('user/report', 'operation=export')));

            $this->response->setOutput($this->load->view('user/report_list', $this->output));
        }
    }

    /**
     * 日报表
     */
    public function day() {
        // 导出
        $operation = $this->request->get('operation');
        if ($operation == 'export') {
            $filter = $this->request->post(array('search_time', 'cooperator_id','time_type','user_type'));
            if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                $startDay = strtotime($startDay);
                $endDay = strtotime($endDay);
                // 天数
                $dayTotal = $this->calculateDays($startDay, $endDay) + 1;
            } else {    // 默认当天
                $startDay = $endDay = strtotime(date('Y-m-d'));
                // 天数
                $dayTotal = 1;
            }
            $titles = $dayData = array();
            if (isset($this->request->get['page'])) {
                $page = (int)$this->request->get['page'];
            } else {
                $page = 1;
            }
            if ($dayTotal > 1) { // 查询大于1天
                $title = date('Y年n月j日', $startDay) . '-' . date('Y年n月j日', $endDay) . '报表';
                for ($i = 0; $i < $dayTotal; $i++) {
                    $curMonthTimeHorizon = strtotime(date('Y-m-d', $startDay) . '+' . $i . ' days');
                    $titles[] = date('Y年n月j日累计金额', $curMonthTimeHorizon);
                    $thisMonthTimeHorizon = array(
                        array('egt', $curMonthTimeHorizon),
                        array('elt', bcadd(86399, $curMonthTimeHorizon))
                    );
                    $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['cooperator_id']);
                }

            } else {      // 查询1天的数据
                $title = date('Y年n月j日', $startDay) . '报表';
                $thisMonthTimeHorizon = array(
                    array('egt', strtotime(date('Y-m-d', $startDay))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d', $startDay))))
                );
                $titles[] = date('Y年n月j日累计金额', $startDay);
                $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['cooperator_id']);
            }

            $rowNames = array(
                'total' => '现金收入',
                'deposit_net' => '押金净值',
                'deposit_recharge' => '押金充值',
                'deposit_refund' => '押金退回',
                'balance_net' => '余额净值',
                'balance_recharge' => '余额充值',
                'balance_refund' => '余额退回',
                'order_amount' => '消费金额',
                'order_refund' => '消费退回',
                'reginster_net'      => '充值卡净值',
                'reginster_recharge' => '充值卡充值',
                'reginster_refund'   => '充值卡退回',
                'coupon_num' => '优惠劵',
            );
            $list = array();
            foreach ($rowNames as $key => $rowName) {
                $temp = array_column($dayData, $key);
                array_unshift($temp, $rowName);
                $list[] = $temp;
            }
            array_unshift($titles, '项目');

            $data = array(
                'title' => $title,
                'header' => $titles,
                'list' => $list
            );
            $this->load->controller('common/base/exportExcel', $data);
        } else {
            /** 页面显示 **/
            $filter = $this->request->get(array('search_time', 'cooperator_id','time_type','user_type','region_id','city_id'));
            if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                $startDay = strtotime($startDay);
                $endDay = strtotime($endDay);
                // 天数
                $dayTotal = $this->calculateDays($startDay, $endDay) + 1;
            } else {    // 默认当天
                $startDay = $endDay = strtotime(date('Y-m-d'));
                // 天数
                $dayTotal = 1;
            }
            $titles = $dayData = array();
            if (isset($this->request->get['page'])) {
                $page = (int)$this->request->get['page'];
            } else {
                $page = 1;
            }
            $total = $dayTotal;
            $rows = 3;
            $offset = ($page - 1) * $rows;
            $max = $total;
            if ($dayTotal > 1) { // 查询大于1天
                if ($rows + $offset < $total) {
                    $max = $rows + $offset;
                }
                for ($i = $offset; $i < $max; $i++) {
                    $curMonthTimeHorizon = strtotime(date('Y-m-d', $startDay) . '+' . $i . ' days');
                    $titles[] = date('Y年n月j日累计金额', $curMonthTimeHorizon);
                    $thisMonthTimeHorizon = array(
                        array('egt', $curMonthTimeHorizon),
                        array('elt', bcadd(86399, $curMonthTimeHorizon))
                    );
                    $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['cooperator_id']);
                }

            } else {      // 查询1天的数据
                $thisMonthTimeHorizon = array(
                    array('egt', strtotime(date('Y-m-d', $startDay))),
                    array('elt', bcadd(86399, strtotime(date('Y-m-d', $startDay))))
                );
                $titles[] = date('Y年n月j日累计金额', $startDay);
                $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['cooperator_id']);
            }

            $rowNames = array(
                'total' => '现金收入',
                'deposit_net' => '押金净值',
                'deposit_recharge' => '押金充值',
                'deposit_refund' => '押金退回',
                'balance_net' => '余额净值',
                'balance_recharge' => '余额充值',
                'balance_refund' => '余额退回',
                'order_amount' => '消费金额',
                'order_refund' => '消费退回',
                'reginster_net'      => '充值卡净值',
                'reginster_recharge' => '充值卡充值',
                'reginster_refund'   => '充值卡退回',
                'coupon_num' => '优惠劵',
            );
            $list = array();
            foreach ($rowNames as $key => $rowName) {
                $temp = array_column($dayData, $key);
                array_unshift($temp, $rowName);
                $list[] = $temp;
            }

            // 分页
            $pagination = new Pagination();
            $pagination->total = $total;
            $pagination->page = $page;
            $pagination->page_size = $rows;
            $pagination->url = $this->cur_url . '&amp;page={page}' . '&amp;' . str_replace('&', '&amp;', http_build_query($filter));
            $pagination = $pagination->render();
            $results = sprintf($this->language->get('text_pagination'), ($total) ? $offset + 1 : 0, ($offset > ($total - $rows)) ? $total : ($offset + $rows), $total, ceil($total / $rows));

            // 合伙人列表
            $this->load->library('sys_model/cooperator', true);
            $condition = array();
            $order = '';
            $limit = '';
            $field = 'cooperator.cooperator_id, cooperator.cooperator_name';
            $cooperators = $this->sys_model_cooperator->getCooperatorList($condition, $order, $limit, $field);

             //根据区域获取城市
            $this->load->library('sys_model/region');
            $this->load->library('sys_model/city');
            $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
            foreach ($filter_regions as $key2 => $val2) {
                $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
            }

            $this->assign('filter_regions', $filter_regions);
            $this->assign('time_type',get_time_type());
            $this->assign('user_types', array('0'=>"App用户",'1'=>'刷卡用户'));
            $this->assign('list', $list);
            $this->assign('pagination', $pagination);
            $this->assign('cooperators', $cooperators);
            $this->assign('results', $results);
            $this->assign('titles', $titles);
            $this->assign('filter', $filter);
            $this->assign('action', $this->cur_url);
            $this->assign('month_report_action', $this->url->link('user/report'));
            $this->assign('summary_report_action', $this->url->link('user/report/summary'));
            $this->assign('export_action', htmlspecialchars_decode($this->url->link('user/report/day', 'operation=export')));

            $this->response->setOutput($this->load->view('user/report_day_list', $this->output));
        }
    }

    /**
     * 总报表
     */
    public function summary() {
        // 导出
        $operation = $this->request->get('operation');
        if ($operation == 'export') {
            $filter = $this->request->post(array('search_time','time_type','user_type','city_id','region_id'));
            $titles = $dayData = array();
            if ($filter['time_type']==1) {
                if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                    list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                    $startDay = strtotime($startDay.'-01-01');
                    $endDay = bcadd(86399,strtotime($endDay.'-12-31'));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年', $startDay), date('Y年', $endDay));
                }else{
                    $startDay = strtotime(date('Y-01-01'));
                    $endDay = bcadd(86399,strtotime(date('Y-12-31')));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年', $startDay), date('Y年', $endDay));
                }
            }else if ($filter['time_type']==2) {
                if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                    list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                    $startDay = strtotime($startDay);
                    $endDay = bcadd(86399,strtotime($endDay.'+1 month -1 day'));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年n月', $startDay), date('Y年n月', $endDay));
                }else{
                    $startDay = strtotime(date('Y-m'));
                    $endDay = bcadd(86399,strtotime(date('Y-m-t')));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年n月', $startDay), date('Y年n月', $endDay));
                }
            }else if($filter['time_type']==3){
                if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                    list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                    $startDay = strtotime($startDay);
                    $endDay = bcadd(86399,strtotime($endDay));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年n月d日', $startDay), date('Y年n月d日', $endDay)); 
                }else{
                    $startDay = strtotime(date('Y-m-d'));
                    $endDay = bcadd(86399,strtotime(date('Y-m-d')));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年n月d日', $startDay), date('Y年n月d日', $endDay));
                }
            }else{
                if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                    list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                    $startDay = strtotime($startDay);
                    $endDay = bcadd(86399,strtotime($endDay));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年n月d日', $startDay), date('Y年n月d日', $endDay));
                }else{
                    $titles[] = $this->language->get('t33');
                $thisMonthTimeHorizon = array();
                }
            }


            $title = $this->language->get('t34');
            $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['city_id'],$filter['region_id'],$filter['user_type']);

            $rowNames = array(
                'total' => $this->language->get('t16'),
                'deposit_net' => $this->language->get('t17'),
                'deposit_recharge' => $this->language->get('t18'),
                'deposit_refund' => $this->language->get('t19'),
                'balance_net' => $this->language->get('t20'),
                'balance_recharge' => $this->language->get('t21'),
                'balance_refund' => $this->language->get('t22'),
                'order_amount' => $this->language->get('t23'),
                'order_refund' => $this->language->get('t24'),
                'reginster_net'      => $this->language->get('t25'),
                'reginster_recharge' => $this->language->get('t26'),
                'reginster_refund'   => $this->language->get('t27'),
                'coupon_num' => $this->language->get('t28'),
            );
            $list = array();
            foreach ($rowNames as $key => $rowName) {
                $temp = array_column($dayData, $key);
                array_unshift($temp, $rowName);
                $list[] = $temp;
            }
            array_unshift($titles, $this->language->get('t15'));

            $data = array(
                'title' => $title,
                'header' => $titles,
                'list' => $list
            );
            $this->load->controller('common/base/exportExcel', $data);
        } else {
            /** 页面显示 **/
            $filter = $this->request->get(array('search_time','time_type','user_type','city_id','region_id'));
            $titles = $dayData = array();
            if ($filter['time_type']==1) {
                if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                    list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                    $startDay = strtotime($startDay.'-01-01');
                    $endDay = bcadd(86399,strtotime($endDay.'-12-31'));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年', $startDay), date('Y年', $endDay));
                }else{
                    $startDay = strtotime(date('Y-01-01'));
                    $endDay = bcadd(86399,strtotime(date('Y-12-31')));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年', $startDay), date('Y年', $endDay));
                }
            }else if ($filter['time_type']==2) {
                if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                    list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                    $startDay = strtotime($startDay);
                    $endDay = bcadd(86399,strtotime($endDay.'+1 month -1 day'));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年n月', $startDay), date('Y年n月', $endDay));
                }else{
                    $startDay = strtotime(date('Y-m'));
                    $endDay = bcadd(86399,strtotime(date('Y-m-t')));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年n月', $startDay), date('Y年n月', $endDay));
                }
            }else if($filter['time_type']==3){
                if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                    list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                    $startDay = strtotime($startDay);
                    $endDay = bcadd(86399,strtotime($endDay));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年n月d日', $startDay), date('Y年n月d日', $endDay)); 
                }else{
                    $startDay = strtotime(date('Y-m-d'));
                    $endDay = bcadd(86399,strtotime(date('Y-m-d')));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年n月d日', $startDay), date('Y年n月d日', $endDay));
                }
            }else{
                if (!empty($filter['search_time']) && strstr($filter['search_time'], '至')) {
                    list($startDay, $endDay) = explode(' 至 ', $filter['search_time']);
                    $startDay = strtotime($startDay);
                    $endDay = bcadd(86399,strtotime($endDay));
                    $thisMonthTimeHorizon = array(
                        array('egt', $startDay),
                        array('elt', $endDay)
                    );
                    $titles[] = sprintf($this->language->get('t32').'（%s-%s）', date('Y年n月d日', $startDay), date('Y年n月d日', $endDay));
                }else{
                    $titles[] = $this->language->get('t33');
                $thisMonthTimeHorizon = array();
                }
            }

            $dayData[] = $this->getFinanceData($thisMonthTimeHorizon, $filter['region_id'],$filter['city_id'],$filter['user_type']);


            $rowNames = array(
                'total' => $this->language->get('t16'),
                'deposit_net' => $this->language->get('t17'),
                'deposit_recharge' => $this->language->get('t18'),
                'deposit_refund' => $this->language->get('t19'),
                'balance_net' => $this->language->get('t20'),
                'balance_recharge' => $this->language->get('t21'),
                'balance_refund' => $this->language->get('t22'),
                'order_amount' => $this->language->get('t23'),
                'order_refund' => $this->language->get('t24'),
                'reginster_net'      => $this->language->get('t25'),
                'reginster_recharge' => $this->language->get('t26'),
                'reginster_refund'   => $this->language->get('t27'),
                'coupon_num' => $this->language->get('t28'),
            );
            $list = array();
            foreach ($rowNames as $key => $rowName) {
                $temp = array_column($dayData, $key);
                array_unshift($temp, $rowName);
                $list[] = $temp;
            }

            // 合伙人列表
            $this->load->library('sys_model/cooperator', true);
            $condition = array();
            $order = '';
            $limit = '';
            $field = 'cooperator.cooperator_id, cooperator.cooperator_name';
            $cooperators = $this->sys_model_cooperator->getCooperatorList($condition, $order, $limit, $field);


             //根据区域获取城市
            $this->load->library('sys_model/region');
            $this->load->library('sys_model/city');
            $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
            foreach ($filter_regions as $key2 => $val2) {
                $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
            }
            $user_type = array(
                '0' => $this->language->get('t6'),
                '1' => $this->language->get('t7')
            );
            $this->assign('filter_regions', $filter_regions);
            $this->assign('time_type',get_time_type());
            $this->assign('user_types', $user_type);
            $this->assign('list', $list);
            $this->assign('cooperators', $cooperators);
            $this->assign('titles', $titles);
            $this->assign('filter', $filter);
            $this->assign('action', $this->cur_url);
            $this->assign('month_report_action', $this->url->link('user/report'));
            $this->assign('day_report_action', $this->url->link('user/report/day'));
            $this->assign('export_action', htmlspecialchars_decode($this->url->link('user/report/summary', 'operation=export')));

            $this->response->setOutput($this->load->view('user/report_summary_list', $this->output));
        }
    }

    /**
     * 获取财务数据
     */
    private function getFinanceData($timeHorizon = array(), $region_id = 0,$city_id=0,$user_type=0) {
        // 初始化数据
        // add vincent:2017-08-16 增加充值卡汇总数据
        $data = array(
            'total' => 0,
            'deposit_net' => 0,
            'deposit_recharge' => 0,
            'deposit_refund' => 0,
            'balance_net' => 0,
            'balance_recharge' => 0,
            'balance_refund' => 0,
            'order_amount' => 0,
            'order_refund' => 0,
            'reginster_net'      => 0,
            'reginster_recharge' => 0,
            'reginster_refund'   => 0,
            'coupon_num' => 0,
        );

            /**
             * 押金
             */
            // 押金充值
            $fields = 'SUM(pdr_amount) as amount';
            $condition = array(
                'pdr_type' => '1',
                'pdr_payment_state' => array('in', array(1, -1, -2)),
            );
            if (!empty($timeHorizon)) {
                $condition['pdr_payment_time'] = $timeHorizon;
            }
            if (is_numeric($city_id)) {
            $condition['user.city_id'] = (int)$city_id;
            }
            if (is_numeric($region_id)) {
                    $condition['user.region_id'] = (int)$region_id;
            }
            if (is_numeric($user_type)) {
                    $condition['user_type'] = (int)$user_type;
            }
            $join = array(
                'user' => 'user.user_id=deposit_recharge.pdr_user_id',
                'region' => 'region.region_id=user.region_id',
                'city' => 'city.city_id=user.city_id'
            );
            $result = $this->sys_model_deposit->getOneRecharge($condition, $fields,'',$join);
            if (isset($result['amount'])) {
                $data['deposit_recharge'] = (float)$result['amount'];
            }
            unset($fields);
            unset($condition);
            unset($result);

            // 押金退回
            $fields = 'SUM(pdc_amount) as amount';
            $condition = array(
                'pdc_payment_state' => 1,
                'pdc_type' => 1,
            );
            if (!empty($timeHorizon)) {
                $condition['pdc_payment_time'] = $timeHorizon;
            }
            if (is_numeric($city_id)) {
            $condition['user.city_id'] = (int)$city_id;
            }
            if (is_numeric($region_id)) {
                    $condition['user.region_id'] = (int)$region_id;
            }
            if (is_numeric($user_type)) {
                    $condition['user_type'] = (int)$user_type;
            }
            $join = array(
                'user' => 'user.user_id=deposit_cash.pdc_user_id',
                'region' => 'region.region_id=user.region_id',
                'city' => 'city.city_id=user.city_id'
            );
            $result = $this->sys_model_deposit->getDepositCashInfo($condition, $fields,$join);
            if (isset($result['amount'])) {
                $data['deposit_refund'] = (float)$result['amount'];
            }
            unset($fields);
            unset($condition);
            unset($result);

            // 押金净值
            $data['deposit_net'] = $data['deposit_recharge'] - $data['deposit_refund'];
            
            /**
             * 注册金
             */
            //注册金充值
            $fields = 'SUM(pdr_amount) as amount';
            $condition = array(
                'pdr_type' => '3',
                'pdr_payment_state' => array('in', array(1, -1, -2)),
            );
            if (!empty($timeHorizon)) {
                $condition['pdr_payment_time'] = $timeHorizon;
            }
            if (is_numeric($city_id)) {
            $condition['user.city_id'] = (int)$city_id;
            }
            if (is_numeric($region_id)) {
                    $condition['user.region_id'] = (int)$region_id;
            }
            if (is_numeric($user_type)) {
                    $condition['user_type'] = (int)$user_type;
            }
            $join = array(
                'user' => 'user.user_id=deposit_recharge.pdr_user_id',
                'region' => 'region.region_id=user.region_id',
                'city' => 'city.city_id=user.city_id'
            );
            $result = $this->sys_model_deposit->getOneRecharge($condition, $fields,'',$join);
            if (isset($result['amount'])) {
                $data['reginster_recharge'] = (float)$result['amount'];
            }
            unset($fields);
            unset($condition);
            unset($result);

            // 注册金退回
            $fields = 'SUM(pdc_amount) as amount';
            $condition = array(
                'pdc_payment_state' => 1,
                'pdc_type' => 3,
            );
            if (!empty($timeHorizon)) {
                $condition['pdc_payment_time'] = $timeHorizon;
            }
            if (is_numeric($city_id)) {
                $condition['user.city_id'] = (int)$city_id;
            }
            if (is_numeric($region_id)) {
                    $condition['user.region_id'] = (int)$region_id;
            }
            if (is_numeric($user_type)) {
                    $condition['user_type'] = (int)$user_type;
            }
            $join = array(
                'user' => 'user.user_id=deposit_cash.pdc_user_id',
                'region' => 'region.region_id=user.region_id',
                'city' => 'city.city_id=user.city_id'
            );
            $result = $this->sys_model_deposit->getDepositCashInfo($condition, $fields,$join);
            if (isset($result['amount'])) {
                $data['reginster_refund'] = (float)$result['amount'];
            }
            unset($fields);
            unset($condition);
            unset($result);

            // 注册金净值
            $data['reginster_net'] = $data['reginster_recharge'] - $data['reginster_refund'];
            
            /**
             * 余额
             */
            // 余额充值
            $fields = 'SUM(pdr_amount) as amount';
            $condition = array(
                'pdr_type' => '0',
                'pdr_payment_state' => array('in', array(1, -1, -2)),
            );
            if (!empty($timeHorizon)) {
                $condition['pdr_payment_time'] = $timeHorizon;
            }
            if (is_numeric($city_id)) {
            $condition['user.city_id'] = (int)$city_id;
            }
            if (is_numeric($region_id)) {
                    $condition['user.region_id'] = (int)$region_id;
            }
            if (is_numeric($user_type)) {
                    $condition['user_type'] = (int)$user_type;
            }
            $join = array(
                'user' => 'user.user_id=deposit_recharge.pdr_user_id',
                'region' => 'region.region_id=user.region_id',
                'city' => 'city.city_id=user.city_id'
            );
            $result = $this->sys_model_deposit->getRechargeInfo($condition, $fields,$join);
            if (isset($result['amount'])) {
                $data['balance_recharge'] = (float)$result['amount'];
            }
            unset($fields);
            unset($condition);
            unset($result);

            // 余额退回
            $fields = 'SUM(pdc_amount) as amount';
            $condition = array(
                'pdc_payment_state' => 1,
                'pdc_type' => 0,
            );
            if (!empty($timeHorizon)) {
                $condition['pdc_payment_time'] = $timeHorizon;
            }
            if (is_numeric($city_id)) {
            $condition['user.city_id'] = (int)$city_id;
            }
            if (is_numeric($region_id)) {
                    $condition['user.region_id'] = (int)$region_id;
            }
            if (is_numeric($user_type)) {
                    $condition['user_type'] = (int)$user_type;
            }
            $join = array(
                'user' => 'user.user_id=deposit_cash.pdc_user_id',
                'region' => 'region.region_id=user.region_id',
                'city' => 'city.city_id=user.city_id'
            );
            $result = $this->sys_model_deposit->getDepositCashInfo($condition, $fields,$join);
            if (isset($result['amount'])) {
                $data['balance_refund'] = (float)$result['amount'];
            }
            unset($fields);
            unset($condition);
            unset($result);
 
         
        // 消费金额
        $fields = 'SUM(pay_amount) as amount';
        $condition = array(
            'order_state' => 2
        );
        if (!empty($timeHorizon)) {
            $condition['settlement_time'] = $timeHorizon;
        }
        if (is_numeric($city_id)) {
            $condition['user.city_id'] = (int)$city_id;
        }
        if (is_numeric($region_id)) {
                $condition['user.region_id'] = (int)$region_id;
        }
        if (is_numeric($user_type)) {
                $condition['user_type'] = (int)$user_type;
        }
        $join = array(
                'user' => 'user.user_id=orders.user_id',
                'region' => 'region.region_id=user.region_id',
                'city' => 'city.city_id=user.city_id'
        );
        $result = $this->sys_model_orders->getOrdersInfo($condition, $fields,'',$join);
        if (isset($result['amount'])) {
            $data['order_amount'] = (float)$result['amount'];
        }

        // 消费退回
        $fields = 'SUM(apply_cash_amount) as amount';
        $condition = array(
            'apply_state' => 1,
        );
        if (!empty($timeHorizon)) {
            $condition['apply_audit_time'] = $timeHorizon;
        }
        if (is_numeric($city_id)) {
            $condition['user.city_id'] = (int)$city_id;
        }
        if (is_numeric($region_id)) {
                $condition['user.region_id'] = (int)$region_id;
        }
        if (is_numeric($user_type)) {
                $condition['user_type'] = (int)$user_type;
        }
        $join = array(
            'orders' => 'orders.order_sn=orders_modify_apply.order_sn',
            'user' => 'user.user_id=orders_modify_apply.apply_user_id',
            'region' => 'region.region_id=user.region_id',
            'city' => 'city.city_id=user.city_id'
        );
        $result = $this->sys_model_orders->getOrderApplyInfo($condition, $fields, $join);
        if (isset($result['amount'])) {
            // 现金收入 = 消费金额
            $data['order_refund'] = (float)$result['amount'];
        }

        // 现金收入 = 消费金额 - 退回金额
        $data['total'] = $data['order_amount'] - $data['order_refund'];

        unset($fields);
        unset($condition);
        unset($result);

        
        // 余额净值 = 余额充值 - 余额退回 - 消费金额 + 消费退回
            $data['balance_net'] = $data['balance_recharge'] - $data['balance_refund'] - $data['order_amount'] - $data['order_refund'];
        

        return $data;
    }

    /**
     * 计算连俩个日期相差月份
     * @param $startTime
     * @param $endTime
     * @return bool|string
     */
    private function calculateMonths($startTime, $endTime) {
        $startYear = date('Y', $startTime);
        $startMonth = date('m', $startTime);
        $endYear = date('Y', $endTime);
        $endMonth = date('m', $endTime);
        return ($endYear * 12 + $endMonth) - ($startYear * 12 + $startMonth);
    }
    /**
     * 计算连俩个日期相差天数
     * @param $startTime
     * @param $endTime
     * @return bool|string
     */
    private function calculateDays($startTime, $endTime) {
        $startDay = strtotime(date('Y-m-d', $startTime));
        $endDay = strtotime(date('Y-m-d', $endTime));
        return ($endDay - $startDay) / 86400;
    }
    /**
     * 计算连俩个日期相差年份
     * @license  [license]
     * @param    [type]     $startTime [description]
     * @param    [type]     $endTime   [description]
     * @return   [type]                [description]
     */
    private function calculateYears($startTime, $endTime) {
        $startYear = date('Y', $startTime);
        $endYear = date('Y', $endTime);
        return ($endYear - $startYear);
    }
}