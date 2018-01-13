<?php
use Enum\ErrorCode;

class ControllerAccountOrder extends Controller {
    private $direct_output = false;

    const LOCK_FACTORY_LUOPING = 1;
    /**
     * 订单预约，已在startup/deposit和startup/order里面做特殊处理
     */

    /**
     * 获取订单详情
     */
    public function getOrderInfo() {
        if (!isset($this->request->post['order_sn']) || empty($this->request->post['order_sn'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'),ErrorCode::ERROR_MISSING_PARAMETER);
        }
        $order_sn = $this->request->post['order_sn'];
        $this->load->library('sys_model/orders');
        $order_info = $this->sys_model_orders->getOrdersInfo(array('order_sn' => $order_sn));

        if (empty($order_info)) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'),ErrorCode::ERROR_MISSING_PARAMETER);
        }

        $user_id = $this->startup_user->userId();
        $user_info = $this->startup_user->getUserInfo();
        if ($order_info['user_id'] != $user_id) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'),ErrorCode::ERROR_MISSING_PARAMETER);
        }
        //$order_info['order_state'] = $order_info['order_state'] == -2 ? 0 : $order_info['order_state'];
        $order_info['available_deposit'] = $user_info['available_deposit'];
        $order_info = $this->format($order_info);
        $this->response->showSuccessResult($order_info, $this->language->get('success_loading'));
    }

    /**
     * 获取订单轨迹，此方法可以作为接口，也可以直接返回（事先设置$this->direct_output为true）
     * @return array
     */
    public function getOrderLine() {
        $order_sn = $this->request->post['order_sn'];
        $user_id = $this->startup_user->userId();
        $this->load->library('sys_model/orders');
        $order_info = $this->sys_model_orders->getOrdersInfo(array('order_sn' => $order_sn, 'user_id' => $user_id));
        if (empty($order_info)) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'),1);
        }
        $order_line = $this->sys_model_orders->getOrderLine(array('order_id' => $order_info['order_id']));
        $line_data = array();
        foreach ($order_line as $line) {
            $line_data[] = array('lat' => $line['lat'], 'lng' => $line['lng']);
        }
        $this->load->library('tool/distance');
        $distance = $this->tool_distance->sumDistance($line_data);
        $distance = round($distance, 2);
        $calorie = round(60 * $distance * 1.036, 2);
        $emission = $distance ? round($distance * 0.275 * 1000.0) : 0;
        if ($this->direct_output) {
            return array('line_data' => $line_data, 'distance' => $distance, 'calorie' => $calorie, 'emission' => $emission);
        }
        $this->response->showSuccessResult(array('distance' => $distance, 'line_list' => $line_data, 'order_sn' => $order_sn));
    }

    /**
     * 取消订单
     */
    public function cancelOrder() {
        $order_sn = $this->request->post['order_sn'];
        $user_id = $this->startup_user->userId();
        $this->load->library('sys_model/orders');
        //$order_info = $this->sys_model_orders->getOrdersInfo(array('order_sn' => $order_sn, 'user_id' => $user_id, 'order_state' => '0', 'add_time' => array('egt', time() - BOOK_EFFECT_TIME)));
	$order_info = $this->sys_model_orders->getOrdersInfo(array('order_sn' => $order_sn, 'user_id' => $user_id, '_string' => '(order_state=-2) OR (order_state=0 and add_time > ' . (time() - BOOK_EFFECT_TIME). ')'));
        if (empty($order_info)) {
            $this->response->showErrorResult($this->language->get('error_invalid_order'), 135);
        }
        $order_id = $order_info['order_id'];
        $update = $this->sys_model_orders->updateOrders(array('order_id' => $order_id), array('order_state' => '-1', 'end_time'=>time()));
        if (!$update) {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
        $this->response->showSuccessResult(array('order_sn' => $order_sn), $this->language->get('success_cancel'));
    }

    /**
     * 获取用户当前订单状况
     */
    public function current() {
        $user_id = $this->startup_user->userId();
        $this->load->library('sys_model/orders');
        $t = time() - BOOK_EFFECT_TIME;
        $order_info = $this->sys_model_orders->getOrdersInfo("`user_id`={$user_id} AND (`order_state`=1 OR (`order_state`=0 AND `add_time`>={$t}) OR `order_state`=-2)");
        if (empty($order_info)) {
            $this->response->showSuccessResult(array('has_order' => false), $this->language->get('error_no_order'));
        }
        else {
            $user_info = $this->startup_user->getUserInfo();
            $order_info['available_deposit'] = $user_info['available_deposit'];
            $this->request->post['order_sn'] = $order_info['order_sn'];

            if($order_info['order_state'] == 0 || $order_info['order_state'] == 1) {
                $this->load->library('sys_model/lock');
                $lock_info = $this->sys_model_lock->getLockInfo(array('lock_sn'=>$order_info['lock_sn']));
                $this->load->library('sys_model/bicycle');
                $bike_info = $this->sys_model_bicycle->getBicycleInfo(array('bicycle_sn'=>$order_info['bicycle_sn']), 'is_scenic');
                $order_info['end_lat'] = $lock_info['lat'];
                $order_info['end_lng'] = $lock_info['lng'];
                $order_info['mac_address'] = $lock_info['mac_address'];
                $order_info['encrypt_key'] = $lock_info['encrypt_key'];
                $order_info['password'] = $lock_info['password'];
                $order_info['is_scenic'] = $bike_info['is_scenic'];
                $order_info['left_time'] = (time() - $order_info['start_time'] <= 120) ? 120 - (time() - $order_info['start_time']): 0;
            }
            $this->response->showSuccessResult(array('has_order' => true, 'current_order' => $this->format($order_info)), $this->language->get('success_read'));
        }
    }

    /**
     * 格式化订单数据
     * @param $data
     * @return array
     */
    private function format($data) {
        $this->direct_output = true;
        $arr = $this->getOrderLine();

        //修正预约有效期内没有取消预约的订单的数据
        if($data['order_state']==0 && time()>($data['add_time']+BOOK_EFFECT_TIME) && $data['start_time']==0 && $data['end_time']==0) {
            $data['order_state']= -1;
            $data['end_time'] = $data['add_time']+BOOK_EFFECT_TIME;
        }

        //计算的结束时间
        $time = ($data['order_state'] == 2 || $data['order_state'] == -1) ? $data['end_time'] : time();
        $riding_time = $time - ($data['order_state'] <= 0 ? $data['add_time'] : $data['start_time']);

        $hours = floor($riding_time / (60 * 60));
        $min = floor(($riding_time - ($hours * 60 * 60)) / 60);
        $data['time'] = array(
            'hours' => $hours,
            'min' => $min
        );

        if($data['order_state']==0) {
            $data['keep_time'] = BOOK_EFFECT_TIME - (time() - $data['add_time']);
        } else {
            $data['keep_time'] = 0;
        }

        $unit = ($data['is_limit_free'] || $data['is_month_card']) ? ($riding_time > 10800 ? ceil($riding_time - 10800) / TIME_CHARGE_UNIT : 0) : ceil($riding_time / TIME_CHARGE_UNIT);//计费单元
        $amount = $unit * PRICE_UNIT; //骑行费用

        $this->load->library('sys_model/region');
        $region_info = $this->sys_model_region->getRegionInfo(array('region_id' => $data['region_id']));
        if (!empty($region_info)) {
            if ($region_info['region_charge_time'] == 0) $region_info['region_charge_time'] = 30 * 60; //防止0
            $free_time = $data['is_limit_free'] ? 10800 : 7200;
            $unit = ($region_info['region_charge_time']) ? ($data['is_limit_free'] || $data['is_month_card']) ? ceil($riding_time > $free_time ? ($riding_time - $free_time) / ($region_info['region_charge_time'] * 60) : 0) : ceil($riding_time / ($region_info['region_charge_time'] * 60)) : $unit;
            $amount = isset($region_info['region_charge_fee']) ? floatval($unit * $region_info['region_charge_fee']) : $amount;
        }

        $data['price_unit'] = isset($region_info['region_charge_fee']) ? $region_info['region_charge_fee'] : PRICE_UNIT;
        if ($riding_time <= 120) {
            $amount = 0;
        }

        if ($data['order_state'] == 1) {
            $data['order_amount'] = $amount;
        }

        $data = array_merge($data, $arr);
        return $data;
    }

    /**
     * 结束异常订单，几个问题，1.用户存在未结束的订单可否继续租车，2.如果可以显示余额怎么玩，3.无登陆也可以调用此接口
     */
    public function checkPreFinishedRide() {
        $post = $this->request->post(array('trade_no', 'phone', 'finish_time', 'lat', 'lng'));
        if (!$post['trade_no']) {
            $this->response->showErrorResult('trade no不能为空', 12301);
        }
        if (!$post['finish_time']) {
            $this->response->showErrorResult('finish time不能为空', 12302);
        }

        $record_data = array(
            'trade_no' => $post['trade_no'],
            'finish_time' => $post['finish_time'],
            'system_time' => time(),
            'user_id' => $this->startup_user->userId(),
            'lat' => $post['lat'],
            'lng' => $post['lng'],
            'phone' => $post['phone'],
            'from_client' => isset($this->request->get['fromApi']) ? $this->request->get['fromApi'] : ($this->request->get_request_header('client') && $this->request->get_request_header('client') == 'miniapp' ? 'mini_app' : 'wechat'),
            'client_version' => isset($this->request->get['version']) ? $this->request->get['version'] : 0
        );
        $this->db->table('trade')->insert($record_data);

        if ($post['finish_time'] > time()) {
            $post['finish_time'] = time();
        }

        $post['trade_no'] = strtoupper($post['trade_no']);

        $this->load->library('sys_model/orders', true);
        $order_info = $this->sys_model_orders->getOrdersInfo(array('trade_no' => $post['trade_no']));
        if (empty($order_info)) {
            $this->response->showErrorResult('不存在此订单', 12306);
        }
        if ($order_info['order_state'] == 2) {
            $this->response->showSuccessResult(array(), '订单已结束');
        }
        $this->load->library('logic/orders', true);
        //订单未结束，仍在计费的情况
        if ($order_info['order_state'] == 1) {
            if ($order_info['user_id'] == $this->startup_user->userId()) $this->response->showErrorResult('当前进行中的订单');
            $update_data = array(
                'finish_time' => $post['finish_time'],
                'order_id' => $order_info['order_id'],
                'lat' => $post['lat'],
                'lng' => $post['lng']
            );
            $callback = $this->logic_orders->closeMCHOrder($update_data);
        } elseif ($order_info['order_state'] == 3) {
            $update_data = array(
                'order_id' => $order_info['order_id'],
                'finish_time' => $post['finish_time'],
                'lat' => $post['lat'],
                'lng' => $post['lng']
            );

            $callback = $this->logic_orders->closeMCHOrder($update_data);
        } else {
            $callback = array('state' => false, '订单状态不对');
        }

        $lock_data = array(
            'lock_status' => 0,
            'system_time' => time(),
            'device_time' => $post['finish_time'] ? $post['finish_time'] : time(),
            'last_close_time' => $post['finish_time'] ? $post['finish_time'] : time(),
        );

        if (abs($post['lat']) > 0 && abs($post['lng']) > 0) {
            $lock_data['lat'] = $lock_data['amap_lat'] = $post['lat'];
            $lock_data['lng'] = $lock_data['amap_lng'] = $post['lng'];
        }

        if ($callback['state']) {
            $this->load->library('sys_model/bicycle', true);
            $bicycle_info = $this->sys_model_bicycle->getBicycleInfo(array('bicycle_sn' => $order_info['bicycle_sn']));
            if (!empty($bicycle_info)) {
                if ($bicycle_info['is_using']) {
                    $this->sys_model_bicycle->updateBicycle(array('bicycle_id' => $bicycle_info['bicycle_id']), array('is_using' => 0, 'last_used_time' => $lock_data['device_time']));
                }
            }

            $userInfo = $this->startup_user->getUserInfo();
            if ($userInfo['cooperator_id'] == 0) {
                $user_data = array(
                    'cooperator_id' => $bicycle_info['cooperator_id'],
                    'region_id' => $bicycle_info['region_id'],
                );
                $this->db->table('user')->where(array('user_id' => $userInfo['user_id']))->update($user_data);
            }

            $this->load->library('sys_model/lock', true);
            $this->sys_model_lock->updateLock(array('lock_sn' => $order_info['lock_sn']), $lock_data);

            $this->response->showSuccessResult(array('order_id' => $order_info['order_id'], 'order_sn' => $order_info['order_sn']), '成功结束订单');
        } else {
            $this->response->showErrorResult($callback['msg']);
        }
    }

    /**
     * 创建新订单
     */
    public function createNewOrder() {
        $user_id = $this->startup_user->userid();
        $this->load->library('sys_model/orders', true);
        $t = time() - BOOK_EFFECT_TIME;
        $where = "(user_id='$user_id' AND order_state=1)";
        $where .= " OR (user_id='$user_id' AND order_state=0 AND add_time > {$t})";
        $where .= " OR (user_id='$user_id' AND order_state=-2 AND add_time > " . (time() - $this->config->get('config_cancel_under_riding_time') + 0) . ")";
        $order_info = $this->sys_model_orders->getOrdersInfo($where);
        if (!empty($order_info)) {
            $this->response->showSuccessResult(array('order_id' => $order_info['order_id']));
        }
        $input = $this->request->post(array('trade_no', 'bicycle_sn'));
        if (!$input['bicycle_sn']) {
            $this->response->showErrorResult('单车编号不能为空');
        }
        $region_city_code = $region_city_ranking = $bicycle_sn = '';
        if (strlen($input['bicycle_sn']) == 11) {
            sscanf($input['bicycle_sn'], '%03d%02d%06d', $region_city_code, $region_city_ranking, $bicycle_sn);
            $bicycle_sn = sprintf('%06d', $bicycle_sn);
        }
        $condition = array(
            'bicycle_sn' => $bicycle_sn
        );
        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/lock', true);
        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo($condition, 'bicycle_id, bicycle_sn, lock_sn');
        if (empty($bicycle_sn)) {
            $this->response->showErrorResult('系统不存在此单车');
        }
        if (empty($bicycle_info['lock_sn'])) {
            $this->response->showErrorResult('锁未入库');
        }
        $lock_info = $this->sys_model_lock->getLockInfo(array('lock_sn' => $bicycle_info['lock_sn']));
        if (empty($lock_info)) {
            $this->response->showErrorResult('单车绑定的锁已被移除，请重新入库后再试');
        }
        $user_info = $this->startup_user->getUserInfo();
        $insert_data = array(
            'user_id' => $user_info['user_id'],
            'user_name' => $user_info['mobile'],
            'lock_sn' => $lock_info['lock_sn'],
            'bicycle_id' => $bicycle_info['bicycle_id'],
            'bicycle_sn' => $bicycle_info['bicycle_sn'],
            'lock_type' => $lock_info['lock_type'],
            'add_time' => time(),
        );
        if ($lock_info['lock_factory'] == self::LOCK_FACTORY_LUOPING) {
            if (!$input['trade_no']) $this->response->showErrorResult('trade_no不能为空');
            $insert_data['trade_no'] = $input['trade_no'];
        }

        $insert_id = $this->sys_model_orders->addOrders($insert_data);
        $insert_id ? $this->response->showSuccessResult(array('order_id' => $insert_id), '订单成功生产') : $this->response->showErrorResult('订单生产失败，请重试');
    }

	// public function 
    /**
     * 骑行中添加定位到订单
     */
    public function addLocationToOrder() {
        $post = $this->request->post(array('order_id', 'lat', 'lng', 'add_time'));
        if (!$post['order_id'] || !$post['lat'] || !$post['lng']) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'));
        }

        $user_id = $this->startup_user->userId();
        $this->load->library('sys_model/orders', true);
        $this->load->library('sys_model/lock', true);
        $order_info = $this->sys_model_orders->getOrdersInfo(array('order_id' => $post['order_id']));

        $data = array (
            'assist_time' => time(),
            'from_platform' => 1,
            'system_time' => time(),
        );

        if (abs($post['lat']) > 0 && abs($post['lng']) > 0) {
            $data['lat'] = $data['amap_lat'] = $post['lat'];
            $data['lng'] = $data['amap_lng'] = $post['lng'];
        }

        //更新锁信息
        $this->sys_model_lock->updateLock(array('lock_sn' => $order_info['lock_sn']), $data);
        if ($order_info['order_state']  == 2 || $user_id != $order_info['user_id']) {
            $this->response->showErrorResult('订单已完成，上传坐标不保存', 12308);
        }

        //写入
        $line_data['lat'] = $post['lat'];
        $line_data['lng'] = $post['lng'];
        $line_data['order_id'] = $order_info['order_id'];
        $line_data['user_id'] = $order_info['user_id'];
        $line_data['add_time'] = $post['add_time'] ? $post['add_time'] : time();
        $line_data['status'] = 1;
        if (abs($line_data['lat']) > 0 && abs($line_data['lng']) > 0) {
            $insert_id = $this->sys_model_orders->addOrderLine($line_data);
            $this->response->showSuccessResult(array('insert_id' => $insert_id), '添加成功');
        } else {
            $this->response->showErrorResult('0.00经度，纬度不写入数据库');
        }
    }

    /**
     * 蓝牙使订单生效
     */
    public function effectOrderByBT() {
        $post = $this->request->post(array('order_id', 'lat', 'lng', 'trade_no', 'voltage'));
        if (!$post['order_id'] || !$post['lat'] || !$post['lng']) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'));
        }
        $this->load->library('sys_model/orders', true);
        $this->load->library('sys_model/lock', true);
        $order_info = $this->sys_model_orders->getOrdersInfo(array('order_id' => $post['order_id']));
        if (empty($order_info)) {
            $this->response->showErrorResult('不存在此订单', 12306);
        }

        $lock_info = $this->sys_model_lock->getLockInfo(array('lock_sn' => $order_info['lock_sn']));
        if (empty($lock_info)) {
            $this->response->showErrorResult('系统不存在此锁信息');
        }

        //如果没有定位，就把锁的定位作为当前的定位
        $lat = $post['lat'] ? $post['lat'] : $lock_info['lat'];
        $lng = $post['lng'] ? $post['lng'] : $lock_info['lng'];

        $data = array (
            'cmd' => 'open',
            'device_id' => $lock_info['lock_sn'],
            'result' => 'ok',
            'info' => '蓝牙开锁成功',
            'serialnum' => $order_info['add_time'],
            'open_time' => time(),
            'lock_type' => $lock_info['lock_type'],
            'lat' => $lat,
            'lng' => $lng,
        );
        //保存trade no
        if ($post['trade_no']) {
            $data['trade_no'] = strtoupper($post['trade_no']);
        }

        $lock_data = array(
            'system_time' => time(),
            'open_nums' => array('exp', 'open_nums+1'),
            'lock_status' => 1,
            'voltage' => $post['voltage'],
        );

        if (abs($lat) > 0 && abs($lng) > 0) {
            $lock_data['lat'] = $lock_data['amap_lat'] = $lat;
            $lock_data['lng'] = $lock_data['amap_lng'] = $lng;
        }

        if ($this->voltage2Power($post['voltage'])) {
            $lock_data['battery'] = $this->voltage2Power($post['voltage']);
            $lock_data['gx'] = $post['voltage'] * 100;
        }

        $this->sys_model_lock->updateLock(array('lock_id' => $lock_info['lock_id']), $lock_data);

        //更新单车使用状态
        $this->load->library('sys_model/bicycle', true);
        $this->sys_model_bicycle->updateBicycle(array('bicycle_id' => $order_info['bicycle_id']), array('is_using' => 1));

        $result = $this->effectOrder($data);
        $result['state'] ? $this->response->showSuccessResult($result['data'], $result['msg']) : $this->response->showErrorResult($result['msg'], 12307);
    }

    /**
     * 生订单生效
     * @param $data
     * @return mixed
     */
    private function effectOrder($data) {
        $this->load->library('logic/orders', true);
        $this->load->library('sys_model/instruction', true);
        //添加开锁记录
        $insert_data = array();
        $fields = array('cmd', 'device_id', 'result', 'info', 'serialnum', 'open_time', 'lock_type');

        foreach ($data as $key => $value) {
            if (in_array($key, $fields)) {
                $insert_data[$key] = $data[$key];
            }
        }

        $this->sys_model_instruction->addInstructionRecord($insert_data);
        $result = $this->logic_orders->effectOrders($data);
        return $result;
    }

    //因为电池电压受多种影响
    private function voltage2Power($voltage) {
        if ($voltage == 4.2) {
            return 100;
        } elseif ($voltage < 4.2 && $voltage >= 4.06) {
            return 100 - round((4.2 - $voltage) * 10 / (4.2 - 4.06));
        } elseif ($voltage < 4.06 && $voltage >= 3.98) {
            return 90 - round((4.06 - $voltage) * 10 / (4.06 - 3.98));
        } elseif ($voltage < 3.98 && $voltage >= 3.92) {
            return 80 - round((3.98 - $voltage) * 10 / (3.98 - 3.92));
        } elseif ($voltage < 3.92 && $voltage >= 3.87) {
            return 70 - round((3.92 - $voltage) * 10 / (3.92 - 3.87));
        } elseif ($voltage < 3.87 && $voltage >= 3.82) {
            return 60 - round((3.87 - $voltage) * 10 / (3.87 - 3.82));
        } elseif ($voltage < 3.82 && $voltage >= 3.79) {
            return 50 - round((3.82 - $voltage) * 10 / (3.82 - 3.79));
        } elseif ($voltage < 3.79 && $voltage >= 3.77) {
            return 40 - round((3.79 - $voltage) * 10 / (3.79 - 3.77));
        } elseif ($voltage < 3.77 && $voltage >= 3.74) {
            return 30 - round((3.77 - $voltage) * 10 / (3.77 - 3.74));
        } elseif ($voltage < 3.74 && $voltage >= 3.68) {
            return 20 - round((3.74 - $voltage) * 10 / (3.74 - 3.68));
        } elseif ($voltage < 3.68 && $voltage >= 3.45) {
            return 10 - round((3.68 - $voltage) * 10 / (3.68 - 3.45));
        } elseif ($voltage < 3.45 && $voltage >= 3) {
            return 5 - round((3.45 - $voltage) * 10 / (3.45 - 3.00));
        } else {
            return 0;
        }
    }

    /**
     * 关闭订单
     */
    public function closeOrder() {
        $post = $this->request->post(array('order_id', 'lat', 'lng', 'is_pre', 'finish_time'));
        if (!$post['order_id'] || !$post['lat'] || !$post['lng']) {
            $this->response->showErrorResult('参数错误');
        }

        //处理IOS某个版本的问题
        $gets = $this->request->get(array('fromApi', 'version'));
        if ($gets['version'] == 61 && $gets['fromApi'] == 'ios') {
            $post['is_pre'] = 1;
        }

        $this->load->library('logic/orders', true);
        $this->load->library('sys_model/orders', true);
        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/lock', true);

        $order_info = $this->sys_model_orders->getOrdersInfo(array('order_id' => $post['order_id']));
        if (empty($order_info)) {
            $this->response->showErrorResult('参数有误');
        }

        if ($order_info['order_state'] == 2) {
            $this->response->showErrorResult('行程已结束，无需重复提交');
        }
        //-3冻结的订单暂不考虑处理
        if ($order_info['order_state'] != 1) {
            $this->response->showErrorResult('非进行中的订单，无需结束');
        }

        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo(array('bicycle_sn' => $order_info['bicycle_sn']));
        if (empty($bicycle_info)) {
            $this->response->showErrorResult('不存在此编号的单车');
        }

        $userInfo = $this->startup_user->getUserInfo();
        if ($userInfo['cooperator_id'] == 0) {
            $user_data = array(
                'cooperator_id' => $bicycle_info['cooperator_id'],
                'region_id' => $bicycle_info['region_id'],
            );
            $this->db->table('user')->where(array('user_id' => $userInfo['user_id']))->update($user_data);
        }

        if ($bicycle_info['is_using']) {
            $this->sys_model_bicycle->updateBicycle(array('bicycle_id' => $bicycle_info['bicycle_id']), array('is_using' => 0));
        }

        $lock_info = $this->sys_model_lock->getLockInfo(array('lock_sn' => $bicycle_info['lock_sn']));

        $data = array(
            'order_id' => $post['order_id'],
            'lat' => $post['lat'],
            'lng' => $post['lng'],
        );
        if ($post['finish_time']) $data['finish_time'] = $post['finish_time'];
        //机械锁关锁
        if ($lock_info['lock_type'] == 3) {
            if (!isset($this->request->post['order_img']) && isset($this->request->files['order_img'])) {
                $this->response->showErrorResult('请上传图片');
            }
            $uploader = new \Uploader(
                'order_img', //字段名
                array(
                    'allowFiles' => array('.jpg', '.jpeg', '.png'),
                    'maxSize' => 5 * 1024 * 1024,
                    'pathFormat' => 'orders/{yyyy}{mm}{dd}{hh}{ii}{ss}{rand:4}'
                ),
                empty($this->request->files['order_img']) ? 'base64' : 'upload',
                $this->request->files
            );

            $fileInfo = $uploader->getFileInfo();
            if ($fileInfo['state'] == 'SUCCESS') {
                $data['has_upload_image'] = 1;
                $data['image_url'] = $fileInfo['url'];
            } else {
                $this->response->showErrorResult('上传图片失败');
            }
        }

        $update = false;

		$order_data = array(
            'device_time' => time(),
            'close_type' => in_array($lock_info['lock_type'], array(2, 4)) ? 'blt' : 'machine',
            'data_type' => 'post',
            'before_state' => $post['is_pre'] ? 3 : 1
        );

        //蓝牙锁(包括二合一锁)
        if (in_array($lock_info['lock_type'], array(2, 4, 5))) {
            if (!$post['is_pre']) { //正常结束订单才计费
                $callback = $this->logic_orders->closeBLTOrder($data);
                if (!$callback['state']) {
                    $this->response->showErrorResult('计费失败');
                }
            }
            //预结束订单
            if ($post['is_pre']) {
                $order_data['order_state'] = 3;
            }

            $update = $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), $order_data);
        } else if ($lock_info['lock_type'] == 3) { //机械锁
            $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), $order_data);
            $update = $this->logic_orders->closeMCHOrder($data);
        }

        //更新锁的信息
        $lock_data = array(
            'lock_status' => 0,
            'system_time' => time(),
            'device_time' => $post['finish_time'] ? $post['finish_time'] : time(),
        );
        //经纬度为零时不更新这数据
        if (abs($post['lat']) > 0 && abs($post['lng']) > 0) {
            $lock_data['lat'] = $lock_data['amap_lat'] = $post['lat'];
            $lock_data['lng'] = $lock_data['amap_lng'] = $post['lng'];
        }

        if (!$post['is_pre']) {
            $lock_data['last_close_time'] = $post['finish_time'];
        }

        $bicycle_data = array(
            'is_using' => 0,
            'last_used_time' => $lock_data['device_time'],
        );

        $this->sys_model_bicycle->updateBicycle(array('bicycle_id' => $bicycle_info['bicycle_id']), $bicycle_data);
        $this->sys_model_lock->updateLock(array('lock_id' => $lock_info['lock_id']), $lock_data);

        $order_info = $this->sys_model_orders->getOrdersInfo(array('order_id' => $post['order_id']));
        $update ? $this->response->showSuccessResult($order_info, '成功结束订单') : $this->response->showErrorResult('操作失败');
    }

    /**
     * 补发GPRS开锁
     */
    public function effectOrderByGPRS() {
        $post = $this->request->post(array('order_id'));
        if (!$post['order_id']) {
            $this->response->showErrorResult('参数错误');
        }
        $this->load->library('sys_model/orders', true);
        $this->load->library('sys_model/lock', true);
        $order_info = $this->sys_model_orders->getOrdersInfo(array('order_id' => $post['order_id']));
        if (empty($order_info)) {
            $this->response->showErrorResult('不存在此订单', 12306);
        }
        if ($order_info['order_state'] != -2) {
            $this->response->showErrorResult('此订单已取消或已生效');
        }
        $lock_info = $this->sys_model_lock->getLockInfo(array('lock_sn' => $order_info['lock_sn']));
        if (empty($lock_info)) {
            $this->response->showErrorResult('订单信息有误');
        }
        if ($lock_info['lock_type'] != 4) {
            $this->response->showErrorResult('此锁类型支持补发GPRS开锁，请联系管理员确认是否锁类型设置错误');
        }

        $this->instructions_instructions = new Instructions\Instructions($this->registry);
        $this->instructions_instructions->openLock($order_info['lock_sn'], $order_info['add_time']);

        $data = array(
            'order_id' => $order_info['order_id'],
            'order_sn' => $order_info['order_sn']
        );

        $this->response->showSuccessResult($data, '指令发送成功');
    }

    /**
     * 获取异常未计费订单
     */
    public function getPreFinishedOrders() {
        $userInfo = $this->startup_user->getUserInfo();

        $this->load->library('logic/orders', true);

        $page = (isset($this->request->post['page']) && intval($this->request->post['page'])) >= 1 ? intval($this->request->post['page']) : 1;

        $count = $this->logic_orders->getPreOrdersCountByUserId($userInfo['user_id']);
        $items = $this->logic_orders->getPreOrdersByUserId($userInfo['user_id'], $page);

        $this->load->library('sys_model/coupon');
        foreach($items as &$item){
            $coupon = $this->sys_model_coupon->getCouponInfo(array('coupon_id'=>$item['coupon_id']));
            switch($coupon['coupon_type']){
                case 1 :
                    $show_hour = false;
                    if ($coupon['number'] / 60 >= 1) $show_hour = true;//半小时取整
                    $coupon['number'] = $show_hour ? round($coupon['number'] / 60, 2) : $coupon['number'];
                    $row['unit'] = $show_hour ? $this->language->get('text_hour') : $this->language->get('text_minute');
                    $item['coupon_type'] = '(已抵'. $coupon['number'] . $row['unit'] . '用车券)';
                    break;
                case 3 :
                    $item['coupon_type'] = '(已抵'.$coupon['number'].'元现金券)';
                    break;
                case 4 :
                    $item['coupon_type'] = '(已抵'.end(explode('.', $coupon['number'])).'折折扣券)';
                    break;
                default :
                    $item['coupon_type'] = '';
            }
        }

        $result = array(
            'total_items_count' => $count + 0,
            'total_pages' => ceil($count/10.0),
            'page' => $page + 0,
            'items' => $items
        );
        $this->response->showSuccessResult($result);
    }

    public function getLastViewOrderByBeforeStatus3() {
        $user_id = $this->startup_user->userId();
        $this->load->library('sys_model/orders');
        $t = time() - BOOK_EFFECT_TIME;
        $order_info = $this->sys_model_orders->getOrdersInfo("`user_id`={$user_id} AND (`order_state`=2 AND `before_state`=3 AND `already_show`=0)");
        if (empty($order_info)) {
            $this->response->showSuccessResult(array('has_order' => false), $this->language->get('error_no_order'));
        }
        else {
            $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), array('already_show' => 1));
            $user_info = $this->startup_user->getUserInfo();
            $order_info['available_deposit'] = $user_info['available_deposit'];
            $this->request->post['order_sn'] = $order_info['order_sn'];
            $this->response->showSuccessResult(array('has_order' => true, 'current_order' => $this->format($order_info)), $this->language->get('error_no_order'));
        }
    }

    public function addOrdersLines() {
        $post = $this->request->post(array('order_id', 'line_data'));
        if (!$post['order_id']) {
            $this->response->showErrorResult('订单ID不能为空');
        }
        if (!$post['line_data']) {
            $this->response->showErrorResult('JSON数据为空');
        }

        $this->load->library('sys_model/orders', true);
        $order_info = $this->sys_model_orders->getOrdersInfo(array('order_id' => $post['order_id']));

        $line_data = array();
        $json_arr = $post['line_data'];
        $json_data = json_decode($json_arr, true);
        if (!empty($json_data)) {
            foreach ($json_data as $line) {
                $line_data[]['lat'] = $line['lat'];
                $line_data[]['lng'] = $line['lng'];
                $line_data[]['add_time'] = $line['add_time'];
                $line_data[]['user_id'] = $order_info['user_id'];
                $line_data[]['status'] = 1;
            }
        }

        //写入
        $insert_id = $this->sys_model_orders->addOrderLines($line_data);
        $this->response->showSuccessResult(array('insert_id' => $insert_id), '添加成功');
    }
}
