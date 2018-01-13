<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/12/17
 * Time: 13:41
 * Author yangjifang 1427047861@qq.com
 *
 * 封装注册逻辑
 *
 * 每种注册方法 写成一个类应该更好 but现在没时间了
 */
namespace Logic;

use Enum\RegisterType;
use Enum\ErrorCode;
use Tool\Email;

class Register {

    protected $registry ;

    /**
     * 注册方法
     * @var \Enum\RegisterType
     */
    protected $register_type;

    /**
     * 用户 POST 传进来的注册参数
     * @var array
     */
    protected $register_param;

    /**
     * 用户 GET 传进来的参数
     * @var
     */
    protected $gets;

    /**
     * 要插入到数据库中的数据
     * @var
     */
    protected $insert_param = [];


    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->logic_user = new \Logic\User($registry);
        $this->logic_sms = new \Logic\Sms($registry);
        $this->sys_model_user = new \Sys_Model\User($registry);
        $this->sys_model_region = new \Sys_Model\Region($registry);
        $this->sys_model_deposit = new \Sys_Model\Deposit($registry);
        $this->sys_model_setting = new \Sys_Model\Setting($registry);
        $this->request = $registry->get('request');
        $this->event  = $registry->get('event');
        $this->url = $registry->get('url');
        $this->language = $registry->get('language');

    }


    /**
     * @param $param
     */
    public function register($register_param,$gets){

        $this->register_param = $register_param;
        //主要为了兼容以前的做法 来源,版本等参数 依然用get的方法传入
        $this->gets_param = $gets;

        $this->parseType();
        $this->checkParam();
        $this->parseFrom();
        $this->parseRegion();

        return $this->dispatchRegister();
    }

    /**
     * 判断注册类型
     * @param $register_param
     */
    protected function parseType(){

        if(isset($this->register_param['mobile']) && !empty($this->register_param['mobile'])){
            $this->register_type = RegisterType::MOBILE;
            return;
        }
        if(isset($this->register_param['email']) && !empty($this->register_param['email'])){
            $this->register_type = RegisterType::EMAIL;
            return;
        }

    }

    /**
     * 检查注册参数是否正确
     * @throws \Exception
     */
    protected function checkParam()
    {
        switch ($this->register_type){
            case RegisterType::MOBILE:
                $this->checkMobileParam();
                break;
            case RegisterType::EMAIL:
                $this->checkEmailParam();
                break;
            default:
                throw new \Exception("register_failure",ErrorCode::REGISTER_FAILURE);
        }

        $this->insert_param['uuid'] = $this->register_param['uuid'];
        $this->insert_param['register_lat'] = $this->register_param['lat'];
        $this->insert_param['register_lng'] = $this->register_param['lng'];

    }

    protected function checkMobileParam(){
        $check_list = ['uuid','lat','lng','code','mobile'];

        foreach( $check_list as $key => $val ){
            if(!isset($this->register_param[$val])){
                throw new \Exception("error_missing_parameter",ErrorCode::ERROR_MISSING_PARAMETER);
            }
            if(empty($this->register_param[$val])){
                throw new \Exception("error_empty_login_param",ErrorCode::ERROR_EMPTY_LOGIN_PARAM);
            }
        }
        if (!is_mobile($this->register_param['mobile'])) {
            throw new \Exception("error_mobile",ErrorCode::ERROR_MOBILE);
        }

        $this->insert_param['mobile'] = $this->register_param['mobile'];
    }

    protected function checkEmailParam(){

        $check_list = ['uuid','lat','lng','email'];

        foreach( $check_list as $key => $val ){
            if(!isset($this->register_param[$val])){
                throw new \Exception("error_missing_parameter",ErrorCode::ERROR_MISSING_PARAMETER);
            }
            if(empty($this->register_param[$val])){
                throw new \Exception("error_empty_login_param",ErrorCode::ERROR_EMPTY_LOGIN_PARAM);
            }
        }

        if (!is_email($this->register_param['email'])) {
            throw new \Exception("error_email",ErrorCode::ERROR_EMAIL);
        }

        $this->insert_param['email'] = $this->register_param['email'];
    }


    /**
     * 分析用户来源
     */
    protected function parseFrom(){
        if ($this->gets['fromApi'] == 'android') {
            $data['from'] = 'android';
        } elseif ($this->gets['fromApi'] == 'ios') {
            $data['from'] = 'ios';
        } else {
            if ($this->request->get_request_header('client') == 'wechat') {
                $data['from'] = 'wechat';
            } elseif ($this->request->get_request_header('client') == 'miniapp') {
                $data['from'] = 'mini_app';
            } else {
                $data['from'] = 'web';
            }
        }
    }


    /**
     * 找到注册区域
     */
    protected function parseRegion(){

        //注册区域
        $region_info = $this->sys_model_region->getRegionInfo(array(
            'region_bounds_northeast_lng' => array('gt', $this->register_param['lng']),
            'region_bounds_southwest_lng' => array('lt', $this->register_param['lng']),
            'region_bounds_northeast_lat' => array('gt', $this->register_param['lat']),
            'region_bounds_southwest_lat' => array('lt', $this->register_param['lat'])
        ));
        if (!empty($region_info)) {
            $this->insert_param['register_region_id'] = $region_info['region_id'];
        }
    }


    /**
     * 根据不同的情况 调用不同的注册方法
     */
    protected function dispatchRegister(){
        switch($this->register_type) {
            case RegisterType::MOBILE:
                return $this->mobileRegister();
                break;
            case RegisterType::EMAIL:
                return $this->emailRegister();
                break;
            default:
                throw new \Exception("register_failure",ErrorCode::REGISTER_FAILURE);
        }
    }


    /**
     * 手机注册
     */
    protected function mobileRegister(){

        //下面这两个判断是完全可以合并的 暂时不动以前的代码了
        //验证短信
        if (!$this->logic_sms->disableInvalid($this->register_param['mobile'], $this->register_param['code'])) {
            throw new \Exception('error_invalid_message_code',ErrorCode::ERROR_INVALID_MESSAGE_CODE);
        }
        //更新短信的状态
        $update = $this->logic_sms->enInvalid($this->register_param['mobile'], $this->register_param['code']);
        if (!$update) {
            throw new \Exception('error_database_failure',ErrorCode::ERROR_DATABASE_FAILURE);
        }

        $this->insert_param['register_type'] = RegisterType::MOBILE;
        $result = $this->logic_user->register($this->insert_param,RegisterType::MOBILE);
        if (!$result['state']) {
            throw new \Exception($result['msg'],$result['code']);
        }

        //优惠 活动 积分什么的 都在事件里处理
        $this->event->trigger('register_success',$result);

        return $result;
    }

    /**
     * 邮箱注册
     */
    protected function emailRegister(){
        
        $result = $this->logic_user->register($this->insert_param,RegisterType::EMAIL);
        if (!$result['state']) {
            throw new \Exception($result['msg'],$result['state']);
        }
        
        $now = time();
        $code = [
            'email'=> $this->register_param['email'],
            'time'=>$now,
            'sign'=>md5(API_ACCESSKEY.$this->register_param['email'].$now)
        ];
        $code = base64_encode(json_encode($code));
        $active_url = $this->url->link('account/account/emailRegisterActive',['code'=>$code]);

        //发送邮件
        $config_from_db = $this->sys_model_setting->getSettingList(['key'=>['in',['smtp_server','smtp_server_port','smtp_user_mail','smtp_pass','mail_type','smtp_user']]]);
        foreach( $config_from_db as $key => $val ){
            $config[$val['key']] = $val['value'];
        }
        $config['smtp_pass'] = '';
        $email_tool = new Email($config);
        //$res = $mail->sendEmail('','1427047861@qq.com','test','hello world2');

        //发送激活邮件
        //$email_tool = new Email();
        //from,title,content 读取配置
        //邮箱注册积分应该是激活之后 在做了 或者智力应该是事件
        $title = $this->language->get('email_register_title');
        $title = 'Welcome to eazymov!';
        $content = $this->language->get('email_register_content');
        $content = 'please click #anchor# to active your account';
        $content = str_replace('#anchor#','<a href="'.$active_url.'">'.$this->language->get('click_me_to_active_email').'</a>',$content);

        $res = $email_tool -> sendEmail(null,$this->register_param['email'],$title,$content);
        if(!$res){
            throw new \Exception($this->language->get('error_send_email_failure'),ErrorCode::ERROR_SEND_EMAIL_FAILURE);
        }

        
        return $result;
    }

}