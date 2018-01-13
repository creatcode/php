<?php

use Enum\ErrorCode;
use Tool\IosPush;
use Enum\PushCode;
use Tool\AndroidPush;
use Tool\Email;

class ControllerAccountAccount extends Controller {

    /**
     * 优先手机注册
     *  modify by yangjifang
     */
    public function register() {

        $post_param = $this->request->post();
        $get_param = $this->request->get();

        $this->load->library('logic/register');
        try {
            $result = $this->logic_register->register($post_param, $get_param);
            //如果没有异常到这里肯定是 注册成功了
            $this->response->showSuccessResult($result['data'], $this->language->get('success_register'));
        } catch (\Exception $e) {
            $this->response->showErrorResult($this->language->get($e->getMessage()), $e->getCode());
        }
    }

    /**
     * 用邮箱注册 用户点击邮件激活用户
     * 需要添加一个 用户是否激活的状态 以前统一手机注册 不需要这个状体 添加了这个状态后续的判断都需要增加对这个状态的判断 牵涉到的接口 有用户登录 好像就是用户登录
     * 这里应该是一个页面展示
     * 设置密码的逻辑应该直接在网页 而不应该在手机端
     */
    public function emailRegisterActive() {
        $code = $this->request->get('code');
        $code = json_decode(base64_decode($code), true);

        $sign = md5(API_ACCESSKEY . $code['email'] . $code['time']);
        if ($sign !== $code['sign']) {
            $this->response->showErrorResult($this->language->get('error_invalid_url'), ErrorCode::ERROR_INVALID_URL);
        }
        if (time() - intval($code['time']) > EMAIL_EXPIRE_TIME) {
            $this->response->showErrorResult($this->language->get('error_url_overtime'), ErrorCode::ERROR_URL_OVERTIME);
        }

        $this->load->library('sys_model/user');
        $user_info = $this->sys_model_user->getUserInfo(array('email' => $code['email']));
        if (!$user_info) {
            $this->response->showErrorResult($this->language->get('user_not_exists'), ErrorCode::USER_NOT_EXISTS);
        } else if ($user_info['is_active'] == 1) {
            $this->response->showErrorResult($this->language->get('user_already_active'), ErrorCode::USER_ALREADY_ACTIVE);
        }

        $res = $this->sys_model_user->updateUser(['user_id' => $user_info['user_id']], ['is_active' => 1]);
        if ($res) {

            //推送邮件验证成功的消息
            //目前不知道安卓推送是什么 结构 所以先不做统一
            if ($user_info['ios_token']) {
                $ios_push = new IosPush();
                $ios_push->push($user_info['ios_token'], $this->language->get('email_activation_success'), PushCode::EMAIL_ACTIVATION_SUCCESS);
            }
            if ($user_info['android_token']) {
                $android_push = new AndroidPush();
                $android_push->push($user_info['android_token'], ['content' => '已激活，请登录', 'type' => 1]);
            }
            $this->response->showSuccessResult([], $this->language->get('success_active'));
        } else {
            $this->response->showErrorResult([], $this->language->get('failure_active'), ErrorCode::FAILURE_ACTIVE);
        }
    }

    /**
     * 获取当前邮箱注册状态 这一个接口需要授权验证的 道理应该是 每次重新打开 都应该算是重新注册 这个功能只需要在每次邮箱注册更新uuid 就行了
     * uuid 可以保证用户是在同一台机子上
     */
    public function getEmailStatus() {
        $email = trim($this->request->post('email'));
        $this->load->library('sys_model/user');
        if (empty($email)) {
            $this->response->showErrorResult($this->language->get('error_email'), ErrorCode::ERROR_EMAIL);
        }
        $user = $this->sys_model_user->getUserInfo(['email' => $email]);
        if (!$user) {
            $this->response->showErrorResult([], 501, $this->language->get('email_not_register'));
        } else {
            $this->response->showSuccessResult([
                'user_id' => $user['user_id'],
                'user_sn' => $user['user_sn'],
                'email' => $user['email'],
                //'register_type'=>$user['register_type'],
                'is_active' => $user['is_active']
                    ], $this->language->get('success'));
        }
    }

    /**
     * 设置用户密码
     */
    public function setPassword() {
        $user_id = $this->request->post('user_id');
        $pass = $this->request->post(['password', 're_password']);
        if ($pass['password'] != $pass['re_password']) {
            $this->response->showErrorResult($this->language->get('两次密码输入不正确'), 2);
        }
        if (!preg_match('/\w{6,20}/i', $pass['password'])) {
            $this->response->showErrorResult($this->language->get('密码不符合规则'), 2);
        }
        $this->load->library('sys_model/user', true);
        $user_info = $this->sys_model_user->getUserInfo(['user_id' => $user_id], 'is_ready_mail');
        if($user_info['is_ready_mail']==1){//发送了邮件没点确定
           $this->response->showErrorResult([], '请先确认修改密码邮件链接', 2002);
        }
        

        $res = $this->logic_user->setPassword($user_id, $pass['password']);
        if ($res) {
            $this->response->showSuccessResult([], $this->language->get('success'));
        }

        $this->response->showErrorResult([], $this->language->get('failure'), 1);
    }

    private function getSMSConfig() {
        define('SMS_ACCOUNT_SID', $this->config->get('config_sms_account_sid'));
        define('SMS_ACCOUNT_TOKEN', $this->config->get('config_sms_account_token'));
        define('SMS_APP_ID', $this->config->get('config_sms_app_id'));
        define('SMS_TEMP_ID', $this->config->get('config_sms_temp_id'));
    }

    private function getMobileTodaySendTimes($mobile) {
        $isInOneMinute = $this->db->table('sms')->where(array('mobile' => $mobile, 'add_time' => array('EGT', time() - 60)))->find();
        if ($isInOneMinute) {
            $this->response->showErrorResult('一分钟之内请勿重复请求');
        }
        $start = array('EGT', strtotime(date('Y-m-d')));
        $end = array('ELT', (int) bcadd(86399, strtotime(date('Y-m-d'))));
        $count = $this->db->table('sms')->where(array('mobile' => $mobile, array('add_time' => array($start, $end))))->count('sms_id');
        if ($count >= 6) {
            $this->response->showErrorResult('同一个手机号码每天只能请求6次');
        }
    }

    /**
     * [startCaptchaSer 初始化极验验证服务]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-08-30T17:20:42+0800
     */
    public function startCaptchaSer() {
        $this->load->library('gt3/captcha');
        $this->gt3_captcha->wb_start();
    }

    /**
     * 分享获取优惠券接口验证码
     */
    public function sendShareCode() {
       // file_put_contents('/dev/shm/sms.log', '[' . date('Y-m-d H:i:s ') . '] sendShareCode ' . getIP() . ' ' . $_SERVER['HTTP_USER_AGENT'] . ' ' . $_SERVER['HTTP_VIA'] . ' ' . $this->request->post['mobile'] . PHP_EOL, FILE_APPEND);
        if (!isset($this->request->post['mobile']) || !isset($this->request->post['encrypt_code'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }

        //add vincent : 2017-08-29 增加极验验证
        $this->load->library('gt3/captcha');
        $ver_geetest = $this->gt3_captcha->wb_verify();
        if (!$ver_geetest['state']) {
            $this->response->showErrorResult($ver_geetest['msg'], 128);
        }

        $share_type = isset($this->request->post['order_id']) ? 'share_trip' : 'share_front';

        //加载短信配置，使用常量
        $this->getSMSConfig();
        $alert = $this->language->get('text_message_upper_limit');
        $mobile = trim($this->request->post['mobile']);
        if (!is_mobile($mobile)) {
            $this->response->showJsonResult($this->language->get('error_mobile'), 0, array('alert' => $alert), 2);
        }

        //限制发送次数
        $this->getMobileTodaySendTimes($mobile);

        $encrypt_code = $this->request->post['encrypt_code'];
        $code = decrypt($encrypt_code);
        if (!strpos($code, '_')) {
            $this->response->showErrorResult($this->language->get('error_data_parse_failure'));
        }

        $arr = explode('_', $code);
        $user_id = $arr[0];
        $this->load->library('sys_model/user');
        $user_info = $this->sys_model_user->getUserInfo(array('user_id' => $user_id), 'user_id,avatar,real_name,mobile');
        if (empty($user_info)) {
            $this->response->showErrorResult($this->language->get('error_get_user_infomation'));
        }

        if (isset($this->request->post['order_id'])) {
            $this->load->library('sys_model/orders');
            $order_info = $this->sys_model_orders->getOrdersInfo(array('order_id' => $this->request->post['order_id'], 'user_id' => $user_id));
            if (empty($order_info)) {
                $this->response->showErrorResult($this->language->get('error_invalid_share'));
            }
            if ($order_info['end_time'] - $order_info['start_time'] <= 120) {
                $this->response->showErrorResult('两分钟之内的行程无法分享');
            }
        }

        //可能已注册，可能未注册
        $result = $this->sys_model_user->getUserInfo(array('mobile' => $mobile));
        $this->load->library('sys_model/coupon');
        //已经注册
        if ($result) {
            if (!isset($this->request->post['order_id'])) {
                $this->response->showErrorResult($this->language->get('error_already_register'));
            }
            $where = array('order_id' => $this->request->post['order_id'], 'user_id' => $result['user_id']);
            $coupon_info = $this->sys_model_coupon->getCouponInfo($where);

            if ($coupon_info) {
                $this->response->showErrorResult($this->language->get('error_repeat_receive'), 202, $this->format($coupon_info));
            }
        }

        $type = 'share';

        $this->load->library('logic/sms', true);

        //vincent:2017-07-27 增加短信防轰炸
        //modify vincent : 2017-08-30 增加极验验证，取消防轰炸
        /* if($this->logic_sms->isOutOfSendLimit($mobile,$type,getIP())){
          $this->response->showJsonResult('您发送短信过于频繁，请您稍后重试！', 0, array('alert'=>'您发送短信过于频繁，请您稍后重试！'), 5);
          } */

        $code = $this->logic_sms->createVerifyCode();
        $result_id = $this->logic_sms->sendSms($mobile, $code, $type);
        if ($result_id['state']) {
            $this->response->showSuccessResult(array('type' => $share_type, 'alert' => $alert));
        } else {
            if (isset($result_id['data']['code']) && $result_id['data']['code']) {
                $this->response->showJsonResult($this->language->get('error_send_message_failure_limit'), 0, array('alert' => $alert), 5);
            } else {
                $this->response->showJsonResult($this->language->get('error_send_message_failure'), 0, array('alert' => $alert), 4);
            }
        }
    }

    /**
     * 发送注册|登录验证码
     */
    public function sendRegisterCode() {
        //file_put_contents('/dev/shm/sms.log', '['.date('Y-m-d H:i:s ') .'] sendRegisterCode '. getIP() . ' ' . $_SERVER['HTTP_USER_AGENT'] . ' '. $_SERVER['HTTP_VIA'].' '. $this->request->post['mobile'] . PHP_EOL, FILE_APPEND);
        if (!isset($this->request->post['mobile'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        //加载短信配置，使用常量
        $this->getSMSConfig();

        $alert = $this->language->get('text_message_upper_limit');
        $mobile = trim($this->request->post['mobile']);
        if (!is_mobile($mobile)) {
            $this->response->showJsonResult($this->language->get('error_mobile'), 0, array('alert' => $alert), 2);
        }

        //限制发送次数
        $this->getMobileTodaySendTimes($mobile);

        $this->load->library('logic/sms', true);
        $this->load->library('logic/user', true);
        $result = $this->logic_user->existMobile($mobile);

        $type = 'register';
        if ($result['state']) {
            $type = 'login';
            if ($result['data']['deposit_state'] == 0) {
                $state = 0; //未交押金
            } elseif ($result['data']['verify_state'] == 0) {
                $state = 1; //未实名认证
            } elseif ($result['data']['available_deposit'] == 0) {
                $state = 2; //未充值
            } else {
                $state = 3; //正常状态
            }
            if ($result['data']['deposit_state'] == 0 && $result['data']['verify_state'] == 1 && $result['data']['available_deposit'] > 0) {
                $state = 4;
            }
            if ($result['data']['deposit_state'] == 0 && $result['data']['verify_state'] == 1 && $result['data']['available_deposit'] <= 0) {
                $state = 5;
            }
        }

        //vincent:2017-07-27 增加短信防轰炸
        if ($this->logic_sms->isOutOfSendLimit($mobile, $type, getIP())) {
            $this->response->showJsonResult('您发送短信过于频繁，请您稍后重试！', 0, array('alert' => '您发送短信过于频繁，请您稍后重试！'), 5);
        }

        $state = isset($state) ? $state : '0';
        $code = $this->logic_sms->createVerifyCode();
        $result_id = $this->logic_sms->sendSms($mobile, $code, $type);
        $result_id['state'] = 'true';
        if ($result_id['state']) {
            $this->response->showSuccessResult(array('type' => $type, 'state' => $state, 'alert' => $alert));
        } else {
            if ($result_id['data']['code']) {
                $this->response->showJsonResult($this->language->get('error_send_message_failure_limit'), 0, array('alert' => $alert), 5);
            } else {
                $this->response->showJsonResult($this->language->get('error_send_message_failure'), 0, array('alert' => $alert), 4);
            }
        }
    }

    /**
     * 密码登陆
     */
    public function passwordLogin() {
        if (!isset($this->request->post['uuid']) || !isset($this->request->post['password'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        $username = empty($this->request->post['mobile']) ? $this->request->post['email'] : $this->request->post['mobile'];

        if (empty($username)) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 2);
        }

        $this->load->library('logic/user', true);

        $result = $this->logic_user->passwordLogin($username, trim($this->request->post['password']), trim($this->request->post['uuid']));
        if (!$result['state']) {
            $this->response->showErrorResult($this->language->get($result['msg']), 106);
        }
        $this->response->showSuccessResult($result['data'], $this->language->get($result['msg']));
    }

    /**
     * 登录
     */
    public function login() {
        if (!isset($this->request->post['mobile']) || !isset($this->request->post['uuid']) || !isset($this->request->post['code'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }

        $mobile = trim($this->request->post['mobile']);
        $device_id = $this->request->post['uuid'];
        $code = $this->request->post['code'];

        if (empty($mobile)) {
            $this->response->showErrorResult($this->language->get('error_empty_mobile'), 103);
        }

        if (!is_mobile($mobile)) {
            $this->response->showErrorResult($this->language->get('error_mobile'), 2);
        }

        if (empty($code)) {
            $this->response->showErrorResult($this->language->get('error_mobile'), 104);
        }

        if (empty($device_id)) {
            $this->response->showErrorResult($this->language->get('error_device_id'), 105);
        }

        $this->load->library('logic/sms', true);
        $this->load->library('logic/user', true);

        if (!$this->logic_sms->disableInvalid($mobile, $code, 'login')) {
            $this->response->showErrorResult($this->language->get('error_invalid_message_code'), 3);
        }

        //更新短信的
        $update = $this->logic_sms->enInvalid($mobile, $code, 'login');

        if (!$update) {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }

        $result = $this->logic_user->login($mobile, $device_id);
        if (!$result['state']) {
            $this->response->showErrorResult($this->language->get($result['msg']), 106);
        }

        //相同账号在不同设备登录
        $this->load->library('sys_model/user', true);
        $userinfo = $this->sys_model_user->getUserInfo(['mobile' => $mobile], '*');
        if (!empty($userinfo['uuid'])) {
            if (strcmp($userinfo['uuid'], $device_id) != 0) {//不相同的设备登录
                if (!empty($userinfo['android_token'])) {
                    $android_push = new AndroidPush();
                    $android_push->push($userinfo['android_token'], ['content' => '账号在其他设备登录', 'type' => PushCode::DIFFERENT_DEVICE, 'old_device' => $userinfo['uuid'], 'user_id' => $userinfo['user_id'], 'new_device' => $device_id]);
                }
            }
        }



        $this->response->showSuccessResult($result['data'], $this->language->get($result['msg']));
    }

    /**
     * 获取个人信息
     */
    public function info() {
        $result = $this->startup_user->getUserInfo();
        $info = array(
            'user_id' => $result['user_id'],
            'user_sn' => $result['user_sn'],
            'mobile' => $result['mobile'],
            'nickname' => $result['nickname'],
            'avatar' => $result['avatar'],
            'deposit' => $result['deposit'],
            'deposit_state' => $result['deposit_state'],
            'available_deposit' => $result['available_deposit'],
            'freeze_deposit' => $result['freeze_deposit'],
            'freeze_recharge' => $result['freeze_recharge'],
            'credit_point' => $result['credit_point'],
            'real_name' => $result['real_name'],
            'identification' => $result['identification'],
            'verify_state' => $result['verify_state'],
            'available_state' => $result['available_state'],
            'recommend_num' => $result['recommend_num'],
            'has_month_card' => (int) $result['card_expired_time'] > time() ? 1 : 0,
            'email' => empty($result['email']) ? '' : $result['email']
        );
        if ($result['deposit_state'] == 0) {
            $info['user_state'] = $result['verify_state'] ? 4 : 0;
        } else {
            if ($result['verify_state'] == 0) {
                $info['user_state'] = 1;
            } elseif ($result['available_deposit'] == 0) {
                $info['user_state'] = 2;
            } else {
                $info['user_state'] = 3;
            }
        }

        $user_id = $this->startup_user->userId();
        //是否有新消息
        $this->load->library('logic/message', true);
        $count = $this->logic_message->getMessagesCount('user_id = ' . $user_id . ' AND m.msg_time > ' . $result['read_news_last_time']);
        $info['new_message'] = $count ? 1 : 0;
        //是否有新优惠券
        $this->load->library('sys_model/coupon', true);
        $condition = array(
            'user_id' => $user_id,
            'failure_time' => array('gt', time()),
            'add_time' => array('gt', $result['read_wallet_last_time']),
            'used' => '0'
        );
        $total = $this->sys_model_coupon->getCouponCount($condition);
        $info['new_coupon'] = $total ? 1 : 0;

        $this->load->library('sys_model/credit_card', true);
        $car_info = $this->sys_model_credit_card->getCreditcardInfo(['user_id' => $user_id, 'isvalid' => 1]);
        if (!empty($car_info['number'])) {
            if (strlen($car_info['number']) > 4) {//外国的信用卡不知道多少位的，反正保留最后4位，其他变*
                $replace_string = '';
                for ($i = 0; $i < strlen($car_info['number']) - 4; $i++) {
                    $replace_string .= '*';
                }
                $creditcard_number = $replace_string . substr($car_info['number'], strlen($car_info['number']) - 4, 4);
            } else {
                $creditcard_number = $car_info['number'];
            }
        } else {
            $creditcard_number = '';
        }
        $info['creditcard'] = $creditcard_number;


        if (!empty($result)) {
            $this->response->showSuccessResult($info, $this->language->get('success_operation'));
        } else {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
    }

    /**
     * 更新个人信息（暂时只有更新昵称）
     */
    public function updateInfo() {
        if (!isset($this->request->post['nickname']) || empty($this->request->post['nickname'])) {
            $this->response->showErrorResult($this->language->get('error_empty_nickname'), 114);
        }

        $user_id = $this->startup_user->userId();
        $result = $this->startup_user->updateUserInfo($user_id, array('nickname' => $this->request->post['nickname']));
        if ($result['state']) {
            $this->response->showSuccessResult();
        } else {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
    }

    /**
     * 更新个人头像
     */
    public function updateAvatar() {
        $uploader = new \Uploader(
                'avatar', //字段名
                array(// 配置项
            'allowFiles' => array('.jpg', '.jpeg', '.png'),
            'maxSize' => 10 * 1024 * 1024,
            'pathFormat' => 'avatar/{yyyy}{mm}{dd}{hh}{ii}{ss}{rand:4}'
                ), empty($this->request->files['avatar']) ? 'base64' : 'upload', //类型，可以是upload，base64或者remote
                $this->request->files //文件上传变量数组，base64的不用提供，内部直接用$_POST[字段名]作为数据
        );

        $fileInfo = $uploader->getFileInfo();
        if ($fileInfo['state'] == 'SUCCESS') {
            // 图片压缩
            $image_obj = new \Image(DIR_STATIC . $fileInfo['filePath']);
            $w = $image_obj->getWidth();
            $h = $image_obj->getHeight();
            $image_obj->resize($w, $h);
            $image_obj->save(DIR_STATIC . $fileInfo['filePath']);

            $user_id = $this->startup_user->userId();
            $user_info = $this->startup_user->getUserInfo();
            //如果更换头像之前就存在头像，则删除头像
            if ($user_info['avatar']) {
                @unlink(DIR_STATIC . 'avatar/' . retrieve($user_info['avatar']));
            }

            $result = $this->startup_user->updateUserInfo($user_id, array('avatar' => $fileInfo['url']));
            if ($result['state']) {
                $this->response->showSuccessResult(array('user_id' => $user_id, 'avatar' => $fileInfo['url']), $this->language->get('success_operation'));
            } else {
                $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
            }
        } else {
            $this->response->showErrorResult($fileInfo['state'], 5);
        }
    }

    /**
     * 更新手机号码
     */
    public function updateMobile() {
        //能进来到这里都是有userInfo的
        $userInfo = $this->startup_user->getUserInfo();
        $this->log->write(print_r($userInfo, true));
        /* if (empty($userInfo['verify_state']) //  verify_state=='0'，没有通过实名验证
          || empty($userInfo['real_name']) || empty($userInfo['identification'])) { // 用户实名或者身份证信息为空
          $this->response->showErrorResult($this->language->get('error_not_identification'), 115);
          } */

        if (!isset($this->request->post['code']) || empty($this->request->post['code'])) {
            $this->response->showErrorResult($this->language->get('error_empty_message_code'), 116);
        }

        /* if (!isset($this->request->post['real_name']) || empty($this->request->post['real_name'])) {
          $this->response->showErrorResult($this->language->get('error_empty_real_name'),117);
          }

          if (!isset($this->request->post['identification']) || empty($this->request->post['identification'])) {
          $this->response->showErrorResult($this->language->get('error_empty_identification'),118);
          } */

        if (!isset($this->request->post['mobile']) || empty($this->request->post['mobile'])) {
            $this->response->showErrorResult($this->language->get('error_empty_new_mobile'), 119);
        }

        if (!is_mobile($this->request->post['mobile'])) {
            $this->response->showErrorResult($this->language->get('error_mobile'), 2);
        }

        /* if($this->request->post['real_name']!=$userInfo['real_name']) {
          $this->response->showErrorResult($this->language->get('error_name_inconsistent'), 122);
          }

          if($this->request->post['identification']!=$userInfo['identification']) {
          $this->response->showErrorResult($this->language->get('error_identification_inconsistent'), 123);
          } */

        if (time() < $userInfo['last_update_mobile_time'] + UPDATE_MOBILE_INTERVAL) {
            $this->response->showErrorResult($this->language->get('error_replace_mobile_limit'), 120);
        }

        $existMobile = $this->startup_user->existMobile($this->request->post['mobile']);
        if ($existMobile['state']) {
            $this->response->showErrorResult($this->language->get('error_mobile_existed'), 121);
        }

        // 验证短信码
        $this->load->library('logic/sms', true);
        if (!$this->logic_sms->disableInvalid($this->request->post['mobile'], $this->request->post['code'], 'changemobile')) {
            $this->response->showErrorResult($this->language->get('error_invalid_message_code'), 3);
        }
        //更新短信的
        $update = $this->logic_sms->enInvalid($this->request->post['mobile'], $this->request->post['code'], 'changemobile');

        $result = $this->startup_user->updateUserInfo($userInfo['user_id'], array(
            'mobile' => $this->request->post['mobile'],
            'last_update_mobile_time' => time()
        ));

        if ($result['state']) {
            $this->response->showSuccessResult();
        } else {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
    }

    /**
     * 获取信用积分记录
     */
    public function getCreditLog() {
        $userInfo = $this->startup_user->getUserInfo();

        $this->load->library('logic/credit', true);

        $page = (isset($this->request->post['page']) && intval($this->request->post['page'])) >= 1 ? intval($this->request->post['page']) : 1;

        $count = $this->logic_credit->getCreditPointsCount($userInfo['user_id']);

        $result = array(
            'credit_point' => $userInfo['credit_point'],
            'total_items_count' => $count + 0,
            'total_pages' => ceil($count / 10.0),
            'page' => $page + 0,
            'items' => $this->logic_credit->getCreditPoints($userInfo['user_id'], $page)
        );

        $this->response->showSuccessResult($result);
    }

    /**
     * 获取钱包信息
     */
    public function getWalletInfo() {
        $userInfo = $this->startup_user->getUserInfo();

        $this->load->library('sys_model/coupon', true);
        $where = array('user_id' => $userInfo['user_id'], 'failure_time' => array('gt', time()), 'used' => '0');
        $total = $this->sys_model_coupon->getCouponCount($where);

        $condition = array(
            'user_id' => $userInfo['user_id'],
            'failure_time' => array('gt', time()),
            'add_time' => array('gt', $userInfo['read_wallet_last_time']),
            'used' => '0'
        );

        $count = $this->db->table('present_recharge')->where(array('start_time' => array('elt', time()), 'end_time' => array('egt', time()), 'state' => 1))->count(1);

        $has_new = $this->sys_model_coupon->getCouponCount($condition);

        $result = array(
            'deposit' => $userInfo['deposit'], //押金
            'deposit_state' => $userInfo['deposit_state'], //是否已交押金（0未交，1已交）
            'available_deposit' => (string) ($userInfo['available_deposit'] + $userInfo['present_amount']), //余额
            'freeze_deposit' => $userInfo['freeze_deposit'], //未退回的押金
            'freeze_recharge' => $userInfo['freeze_recharge'], // 被冻结的余额
            'available_recharge' => $userInfo['available_deposit'], //原始金额
            'present_amount' => $userInfo['present_amount'], //赠送金额
            'coupon_total' => $total + 0,
            'has_new_coupon' => ($has_new > 0),
            'has_recharge_present' => $count > 0 ? 1 : 0,
            'has_month_card' => $userInfo['card_expired_time'] ? 1 : 0,
            'card_end_time' => $userInfo['card_expired_time'] ? ($userInfo['card_expired_time'] >= time() ? '至' . date('Y-m-d', $userInfo['card_expired_time']) : '已过期') : 'is_hot',
        );
        $this->response->showSuccessResult($result);
    }

    /**
     * 获取钱包明细
     */
    public function getWalletDetail() {
        $userInfo = $this->startup_user->getUserInfo();

        $this->load->library('logic/deposit', true);

        $page = (isset($this->request->post['page']) && intval($this->request->post['page'])) >= 1 ? intval($this->request->post['page']) : 1;

        $count = $this->logic_deposit->getDepositLogCountByUserId($userInfo['user_id']);
        $items = $this->logic_deposit->getDepositLogByUserId($userInfo['user_id'], $page, true);

        if ($items['state']) {
            $result = array(
                'total_items_count' => $count + 0,
                'total_pages' => ceil($count / 10.0),
                'page' => $page + 0,
                'items' => $items['data']
            );
            $this->response->showSuccessResult($result);
        } else {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
    }

    /**
     * 获取我的行程列表
     */
    public function getOrders() {
        $userInfo = $this->startup_user->getUserInfo();

        $this->load->library('logic/orders', true);

        $page = (isset($this->request->post['page']) && intval($this->request->post['page'])) >= 1 ? intval($this->request->post['page']) : 1;

        $count = $this->logic_orders->getOrdersCountByUserId($userInfo['user_id']);
        $items = $this->logic_orders->getOrdersByUserId($userInfo['user_id'], $page, true);

        $recharge_sns = array();
        /* foreach ($items as $item) {
          if ($item['recharge_sn'] > 0) {
          $recharge_sns[$item['recharge_sn']] = $item['recharge_sn'];
          }
          } */
        if (!empty($recharge_sns)) {
            $in = implode(',', $recharge_sns);
            $collections = $this->db->table('deposit_recharge')->field('pdr_sn,pdr_amount')->where(array('pdr_sn' => array('in', $in)))->select();
            foreach ($collections as $collection) {
                if (isset($recharge_sns[$collection['pdr_sn']])) {
                    $recharge_sns[$collection['pdr_sn']] = $collection['pdr_amount'];
                }
            }
        }

        $this->load->library('sys_model/coupon');
        //语言包判断，两种语言就够了，不然呛，下面的方式得换了
        $gets = $this->request->get(array('lang'));
        foreach ($items as &$item) {
            $item['pay_amount'] = $item['pay_amount'] - $item['refund_amount'];
            //$coupon = $this->sys_model_coupon->getCouponInfo(array('coupon_id'=>$item['coupon_id']));
            switch ($item['coupon_type']) {
                case 1 :
                    $show_hour = false;
                    if ($item['number'] / 60 >= 1)
                        $show_hour = true; //半小时取整
                    $item['number'] = $show_hour ? round($item['number'] / 60, 2) : $item['number'];
                    $row['unit'] = $show_hour ? $this->language->get('text_hour') : $this->language->get('text_minute');
                    $item['coupon_type'] = $gets['lang'] == 'en' ? '(used' . $item['number'] . $row['unit'] . 'coupon)' : '(已抵' . $item['number'] . $row['unit'] . '用车券)';
                    break;
                case 2 :
                    $item['coupon_type'] = $gets['lang'] == 'en' ? '(Coupon for Once used)' : '(单次体验券)';
                    break;
                case 3 :
                    $item['coupon_type'] = $gets['lang'] == 'en' ? '(reduce ' . $item['number'] . ')' : '(' . $item['number'] . '元现金券)';
                    break;
                case 4 :
                    $item['coupon_type'] = $gets['lang'] == 'en' ? '(reduce ' . $item['number'] . '%)' : '(' . $item['number'] . '折折扣券)';
                    break;
                default :
                    $item['coupon_type'] = '';
            }

            if ($item['is_month_card']) {
                $item['coupon_type'] = '骑行卡';
            }

            if ($item['is_limit_free']) {
                $item['coupon_type'] = '免费车';
            }

            /* if (isset($recharge_sns[$item['recharge_sn']])) {
              $item['recharge_amount'] = $recharge_sns[$item['recharge_sn']];
              } else {
              $item['recharge_amount'] = 0;
              } */
        }

        $result = array(
            'total_items_count' => $count + 0,
            'total_pages' => ceil($count / 10.0),
            'page' => $page + 0,
            'items' => $items
        );
        $this->response->showSuccessResult($result);
    }

    /**
     * 获取行程详情
     */
    public function getOrderDetail() {
        if (!isset($this->request->post['order_id']) || empty($this->request->post['order_id'])) {
            $this->response->showErrorResult($this->language->get('error_empty_order_id'), 124);
        }

        $this->load->library('logic/orders', true);

        $result = $this->logic_orders->getOrderDetail($this->request->post['order_id']);

        if (empty($result)) {
            $this->response->showErrorResult($this->language->get('error_empty_order_id'), 124);
        }

        if (isset($result['order_info']['coupon_info'])) {
            
        }

        if ($result['order_info']['coupon_id'] == 0) {
            $result['order_info']['coupon_info'] = array();
        }
        $user_info = $this->startup_user->getUserInfo();
        $fields = array('nickname', 'avatar', 'real_name', 'available_deposit', 'present_amount', 'card_expired_time');
        //直接输出用户所有信息太危险
        $output_user_info = array();
        foreach ($fields as $field) {
            if (isset($user_info[$field])) {
                $output_user_info[$field] = $user_info[$field];
            }
        }
        $output_user_info['available_deposit'] = $output_user_info['available_deposit'] + $output_user_info['present_amount'];
        $result['user_info'] = $output_user_info;
        $result['order_info']['month_card'] = '';
        if ($result['order_info']['is_month_card']) {
            $result['order_info']['month_card'] = date('Y年m月d日', $user_info['card_expired_time']);
        } else {
            $result['order_info']['month_card'] = '';
        }

        // 输出给前端判断是否有此订单有评论
        $this->load->library('sys_model/comment', true);
        $has_comment = $this->sys_model_comment->getCommentInfo(['order_sn' => $result['order_info']['order_sn']]);
        $has_comment ? $result['has_comment'] = '1' : $result['has_comment'] = '0';

        $this->response->showSuccessResult($result);
    }

    /**
     * 无登录获取订单信息
     */
    public function getOrderDetailByEncrypt() {
        if (!isset($this->request->post['order_id']) || empty($this->request->post['order_id'])) {
            $this->response->showErrorResult($this->language->get('error_empty_order_id'), 124);
        }

        $encrypt_code = $this->request->post['encrypt_code'];
        $code = decrypt($encrypt_code);
        if (!strpos($code, '_')) {
            $this->response->showErrorResult($this->language->get('error_data_parse_failure'));
        }

        $arr = explode('_', $code);
        $user_id = $arr[0];

        $this->load->library('sys_model/user', true);
        $this->load->library('logic/orders', true);

        $result = $this->logic_orders->getOrderDetail($this->request->post['order_id']);
        $user_info = $this->sys_model_user->getUserInfo(array('user_id' => $user_id), 'avatar,nickname,mobile');
        $user_info['mobile'] = substr($user_info['mobile'], 0, 3) . '****' . substr($user_info['mobile'], -4);
        if (is_numeric($user_info['nickname'])) {
            $user_info['nickname'] = $user_info['mobile'];
        }

        $result['user_info'] = $user_info;
        $this->response->showSuccessResult($result);
    }

    /**
     * 获取我的消息列表
     */
    public function getMessages() {
        $this->load->library('logic/message', true);

        $page = (isset($this->request->post['page']) && intval($this->request->post['page'])) >= 1 ? intval($this->request->post['page']) : 1;

        $user_id = $this->startup_user->userId();
        $condition = array(
            '_string' => 'find_in_set(' . (int) $user_id . ', user_id) OR user_id=\'0\''
        );
        $count = $this->logic_message->getMessagesCount($condition);
        $items = $this->logic_message->getMessages($condition, $page);

        if (!empty($items) && is_array($items)) {
            foreach ($items as &$item) {
                if (isset($item['msg_image']) && !empty($item['msg_image'])) {
                    $item['msg_image'] = HTTP_IMAGE . $item['msg_image'];
                }
                if (!empty($item['msg_link'])) {
                    $item['msg_link'] = htmlspecialchars_decode($item['msg_link']);
                }
            }
        }

        $result = array(
            'total_items_count' => $count + 0,
            'total_pages' => ceil($count / 10.0),
            'page' => $page + 0,
            'items' => $items
        );

        //记录前端查看消息时间
        $this->load->library('sys_model/user');
        $this->sys_model_user->updateUser(array('user_id' => $user_id), array('read_news_last_time' => time()));

        $this->response->showSuccessResult($result);
    }

    /**
     * 生成押金充值订单
     */
    public function deposit() {

        if(empty($this->request->post['city_id'])&&empty($this->request->post['region_id'])){
            $this->response->showErrorResult('city_id和region_id不能同时空');
        }
        
        if(empty($this->request->post['city_id'])){//押金交区域数据设置的押金
            $this->load->library('sys_model/region');
            $area_info = $this->sys_model_region->getRegionInfo(['region_id'=>$this->request->post['region_id']]);
            $data['amount'] = $area_info['deposit'];
        }else{//交城市设置的押金
            $this->load->library('sys_model/city');
            $area_info = $this->sys_model_city->getCityInfo(['city_id'=>$this->request->post['city_id']]);
            $data['amount'] = $area_info['deposit'];
        }
        
       
            
        
        $data['type'] = 1; //押金充值
        //$data['amount'] = floatval($amount);
        $user_info = $this->startup_user->getUserInfo();
        $data['user_id'] = $user_info['user_id'];
        $data['user_name'] = $user_info['mobile'];
        $this->load->library('logic/deposit', true);
        $this->load->library('logic/user', true);
        $checked = $this->logic_user->checkDeposit($data['user_id']);
        //检测押金是否已交，如果已经交了押金
        if ($checked['state'] == false) {
            $this->response->showErrorResult($checked['msg']);
        }

        $result = $this->logic_deposit->addRecharge($data);
        if ($result['state']) {
            $this->response->showSuccessResult($result['data'], $this->language->get('success_deposit_checkout'));
        } else {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
    }

    /**
     * 申请退押金
     */
    public function cashApply() {
        $user_info = $this->startup_user->getUserInfo();
        if (!$user_info['deposit_state']) {
            $this->response->showErrorResult($this->language->get('error_non_payment_deposit_cannot_refund'), 201);
        }
        //判断是否有欠款
        if ($user_info['freeze_recharge'] > 0) {
            $this->response->showErrorResult($this->language->get('account_arrears_refund'));
        }
        if ($user_info['is_freeze']) {
            $this->response->showErrorResult($this->language->get('account_deposit_frozen'));
        }
        //判断是否有进行中/待计费的订单
        $this->load->library('sys_model/orders');
        $order_info = $this->sys_model_orders->getOrdersInfo(array('user_id' => $user_info['user_id'], 'order_state' => array('in', array('1', '3'))));
        if ($order_info && isset($order_info['order_state'])) {
            if ($order_info['order_state'] == '1') {
                $this->response->showErrorResult($this->language->get('account_ongoing'));
            } else if ($order_info['order_state'] == '3') {
                $this->response->showErrorResult($this->language->get('account_waiting_checkout_refund'));
            }
        }

        $this->load->library('sys_model/deposit', true);
        //是否存在提现申请
        //fix vincent:2017-08-09 查询条件 'pdc_payment_state' => '0' 更改为 'pdc_payment_state' => array('neq','1')
        $cash_info = $this->sys_model_deposit->getDepositCashInfo(array('pdc_user_id' => $user_info['user_id'], 'pdc_payment_state' => array('neq', '1')));
        if (!empty($cash_info)) {
            $this->response->showErrorResult($this->language->get('error_repeat_refund'), 202);
        }

        //获取最后的充值记录
        $deposit_recharge = $this->sys_model_deposit->getOneRecharge(array('pdr_user_id' => $user_info['user_id'], 'pdr_type' => 1, 'pdr_payment_state' => 1), '*', 'pdr_add_time DESC');

        if (empty($deposit_recharge)) {
            $this->response->showErrorResult($this->language->get('error_no_prepaid_records'), 203);
        }
        // 退款金额,退押金默认为全退
        $deposit_recharge['cash_amount'] = $deposit_recharge['pdr_amount'];
        // 操作管理员信息
        $deposit_recharge['admin_id'] = 0;
        $deposit_recharge['admin_name'] = 'system';
        //写入到提现申请表，并写入日志
        $result = $this->sys_model_deposit->cashApply($deposit_recharge);
        if ($result['state']) {
            $pdc_info = array(
                'pdc_id' => $result['data']['pdc_id'],
                'pdc_sn' => $result['data']['pdc_sn'],
                'pdc_user_id' => $user_info['user_id'],
                'pdc_user_name' => $user_info['mobile'],
                'pdc_payment_name' => $deposit_recharge['pdr_payment_name'],
                'pdc_payment_code' => $deposit_recharge['pdr_payment_code'],
                'pdc_payment_type' => $deposit_recharge['pdr_payment_type'],
                'pdc_payment_state' => '0',
                'pdr_amount' => $deposit_recharge['pdr_amount'],
                'has_cash_amount' => 0,
                'cash_amount' => $deposit_recharge['cash_amount'],
                'pdr_sn' => $deposit_recharge['pdr_sn'],
                'trace_no' => $deposit_recharge['trace_no'],
                'admin_id' => $deposit_recharge['admin_id'],
                'admin_name' => $deposit_recharge['admin_name'],
                'pdc_type' => $deposit_recharge['pdr_type'],
            );
            // 自动退押金
            $auto_refund_deposit = $this->config->get('config_auto_refund_deposit');
            if ($auto_refund_deposit) {
                if ($pdc_info['pdc_payment_code'] == 'alipay') {
                    //支付宝无密码退款
                    $result = $this->sys_model_deposit->aliPayRefund($pdc_info);
                    if ($result['state'] == 1) {
                        $this->response->showSuccessResult('', $this->language->get('success_application'));
                    } else {
                        $this->response->showErrorResult($result['msg'], 204);
                    }
                } else if ($pdc_info['pdc_payment_code'] == 'wxpay') {
                    // 微信无密退款
                    $ssl_cert_path = WX_SSL_CONF_PATH . $this->config->get('config_wxpay_ssl_cert_path') . '/' . $pdc_info['pdc_payment_type'] . '/apiclient_cert.pem';
                    $ssl_key_path = WX_SSL_CONF_PATH . $this->config->get('config_wxpay_ssl_cert_path') . '/' . $pdc_info['pdc_payment_type'] . '/apiclient_key.pem';
                    define('WX_SSLCERT_PATH', $ssl_cert_path);
                    define('WX_SSLKEY_PATH', $ssl_key_path);
                    $result = $this->sys_model_deposit->wxPayRefund($pdc_info);
                    if ($result['state'] == true) {
                        $this->response->showSuccessResult('', $this->language->get('success_application'));
                    } else {
                        $this->response->showErrorResult($result['msg'], 204);
                    }
                }
            }
            $this->response->showSuccessResult('', $this->language->get('success_application'));
        } else {
            $this->response->showErrorResult($result['msg'], 204);
        }
    }

    /**
     * 生成充值订单
     */
    public function charging() {
        $recharge_id = $this->request->post['recharge_id'];
        /*$amount = floatval($amount);

        if ($this->request->get_request_header('sing') != 'BBC' && $amount > MAX_RECHARGE) {
            $this->response->showErrorResult($this->language->get('error_recharge_upper_limit'), 205);
        }

        if ($this->request->get_request_header('sing') != 'BBC' && $amount < MIN_RECHARGE) {
            $this->response->showErrorResult($this->language->get('error_recharge_lower_limit'), 206);
        }*/
        $this->load->library('sys_model/present', true);
        $info=$this->sys_model_present->getPresentInfo(['prc_id'=>$recharge_id]);
        if(empty($info)){
            $this->response->showErrorResult('充值优惠不存在');
        }

        $data['type'] = '0'; //普通充值
        $data['amount'] = $info['recharge_amount'];
        $data['pdr_present_amount'] = $info['present_amount'];
        $user_info = $this->startup_user->getUserInfo();
        if ($user_info['deposit_state'] == 0) {
            $this->response->showErrorResult($this->language->get('account_refund_recharge'), 506);
        }

        $data['user_id'] = $user_info['user_id'];
        $data['user_name'] = $user_info['mobile'];
        $this->load->library('logic/deposit', true);
        $result = $this->logic_deposit->addRecharge($data);
        if (!$result) {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
        $this->response->showSuccessResult($result['data'], $this->language->get('success_recharge_checkout'));
    }

    /**
     * 月卡充值
     */
    public function rechargeMonthCard() {
        $post = $this->request->post(array('setting_id'));
        $this->load->library('sys_model/month_card_setting');
        $card_info = $this->sys_model_month_card_setting->getMonthCardSetting(array('setting_id' => $post['setting_id']));

        if (empty($card_info)) {
            $this->response->showErrorResult('card_nonexists');
        }

        $data['type'] = 2;
        $data['amount'] = $card_info['amount'];

        $user_info = $this->startup_user->getUserInfo();
        if ($user_info['deposit_state'] == 0) {
            $this->response->showErrorResult($this->language->get('account_refund_recharge'), 506);
        }

        $data['user_id'] = $user_info['user_id'];
        $data['user_name'] = $user_info['mobile'];
        $this->load->library('logic/deposit', true);
        $result = $this->logic_deposit->addRecharge($data);
        if (!$result['state']) {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
        $callback = $result['data'];
        $insert = array(
            'user_id' => $user_info['user_id'],
            'user_name' => $user_info['mobile'],
            'recharge_sn' => $callback['pdr_sn'],
            'cooperator_id' => $user_info['cooperator_id'] ? $user_info['cooperator_id'] : 0,
            'region_id' => $user_info['region_id'] ? $user_info['region_id'] : 0,
            'time_length' => $card_info['time_length'],
        );

        $this->db->table('month_card')->insert($insert);
        $this->response->showSuccessResult($result['data'], $this->language->get('success_recharge_checkout'));
    }

    /**
     *
     */
    public function rechargeScenic() {
        $amount = (int) $this->request->post['amount'];
        if (floatval($amount) == 0) {
            $this->response->showErrorResult($this->language->get('error_deposit_amount'), 200);
        }
        $data['type'] = 0; //押金充值
        $data['amount'] = floatval($amount);
        $user_info = $this->startup_user->getUserInfo();
        $data['user_id'] = $user_info['user_id'];
        $data['user_name'] = $user_info['mobile'];
        $data['is_scenic'] = 1;
        $this->load->library('logic/deposit', true);

        $result = $this->logic_deposit->addRecharge($data);
        if ($result['state']) {
            $this->response->showSuccessResult($result['data'], $this->language->get('success_deposit_checkout'));
        } else {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
    }

    /**
     * 获取月卡充值信息
     */
    public function getMonthCardSetting() {
        $this->load->library('sys_model/month_card_setting');
        $userInfo = $this->startup_user->getUserInfo();
        $where = array('state' => 1);
        $month_setting_list = $this->sys_model_month_card_setting->getMonthCardSettingList($where);
        foreach ($month_setting_list as &$setting) {
            $setting['title'] = $setting['title'] . ' ' . intval($setting['amount']) . '元';
            $setting['expired'] = empty($userInfo['card_expired_time']) ? '未购买' : ( $userInfo['card_expired_time'] < time() ? '已过期' : '有效期至' . date('Y年m月d日', ($userInfo['card_expired_time'])));
        }
        $this->response->showSuccessResult($month_setting_list);
    }

    /**
     * 实名认证
     */
    public function identity() {
        $data['real_name'] = $this->request->post['real_name'];
        $data['identity'] = $this->request->post['identity'];

        if (empty($data['real_name'])) {
            $this->response->showErrorResult($this->language->get('error_empty_real_name'), 107);
        }
        if (empty($data['identity'])) {
            $this->response->showErrorResult($this->language->get('error_empty_identification'), 108);
        }

        //vincent: 2017-07-28 加入年龄限制12-60岁
        $date = strtotime(substr($data['identity'], 6, 8)); //获得出生年月日的时间戳
        $today = strtotime('today'); //获得今日的时间戳
        $diff = floor(($today - $date) / 86400 / 365); //得到两个日期相差的大体年数
        //strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
        $age = strtotime(substr($id, 6, 8) . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;
        if ($age < 12 || $age > 60) {
            $this->response->showErrorResult($this->language->get('error_age_limit'), 211);
        }

        //加入限制，1个身份证正能验证一次
        $exist = $this->startup_user->getUserInfo(array('identification' => $data['identity']));
        if ($exist) {
            $this->response->showErrorResult($this->language->get('error_identification_existed'), 109);
        }

        $user_info = $this->startup_user->getUserInfo();
        if (empty($user_info)) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        if (intval($user_info['verify_state']) > 0) {
            $this->response->showErrorResult($this->language->get('error_identified'), 110);
        }

        if (!intval($user_info['deposit_state'])) {
            $this->response->showErrorResult($this->language->get('error_non_payment_deposit'), 111);
        }

        $this->load->library('YinHan/YinHan');
        $this->YinHan_YinHan->setIDCondition($data['real_name'], $data['identity']);
        $result = $this->YinHan_YinHan->idCardAuth();
        //判断验证结果
        if (!$result->data) {
            $this->response->showErrorResult($result->msg->codeDesc, 112);
        } elseif ($result->data[0]->record[0]->resCode && (string) $result->data[0]->record[0]->resCode != '00') {
            $this->response->showErrorResult($result->data[0]->record[0]->resDesc, 112);
        } elseif ($result->data[0]->record[0]->resCode && (string) $result->data[0]->record[0]->resCode == '00') {
            $res_arr = (json_decode(json_encode($result), true));
            $data['verify_sn'] = $result->header->qryBatchNo;
            //资料入库
            $this->load->library('sys_model/user');
            $this->load->library('sys_model/identity');
            $user = $this->sys_model_user->getUserInfo(array('user_id' => $this->request->post['user_id']));
            $arr = array();
            $arr['il_user_id'] = $user['user_id'];
            $arr['il_user_mobile'] = $user['mobile'];
            $arr['il_real_name'] = $res_arr['data'][0]['record'][0]['realName'];
            $arr['il_identification'] = $res_arr['data'][0]['record'][0]['idCard'];
            $arr['il_cert_time'] = time();
            $arr['il_has_photo'] = isset($res_arr['data'][0]['record'][0]['photo']) ? 1 : 0;
            $arr['il_verify_state'] = $res_arr['data'][0]['record'][0]['resCode'] == '00' ? 1 : 0;
            $arr['il_verify_error_code'] = $res_arr['data'][0]['record'][0]['resCode'];
            $arr['il_verify_error_desc'] = $res_arr['data'][0]['record'][0]['resDesc'];
            $arr['il_charged'] = $res_arr['data'][0]['record'][0]['resCode'] == '00' ? 1 : 0;
            $arr['il_api_reply'] = print_r($result, true);
            $this->sys_model_identity->addIdentity($arr);
        }


        $update = $this->startup_user->verify_identity($user_info['user_id'], $data);
        if ($update) {
            $this->load->library('logic/credit', true);
            $this->logic_credit->addCreditPointOnVerification($user_info['user_id']);

            $this->response->showSuccessResult('', $this->language->get('success_identity'));
        }
        $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
    }

    /**
     * 注册推荐码
     */
    public function signRecommend() {
        if (!isset($this->request->post['mobile']) || empty($this->request->post['mobile'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        $mobile = $this->request->post['mobile'];
        if (!is_mobile($mobile)) {
            $this->response->showErrorResult($this->language->get('error_mobile'), 2);
        }

        $user_id = $this->startup_user->userId();
        $this->load->library('sys_model/user');

        $user_info = $this->sys_model_user->getUserInfo(array('mobile' => $mobile), 'user_id');
        if (empty($user_info)) {
            $this->response->showErrorResult($this->language->get('error_referrer'), 113);
        }
        //判断是否已分享
        $this->load->library('sys_model/coupon');
        $coupon_info = $this->sys_model_coupon->getCouponInfo(array('user_id' => $user_id, 'obtain' => 1));
        if (!empty($coupon_info)) {
            $this->response->showErrorResult($this->language->get('account_one_more'));
        }

        $time = $this->config->get('config_register_coupon_number');
        $this->addCoupon(array('user_id' => $user_id), $time, 1, 1);
        $this->addCoupon(array('user_id' => $user_info['user_id'], 'mobile' => $mobile), $time, 1, 1);

        $data = array(
            'recommend_num' => array('exp', 'recommend_num+1'),
            'credit_point' => array('exp', 'credit_point+' . RECOMMEND_POINT)
        );

        $update = $this->sys_model_user->updateUser(array('user_id' => $user_info['user_id']), $data);
        if (!$update) {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
        $this->response->showSuccessResult('', $this->language->get('success_referrer'));
    }

    /**
     * 退出登录
     */
    public function logout() {
        $user_id = $this->startup_user->userId();
        $this->startup_user->logout($user_id);
        $this->response->showSuccessResult();
    }

    //分享时候用到
    public function getEncryptCode() {
        $user_id = $this->startup_user->userId();
        $time = time();
        $code = $user_id . '_' . $time;
        $encrypt_code = encrypt($code);
        $this->response->showSuccessResult(array('encrypt_code' => $encrypt_code), $this->language->get('success_build'));
    }

    //通过encrypt获取用户的部分信息，无需登录
    public function getUserInfoByEncrypt() {
        if (!isset($this->request->post['encrypt_code'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'));
        }
        $encrypt_code = $this->request->post['encrypt_code'];
        $code = decrypt($encrypt_code);
        if (!strpos($code, '_')) {
            $this->response->showErrorResult($this->language->get('error_data_parse_failure'));
        }
        $arr = explode('_', $code);
        $user_id = $arr[0];
        $this->load->library('sys_model/user');
        $user_info = $this->sys_model_user->getUserInfo(array('user_id' => $user_id), 'avatar, nickname, mobile');
        if (empty($user_info)) {
            $this->response->showErrorResult($this->language->get('error_get_user_infomation'));
        }

        $user_info['mobile'] = substr($user_info['mobile'], 0, 3) . '****' . substr($user_info['mobile'], -4);
        if (is_mobile($user_info['nickname'])) {
            $user_info['nickname'] = substr($user_info['nickname'], 0, 3) . '****' . substr($user_info['nickname'], -4);
        }
        $this->response->showSuccessResult($user_info, $this->language->get('success_get'));
    }

    private function addCoupon($user_info, $number, $coupon_type, $obtain_type, $order_id = 0) {
        $this->load->library('sys_model/coupon');

        if (empty($user_info))
            return false;
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

    private function format($row) {
        $row['used'] = $row['used'] == '1';
        $row['expired'] = $row['failure_time'] < TIMESTAMP;
        $row['failure_time'] = date('Y-m-d', $row['failure_time']);
        if ($row['coupon_type'] == 1) {
            $show_hour = false;
            if ($row['number'] / 60 >= 1)
                $show_hour = true; //半小时取整
            $row['number'] = $show_hour ? round($row['number'] / 60, 2) : $row['number'];
            $row['unit'] = $show_hour ? $this->language->get('text_hour') : $this->language->get('text_minute');
        } elseif ($row['coupon_type'] == 2) {
            $row['unit'] = $this->language->get('text_time_unit');
        } elseif ($row['coupon_type'] == 3) {
            $row['unit'] = $this->language->get('text_money_unit');
        } elseif ($row['coupon_type'] == 4) {
            $row['unit'] = $this->language->get('text_discount_unit');
        }
        return $row;
    }

    /**
     * 获取故障详情
     */
    public function getFaultInfo() {
        $this->load->library('sys_model/fault');
        $this->load->library('sys_model/user');
        if (!isset($this->request->post['fault_id']) || empty($this->request->post['fault_id'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'));
        }
        $faultInfo = $this->sys_model_fault->getFaultInfo(array('fault_id' => $this->request->post['fault_id']));
        $userInfo = $this->sys_model_user->getUserInfo(array('user_id' => $this->request->post['user_id']));

        $faultInfo['nickname'] = $userInfo['nickname'];
        $faultInfo['add_time'] = date('Y-m-d H:i:s', $faultInfo['add_time']);
        $get_fault_status = get_fault_status();
        $fault_type = '';
        foreach (explode(',', $faultInfo['fault_type']) as $v) {
            $fault_type .= $get_fault_status[$v] . ',';
        }
        $faultInfo['fault_type'] = substr($fault_type, 0, -1);
        $this->response->showSuccessResult($faultInfo);
    }

    /**
     * 获取充值优惠
     */
    public function getRechargeOffer() {
        if (!isset($this->request->post['lat']) || empty($this->request->post['lng'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'));
        }
        $lat = $this->request->post['lat'] ;
        $lng = $this->request->post['lng'] ;
        //判断坐标是否在已开通的区域内；
        $this->load->library('sys_model/city');
        $city_where = array(
            'city_bounds_southwest_lat' => array('elt', $lat),
            'city_bounds_northeast_lat' => array('egt', $lat),
            'city_bounds_southwest_lng' => array('elt', $lng),
            'city_bounds_northeast_lng' => array('egt', $lng),
        );
        $city_list=$this->sys_model_city->getCityList($city_where, '',  '','city_id',[]);

        $area_list=array();
        if(empty($city_list)){//如果空，则去查地区列表
            $this->load->library('sys_model/region', true);
            $region_where = array(
                'region_bounds_southwest_lat' => array('elt', $lat),
                'region_bounds_northeast_lat' => array('egt', $lat),
                'region_bounds_southwest_lng' => array('elt', $lng),
                'region_bounds_northeast_lng' => array('egt', $lng),
            );
            $region_list = $this->sys_model_region->getRegionList($region_where);
            foreach($region_list as $key=>$val){
                $area_list[]=$val['region_id'];
            }
            $where['present_region_id']=array('in',$area_list);
        }else{
            foreach($city_list as $key=>$val){
                $area_list[]=$val['city_id'];
            }
            $where['present_city_id']=array('in',$area_list);
        }
        if(empty($area_list)){//当前位置没有充值优惠
            $this->response->showErrorResult('当前位置没有充值活动');
        }
        $this->load->library('sys_model/recharge_offer', true);
        $where['state']=1;
        $order='recharge_amount desc,present_amount desc';
        $recharge_list = $this->sys_model_recharge_offer->getRechargeOfferList($where,$order);
        //var_dump($recharge_list);
        $output = array();
        foreach ($recharge_list as $item) {
            $output[] = array(
                'recharge_id' => $item['prc_id'],
                'recharge_amount' => $item['recharge_amount'],
                'gift_amount' =>  $item['present_amount'],
                'gift_desc' => $item['recharge_amount']. '送' . $item['present_amount'] . '元'
            );
        }
        $this->response->showSuccessResult($output);
    }

    public function getOrders2() {
        $this->response->showSuccessResult($this->request->post);
    }

    /**
     * 获取区域列表 注册用
     */
    public function getRegions() {
        $this->load->library('sys_model/region');
        $regions = $this->sys_model_region->getRegionList([], '', '', 'region_id,region_name,code');

        $this->response->showSuccessResult($regions);
    }

    /**
     * 获取注册年龄大小，用户协议地址
     */
    public function getLanguageSetting() {
        $code = $this->request->post('code');
        if (empty($code)) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'));
        }
        $this->load->library('sys_model/language');
        $language = $this->sys_model_language->getLanguageInfo(['code' => $code]);

        $language['agreement_url'] = HTTP_IMAGE . 'language/agreement/' . $code . '.html';

        $this->response->showSuccessResult([
            'language_id' => $language['language_id'],
            'code' => $language['code'],
            'age_lower_limit' => $language['age_lower_limit'],
            'age_lower_limit_text' => $language['age_lower_limit_text'],
            'agreement' => $language['agreement'],
            'agreement_url' => $language['agreement_url']
        ]);
    }

    /**
     * 获取ios推送的token
     */
    public function setIosToken() {
        $param = $this->request->post(['ios_token', 'user_id']);
        if (empty($param['ios_token']) || empty($param['user_id'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), ErrorCode::ERROR_MISSING_PARAMETER);
        }
        $param['ios_token'] = str_replace(array('&lt;', '&gt;', ' '), '', $param['ios_token']);

        $this->load->library('sys_model/user');
        $a = $this->sys_model_user->updateUser(['user_id' => $param['user_id']], ['ios_token' => $param['ios_token']]);
        $this->response->showSuccessResult([]);
    }

    /**
     * 更新谷歌推送的token
     */
    public function setAndroidToken() {
        $param = $this->request->post(['android_token', 'user_id']);
        if (empty($param['android_token']) || empty($param['user_id'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), ErrorCode::ERROR_MISSING_PARAMETER);
        }
        $param['android_token'] = str_replace(array('&lt;', '&gt;', ' '), '', $param['android_token']);

        $this->load->library('sys_model/user');
        $this->sys_model_user->updateUser(['user_id' => $param['user_id']], ['android_token' => $param['android_token']]);
        $this->response->showSuccessResult([]);
    }

    public function test() {
        $ios_push = new \Tool\IosPush();
        $ios_push->push('99117114db49ed0377b0d7ac39657645f68f4f0d68da487f82f5a295fefcb109', 'hello json', 1);
        //$title = $this->language->get('email_register_title');
        //var_dump($title);exit;
    }

    /**
     * 申请更新邮箱
     */
    public function updateEmail() {
        //能进来到这里都是有userInfo的
        $userInfo = $this->startup_user->getUserInfo();
        $this->log->write(print_r($userInfo, true));
        /* if (empty($userInfo['verify_state']) //  verify_state=='0'，没有通过实名验证
          || empty($userInfo['real_name']) || empty($userInfo['identification'])) { // 用户实名或者身份证信息为空
          $this->response->showErrorResult($this->language->get('error_not_identification'), 115);
          } */
        if (empty($this->request->post['email'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }

        $old_user = $this->startup_user->getUserInfo(['user_id' => array('neq', $userInfo['user_id']), 'email' => $this->request->post['email']]);
        if (!empty($old_user['user_id'])) {
            $this->response->showErrorResult('此邮箱已被注册');
        }



        $now = time();
        $code = [
            'email' => $this->request->post['email'],
            'time' => $now,
            'sign' => md5(API_ACCESSKEY . $this->request->post['email'] . $now),
            'user_id' => $userInfo['user_id']
        ];
        $code = base64_encode(json_encode($code));
        $active_url = $this->url->link('account/account/updateEmail_confirm', ['code' => $code]);

        //发送邮件
        $this->load->library('sys_model/setting');
        $config_from_db = $this->sys_model_setting->getSettingList(['key' => ['in', ['smtp_server', 'smtp_server_port', 'smtp_user_mail', 'smtp_pass', 'mail_type', 'smtp_user']]]);
        foreach ($config_from_db as $key => $val) {
            $config[$val['key']] = $val['value'];
        }
        $config['smtp_pass'] = '';
        $email_tool = new Email($config);
        $title = $this->language->get('email_register_title');
        $title = 'Active your account!';
        $content = $this->language->get('email_register_content');
        $content = 'please click #anchor# to change your email';
        $content = str_replace('#anchor#', '<a href="' . $active_url . '">' . $this->language->get('click_me_to_change_email') . '</a>', $content);

        $res = $email_tool->sendEmail(null, $this->request->post['email'], $title, $content);
        if (!$res) {
            throw new \Exception($this->language->get('error_send_email_failure'), ErrorCode::ERROR_SEND_EMAIL_FAILURE);
        } else {
            $this->response->showSuccessResult();
        }
    }

    /**
     * 确认更新邮箱
     */
    public function updateEmail_confirm() {
        $this->load->library('sys_model/user');
        $code = $this->request->get('code');
        $code = json_decode(base64_decode($code), true);

        $sign = md5(API_ACCESSKEY . $code['email'] . $code['time']);
        if ($sign !== $code['sign']) {
            $this->response->showErrorResult($this->language->get('error_invalid_url'), ErrorCode::ERROR_INVALID_URL);
        }
        if (time() - intval($code['time']) > EMAIL_EXPIRE_TIME) {
            $this->response->showErrorResult($this->language->get('error_url_overtime'), ErrorCode::ERROR_URL_OVERTIME);
        }
        $userInfo = $this->sys_model_user->getUserInfo(['user_id' => $code['user_id']]);
        $this->log->write(print_r($userInfo, true));

        if (empty($code['email'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }

        $old_user = $this->sys_model_user->getUserInfo(['user_id' => array('neq', $userInfo['user_id']), 'email' => $code['email']]);
        if (!empty($old_user['user_id'])) {
            $this->response->showErrorResult('此邮箱已被注册');
        }

        $result = $this->sys_model_user->updateUser(['user_id' => $code['user_id']], array(
            'is_active' => 1,
            'email' => $code['email']
        ));

        if ($result) {
            $this->response->showSuccessResult();
        } else {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
    }

    /**
     * 发送验证码
     */
    public function sendChangemobileCode() {
        if (!isset($this->request->post['mobile'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }

        //加载短信配置，使用常量
        $this->getSMSConfig();

        $alert = $this->language->get('text_message_upper_limit');
        $mobile = trim($this->request->post['mobile']);
        if (!is_mobile($mobile)) {
            $this->response->showJsonResult($this->language->get('error_mobile'), 0, array('alert' => $alert), 2);
        }

        //限制发送次数
        $this->getMobileTodaySendTimes($mobile);

        $this->load->library('logic/sms', true);
        $this->load->library('logic/user', true);
        $result = $this->logic_user->existMobile($mobile);

        $type = 'changemobile';


        //vincent:2017-07-27 增加短信防轰炸
        if ($this->logic_sms->isOutOfSendLimit($mobile, $type, getIP())) {
            $this->response->showJsonResult('您发送短信过于频繁，请您稍后重试！', 0, array('alert' => '您发送短信过于频繁，请您稍后重试！'), 5);
        }

        $state = isset($state) ? $state : '0';
        $code = $this->logic_sms->createVerifyCode();
        $result_id = $this->logic_sms->sendSms($mobile, $code, $type);

        if ($result_id['state']) {
            $this->response->showSuccessResult(array('type' => $type, 'state' => 3, 'alert' => $alert));
        } else {
            if ($result_id['data']['code']) {
                $this->response->showJsonResult($this->language->get('error_send_message_failure_limit'), 0, array('alert' => $alert), 5);
            } else {
                $this->response->showJsonResult($this->language->get('error_send_message_failure'), 0, array('alert' => $alert), 4);
            }
        }
    }

    /**
     * 发送忘记密码验证码,虽然这堆发送验证码函数都很像，单还是分开写好，感觉随时会改，2018-01-08
     */
    public function sendForgetCode() {
        if (!isset($this->request->post['mobile'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }

        //加载短信配置，使用常量
        $this->getSMSConfig();

        $alert = $this->language->get('text_message_upper_limit');
        $mobile = trim($this->request->post['mobile']);
        if (!is_mobile($mobile)) {
            $this->response->showJsonResult($this->language->get('error_mobile'), 0, array('alert' => $alert), 2);
        }

        //限制发送次数
        $this->getMobileTodaySendTimes($mobile);

        $this->load->library('logic/sms', true);
        $this->load->library('logic/user', true);
        $result = $this->logic_user->existMobile($mobile);

        $type = 'forgetpwd';


        //vincent:2017-07-27 增加短信防轰炸
        if ($this->logic_sms->isOutOfSendLimit($mobile, $type, getIP())) {
            $this->response->showJsonResult('您发送短信过于频繁，请您稍后重试！', 0, array('alert' => '您发送短信过于频繁，请您稍后重试！'), 5);
        }

        $state = isset($state) ? $state : '0';
        $code = $this->logic_sms->createVerifyCode();
        $result_id = $this->logic_sms->sendSms($mobile, $code, $type);

        if ($result_id['state']) {
            $this->response->showSuccessResult(array('type' => $type, 'state' => 0, 'alert' => $alert));
        } else {
            if ($result_id['data']['code']) {
                $this->response->showJsonResult($this->language->get('error_send_message_failure_limit'), 0, array('alert' => $alert), 5);
            } else {
                $this->response->showJsonResult($this->language->get('error_send_message_failure'), 0, array('alert' => $alert), 4);
            }
        }
    }

    /**
     * 验证忘记密码的验证码
     */
    public function checkForgetCode() {
        if (!isset($this->request->post['mobile']) || !isset($this->request->post['code'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        $mobile = $this->request->post['mobile'];
        $code = $this->request->post['code'];
        $this->load->library('sys_model/sms', true);
        $this->load->library('sys_model/user', true);
        $sms_record = $this->sys_model_sms->getSmsInfo(['mobile' => $mobile, 'code' => $code, 'state' => 0]);
        if (empty($sms_record)) {
            $this->response->showErrorResult('没有发送记录', 1901, []);
        } else {
            if (strcmp($code, $sms_record['code']) == 0) {//验证码正确
                $this->sys_model_sms->updateSmsStatus(['mobile' => $mobile, 'code' => $code]); //更新验证状态
                $user_info = $this->sys_model_user->getUserInfo(['mobile' => $mobile], 'user_id');
                if (empty($user_info)) {
                    $this->response->showErrorResult('没有此用户记录', 1903, []);
                } else {
                    $this->response->showSuccessResult(['user_id' => $user_info['user_id']], '验证成功');
                }
            } else {//验证码不正确
                $this->response->showErrorResult('验证码错误', 1902, []);
            }
        }
    }

    /**
     * 更改密码
     */
    public function resetPassword() {
        $mobile = $this->request->post('mobile');
        $email = $this->request->post('email');
        $pass = $this->request->post(['password', 're_password']);
        if ($pass['password'] != $pass['re_password']) {
            $this->response->showErrorResult($this->language->get('两次密码输入不正确'), 2);
        }
        if (!preg_match('/\w{6,20}/i', $pass['password'])) {
            $this->response->showErrorResult($this->language->get('密码不符合规则'), 2);
        }
        if (empty($mobile) && empty($email)) {
            $this->response->showErrorResult('邮箱或手机号不能同时空', 2001);
        }
        $password = md5($pass['password'] . 'thisispassword');
        $this->load->library('sys_model/user', true);
        if (!empty($mobile) && is_mobile($mobile)) {//用手机修改密码
            $this->sys_model_user->updateUser(['mobile' => $mobile], ['password' => $password]);
            $user_inof = $this->sys_model_user->getUserInfo(['mobile' => $mobile], '*');
        } else if (!empty($email) && is_email($email)) {//用邮箱修改密码
            $user_inof = $this->sys_model_user->getUserInfo(['email' => $email], '*');
            if ($user_inof['is_ready_mail'] == 1) {//没有点确认邮箱
                $this->response->showErrorResult([], '请先确认修改密码邮件链接', 2002);
            }
            $this->sys_model_user->updateUser(['email' => $email], ['password' => $password]);
            $user_inof = $this->sys_model_user->getUserInfo(['email' => $email], '*');
        } else {//其他都是错误修改密码的方式
            $this->response->showErrorResult([], $this->language->get('failure'), 1);
        }
        $this->response->showSuccessResult($user_inof, $this->language->get('success'));
    }

    /**
     * 忘记密码用邮箱确认
     */
    public function sendForgetEmail() {

        if (empty($this->request->post['email'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }

        $this->load->library('sys_model/user', true);
        $user_info = $this->sys_model_user->getUserInfo(['email' => $this->request->post['email']], 'user_id');
        if (empty($user_info)) {
            $this->response->showErrorResult('没有此用户邮箱', 2101);
        }
        $now = time();
        $code = [
            'email' => $this->request->post['email'],
            'time' => $now,
            'sign' => md5(API_ACCESSKEY . $this->request->post['email'] . $now),
                // 'user_id' => $user_info['user_id']
        ];
        $code = base64_encode(json_encode($code));
        $active_url = $this->url->link('account/account/checkForgetEmail', ['code' => $code]);

        //发送邮件
        $this->load->library('sys_model/setting');
        $config_from_db = $this->sys_model_setting->getSettingList(['key' => ['in', ['smtp_server', 'smtp_server_port', 'smtp_user_mail', 'smtp_pass', 'mail_type', 'smtp_user']]]);
        foreach ($config_from_db as $key => $val) {
            $config[$val['key']] = $val['value'];
        }
        $config['smtp_pass'] = '';
        $email_tool = new Email($config);
        $title = $this->language->get('email_register_title');
        $title = 'Active your email to change password!';
        $content = $this->language->get('email_register_content');
        $content = 'please click #anchor# to change your password';
        $content = str_replace('#anchor#', '<a href="' . $active_url . '">' . $this->language->get('click_me_to_change_password') . '</a>', $content);

        $res = $email_tool->sendEmail(null, $this->request->post['email'], $title, $content);
        if (!$res) {
            throw new \Exception($this->language->get('error_send_email_failure'), ErrorCode::ERROR_SEND_EMAIL_FAILURE);
        } else {
            $this->load->library('sys_model/user');
            $this->sys_model_user->updateUser(['email' => $this->request->post['email']], ['is_ready_mail' => 1]);
            $this->response->showSuccessResult();
        }
    }

    /**
     * 确认忘记密码邮箱
     */
    public function checkForgetEmail() {
        $this->load->library('sys_model/user');
        $code = $this->request->get('code');
        $code = json_decode(base64_decode($code), true);

        $sign = md5(API_ACCESSKEY . $code['email'] . $code['time']);
        if ($sign !== $code['sign']) {
            $this->response->showErrorResult($this->language->get('error_invalid_url'), ErrorCode::ERROR_INVALID_URL);
        }
        if (time() - intval($code['time']) > EMAIL_EXPIRE_TIME) {
            $this->response->showErrorResult($this->language->get('error_url_overtime'), ErrorCode::ERROR_URL_OVERTIME);
        }



        if (empty($code['email'])) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        $this->sys_model_user->updateUser(['email' => $code['email']], ['is_ready_mail' => 0]);
        $userinfo = $this->sys_model_user->getUserInfo(['email' => $code['email']]);

        if (!empty($userinfo['android_token'])) {
            $android_push = new AndroidPush();
            $android_push->push($userinfo['android_token'], ['content' => '请修改密码', 'type' => PushCode::EMAIL_SET_PASSWORD, 'user_id' => $userinfo['user_id']]);
        }
        if ($userinfo['ios_token']) {
            $ios_push = new IosPush();
            $ios_push->push($userinfo['ios_token'], '请修改密码', PushCode::EMAIL_SET_PASSWORD);
        }
        if (!empty($userinfo)) {
            $this->response->showSuccessResult();
        } else {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
    }

    /**
     * 生成注册金
     */
    public function registration_gold() {
        $lat = $this->request->post['lat'];
        $lng = $this->request->post['lng'];
        $type = $this->request->post('type');
        if (empty($lat) || empty($lng) || empty($type)) {
            $this->response->showErrorResult('缺少参数', 2502);
        }
        $user_info = $this->startup_user->getUserInfo();
        if ($user_info['deposit_state'] == 0) {
            $this->response->showErrorResult($this->language->get('account_refund_recharge'), 506);
        }

        //判断坐标是否在已开通的区域内；
        $this->load->library('sys_model/city');
        $city_where = array(
            'city_bounds_southwest_lat' => array('elt', $lat),
            'city_bounds_northeast_lat' => array('egt', $lat),
            'city_bounds_southwest_lng' => array('elt', $lng),
            'city_bounds_northeast_lng' => array('egt', $lng),
        );
        $area_list = $this->sys_model_city->getCityInfo($city_where);
        if (empty($area_list)) {
            $this->load->library('sys_model/region');
            $region_where = array(
                'region_bounds_southwest_lat' => array('elt', $lat),
                'region_bounds_northeast_lat' => array('egt', $lat),
                'region_bounds_southwest_lng' => array('elt', $lng),
                'region_bounds_northeast_lng' => array('egt', $lng),
            );
            $area_list = $this->sys_model_region->getRegionInfo($region_where);
        }
        if (empty($area_list)) {//城市和区域都没有，不同意充值注册金
            $this->response->showErrorResult('你所在地没有开通注册金功能', 2501);
        }
        //$data['type'] = '3'; //注册金充值
        if ($type == 1) {//月注册金
            $data['amount'] = $area_list['monthly_card_money'];
           // $affect_time = 60 * 60 * 24 * 30;
            $data['type'] = '2';
        } else {//年注册金
            $data['amount'] = $area_list['yearly_card_money'];
           // $affect_time = 60 * 60 * 24 * 30 * 12;
            $data['type'] = '3';
        }


        $data['user_id'] = $user_info['user_id'];
        $data['user_name'] = $user_info['mobile'];
        $this->load->library('logic/deposit', true);
        $result = $this->logic_deposit->addRecharge($data);
        if (!$result) {
            $this->response->showErrorResult($this->language->get('error_database_operation_failure'), 4);
        }
        $this->response->showSuccessResult($result['data'], $this->language->get('success_recharge_checkout'));
    }
    /**
     * 获取用户应该充值多少押金
     */
    public function getdepositamount(){
        $input = $this->request->post(array('lng','lat'));
        if (empty($input['lat']) || empty($input['lng'])) {
            $this->response->showErrorResult('缺少参数');
        }
        $lat=$input['lat'];
        $lng=$input['lng'];
        $this->load->library('sys_model/city');
        $area_list=array();
        $city_where = array(
            'city_bounds_southwest_lat' => array('elt', $lat),
            'city_bounds_northeast_lat' => array('egt', $lat),
            'city_bounds_southwest_lng' => array('elt', $lng),
            'city_bounds_northeast_lng' => array('egt', $lng),
        );
        $area_list = $this->sys_model_city->getCityInfo($city_where);
        if (empty($area_list)) {
            $this->load->library('sys_model/region');
            $region_where = array(
                'region_bounds_southwest_lat' => array('elt', $lat),
                'region_bounds_northeast_lat' => array('egt', $lat),
                'region_bounds_southwest_lng' => array('elt', $lng),
                'region_bounds_northeast_lng' => array('egt', $lng),
            );
            $area_list = $this->sys_model_region->getRegionInfo($region_where);
        }
        if(empty($area_list)){
            $this->response->showErrorResult('当前位置不支持押金充值');
        }
        if(isset($area_list['city_id'])){//查到城市数据
            $return_data=array(
                'city_id'=>$area_list['city_id'],
                'deposit'=>$area_list['deposit'],
                'deposit_type'=>'city',
            );
            $this->response->showSuccessResult($return_data);
        }else if(isset($area_list['region_id'])){//查到区域数据
            $return_data=array(
                'region_id'=>$area_list['region_id'],
                'deposit'=>$area_list['deposit'],
                'deposit_type'=>'region',
            );
            $this->response->showSuccessResult($return_data);
        }
        
    }

}
