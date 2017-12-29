<?php
use Tool\Distance;
class ControllerAdminIndex extends Controller {
    private $cooperator_id = null;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->cooperator_id = $this->logic_admin->getParam('cooperator_id');
        // 加载Model
        $this->load->library('sys_model/coupon', true);
        $this->load->library('sys_model/user', true);
        $this->load->library('sys_model/orders',true);
        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/fault',true);
        $this->load->library('sys_model/bicycle_usage',true);
        $this->load->library('sys_model/lock',true);
        $this->load->library('sys_model/deposit', true);
        $this->load->library('sys_model/points', true);
        $this->load->library('sys_model/cooperator',true);
        $this->load->library('sys_model/region',true);
        $this->load->library('sys_model/repair',true);
    }

    /**
     * 首页
     */
    public function index() {
        $this->summary();
        $this->assign('servertime', time());
        $this->assign('export_action', $this->url->link('bicycle/bicycle/export'));
        $this->assign('heatmapData_action', $this->url->link('admin/index/apiHeatmapData'));
        $region = $this->sys_model_region
            ->getAdminToRegionList('admin_to_region.admin_id=' . $this->logic_admin->getId(),
                'region.region_id ASC', '', 'region.region_name',
                array('region' => 'region.region_id=admin_to_region.region_id'));
        $this->assign('region', $region);
        $this->response->setOutput($this->load->view('admin/index', $this->output));
    }

    /**
     * 热力图数据
     */
    public function apiHeatmapData() {
        $this->load->library('sys_model/orders',true);
        $str = '';
        $condition = array(
            'cooperator_id' => $this->cooperator_id
        );
        $filter = $this->request->get(array('add_time'));
        if (!empty($filter['add_time'])) {
            $pdr_add_time = explode(' 至 ', $filter['add_time']);
            $firstday = strtotime($pdr_add_time[0]);
            $lastday  = bcadd(86399, strtotime($pdr_add_time[1]));
            $condition['orders_line.add_time'] = array(
                array('egt', $firstday),
                array('elt', $lastday)
            );
        } else {
            $firstday = date('Y-m-d', strtotime("-30 days"));
            $condition['orders_line.add_time'] = array('egt', strtotime($firstday));
            $filter['add_time'] = sprintf('%s 至 %s', $firstday, date('Y-m-d'));
        }

        // 所有位置
        $field = '`lng`,`lat`,\'1\' as `total`';
        $join = array(
            'orders' => 'orders.order_id=orders_line.order_id'
        );
        $order = 'orders_line.add_time ASC';
        $positions = $this->sys_model_orders->getOrderLine($condition, $field, $join, $order);
        $str .= '"all":' . json_encode($positions);
        unset($positions);

        // 开始位置
        $order = 'orders_line.add_time ASC';
        $limit = '';
        $field = '`lng`,`lat`,\'1\' as `total`';
        $join = array(
            'orders' => 'orders.order_id=orders_line.order_id'
        );
        $group = 'orders_line.order_id';
        $start_positions = $this->sys_model_orders->getOrderLine($condition, $field, $join, $order, $limit, $group);
        $str .= ',"start":' . json_encode($start_positions);
        unset($start_positions);

        // 结束位置
        $order = 'orders_line.add_time DESC';
        $limit = '';
        $field = '`lng`,`lat`,\'1\' as `total`';
        $join = array(
            'orders' => 'orders.order_id=orders_line.order_id'
        );
        $group = 'orders_line.order_id';
        $end_positions = $this->sys_model_orders->getOrderLine($condition, $field, $join, $order, $limit, $group);
        $str .= ',"end":' . json_encode($end_positions);
        unset($end_positions);

        echo 'var heatmapData = {'. $str .'}';
    }

    public function apiGetMarker() {
        $marker_init = isset($this->request->post['marker_init']) ? $this->request->post['marker_init'] : 0;
        $min_lat = $marker_init ? -90 : $this->request->post['min_lat'];
        $min_lng = $marker_init ? -180 : $this->request->post['min_lng'];
        $max_lat = $marker_init ? 90 : $this->request->post['max_lat'];
        $max_lng = $marker_init ? 180 : $this->request->post['max_lng'];

        $status = isset($this->request->post['status']) ? $this->request->post['status'] : false;

        $this->load->library('sys_model/bicycle', true);
		$markers = $this->sys_model_bicycle->getBicyclesByBounds($min_lat, $min_lng, $max_lat, $max_lng, $status, $this->cooperator_id);
	    foreach($markers as &$v){
            if((int)$v['lock_type'] == 2){
                $v['online'] = 1;
            }
        }
        $this->response->showSuccessResult($markers);
    }

    //故障列表
    public function apiGetFaults() {
        $page = isset($this->request->request['page']) ? $this->request->request['page'] : 1;
        $this->load->library('sys_model/fault',true);

        $bike_sn = $this->request->request['bike_sn'];

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $condition = array(
            'bicycle_sn' => $bike_sn,
            'cooperator_id' => $this->cooperator_id,
        );
        $faults = $this->sys_model_fault->getFaultList($condition, $order, $limit);
        $get_fault_processed = get_fault_processed();

        $condition = array(
            'is_show' => 1
        );
        $order = 'display_order ASC, add_time DESC';
        $tempFaultTypes = $this->sys_model_fault->getFaultTypeList($condition, $order);
        $fault_types = array();
        if (!empty($tempFaultTypes)) {
            foreach ($tempFaultTypes as $v) {
                $fault_types[$v['fault_type_id']] = $v['fault_type_name'];
            }
        }

        foreach ($faults as &$v){
            $v['processed'] = $get_fault_processed[$v['processed']];
            $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);

            $fault_type = '';
            $fault_type_ids = explode(',', $v['fault_type']);
            foreach($fault_type_ids as $fault_type_id) {
                $fault_type .= isset($fault_types[$fault_type_id]) ? ',' . $fault_types[$fault_type_id] : '';
            }
            $v['fault_type'] = !empty($fault_type) ? substr($fault_type, 1) : '';
        }

        $this->assign('page', $page+1);
        $this->assign('faults', $faults);
        $this->assign('static', HTTP_IMAGE);
        $this->assign('config_limit_admin', $rows);

        $this->response->setOutput($this->load->view('admin/fault', $this->output));
    }

    //停车列表
    public function apiGetNormalParking() {
        $page = isset($this->request->request['page']) ? $this->request->request['page'] : 1;
        $this->load->library('sys_model/fault',true);

        $bike_sn = $this->request->request['bike_sn'];

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $normalParking = $this->sys_model_fault->getNormalParkingList(array('bicycle_sn'=>$bike_sn), $order, $limit);

        foreach ($normalParking as &$v){
            $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
        }

        $this->assign('page', $page+1);
        $this->assign('parkings', $normalParking);
        $this->assign('config_limit_admin', $rows);
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('admin/normal_parking', $this->output));

    }

    //违停列表
    public function apiGetIllegalParking() {
        $page = isset($this->request->request['page']) ? $this->request->request['page'] : 1;
        $this->load->library('sys_model/fault',true);

        $bike_sn = $this->request->request['bike_sn'];

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $condition = array(
            'cooperator_id' => $this->cooperator_id,
            'bicycle_sn' => $bike_sn,
        );
        $illegalParking = $this->sys_model_fault->getIllegalParkingList($condition, $order, $limit);
        $type = array(
            '1' => '违停上报',
            '2' => '其他上报',
        );

        foreach ($illegalParking as &$v){
            $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            $v['type'] = $type[$v['type']];
        }

        $this->assign('page', $page+1);
        $this->assign('parkings', $illegalParking);
        $this->assign('config_limit_admin', $rows);
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('admin/illegal_parking', $this->output));

    }

    //反馈列表
    public function apiGetFeekbacks() {
        $page = isset($this->request->request['page']) ? $this->request->request['page'] : 1;

        $data = array(
            'feedbacks' => array()
        );

        $this->response->setOutput($this->load->view('admin/feedback', $data));
    }

    //订单列表
    public function apiGetUsedHistory() {
        $page = isset($this->request->request['page']) ? $this->request->request['page'] : 1;
        $this->load->library('sys_model/orders',true);
        $this->load->library('sys_model/user',true);

        $bike_sn = $this->request->request['bike_sn'];

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $get_order_state = get_order_state();

        $condition = array(
            'cooperator_id' => $this->cooperator_id,
            'bicycle_sn' => $bike_sn,
        );
        $orders = $this->sys_model_orders->getOrdersList($condition, $order, $limit);
        foreach ($orders as &$v){
            $user = $this->sys_model_user->getUserInfo(array('user_id'=>$v['user_id']));
            $v['avatar'] = $user['avatar'];
            $v['add_time'] = !empty($v['add_time'])? date('Y-m-d H:i:s',$v['add_time']) : '-';
            $v['start_time'] = !empty($v['start_time'])? date('Y-m-d H:i:s',$v['start_time']) : '-';
            $v['end_time'] = !empty($v['end_time'])? date('Y-m-d H:i:s',$v['end_time']) : '-';
            $v['order_state_describe'] = $get_order_state[$v['order_state']];
        }

        $this->assign('page', $page+1);
        $this->assign('records', $orders);
        $this->assign('config_limit_admin', $rows);
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('admin/used_history', $this->output));
    }

    //使用次数列表
    public function apiGetUsageCount(){
        $this->load->library('sys_model/bicycle_usage');
        $this->load->library('sys_model/lock');
        $this->response->showSuccessResult(array(
            'dayCount' => $this->sys_model_bicycle_usage->getTotalUsageCount(' where DAYOFMONTH(date) = '.date('d', time()), ' bicycle_sn,SUM(count) count', ' group by bicycle_sn'),
            'monthCount' => $this->sys_model_bicycle_usage->getTotalUsageCount(' where MONTH(date) = '.date('m', time()), ' bicycle_sn,SUM(count) count', ' group by bicycle_sn'),
            'totalCount' => $this->sys_model_bicycle_usage->getTotalUsageCount('', ' bicycle_sn,SUM(count) count',' group by bicycle_sn'),
        ));
    }

    //关锁
    function shut(){
        $device_id = $this->request->request['device_id'];
        $this->load->library('instructions/instructions',true);
        $this->response->showSuccessResult($this->instructions_instructions->closeLock($device_id));
    }

    //开锁
    function openLock(){
        $device_id = $this->request->request['device_id'];
        $this->load->library('instructions/instructions',true);
        $this->response->showSuccessResult($this->instructions_instructions->openLock($device_id));
    }

    //设置设备锁关时位置回传间隔
    function setGapTime2(){
        $time = $this->request->request['time'];
        $device_id = $this->request->request['device_id'];
        $this->load->library('instructions/instructions',true);
        $this->load->library('sys_model/lock',true);
        if($this->sys_model_lock->updateLock(array('lock_sn' =>$device_id), array('set_gap_time2'=> $time))){
            $this->response->showSuccessResult($this->instructions_instructions->setGapTime2($device_id, $time));
        };
    }

    //设置设备锁开是位置回传间隔
    function setGapTime(){
        $time = $this->request->request['time'];
        $device_id = $this->request->request['device_id'];
        $this->load->library('instructions/instructions',true);
        $this->load->library('sys_model/lock',true);
        if($this->sys_model_lock->updateLock(array('lock_sn' =>$device_id), array('set_gap_time'=> $time))){
            $this->response->showSuccessResult($this->instructions_instructions->setGapTime($device_id, $time));
        }else{

        };
    }

    //响铃
    function beepLock(){
        $device_id = $this->request->request['device_id'];
        $this->load->library('instructions/instructions',true);
        $this->response->showSuccessResult($this->instructions_instructions->beepLock($device_id));
    }

    //结束订单
    function finishOrder(){
        $device_id = $this->request->request['device_id'];
        $lng = $this->request->request['lng'];
        $lat = $this->request->request['lat'];
        $this->load->library('logic/orders');
        $result = $this->logic_orders->finishOrders(array('device_id'=>$device_id, 'cmd'=>'close','lng'=>$lng, 'lat'=>$lat));
        if(!$result['state']){
            $this->response->showErrorResult();
        };

            //加载管理员操作日志 model
            $this->load->library('sys_model/admin_log', true);
            $data = array(
                'admin_id' => $this->logic_admin->getId(),
                'admin_name' => $this->logic_admin->getadmin_name(),
            'log_description' => '合伙人端：结束订单，锁编号：' . $device_id,
                'log_ip' => $this->request->ip_address(),
            'log_type_id' => 10,
                'log_time' => date('Y-m-d H:i:s')
            );
            $this->sys_model_admin_log->addAdminLog($data);

        $this->response->showSuccessResult();
    }

    //锁资料
    function lockInfo(){
        $device_id = $this->request->request['device_id'];
        $this->load->library('sys_model/lock',true);
        $this->response->showSuccessResult($this->sys_model_lock->getLockInfo(array('lock_sn'=> $device_id)));
    }

    //首页地图右侧搜索单车
    function search(){
        $filter = $this->request->post(array('bicycle_sn','fault','illegal_parking','low_battery'));

        $condition = array(
            'admin_id' => $this->logic_admin->getId()
        );
        $region_ids = array();
        $this->load->library('sys_model/region',true);
        $regionList = $this->sys_model_region->getAdminToRegionList($condition);
        if (is_array($regionList) && !empty($regionList)) {
            foreach ($regionList as $item) {
                $region_ids[] = $item['region_id'];
            }
        }

        $condition = array(
            'bicycle.cooperator_id' => $this->cooperator_id,
            "lock.lat" => array('neq', ''),
            "lock.lng" => array('neq', ''),
            'region_id' => array('in', $region_ids)
        );

        if (!empty($filter['bicycle_sn'])) {
            $condition['bicycle_sn'] = array('like', "%{$filter['bicycle_sn']}%");
        }
        if (!empty($filter['fault'])) {
            $condition['fault'] = $filter['fault'];
        }
        if (!empty($filter['illegal_parking'])) {
            $condition['illegal_parking'] = $filter['illegal_parking'];
        }
        if (!empty($filter['low_battery'])) {
            $condition['low_battery'] = $filter['low_battery'];
        }

        $join = array('lock'=>'bicycle.lock_sn = lock.lock_sn');

        $field = 'bicycle_sn, bicycle_id';
        $order = 'add_time DESC';
        $this->load->library('sys_model/bicycle',true);
        $result = array('bikes' => $this->sys_model_bicycle->getBicycleList($condition, $order, '',  $field, $join));

        $sql = "SELECT u.user_id, u.nickname, u.mobile, o.order_state, o.order_id, o.bicycle_sn, o.bicycle_id "
            ."FROM ".DB_PREFIX."user AS u LEFT JOIN ".DB_PREFIX."orders AS o "
            ."ON u.user_id=o.user_id AND (o.order_state=1 or (o.order_state=0 AND (o.add_time+".BOOK_EFFECT_TIME.")>".TIMESTAMP.")) "
            ."WHERE u.cooperator_id=" . ($this->cooperator_id + 0) . " AND u.mobile LIKE '%{$filter['bicycle_sn']}%' LIMIT 50";
        $result['users'] = $this->db->getRows($sql);


        $this->response->showSuccessResult($result);
    }

    //合伙人列表
    function cooperator(){
        $this->load->library('sys_model/cooperator',true);
        $this->load->library('sys_model/region',true);
        $cooperator = $this->sys_model_cooperator->getCooperatorList('', 'cooperator_id ASC', '',  '');
        $region = $this->sys_model_region->getRegionList('', 'region_id ASC', '',  '');
        $cooperatorToRegion = $this->sys_model_region->getCooperatorToRegionList('');

        $this->response->showSuccessResult(array(
            'cooperator'=> $cooperator,
            'region'=> $region,
            'cooperatorToRegion'=> $cooperatorToRegion,
        ));
    }

    //个人信息
    function userInfo() {
        // 编辑时获取已有的数据
        $user_id = $this->request->post('user_id');
        $condition = array(
            'user_id' => $user_id
        );
        $this->load->library('sys_model/user', true);
        $this->load->library('sys_model/cooperator',true);
        $info = $this->sys_model_user->getUserInfo($condition);
        $get_common_boolean = get_common_boolean();
        $info['verify_state'] = $get_common_boolean[$info['verify_state']];
        $info['available_state'] = $get_common_boolean[$info['available_state']];
        $info['is_freeze'] = $get_common_boolean[$info['is_freeze']];
        $info['last_update_mobile_time'] = $info['last_update_mobile_time']? date('Y-m-d H:i:s', $info['last_update_mobile_time']): '-';
        $cooperator_name = $this->sys_model_cooperator->getCooperatorInfo(array('cooperator_id'=> $info['cooperator_id']))['cooperator_name'];
        $info['cooperator_id'] = $cooperator_name? $cooperator_name: '-' ;
        $info['uuid'] = strlen($info['uuid']) == 40? 'IOS': 'android';
        $info['add_time'] = !empty($info['add_time']) ? date('Y-m-d H:i:s', $info['add_time']) : '';
        $info['avatar'] = !empty($info['avatar']) ? $info['avatar'] : HTTPS_CATALOG.'images/user_default.png';

        $this->response->showSuccessResult($info);
    }

    //充值提现列表
    function cashapply(){
        $page = isset($this->request->request['page']) ? $this->request->request['page'] : 1;

        $user_id = $this->request->request['user_id'];

        $order = '';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $get_payment_state = get_payment_state();

        $this->load->library('sys_model/deposit', true);
        $rechargeLists = $this->sys_model_deposit->getRechargeList(array('pdr_user_id'=> $user_id), ' pdr_id, pdr_type, pdr_add_time, pdr_amount, pdr_payment_type, pdr_payment_state, pdr_payment_name', $order, $limit);
        $depositCashLists = $this->sys_model_deposit->getDepositCashList(array('pdc_user_id'=> $user_id), $limit, $order, ' pdc_id, pdc_add_time, pdc_amount, pdc_payment_type, pdc_payment_state, pdc_payment_name');

        $arr = array();
        $arr2 = array();

        foreach ($rechargeLists as $k=>$rechargeList){
            $arr[$k]['id'] = $rechargeList['pdr_id'];
            $arr[$k]['type'] = $rechargeList['pdr_type'] == 1? '充押金': '充余额';
            $arr[$k]['add_time'] = date('Y-m-d H:i:s',$rechargeList['pdr_add_time']);
            $arr[$k]['amount'] = $rechargeList['pdr_amount'];
            $arr[$k]['payment_type'] = $rechargeList['pdr_payment_name']? $rechargeList['pdr_payment_name']: '-';
            $arr[$k]['payment_state'] = $get_payment_state[$rechargeList['pdr_payment_state']];
        }

        foreach ($depositCashLists as $k=>$depositCashList){
            $arr2[$k]['id'] = $depositCashList['pdc_id'];
            $arr2[$k]['type'] = '退押金';
            $arr2[$k]['add_time'] = date('Y-m-d H:i:s',$depositCashList['pdc_add_time']);
            $arr2[$k]['amount'] = $depositCashList['pdc_amount'];
            $arr2[$k]['payment_type'] = $depositCashList['pdc_payment_name']? $depositCashList['pdc_payment_name']: '-';
            $arr2[$k]['payment_state'] = $depositCashList['pdc_payment_state'] == 1? '支付完成': '待审核';
        }

        $data = array_merge($arr, $arr2);

        $add_times = array();
        foreach ($data as $v) {
            $add_times[] = $v['add_time'];
        }

        array_multisort($add_times, SORT_DESC, $data);

        $this->assign('page', $page+1);
        $this->assign('data', $data);
        $this->assign('config_limit_admin', $rows);
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('admin/cashapply', $this->output));
    }

    //订单列表
    public function order() {
        $page = isset($this->request->request['page']) ? $this->request->request['page'] : 1;

        $user_id = $this->request->request['user_id'];

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $get_order_state = get_order_state();

        $this->load->library('sys_model/orders',true);
        $orders = $this->sys_model_orders->getOrdersList(array('user_id'=>$user_id), $order, $limit);
        foreach ($orders as &$v){
            $v['add_time'] = $v['add_time']? date('Y-m-d H:i:s',$v['add_time']): '-';
            $v['start_time'] = $v['start_time']? date('Y-m-d H:i:s',$v['start_time']): '-';
            $v['end_time'] = $v['end_time']? date('Y-m-d H:i:s',$v['end_time']): '-';
            $v['settlement_time'] = $v['settlement_time']? date('Y-m-d H:i:s',$v['settlement_time']): '-';
            $v['order_state_describe'] = $get_order_state[$v['order_state']];
            $v['pay_amount'] = $v['order_state'] == 2? $v['pay_amount']: $v['order_amount'];
            $v['coupon'] = $v['coupon_id'] == 0? '否': '是';
            $v['is_limit_free'] = $v['is_limit_free'] == 0? '否': '是';
            $v['is_month_card'] = $v['is_month_card'] == 0? '否': '是';
            $v['order_state'] = $get_order_state[$v['order_state']];
        }

        $this->assign('page', $page+1);
        $this->assign('data', $orders);
        $this->assign('config_limit_admin', $rows);
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('admin/order', $this->output));
    }

    //优惠券列表
    function coupon(){
        $page = isset($this->request->request['page']) ? $this->request->request['page'] : 1;

        $user_id = $this->request->request['user_id'];

        $order = 'add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $this->load->library('sys_model/coupon', true);
        $data = $this->sys_model_coupon->getCouponList(array('c.user_id'=>$user_id), $order, $limit);
        foreach ($data as &$v){
            switch($v['coupon_type']){
                case 1:
                    $v['coupon_type'] ='时间券';
                    break;
                case 2:
                    $v['coupon_type'] = '单次使用券';
                    break;
                case 3:
                    $v['coupon_type'] = '代金券';
                    break;
                case 4:
                    $v['coupon_type'] ='折扣券';
                    break;
            }
            $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            $v['failure_time'] = date('Y-m-d',$v['failure_time']);
            $v['used_time'] = $v['used_time']?date('Y-m-d H:i:s',$v['used_time']):'-';
        }

        $this->assign('page', $page+1);
        $this->assign('data', $data);
        $this->assign('config_limit_admin', $rows);
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('admin/coupon', $this->output));
    }

    //积分流水列表
    function points(){
        $page = isset($this->request->request['page']) ? $this->request->request['page'] : 1;
        $this->load->library('sys_model/points', true);

        $user_id = $this->request->request['user_id'];

        $order = 'pl.add_time DESC';
        $rows = $this->config->get('config_limit_admin');
        $offset = ($page - 1) * $rows;
        $limit = sprintf('%d, %d', $offset, $rows);

        $result = $this->sys_model_points->getPointsList(array('pl.user_id'=>$user_id), $order, $limit);
        foreach ($result as &$v){
            $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            $v['points'] = $v['points'] > 0? '+'.$v['points']: $v['points'];
        }

        $this->assign('page', $page+1);
        $this->assign('data', $result);
        $this->assign('config_limit_admin', $rows);
        $this->assign('static', HTTP_IMAGE);

        $this->response->setOutput($this->load->view('admin/points', $this->output));
    }

    private function summary() {
        $this->load->library('sys_model/data_sum', true);
        $condition = array(
            'cooperator_id' => $this->cooperator_id,
        );
        $user_sum = $this->sys_model_data_sum->getRegisterSumC($condition);
        $bicycle_sum = $this->sys_model_data_sum->getBicycleSumC($condition);
        $used_sum = $this->sys_model_data_sum->getUsedBicycleSumC($condition);
        $fault_sum = $this->sys_model_data_sum->getFaultBicycleSumC($condition);

        $recharge_sum = $this->sys_model_data_sum->getRechargeSumC($condition);
        $coupon_sum = $this->sys_model_data_sum->getCouponSumC($condition);

        $this->assign('user_sum', $user_sum);
        $this->assign('bicycle_sum', $bicycle_sum);
        $this->assign('used_sum', $used_sum);
        $this->assign('fault_sum', $fault_sum);
        $this->assign('recharge_sum', $recharge_sum);
        $this->assign('coupon_sum', $coupon_sum);
    }

    /**
     * 订单完成
     * @param $data
     * @return array
     */
    private function finishOrders($data) {
        $device_id = $data['device_id'];
        $cmd = $data['cmd'];

        if (strtolower($cmd) == 'close' || strtolower($cmd) == 'normal') {
            $order_info = $this->sys_model_orders->getOrdersInfo("(order_state = '-3' OR order_state = '1' OR order_state = '-2') AND lock_sn = {$device_id}");

            $faultInfo = array();
            //订单状态为-3的处理
            if($order_info['order_state'] == '-3'){
                $faultInfo = $this->sys_model_fault->getFaultInfo(array(
                    'bicycle_sn' => $order_info['bicycle_sn'],
                    'fault_type' => 12,
                    'processed' => 0,
                ));
                $order_end_time = $faultInfo['add_time'];
            }else{
                $order_end_time = time();
            }

            $arr = array(
                'end_time' => $order_end_time,
                'order_state' => 2
            );

            if (!empty($order_info)) {
                if($order_info['order_state'] == '-2') {
                    $this->sys_model_orders->updateOrders(array('order_id'=>$order_info['order_id']), array('order_state'=>-1));
                    return callback(true);
                }
                try {
                    $this->sys_model_orders->begin();

                    //订单状态为-3的处理
                    if($order_info['order_state'] == '-3'){
                        $faultData = array(
                            'handling_time'=> TIMESTAMP,
                            'processed'=> 1,
                            'content'=> '后台手动结束订单，并修复相关报障',
                        );

                        $repair_data = array(
                            'bicycle_id' => $order_info['bicycle_id'],
                            'repair_type' => 4,
                            'add_time' => TIMESTAMP,
                            'remarks' => '后台手动结束订单，并修复相关报障',
                            'admin_id' => '1',
                            'fault_id' => $faultInfo['fault_id'],
                        );

                        $where = array(
                            'fault_id'=> $faultInfo['fault_id']
                        );

                        if ($this->sys_model_fault->updateFault($where, $faultData) && $this->sys_model_repair->addRepair($repair_data)) {
                            $faultAllOk = !$this->sys_model_fault->getFaultList(array('bicycle_id' => $order_info['bicycle_id'], 'processed' => 0), null, 1);
                            if ($faultAllOk) {
                                $this->sys_model_bicycle->updateBicycle(array('bicycle_id' => $order_info['bicycle_id']), array('fault' => 0));
                            }
                        }
                        $this->sys_model_user->updateUser(array('user_id'=>$faultInfo['user_id']), array('is_freeze'=>0));
                    }

                    $start_time = $order_info['start_time'];
                    $end_time = $arr['end_time'];
                    if ($end_time == 0 || $start_time > $end_time) {
                        $end_time = $start_time = 0;
                    }
                    $riding_time = $end_time - $start_time; //骑行时间
                    $unit = ceil($riding_time / TIME_CHARGE_UNIT);//计费单元
                    $amount = $unit * PRICE_UNIT; //骑行费用

                    $region_info = $this->sys_model_region->getRegionInfo(array('region_id' => $order_info['region_id']));
                    if (!empty($region_info)) {
                        if ($region_info['region_charge_time'] == 0) $region_info['region_charge_time'] = 30 * 60; //防止0
                        $unit = ($region_info['region_charge_time']) ? ceil($riding_time / ($region_info['region_charge_time'] * 60)) : $unit;
                        $amount = isset($region_info['region_charge_fee']) ? floatval($unit * $region_info['region_charge_fee']) : $amount;
                    }

                    $sys_model_deposit = new \Sys_Model\Deposit($this->registry);
                    $sys_model_user = new \Sys_Model\User($this->registry);

                    $arr_data = array(
                        'user_id' => $order_info['user_id'],
                        'user_name' => $order_info['user_name'],
                        'amount' => $amount,
                        'order_sn' => $order_info['order_sn'],
                        'end_lat' => $data['lat'],
                        'end_lng' => $data['lng']
                    );

                    $arr['order_amount'] = $amount;
                    $arr['pay_amount'] = $amount;

                    $user_info = $sys_model_user->getUserInfo(array('user_id' => $order_info['user_id']));
                    if (empty($user_info)) {
                        throw new \Exception('error_user_info');
                    }
                    //扣费金额大于骑行的费用
                    if ($user_info['available_deposit'] < $amount) {
                        $change_type = 'order_freeze';
                        $arr_data['left_amount'] = $user_info['available_deposit'];
                        $arr_data['amount'] = $amount;
                    } else {
                        $change_type = 'order_pay';
                    }

                    $sys_model_coupon = new \Sys_Model\Coupon($this->registry);
                    $coupon_info = $sys_model_coupon->getRightCoupon(array('user_id' => $order_info['user_id']));
                    if (!empty($coupon_info)) {
                        if ($coupon_info['coupon_type'] != 3) {

                        } else {
                            $arr_data['amount'] = $arr_data['amount'] - $coupon_info['number'];
                        }
                        //更新优惠券的信息
                        $update = $sys_model_coupon->dealCoupon($coupon_info);
                        if ($update) {
                            $arr['coupon_id'] = $coupon_info['coupon_id'];
                        }
                    } else {
                        $insert_id = $sys_model_deposit->changeDeposit($change_type, $arr_data);
                        if (!$insert_id) {
                            throw new \Exception('error_insert_order_amount');
                        }
                    }

                    $line_data = array(
                        'user_id' => $order_info['user_id'],
                        'order_id' => $order_info['order_id'],
                        'lng' => $data['lng'],
                        'lat' => $data['lat'],
                        'add_time' => time(),
                    );

                    $this->sys_model_orders->addOrderLine($line_data);

                    $order_lines = $this->sys_model_orders->getOrderLine(array('order_id' => $order_info['order_id']));
                    $tool_distance = new Distance();
                    $distance = $tool_distance->sumDistance($order_lines);
                    $distance = round($distance * 1000, -1);

                    $arr['distance'] = $distance;
                    $arr['end_lat'] = isset($data['lat']) ? $data['lat'] : '';
                    $arr['end_lng'] = isset($data['lng']) ? $data['lng'] : '';

                    //更新订单状态
                    $update = $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), $arr);
                    if (!$update) {
                        throw new \Exception('error_update_order_state_failure');
                    }
                    //单车表的lock_sn应该加了索引，所以使用此字段来更新
                    $this->sys_model_bicycle->updateBicycle(array('lock_sn' => $device_id), array('is_using' => 0, 'last_used_time' => time()));

                    $this->sys_model_orders->commit();

                    $data = array(
                        'cmd' => 'close',
                        'order_sn' => $order_info['order_sn'],
                        'user_id' => $order_info['user_id'],
                        'device_id' => $device_id
                    );
                } catch (\Exception $e) {
                    $this->sys_model_orders->rollback();
                    return callback(false, $e->getMessage());
                }

                // 增加信用分
                $this->registry->get('load')->library('logic/credit', true);
                $this->registry->get('logic_credit')->addCreditPointOnFinishCycling($order_info['user_id']);

                //增加使用次数
                $this->updateUsageCount($data['device_id']);

                return callback(true, '', $data);
            }
        }
        return callback(false, 'data_error', $data);
    }

    //锁使用次数添加
    private function updateUsageCount($lock_sn){
        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo(array('lock_sn'=>$lock_sn));

        if($this->sys_model_bicycle_usage->getUsageCountInfo(array('bicycle_sn' =>$bicycle_info['bicycle_sn'],  'date'=> date('Y-m-d', time())))){//有没有添加今天的使用次数记录的锁
            $this->sys_model_bicycle_usage->updateUsageCount(array('bicycle_sn' =>$bicycle_info['bicycle_sn'],  'date'=> date('Y-m-d', time())), array('count'=> array('exp', 'count+1')));
        }else{
            $data = array(
                'date'=> date('Y-m-d', time()),
                'bicycle_sn'=> $bicycle_info['bicycle_sn'],
                'count'=> 1,
            );
            $this->sys_model_bicycle_usage->addUsageCount($data);
        }
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

    private function cashSubmit($pdc_info) {
        if ($pdc_info['pdc_payment_code'] == 'alipay') {
            //支付宝有密码退款
//            $this->sys_model_deposit->aliPayRefund($pdc_info);
        } else {
            $ssl_cert_path = WX_SSL_CONF_PATH . $this->config->get('config_wxpay_ssl_cert_path') . '/' . $pdc_info['pdc_payment_type'] . '/apiclient_cert.pem';
            $ssl_key_path = WX_SSL_CONF_PATH . $this->config->get('config_wxpay_ssl_cert_path') . '/' . $pdc_info['pdc_payment_type'] . '/apiclient_key.pem';
            define('WX_SSLCERT_PATH', $ssl_cert_path);
            define('WX_SSLKEY_PATH', $ssl_key_path);
            $result = $this->sys_model_deposit->wxPayRefund($pdc_info);
            if ($result['state'] == true) {
                $this->response->showSuccessResult();
            } else {
                die($result['msg']);
            }
        }
    }
}
