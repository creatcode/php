<?php

/**
 * 判断是否登录，ignore为忽略列表
 * Class ControllerStartupLogin
 */
class ControllerStartupLogin extends Controller {
    public function index() {
        $route = isset($this->request->get['route']) ? strtolower(trim($this->request->get['route'])) : '';
        $ignore = array(
            'account/account/sendregistercode',
            'account/account/register',
            'account/account/login',
            'account/account/getOrders2',
            'account/account/startcaptchaser',//初始化极验验证
            'account/account/sendsharecode',//发送分享验证码
            'account/coupon/getcouponbysharetrip',//行程分享获取优惠券
            'account/coupon/getcouponfrontpage',//首页分享获取优惠券
            'account/account/getuserinfobyencrypt',
            'account/account/getorderdetailbyencrypt',
            'account/account/getregions',//获取地区列表
            'account/account/passwordlogin',
            'account/account/getlanguagesetting',
            'account/account/emailregisteractive',
            'account/account/setiostoken',
            'payment/alipay/notify',
            'payment/wxpay/notify',
            'location/location/getbicyclelocation',
            'location/location/getbicyclelocation1',
            'location/location/getlocalprice',
            'location/location/getstation',
            'system/common/wechat_jssdk',
            'system/common/wechat',
            'system/common/wechatapp',
            'article/index',
            'system/test',
            'system/common/contact',
            'system/common/version',
            'system/common/ad',
            'system/test',
            'wechat/mp',
            'wechat/device/openlock',
            'wechat/device/bind',
            'wechat/device/unbind',
            'system/common/launch_ad',
            'article/index/ad',
            'account/ad/index',
            'account/ad',
            'account/account/test',
            'account/account/getemailstatus',//获取邮件状态
            'account/account/updateemail_confirm',//确认修改邮箱
            'account/creditcard/cc',//测试用
            'account/account/sendforgetcode',//发送忘记密码验证码
            'account/account/checkforgetcode',//验证忘记密码验证码
            'account/account/resetpassword',//没登录重新设置密码
            'account/account/sendforgetemail',//发送重新设置密码
            'account/account/checkforgetemail',//确认重新设置密码
            'account/account/getdepositamount',//获取应该充值多少押金
        );

        if (!in_array($route, $ignore)) {
            if (!isset($this->request->post['user_id']) || !isset($this->request->post['sign'])) {
                $this->response->showErrorResult('缺少登录参数', 98);
            }
            if (!$this->request->post['user_id'] || !$this->request->post['sign']) {
                $this->response->showErrorResult('请登陆后操作', 98);
            }

            $this->load->library('logic/user', true);
            $user_id = $this->request->post['user_id'];
            $sign = $this->request->post['sign'];
            $result = $this->logic_user->checkUserSign(array('user_id' => $user_id), $sign);
            if ($result['state']) {
                $this->registry->set('startup_user', $this->logic_user);
            } else {
                $this->response->showErrorResult('您的账号已在其他设备登录', 99);
            }
        }
    }
}
