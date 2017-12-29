<?php
class ControllerFaultFault extends Controller {
    /**
     * 获取故障类型（弃用）
     */
    public function getFaultType() {
        $this->load->library('sys_model/fault');
        $result = $this->sys_model_fault->getAllFaultType();
        $this->response->showSuccessResult($result, $this->language->get('success_read'));
    }

    /**
     * 上报故障
     */
    public function addFault() {
        $is_order_lock = false;
        if (is_scalar($this->request->post['fault_type']) && $this->request->post['fault_type'] == 12) {
            $this->load->library('sys_model/orders', true);
            $user_id = $this->startup_user->userId();
            //是否有进行中的订单
            $order_info = $this->sys_model_orders->getOrdersInfo(array('user_id' => $user_id, 'order_state' => 1));
            if (empty($order_info)) {
                $this->response->showErrorResult('您的订单已结束', '10086');
            }
            //更新订单的状态为-3
            $this->sys_model_orders->updateOrders(array('order_id' => $order_info['order_id']), array('order_state' => -3));
            $this->request->post['bicycle_sn'] = $order_info['bicycle_sn'];
            $is_order_lock = true;
        }

        if (!isset($this->request->post['bicycle_sn']) || !isset($this->request->post['fault_type'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'),1);
        }

        $user_info = $this->startup_user->getUserInfo();
        $data['user_id'] = $this->startup_user->userId();
        $data['user_name'] = $user_info['mobile'];
        $data['bicycle_sn'] = $this->request->post['bicycle_sn'];
        if(strlen($data['bicycle_sn'])==11) {
            $data['bicycle_sn'] = substr($data['bicycle_sn'], 5);
        }

        $data['fault_type'] = (is_array($this->request->post['fault_type']) && !empty($this->request->post['fault_type'])) ? implode(',', $this->request->post['fault_type']) : $this->request->post['fault_type'];
        $data['add_time'] = time();
        $data['lat'] = $this->request->post['lat'];
        $data['lng'] = $this->request->post['lng'];

        if (empty($data['bicycle_sn'])) {
            $this->response->showErrorResult($this->language->get('error_empty_bicycle_sn'), 138);
        }

        if (empty($data['lat'])) {
            $this->response->showErrorResult($this->language->get('error_empty_lat'), 136);
        }

        if (empty($data['lng'])) {
            $this->response->showErrorResult($this->language->get('error_empty_lng'), 137);
        }

        $data['fault_content'] = isset($this->request->post['fault_content']) ? $this->request->post['fault_content'] : '';

        $this->load->library('sys_model/bicycle', true);
        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo(array('bicycle_sn' => $data['bicycle_sn']));
        if (empty($bicycle_info)) {
            $this->response->showErrorResult($this->language->get('error_bicycle_sn_nonexistence'), 140);
        }
        //添加单车编号
        $data['bicycle_id'] = $bicycle_info['bicycle_id'];
        $data['lock_sn'] = $bicycle_info['lock_sn'];
        $data['cooperator_id'] = $bicycle_info['cooperator_id'];

        $this->load->library('sys_model/fault', true);

        $t = time() - 12 * 3600; //12个小时
        $faults = $this->sys_model_fault->getFaultList(array('bicycle_sn' => $data['bicycle_sn'], 'add_time' => array('egt', $t)));
        if (empty($faults)) {
            $time = $this->config->get('config_register_coupon_number');
            if (time() - $bicycle_info['last_used_time'] > $this->config->get('config_free_bike_day') * 24 * 3600) {
                $this->addCoupon(array('user_id' => $data['user_id']), $time, 1, 1);
            } else {
                $this->load->library('sys_model/orders', true);
                $where = array('bicycle_sn' => $bicycle_info['bicycle_sn'], 'user_id' => $data['user_id'], 'is_limit_free' => 1);
                $free_order = $this->sys_model_orders->getOrdersInfo($where);
                if ($free_order && time() - $free_order['add_time'] > 3 * 3600) {
                    $this->addCoupon(array('user_id' => $data['user_id']), $time, 1, 1);
                }
            }

        }
        //h5可能会用到base64记得转码的问题，获取到的数据需要base64_decode
        $file_info['state'] = 'FAILURE';
        if (isset($this->request->files['fault_image']) || isset($this->request->post['fault_image'])) {
            $uploader = new Uploader(
                'fault_image',
                array(
                    'allowFiles' => array('.jpeg', '.jpg', '.png'),
                    'maxSize' => 10 * 1024 * 1024,
                    'pathFormat' => 'fault/{yyyy}{mm}{dd}{hh}{ii}{ss}{rand:4}'
                ),
                empty($this->request->files['fault_image']) ? 'base64' : 'upload', // upload, base64 or remote
                $this->request->files //文件上传变量数组，base64的不用提供，内部直接用$_POST[字段名]作为数据
            );
            $file_info = $uploader->getFileInfo();
        }

        if ($file_info['state'] == 'SUCCESS') {
            // 图片压缩
            $image_obj = new \Image(DIR_STATIC . $file_info['filePath']);
            $w = $image_obj->getWidth();
            $h = $image_obj->getHeight();
            $image_obj->resize($w, $h);
            $image_obj->save(DIR_STATIC . $file_info['filePath']);
            $data['fault_image'] = $file_info['url'];
        }

        if ($is_order_lock && !isset($data['fault_image'])) {
            $this->response->showErrorResult('必须上传图片', '10081');
        }

        $insert_id = $this->sys_model_fault->addFault($data);

        //更新bicycle表的fault字段
        $this->sys_model_bicycle->updateBicycle(array('bicycle_sn' => $data['bicycle_sn']), array('fault'=>1));

        //更新用户为冻结状态，订单为-3状态
        if ($is_order_lock) {
            $this->load->library('sys_model/user', true);
            $this->sys_model_user->updateUser(array('user_id' => $this->startup_user->userId()), array('is_freeze' => 1));
        }
        //向用户添加消息
        $this->addMessage(array('user_id'=>$this->startup_user->userId(), 'fault_id'=>$insert_id));
        $insert_id ? $this->response->showSuccessResult(array('fault_id' => $insert_id), $this->language->get('success_submit')) : $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
    }

    /**
     * 添加消息
     * @param $data
     */
    private function addMessage($data) {
        $this->load->library('sys_model/message');
        $input = array();
        $input['user_id'] = $data['user_id'];
        $input['msg_title'] = '真诚感谢合作，我们已收到您的反馈信息';
        $input['msg_content'] = '我们已经收到你的反馈，真诚感谢您选择小强单车。我们将会尽快为您解决问题，给您造成的不便，我们深表歉意...';
        $input['msg_abstract'] = '我们已经收到你的反馈，真诚感谢您选择小强单车。我们将会尽快为您解决问题，给您造成的不便，我们深表歉意...';
        $data = array(
            'user_id' => $input['user_id'],
            'msg_time' => time(),
            'msg_title' => $input['msg_title'],
            'msg_abstract' => $input['msg_abstract'],
            'msg_content' => $input['msg_content'],
            'msg_type' => 1,
            'fault_id' => $data['fault_id'],
        );
        $this->sys_model_message->addMessage($data);
    }

    /**
     * 违规停车
     */
    public function addIllegalParking() {
        if (!isset($this->request->post['lat']) || empty($this->request->post['lat'])) {
            $this->response->showErrorResult($this->language->get('error_empty_lat'), 136);
        }
        if (!isset($this->request->post['lng']) || empty($this->request->post['lng'])) {
            $this->response->showErrorResult($this->language->get('error_empty_lng'), 137);
        }
        if (!isset($this->request->post['type']) || empty($this->request->post['type'])) {
            $this->request->post['type'] = 1;
        }
        if (!isset($this->request->post['bicycle_sn']) || empty($this->request->post['bicycle_sn'])) {
            $this->response->showErrorResult($this->language->get('error_empty_bicycle_sn'), 138);
        }

        $user_info = $this->startup_user->getUserInfo();
        $data['bicycle_sn'] = substr($this->request->post['bicycle_sn'], 5);

        $data['lat'] = $this->request->post['lat'];
        $data['lng'] = $this->request->post['lng'];
        $data['content'] = isset($this->request->post['content']) ? $this->request->post['content'] : '';
        $data['user_id'] = $user_info['user_id'];
        $data['user_name'] = $user_info['mobile'];
        $data['type'] = $this->request->post['type'];
        $data['add_time'] = time();
        $this->load->library('sys_model/bicycle', true);
        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo(array('bicycle_sn' => $data['bicycle_sn']));
        if (empty($bicycle_info)) {
            $this->response->showErrorResult($this->language->get('error_bicycle_sn_nonexistence'), 140);
        }
		$data['bicycle_id'] = $bicycle_info['bicycle_id'];
        $file_info['state'] = 'FAILURE';
        if (isset($this->request->files['file_image']) || isset($this->request->post['file_image'])) {
            $uploader = new Uploader(
                'file_image',
                array(
                    'allowFiles' => array('.jpeg', '.jpg', '.png'),
                    'maxSize' => 10 * 1024 * 1024,
                    'pathFormat' => 'illegal_parking/{yyyy}{mm}{dd}{hh}{ii}{ss}{rand:4}'
                ),
                empty($this->request->files['file_image']) ? 'base64' : 'upload', // upload, base64 or remote
                $this->request->files //文件上传变量数组，base64的不用提供，内部直接用$_POST[字段名]作为数据
            );
            $file_info = $uploader->getFileInfo();
        }

        $data['cooperator_id'] = $bicycle_info['cooperator_id'];

        if ($file_info['state'] == 'SUCCESS') {
            // 图片压缩
            $image_obj = new \Image(DIR_STATIC . $file_info['filePath']);
            $w = $image_obj->getWidth();
            $h = $image_obj->getHeight();
            $image_obj->resize($w, $h);
            $image_obj->save(DIR_STATIC . $file_info['filePath']);
            $data['file_image'] = $file_info['url'];
        }
        $this->load->library('sys_model/fault', true);
        $insert_id = $this->sys_model_fault->addIllegalParking($data);

        //更新bicycle表的illegal_parking字段
        $this->sys_model_bicycle->updateBicycle(array('bicycle_sn' => $data['bicycle_sn']), array('illegal_parking'=>1));

        $insert_id ? $this->response->showSuccessResult(array('parking_id' => $insert_id), $this->language->get('success_submit')) : $this->response->showErrorResult($this->language->get('error_database_operation_failure'),4);
    }

    private function addCoupon($user_info, $number, $coupon_type, $obtain_type, $order_id = 0) {
        $this->load->library('sys_model/coupon');

        if (empty($user_info)) return false;
        $description = '';
        if ($coupon_type == 1) {
            $description = ($number / 60) . $this->language->get('text_hour_coupon');
        } elseif ($coupon_type == 2) {

        } elseif ($coupon_type == 3) {

        }

        $data = array(
            'user_id' => $user_info['user_id'],
            'coupon_type' => $coupon_type,
            'number' => $number,
            'obtain' => $obtain_type,
            'add_time' => time(),
            'effective_time' => time(),
            'failure_time' => strtotime(date('Y-m-d', strtotime('+7 day'))),
            'description' => $description,
            'order_id' => $order_id
        );
        $data['coupon_code'] = $this->buildCouponCode();
        return $this->sys_model_coupon->addCoupon($data);
    }

    private function buildCouponCode() {
        return token(32);
    }
}