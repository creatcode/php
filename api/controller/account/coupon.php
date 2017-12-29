<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2017/2/9
 * Time: 15:19
 */
class ControllerAccountCoupon extends Controller {
    /**
     * 优惠券列表
     */
    public function getCouponList() {
        $user_id = $this->startup_user->userId();
        $this->load->library('sys_model/coupon', true);
        $where = array('user_id' => $user_id, 'failure_time' => array('gt', time()), 'used' => '0');
        $order = 'add_time DESC';

        $page = isset($this->request->post['page']) ? (intval($this->request->post['page']) ? intval($this->request->post['page']) : 1) : 1;
        $start = ($page - 1) * $this->config->get('config_limit_admin');
        $end = $this->config->get('config_limit_admin');
        $limit = "$start, $end";

        $total = $this->sys_model_coupon->getCouponCount($where);
        $coupon_list = $this->sys_model_coupon->getSimpleCouponList($where, $order, $limit);
        foreach ($coupon_list as &$coupon) {
            $coupon = $this->format($coupon);
        }

        $result = array(
            'total_items_count' => $total+0,
            'total_pages' => ceil($total / $this->config->get('config_limit_admin')),
            'page' => $page,
            'items' => $coupon_list
        );

        //记录查看优惠券时间
        $this->load->library('sys_model/user');
        $this->sys_model_user->updateUser(array('user_id'=>$user_id), array('read_wallet_last_time'=>time()));

        $this->response->showSuccessResult($result);
    }

    /**
     * 历史优惠券
     */
    public function getExpiredList() {
        $user_id = $this->startup_user->userId();
        $this->load->library('sys_model/coupon', true);
        //$where = array('user_id' => $user_id, 'failure_time' => array('lt', time()), 'used' => '1');
        $where = "(user_id='$user_id' AND failure_time < " . time() . ") or (user_id='$user_id' AND used='1')";
        $order = 'add_time DESC';
        $page = isset($this->request->post['page']) ? (intval($this->request->post['page']) ? intval($this->request->post['page']) : 1) : 1;
        $start = ($page - 1) * $this->config->get('config_limit_admin');
        $end = $this->config->get('config_limit_admin');
        $limit = "$start, $end";

        $total = $this->sys_model_coupon->getCouponCount($where);
        $coupon_list = $this->sys_model_coupon->getSimpleCouponList($where, $order, $limit);
        foreach ($coupon_list as &$coupon) {
            $coupon = $this->format($coupon);
        }

        $result = array(
            'total_items_count' => $total+0,
            'total_pages' => ceil($total / $this->config->get('config_limit_admin')),
            'page' => $page,
            'items' => $coupon_list
        );

        $this->response->showSuccessResult($result);
    }

    private function format($row) {
        $row['used'] = $row['used'] == '1';
        $row['expired'] = $row['failure_time'] < TIMESTAMP;
        $row['failure_time'] = date('Y-m-d', $row['failure_time']);
        if ($row['coupon_type'] == 1) {
            $show_hour = false;
            if ($row['number'] / 60 >= 1) $show_hour = true;//半小时取整
            $row['number'] = $show_hour ? round($row['number'] / 60, 2) : $row['number'];
            $row['unit'] = $show_hour ? $this->language->get('text_hour') : $this->language->get('text_minute');
        } elseif ($row['coupon_type'] == 2) {
            $row['unit'] = $this->language->get('text_time_unit');
        } elseif ($row['coupon_type'] == 3) {
            $row['unit'] = $this->language->get('text_money_unit');
        } elseif ($row['coupon_type'] == 4) {
            $row['unit'] = $this->language->get('text_discount_unit');
        }

        $result =  $this->startup_user->getUserInfo();
        $row['new_coupon'] = $row['add_time'] > $result['read_wallet_last_time']? 1 : 0;

        return $row;
    }

    public function getCouponByShareTrip() {
        if (!isset($this->request->post['mobile']) || !isset($this->request->post['code']) || !isset($this->request->post['encrypt_code']) || !isset($this->request->post['order_id'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        //如果做集群会采用UUID
        $order_id = $this->request->post['order_id'];

        $type = 'share';

        $encrypt_code = $this->request->post['encrypt_code'];
        $code = decrypt($encrypt_code);

        if (!strpos($code, '_')) {
            $this->response->showErrorResult($this->language->get('error_data_parse_failure'));
        }

        $arr = explode('_', $code);
        $user_id = $arr[0];

        $coupon_type = 1;

        $mobile = $this->request->post['mobile'];
        $code = $this->request->post['code'];
        if (!is_mobile($mobile)) {
            $this->response->showErrorResult($this->language->get('error_mobile'), 2);
        }

        $this->load->library('logic/sms', true);
        $this->load->library('logic/user', true);

        $this->load->library('sys_model/coupon');
        //稍后限制，每天最多只能领取一张
        $this->load->library('sys_model/user', true);

        if (!$this->logic_sms->disableInvalid($mobile, $code, $type)) {
            $this->response->showErrorResult($this->language->get('error_message_code'), 3);
        }

        $result = $this->sys_model_user->getUserInfo(array('user_id' => $user_id), 'user_id,avatar,real_name,mobile');
        if (!$result) {
            $this->response->showErrorResult($this->language->get('error_data_parse_failure'));
        }

        $user_info = $this->sys_model_user->getUserInfo(array('mobile' => $mobile));
        if (empty($user_info)) {
            $insert_arr = array('mobile' => $mobile, 'add_time' => time());
            $insert_id = $this->sys_model_user->addUser($insert_arr);
            if (!$insert_id) {
                $this->response->showErrorResult($this->language->get('error_get_failure'));
            }
            $user_info['mobile'] = $mobile;
            $user_info['user_id'] = $insert_id;
        } else {
            $coupon_info = $this->sys_model_coupon->getCouponInfo(array('user_id' => $user_info['user_id'], 'coupon_type' => $coupon_type, 'order_id' => $order_id));
            if ($coupon_info) {
                $this->response->showErrorResult($this->language->get('error_repeat_receive'));
            }
        }

        //更新短信的
        $update = $this->logic_sms->enInvalid($mobile, $code, $type);
        if (!$update) {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }

        $obtain = 2;// type=2 分享行程,type = 1 分享邀请码
        $time_length = $this->config->get('config_register_coupon_number') ? $this->config->get('config_register_coupon_number') : 60;

        $insert_id = $this->addCoupon($user_info, $time_length, $coupon_type, $obtain, $order_id);

        $output = array(
            'coupon_id' => $insert_id,
            'number' => $time_length / 60,
            'coupon_type' => $coupon_type,
            'failure_time' => date('Y-m-d', strtotime('+7 day')),
            'description' => $this->language->get('text_cycling_stamps'),
            'unit' => $this->language->get('text_hour'),
        );

        $insert_id ? $this->response->showSuccessResult($output, $this->language->get('success_get_coupon')) : $this->response->showErrorResult($this->language->get('error_get_coupon'));
    }

    /**
     * 可以写到model
     * @param $user_info
     * @param $number
     * @param $coupon_type
     * @param $obtain_type
     * @param $order_id
     * @return bool
     */
    private function addCoupon($user_info, $number, $coupon_type, $obtain_type, $order_id = 0) {
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

    public function getCouponFrontPage() {
        if (!isset($this->request->post['mobile']) || !isset($this->request->post['code']) || !isset($this->request->post['encrypt_code'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }

        $type = 'share';
        $encrypt_code = $this->request->post['encrypt_code'];
        $code = decrypt($encrypt_code);

        if (!strpos($code, '_')) {
            $this->response->showErrorResult($this->language->get('error_data_parse_failure'));
        }

        $arr = explode('_', $code);
        $user_id = $arr[0];

        $coupon_type = 1;

        $mobile = $this->request->post['mobile'];
        $code = $this->request->post['code'];
        if (!is_mobile($mobile)) {
            $this->response->showErrorResult($this->language->get('error_mobile'), 2);
        }

        $this->load->library('logic/sms', true);
        $this->load->library('logic/user', true);

        $this->load->library('sys_model/coupon');
        //稍后限制，每天最多只能领取一张
        $this->load->library('sys_model/user', true);

        $rs_user_info = $this->sys_model_user->getUserInfo(array('user_id' => $user_id));
        if (empty($rs_user_info)) {
            $this->response->showErrorResult($this->language->get('error_invalid_sharer'));
        }

        if (!$this->logic_sms->disableInvalid($mobile, $code, $type)) {
            $this->response->showErrorResult($this->language->get('error_message_code'), 3);
        }

        $user_info = $this->sys_model_user->getUserInfo(array('mobile' => $mobile));
        if (empty($user_info)) {
            $insert_arr = array('mobile' => $mobile, 'add_time' => time());
            $insert_id = $this->sys_model_user->addUser($insert_arr);
            if (!$insert_id) {
                $this->response->showErrorResult($this->language->get('error_get_failure'));
            }
            $user_info['mobile'] = $mobile;
            $user_info['user_id'] = $insert_id;
            //写入被分享用户的优惠券
            $insert_id = $this->addCoupon($rs_user_info, 30, 1, 1);
        } else {
            //老用户不能领取
            $this->response->showErrorResult($this->language->get('error_already_register'));
        }

        //更新短信的
        $update = $this->logic_sms->enInvalid($mobile, $code, $type);
        if (!$update) {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }

        $obtain = 2;// type=2 分享行程,type = 1 分享邀请码
        $time_length = $this->config->get('config_register_coupon_number') ? $this->config->get('config_register_coupon_number') : 60;

        $insert_id = $this->addCoupon($user_info, $time_length, $coupon_type, $obtain);
        $output = array(
            'coupon_id' => $insert_id,
            'number' => $time_length / 60,
            'coupon_type' => $coupon_type,
            'failure_time' => date('Y-m-d', strtotime('+7 day')),
            'description' => $this->language->get('text_cycling_stamps'),
            'unit' => $this->language->get('text_hour')
        );

        $insert_id ? $this->response->showSuccessResult($output, $this->language->get('success_get_coupon')) : $this->response->showErrorResult($this->language->get('error_get_coupon'));
    }

    /**
     * 生成优惠券唯一码
     */
    private function buildCouponCode() {
        return token(32);
    }
}
