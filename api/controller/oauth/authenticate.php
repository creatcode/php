<?php

/**
 * Created by EstrongBike.
 * @Author: vincent
 * @Since: 2017/8/23 15:30
 */
class ControllerOauthAuthenticate extends Controller
{
    /**
     * 忽略签名验证的请求
     * @var array
     */
    static private $ignore = array(
        'account/account/sendsharecode',//发送分享验证码
        'account/coupon/getcouponbysharetrip',//行程分享获取优惠券
        'account/coupon/getcouponfrontpage',//首页分享获取优惠券
        'account/account/getuserinfobyencrypt',
        'account/account/getorderdetailbyencrypt',
        'payment/alipay/notify',
        'payment/wxpay/notify',
        'location/location/getbicyclelocation',
        'location/location/getlocalprice',
        'system/common/wechat_jssdk',
        'system/common/wechat',
        'system/common/wechatapp',
        'article/index',
        'system/common/contact',
        'system/common/version',
        'system/common/ad',
        'wechat/mp',
        'wechat/device/openlock',
        'wechat/device/bind',
        'wechat/device/unbind',
        'system/common/launch_ad',
    );

    /**
     * 需要签名的方法
     * @var array
     */
    static private $method = array(
        'POST',
    );

    /**
     * @Author: vincent
     * @Since: 2017/8/23 16:30
     */
    public function index()
    {
        $route = isset($this->request->get['route']) ? strtolower(trim($this->request->get['route'])) : '';
        $method = $_SERVER['REQUEST_METHOD'];
        if (preg_match("/$method/i", implode("|", self::$method)) && !in_array($route, self::$ignore)) {
            $_method = strtolower($method);
            $data = $this->request->$_method;
        //    if(!isset($data['token']))//未传递token参数，提醒升级
        //        $this->response->showErrorResult('版本过低请升级后使用', 1024);
        //    if (!is_array($data) || empty($data['token']))//参数不符合要求
        //        $this->response->showErrorResult('服务器忙，请稍后重试！', 1026);
            //if (isset($data['token']) && self::MakeToken($data) != $data['token'])//鉴权失败
            //    $this->response->showErrorResult('服务器忙，请稍后重试！', 1027);
        }
    }

    /**
     * @param array $data
     * @param null  $accessKey
     * @return string
     * @Author: vincent
     * @Since: 2017/8/23 16:30
     */
    static private function MakeToken(array $data, $accessKey = null)
    {
        if (empty($accessKey)) $accessKey = API_ACCESSKEY;
        ksort($data);
        $string = self::ToUrlParams($data);
        //签名步骤二：在string后加入KEY：密钥
        $string = $string . "&key=" . $accessKey;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);

        return $result;
    }

    /**
     * @param array $data
     * @return string
     * @Author: vincent
     * @Since: 2017/8/23 16:30
     */
    static private function ToUrlParams(array $data)
    {
        $buff = "";
        foreach ($data as $k => $v) {
            if ($k != "token" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");

        return $buff;
    }
}
