<?php
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
class ControllerUserUser extends Controller {
    public function __construct($registry) {
        parent::__construct($registry);

        // 当前网址
        $this->cur_url = $this->url->link($this->request->get['route']);

        // 加载user Model
        $this->load->library('sys_model/user', true);
        $this->assign('lang',$this->language->all());
    }

    public function index() {
        $filter = $this->request->get(array('filter_type', 'mobile', 'deposit', 'available_deposit', 'credit_point', 'available_state', 'add_time', 'region_id','city_id', 'nickname'));

        $condition = array();
        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }
        if (is_numeric($filter['deposit'])) {
            $condition['deposit'] = (float)$filter['deposit'];
        }
        if (is_numeric($filter['available_deposit'])) {
            $condition['available_deposit'] = (float)$filter['available_deposit'];
        }
        if (is_numeric($filter['credit_point'])) {
            $condition['credit_point'] = (int)$filter['credit_point'];
        }
        if (is_numeric($filter['available_state'])) {
            $condition['available_state'] = (int)$filter['available_state'];
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['add_time'] = array(
                array('egt', strtotime($add_time[0])),
                array('elt', bcadd(86399, strtotime($add_time[1])))
            );
        }
        // if (!empty($filter['cooperator'])) {
        //     $this->load->library('sys_model/cooperator');
        //     $cooperator_info = $this->sys_model_cooperator->getCooperatorInfo(
        //         array(
        //             'cooperator_name' => array(
        //                 'like' , $filter['cooperator'].'%'
        //             ),
        //         )
        //     );
        //     $condition['cooperator_id'] = (int)$cooperator_info['cooperator_id'];
        // }

        if (!empty($filter['nickname'])) {
            $condition['nickname'] = array(
                'like' , '%'.$filter['nickname'].'%'
            );
        }




        $filter_types = array(
            'mobile'        => $this->language->get('t27'),
            'nickname'    => $this->language->get('t28'),
             'email'      => $this->language->get('t29'),
            'facebook'=>$this->language->get('t30')
        );
        $filter_type = $this->request->get('filter_type');
        if (empty($filter_type)) {
            reset($filter_types);
            $filter_type = key($filter_types);
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $this->load->library('sys_model/region');
        $this->load->library('sys_model/city');
        
        $filter_regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name');
        foreach ($filter_regions as $key2 => $val2) {
            $filter_regions[$key2]['city'] = $this->sys_model_city->getCityList(['region_id' => $val2['region_id']], '', '', 'city_id,city_name', []); //地区下面的城市数据
        }


        $this->assign('filter_regions', $filter_regions);

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_user->getUserList($condition, '*', $order, $limit);
        $total = $this->sys_model_user->getTotalUsers($condition);
        #$this->load->library('sys_model/');
        #$region_list = $this->
        $available_states = get_common_boolean();

        $this->load->library('sys_model/cooperator', true);
        $cooperator_list = $this->sys_model_cooperator->getCooperatorList();
        $arr_list = array();
        foreach ($cooperator_list as $value) {
            $arr_list[$value['cooperator_id']] = $value;
        }

        $this->load->library("sys_model/region");
        $region_list = $this->sys_model_region->getRegionList();
        $region_list_arr = array();
        foreach($region_list as $v){
            $region_list_arr[$v['region_id']] = $v;
        }

        if (is_array($result) && !empty($result)) {
            foreach ($result as &$item) {
                $item['available_state'] = isset($available_states[$item['available_state']]) ? $available_states[$item['available_state']] : '';
                $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
                $item['register_region_name'] = isset($region_list_arr[$item['register_region_id']]) ? $region_list_arr[$item['register_region_id']]['region_name'] : '未知';
                $item['edit_action'] = $this->url->link('user/user/edit', 'user_id='.$item['user_id']);
                $item['delete_action'] = $this->url->link('user/user/delete', 'user_id='.$item['user_id']);
                $item['info_action'] = $this->url->link('user/user/info', 'user_id='.$item['user_id']);

                $item['cooperator_name'] = isset($arr_list[$item['cooperator_id']]) ? $arr_list[$item['cooperator_id']]['cooperator_name'] : '平台';
            }
        }

        $data_columns = $this->getDataColumns();
        $this->assign('data_columns', $data_columns);
        $this->assign('available_states', $available_states);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);
        $this->assign('filter_type', $filter_type);
        $this->assign('filter_types', $filter_types);
        $this->assign('action', $this->cur_url);
        $this->assign('add_action', $this->url->link('user/user/add'));

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

        $this->assign('export_action', $this->url->link('user/user/export'));
        $this->assign('user_chart_action', $this->url->link('user/user/chart'));
        $this->assign('cooperator_chart_action', $this->url->link('user/user/cooperator_chart'));
        $this->assign('region_chart_action', $this->url->link('user/user/region_chart'));
        $this->assign('time_type',get_time_type());
        $this->response->setOutput($this->load->view('user/user_list', $this->output));
    }

    // 表格字段
    // 表格字段
    protected function getDataColumns() {
        $this->setDataColumn($this->language->get('t31'));
        $this->setDataColumn($this->language->get('t32'));
        $this->setDataColumn($this->language->get('t27'));
        $this->setDataColumn($this->language->get('t28'));
        $this->setDataColumn($this->language->get('t33'));
        $this->setDataColumn($this->language->get('t34'));
        $this->setDataColumn($this->language->get('t25'));
        $this->setDataColumn($this->language->get('t35'));
        $this->setDataColumn($this->language->get('t36'));
        $this->setDataColumn($this->language->get('t37'));
        return $this->data_columns;
    }


    /**
     * 单车详情
     */
    public function info() {
        // 编辑时获取已有的数据
        $user_id = $this->request->get('user_id');
        $condition = array(
            'user_id' => $user_id
        );
        $info = $this->sys_model_user->getUserInfo($condition);
        if (!empty($info)) {
            $info['login_time'] = date('Y-m-d H:i:s', $info['login_time']);
            $info['add_time'] = date('Y-m-d H:i:s', $info['add_time']);
        }

        $verify_states = $available_states = get_common_boolean();

        $this->assign('verify_states', $verify_states);
        $this->assign('available_states', $available_states);
        $this->assign('return_action', $this->url->link('user/user'));
        $this->assign('data', $info);

        $this->response->setOutput($this->load->view('user/user_info', $this->output));
    }

    /**
     * 导出
     */
    public function export() {
        @ini_set('memory_limit', '2048M');
        $filter = $this->request->post(array('filter_type', 'mobile', 'deposit', 'available_deposit', 'credit_point', 'available_state', 'add_time'));

        $condition = array();
        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }
        if (is_numeric($filter['deposit'])) {
            $condition['deposit'] = (float)$filter['deposit'];
        }
        if (is_numeric($filter['available_deposit'])) {
            $condition['available_deposit'] = (float)$filter['available_deposit'];
        }
        if (is_numeric($filter['credit_point'])) {
            $condition['credit_point'] = (int)$filter['credit_point'];
        }
        if (is_numeric($filter['available_state'])) {
            $condition['available_state'] = (int)$filter['available_state'];
        }
        if (!empty($filter['add_time'])) {
            $add_time = explode(' 至 ', $filter['add_time']);
            $condition['add_time'] = array(
                array('egt', strtotime($add_time[0])),
                array('elt', bcadd(86399, strtotime($add_time[1])))
            );
        }
        $order = 'add_time DESC';
        $limit = '';

        $header = array(
            'mobile' => '手机号码',
            'deposit' => '押金(元)',
            'available_deposit' => '可用金额(元)',
            'credit_point' => '信用积分',
            'available_state' => '是否可踩车',
            'add_time' => '注册时间',
        );
        $total = $this->sys_model_user->getTotalUsers($condition);
        if($total > 50000) {
            $data = array(
                'filename' => '用户列表',
                'title' => '用户列表-导出列表记录太多了，请使用筛选条件缩窄范围',
                'header' => $header,
                'list' => array(array('mobile' => '共 '.$total.' 条记录'))
            );
            $this->load->controller('common/base/exportExcel', $data);
            exit;
        }

        $result = $this->sys_model_user->getUserList($condition, '*', $order, $limit);
        $list = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $v) {
                $available_states = get_common_boolean();
                $list[] = array(
                    'mobile' => $v['mobile'],
                    'deposit' => $v['deposit'],
                    'available_deposit' => $v['available_deposit'],
                    'credit_point' => $v['credit_point'],
                    'available_state' => $available_states[$v['available_state']],
                    'add_time' => date("Y-m-d H:i:s",$v['add_time']),
                );
            }
        }

        $data = array(
            'title' => '用户列表',
            'header' => $header,
            'list' => $list
        );
        $this->load->controller('common/base/exportExcel', $data);
    }

    public function getUserList() {
        $filter = $this->request->get(array('mobile'));
        $condition = array();

        if (!empty($filter['mobile'])) {
            $condition['mobile'] = array('like', "%{$filter['mobile']}%");
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_user->getUserList($condition, 'user_id, mobile', $order, $limit);
        $total = $this->sys_model_user->getTotalUsers($condition);

        $available_states = get_common_boolean();
//        if (is_array($result) && !empty($result)) {
//            foreach ($result as &$item) {
//
//            }
//        }

//        $data_columns = $this->getDataColumns();
//        $this->assign('data_columns', $data_columns);
//        $this->assign('available_states', $available_states);
        $this->assign('data_rows', $result);
        $this->assign('filter', $filter);

//        $this->assign('action', $this->cur_url);
//        $this->assign('add_action', $this->url->link('user/user/add'));

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
        $this->assign('static', HTTP_CATALOG);
//        $this->assign('export_action', $this->url->link('user/user/export'));

        $this->response->setOutput($this->load->view('user/modal_user_list', $this->output));
    }

    /**
     * 群发短信
     */
    public function mass_sms() {
        define('SMS_ACCOUNT_SID', $this->config->get('config_sms_account_sid'));
        define('SMS_ACCOUNT_TOKEN', $this->config->get('config_sms_account_token'));
        define('SMS_APP_ID', $this->config->get('config_sms_app_id'));
        $mobiles = array();
        $starttime = strtotime('2017-4-7');
        $endtime = strtotime('2017-4-9');
        $result = $this->sys_model_user->getUserList();
        if (is_array($result) && !empty($result)) {
            $mobiles = array_column($result, 'mobile');
        }

        // 实例化发送验证码类
        $smsObj = new \Tool\Phone_code();

        if (is_array($mobiles) && !empty($mobiles)) {
            $mobiles = array_chunk($mobiles, 200);
            foreach ($mobiles as $item) {
                $to = implode(',', $item);
                $text1 = sprintf('本周%s至周%s', $this->getWeek($starttime), $this->getWeek($endtime)); //'本周五至周日';
                $text2 = sprintf('%s到%s', date('n月j日', $starttime), date('j日', $endtime)); //'4月7日-9日';
                $data = array($text1, $text2);
                $temp_id = 165511;
                $smsObj->sendSMS($to, $data, $temp_id);
            }
        }
    }

    /**
     * 格式化星期
     * @param $time
     * @return mixed
     */
    public function getWeek($time) {
        $week = date('w', $time);
        $weekArray = array('日', '一', '二', '三', '四', '五', '六');
        return $weekArray[$week];
    }

    /*
     * 统计图表；
     * */

    public function chart(){

        $filter = $this->request->get(array('add_time'));

        $condition = array();
        if (!empty($filter['add_time'])) {
            $pdc_payment_time = explode(' 至 ', $filter['add_time']);

            $firstday = strtotime($pdc_payment_time[0]);
            $lastday  = bcadd(86399, strtotime($pdc_payment_time[1]));
            $condition = array('add_time' => array(array('egt' , $firstday),array('elt' , $lastday)));
        }


        #全部合伙人
        /*$this->load->library('sys_model/cooperator');
        $cooperatorList = $this->sys_model_cooperator->getCooperatorList();
        if(empty($cooperatorList)){
            $this->load->controller('common/base/redirect', $this->url->link('operation/coupon', $filter, true));
        }
        if(isset($filter['cooperator_id']) && $filter['cooperator_id']){
            $w['cooperator_id'] = $filter['cooperator_id'];
        }else{
            $w['cooperator_id'] = $cooperatorList[0]['cooperator_id'];
        }*/

        $color_arr = array('#f56954','#00a65a','#f39c12','#00c0ef','#3c8dbc','#d2d6de','#FF1493','#DC143C','#191970','#00FF7F','#FFD700','#90EE90','#5F9EA0','#FFB6C1');

        $user_total = $this->sys_model_user->getTotalUsers($condition);
        # 统计用户注册区域
        $data['user_reg_region'] = array();
        $this->load->library("sys_model/region");
        $region_list = $this->sys_model_region->getRegionList();
        $data['user_has_region'] = array();
        $region_user_arr = array();
        $count = 0;
        foreach($region_list as $k => $v){
            $where = $condition;
            $where[] = array('register_region_id' => $v['region_id']);
            $total = $this->sys_model_user->getTotalUsers($where);
            $count += $total;
            $var = $user_total ? round(($total/$user_total)*100,2)."%(".$total.")" : "0%(0)";
            $region_user_arr[] = array(
                'color'       => isset($color_arr[$k]) ? $color_arr[$k] : '000000',
                'label'       => $v['region_name'],
                'value'       => $total,
                'px'          => $var,
                'highlight'   => isset($color_arr[$k]) ? $color_arr[$k] : '000000',
            );
        }
        $var_ping = $user_total ? round((($user_total - $count)/$user_total)*100,2)."%(".($user_total - $count).")" : "0%(0)";
        $region_user_arr[]= array(
            'color'      => "#A52A2A",
            'label'      => '平台',
            'value'      => $user_total - $count,
            'highlight'  => "#A52A2A",
            'px'         => $var_ping,
        );
        $data['user_reg_region_arr'] = $region_user_arr;
        $data['user_reg_region'] = json_encode($region_user_arr);



        # 统计合伙人用户统计
        $data['user_cooperator'] = array();
        $coo_user_arr = array();
        $count = 0;
      /*foreach($cooperatorList as $k => $v){
            $where = $condition;
            $where[] = array('cooperator_id' => $v['cooperator_id']);
            $total = $this->sys_model_user->getTotalUsers($where);
            $count += $total;
            $var = $user_total ? round(($total/$user_total)*100,2)."%(".$total.")" : "0%(0)";
            $coo_user_arr[] = array(
                'color'       => isset($color_arr[$k]) ? $color_arr[$k] : '000000',
                'label'       => $v['cooperator_name'],
                'value'       => $total,
                'px'          => $var,
                'highlight'   => isset($color_arr[$k]) ? $color_arr[$k] : '000000',
            );
        }*/
        $var_ping = $user_total ? round((($user_total - $count)/$user_total)*100,2)."%(".($user_total - $count).")" : "0%(0)";
        $coo_user_arr[]= array(
            'color'      => "#A52A2A",
            'label'      => '平台',
            'value'      => $user_total - $count,
            'highlight'  => "#A52A2A",
            'px'         => $var_ping,
        );
        $data['user_cooperator_arr'] = $coo_user_arr;
        $data['user_cooperator'] = json_encode($coo_user_arr);

        # 用户归属区域统计
        $this->load->library("sys_model/region");
        $region_list = $this->sys_model_region->getRegionList();
        $data['user_has_region'] = array();
        $region_user_arr = array();
        $count = 0;
        foreach($region_list as $k => $v){
            $where = $condition;
            $where[] = array('region_id' => $v['region_id']);
            $total = $this->sys_model_user->getTotalUsers($where);
            $count += $total;
            $var = $user_total ? round(($total/$user_total)*100,2)."%(".$total.")" : "0%(0)";
            $region_user_arr[] = array(
                'color'       => isset($color_arr[$k]) ? $color_arr[$k] : '000000',
                'label'       => $v['region_name'],
                'value'       => $total,
                'px'          => $var,
                'highlight'   => isset($color_arr[$k]) ? $color_arr[$k] : '000000',
            );
        }
        $var_ping = $user_total ? round((($user_total - $count)/$user_total)*100,2)."%(".($user_total - $count).")" : "0%(0)";
        $region_user_arr[]= array(
            'color'      => "#A52A2A",
            'label'      => '平台',
            'value'      => $user_total - $count,
            'highlight'  => "#A52A2A",
            'px'         => $var_ping,
        );
        $data['user_has_region_arr'] = $region_user_arr;
        $data['user_has_region'] = json_encode($region_user_arr);

        # 用户注册方式统计
        $data['user_reg_type'] = array();
        $platform_arr[] = array(
            'key' => 'android',
            'name' => '安卓',
        );
        $platform_arr[] = array(
            'key' => 'ios',
            'name' => 'ios',
        );
        $platform_arr[] = array(
            'key' => 'mini_app',
            'name' => '微信小程序',
        );
        $platform_arr[] = array(
            'key' => 'wechat',
            'name' => '微信',
        );

        $data['user_reg_type'] = array();
        $user_reg_type_arr = array();
        $count = 0;
        foreach($platform_arr as $k => $v){
            $where = $condition;
            $where[] = array('platform' => $v['key']);
            $total = $this->sys_model_user->getTotalUsers($where);
            $count += $total;
            $var = $user_total ? round(($total/$user_total)*100,2)."%(".$total.")" : "0%(0)";
            $user_reg_type_arr[] = array(
                'color'       => isset($color_arr[$k]) ? $color_arr[$k] : '000000',
                'label'       => $v['name'],
                'value'       => $total,
                'px'          => $var,
                'highlight'   => isset($color_arr[$k]) ? $color_arr[$k] : '000000',
            );
        }
        $var_ping = $user_total ? round((($user_total - $count)/$user_total)*100,2)."%(".($user_total - $count).")" : "0%(0)";
        $user_reg_type_arr[]= array(
            'color'      => "#A52A2A",
            'label'      => '未知',
            'value'      => $user_total - $count,
            'highlight'  => "#A52A2A",
            'px'         => $var_ping,
        );
        $data['user_reg_type_arr'] = $user_reg_type_arr;
        $data['user_reg_type'] = json_encode($user_reg_type_arr);



        $this->assign('data', $data);
        $this->assign('filter', $filter);
        //$this->assign('cooperList', $cooperatorList);
        $this->assign('user_cooperator_arr', $coo_user_arr);
        $this->assign('static', HTTPS_CATALOG);
        $this->assign('user_list_action', $this->url->link('user/user'));
        $this->assign('user_chart_action', $this->url->link('user/user/chart'));
        $this->assign('cooperator_chart_action', $this->url->link('user/user/cooperator_chart'));
        $this->assign('region_chart_action', $this->url->link('user/user/region_chart'));
        $this->assign('add_action', $this->url->link('user/user/chart'));
        $this->assign('time_type',get_time_type());
        $this->response->setOutput($this->load->view('user/user_chart', $this->output));
    }

    public function cooperator_chart(){

        $filter = $this->request->get(array('filter_type', 'mobile', 'deposit', 'cooperator_id', 'credit_point', 'available_state', 'add_time'));

        $condition = array();
        if (!empty($filter['add_time'])) {
            $pdc_payment_time = explode(' 至 ', $filter['add_time']);

            $firstday = strtotime($pdc_payment_time[0]);
            $lastday  = bcadd(86399, strtotime($pdc_payment_time[1]));
            $condition = array('add_time' => array(array('egt' , $firstday),array('elt' , $lastday)));
        }

        #全部合伙人
        $this->load->library('sys_model/cooperator');
        $cooperatorList = $this->sys_model_cooperator->getCooperatorList();
        if(empty($cooperatorList)){
            $this->load->controller('common/base/redirect', $this->url->link('operation/coupon', $filter, true));
        }
        if(is_numeric($filter['cooperator_id']) ){
            $condition['cooperator_id'] = $filter['cooperator_id'];
        }

        $color_arr = array('#f56954','#00a65a','#f39c12','#00c0ef','#3c8dbc','#d2d6de','#FF1493','#DC143C','#191970','#00FF7F','#FFD700','#90EE90','#5F9EA0','#FFB6C1');
        $user_total = $this->sys_model_user->getTotalUsers($condition);

        # 用户注册方式统计
        $data['user_reg_type'] = array();
        $platform_arr[] = array(
            'key' => 'android',
            'name' => '安卓',
        );
        $platform_arr[] = array(
            'key' => 'ios',
            'name' => 'ios',
        );
        $platform_arr[] = array(
            'key' => 'mini_app',
            'name' => '微信小程序',
        );
        $platform_arr[] = array(
            'key' => 'wechat',
            'name' => '微信',
        );

        $data['user_reg_type'] = array();
        $user_reg_type_arr = array();
        $count = 0;
        foreach($platform_arr as $k => $v){
            $where = $condition;
            $where[] = array('platform' => $v['key']);
            $total = $this->sys_model_user->getTotalUsers($where);
            $count += $total;
            $var = $user_total ? round(($total/$user_total)*100,2)."%(".$total.")" : "0%(0)";
            $user_reg_type_arr[] = array(
                'color'       => isset($color_arr[$k]) ? $color_arr[$k] : '000000',
                'label'       => $v['name'],
                'value'       => $total,
                'px'          => $var,
                'highlight'   => isset($color_arr[$k]) ? $color_arr[$k] : '000000',
            );
        }
        $var_ping = $user_total ? round((($user_total - $count)/$user_total)*100,2)."%(".($user_total - $count).")" : "0%(0)";
        $user_reg_type_arr[]= array(
            'color'      => "#A52A2A",
            'label'      => '其他',
            'value'      => $user_total - $count,
            'highlight'  => "#A52A2A",
            'px'         => $var_ping,
        );
        $data['user_reg_type_arr'] = $user_reg_type_arr;
        $data['user_reg_type'] = json_encode($user_reg_type_arr);

        $this->assign('data', $data);
        $this->assign('filter', $filter);
        $this->assign('cooperList', $cooperatorList);
        $this->assign('cooperator_id', $filter['cooperator_id']);

        $this->assign('static', HTTPS_CATALOG);
        $this->assign('user_list_action', $this->url->link('user/user'));
        $this->assign('user_chart_action', $this->url->link('user/user/chart'));
        $this->assign('cooperator_chart_action', $this->url->link('user/user/cooperator_chart'));
        $this->assign('region_chart_action', $this->url->link('user/user/region_chart'));
        $this->assign('add_action', $this->url->link('user/user/cooperator_chart'));

        $this->response->setOutput($this->load->view('user/user_cooperator_chart', $this->output));
    }

    public function region_chart(){

        $filter = $this->request->get(array('region_id', 'add_time'));

        $condition = array();
        if (!empty($filter['add_time'])) {
            $pdc_payment_time = explode(' 至 ', $filter['add_time']);

            $firstday = strtotime($pdc_payment_time[0]);
            $lastday  = bcadd(86399, strtotime($pdc_payment_time[1]));
            $condition = array('add_time' => array(array('egt' , $firstday),array('elt' , $lastday)));
        }

        #全部区域
        $this->load->library('sys_model/region');
        $regionList = $this->sys_model_region->getRegionList();
        if(empty($regionList)){
            $this->load->controller('common/base/redirect', $this->url->link('user/user', $filter, true));
        }
        if(is_numeric($filter['region_id']) ){
            $condition['region_id'] = $filter['region_id'];
        }
        $color_arr = array('#f56954','#00a65a','#f39c12','#00c0ef','#3c8dbc','#d2d6de','#FF1493','#DC143C','#191970','#00FF7F','#FFD700','#90EE90','#5F9EA0','#FFB6C1');
        $user_total = $this->sys_model_user->getTotalUsers($condition);

        # 用户注册方式统计
        $data['user_reg_type'] = array();
        $platform_arr[] = array(
            'key' => 'android',
            'name' => '安卓',
        );
        $platform_arr[] = array(
            'key' => 'ios',
            'name' => 'ios',
        );
        /*$platform_arr[] = array(
            'key' => 'mini_app',
            'name' => '微信小程序',
        );
        $platform_arr[] = array(
            'key' => 'wechat',
            'name' => '微信',
        );*/

        $data['user_reg_type'] = array();
        $user_reg_type_arr = array();
        $count = 0;
        foreach($platform_arr as $k => $v){
            $where = $condition;
            $where[] = array('platform' => $v['key']);
            $total = $this->sys_model_user->getTotalUsers($where);
            $count += $total;
            $var = $user_total ? round(($total/$user_total)*100,2)."%(".$total.")" : "0%(0)";
            $user_reg_type_arr[] = array(
                'color'       => isset($color_arr[$k]) ? $color_arr[$k] : '000000',
                'label'       => $v['name'],
                'value'       => $total,
                'px'          => $var,
                'highlight'   => isset($color_arr[$k]) ? $color_arr[$k] : '000000',
            );
        }
        $var_ping = $user_total ? round((($user_total - $count)/$user_total)*100,2)."%(".($user_total - $count).")" : "0%(0)";
        $user_reg_type_arr[]= array(
            'color'      => "#A52A2A",
            'label'      => '其他',
            'value'      => $user_total - $count,
            'highlight'  => "#A52A2A",
            'px'         => $var_ping,
        );
        $data['user_reg_type_arr'] = $user_reg_type_arr;
        $data['user_reg_type'] = json_encode($user_reg_type_arr);

        $this->assign('data', $data);
        $this->assign('filter', $filter);
        $this->assign('regionList', $regionList);
        $this->assign('region_id', $filter['region_id']);

        $this->assign('static', HTTPS_CATALOG);
        $this->assign('user_list_action', $this->url->link('user/user'));
        $this->assign('user_chart_action', $this->url->link('user/user/chart'));
        $this->assign('cooperator_chart_action', $this->url->link('user/user/cooperator_chart'));
        $this->assign('region_chart_action', $this->url->link('user/user/region_chart'));
        $this->assign('add_action', $this->url->link('user/user/region_chart'));
        $this->assign('time_type',get_time_type());
        $this->response->setOutput($this->load->view('user/user_region_chart', $this->output));
    }


}