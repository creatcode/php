<?php
/**
 * 极验验证码
 * Created by EstrongBike.
 * @Author: vincent
 * @Since: 2017/8/29 20:33
 */

namespace Gt3;

require_once __DIR__ . '/lib/class.geetestlib.php';
require_once __DIR__ . '/config/config.php';

class Captcha
{
    private $gtSdk = null;
    private $session = null;

    public function __construct($registry)
    {
        $this->gtSdk = new \GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
        $this->session = $registry->get('session');
        $this->request = $registry->get('request');
    }

    /**
     * [wb_start 初始化]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-08-30T18:02:04+0800
     */
    public function wb_start()
    {
        $data = array(
            'mobile' => $this->request->get('mobile'),
        );
        $status = $this->gtSdk->pre_process($data, 1);
        $this->session->data['gtserver'] = $status;
        $this->session->data['mobile'] = $data['mobile'];
        exit($this->gtSdk->get_response_str());
    }

    /**
     * [wb_verify web验证]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-08-30T18:02:37+0800
     */
    public function wb_verify()
    {
        $data = array(
            "mobile"      => isset($this->session->data['mobile'])?$this->session->data['mobile']:'', # 网站用户id
            "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address"  => getIP(), # 请在此处传输用户请求验证时所携带的IP
        );
        $data_p     = $_POST;
        $geetest_challenge  = isset($data_p['geetest_challenge'])   ? $data_p['geetest_challenge']  : '';
        $geetest_validate   = isset($data_p['geetest_validate'])    ? $data_p['geetest_validate']   : '';
        $geetest_seccode    = isset($data_p['geetest_seccode'])     ? $data_p['geetest_seccode']    : '';
        if(empty($geetest_challenge) || empty($geetest_validate) ||empty($geetest_seccode)){
            return callback(false,'你尚未通过验证！');
        }
        if (isset($this->session->data['gtserver']) && $this->session->data['gtserver'] == 1) {   //服务器正常
            $result = $this->gtSdk->success_validate($geetest_challenge, $geetest_validate, $geetest_seccode, $data);
            if ($result) {
                return callback(true);
            } else{
                return callback(false,'你尚未通过验证！');
            }
        }else{  //服务器宕机,走failback模式
            if ($this->gtSdk->fail_validate($geetest_challenge, $geetest_validate, $geetest_seccode)) {
                return callback(true);
            }else{
                return callback(false,'请完成验证！');
            }
        }
    }
}