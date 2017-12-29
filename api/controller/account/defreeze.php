<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2017/6/3
 * Time: 18:09
 */
class ControllerAccountDeFreeze extends Controller {
    public function index() {
        $this->load->library('sys_model/user', true);
        $update = $this->sys_model_user->updateUser(array('user_id' => 60), array('is_freeze' => 0));
        $this->response->showSuccessResult('', 'ok');
    }

    public function addCoupon() {
        $input = $this->request->post(array('mobiles', 'num'));
        $data['coupon_type'] = 1;
        $data['number'] = 60;
        $data['description'] = '1小时优惠券';

        // 优惠券类型
        if ($data['coupon_type'] == 1) {
            $data['left_time'] = $data['number'];
        } else {
            $data['left_time'] = 1;
        }
        $data['add_time'] = time();
        $data['effective_time'] = time();
        $data['failure_time'] = strtotime('+7 day');

        $this->load->library('sys_model/coupon', true);
        $this->load->library('sys_model/user', true);

        $mobiles = explode(',', $input['mobiles']);
        if (is_array($mobiles) && !empty($mobiles)) {
            foreach ($mobiles as $mobile) {
                $condition = array(
                    'mobile' => $mobile
                );
                $user = $this->sys_model_user->getUserInfo($condition, 'user_id');
                if ($user) {
                    $data['user_id'] = $user['user_id'];
                    for ($i = 0; $i < $input['num']; $i++) {
                        $data['coupon_code'] = $this->buildCouponCode();
                        $this->sys_model_coupon->addCoupon($data);
                    }
                }
            }
        }
    }

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