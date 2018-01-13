<?php
namespace Logic;

use \Enum\RegisterType;
use \Enum\ErrorCode;

class User {
    private $sys_model_user;
    public $_user_info = array();
    public $_user_id;

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->sys_model_user = new \Sys_Model\User($registry);
    }

    public function getUserInfo($where = array()) {
        if (!empty($this->_user_info) && empty($where)) {
            return $this->_user_info;
        }
        if (!$this->_user_id) {
            return false;
        }
        $where = !empty($where) ? $where : array('user_id' => $this->_user_id);
        $this->_user_info = $this->sys_model_user->getUserInfo($where);
        return $this->_user_info;
    }

    public function setUserId($user_id) {
        $this->_user_id = $user_id;
    }

    public function userId() {
        if ($this->_user_id) {
            return $this->_user_id;
        }
        return null;
    }

    public function checkUserSign($data, $sign) {
        $user_id = $data['user_id'];
        $this->_user_id = $user_id;
        $user_info = $this->getUserInfo();
        if(empty($user_info['uuid'])) {
            return callback(false, '用户尚未登录');
        }

        $user_sign = md5($user_id . $user_info['uuid']);
        if ($user_sign == $sign) {
            return callback(true, '验证成功');
        }
        return callback(false, '验证失败');
    }

    public function register($data,$register_type){
        switch($register_type){
            case RegisterType::MOBILE:
                return $this->mobileRegister($data);
                break;
            case RegisterType::EMAIL:
                return $this->emailRegister($data);
                break;
            default:
                throw new \Exception("register_failure",ErrorCode::REGISTER_FAILURE);
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    public function mobileRegister($data) {

        $arr = array();
        $arr['nickname'] = $arr['mobile'] = $data['mobile'];
        $arr['uuid'] = $data['uuid'];
        $arr['login_time'] = time();
        $arr['ip'] = getIP();
        $arr['user_sn'] = $this->make_sn();
        $arr['add_time'] = TIMESTAMP;
        $arr['update_time'] = TIMESTAMP;
        $arr['register_lat'] = isset($data['register_lat']) ? $data['register_lat'] : '';
        $arr['register_lng'] = isset($data['register_lng']) ? $data['register_lng'] : '';
        $arr['platform'] = isset($data['from']) ? $data['from'] : 'android';
        if (isset($data['register_region_id'])) $arr['register_region_id'] = $data['register_region_id'];
        if (isset($data['cooperator_id'])) $arr['cooperator_id'] = $data['cooperator_id'];
        $user = $this->sys_model_user->getUserInfo(['mobile'=>$data['mobile']]);
        if($user){
            if(!empty($user['password']))
                return callback(false, '此号码已经被注册');
            else{
                return callback(true, '注册成功', array('user_id' => $user['user_id'], 'user_sn' => $user['user_sn']));
            }
        }
        $insert_id = $this->sys_model_user->addUser($arr);
        if ($insert_id) {
            return callback(true, '注册成功', array('user_id' => $insert_id, 'user_sn' => $arr['user_sn']));
        } else {
            return callback(false, '注册失败，写入数据库失败');
        }
    }


    /**
     * 邮箱方式注册
     */
    private function emailRegister($data){

        $arr = array();
        $arr['nickname'] = $arr['email'] = $data['email'];
        $arr['uuid'] = $data['uuid'];
        $arr['login_time'] = time();
        $arr['ip'] = getIP();
        $arr['user_sn'] = $this->make_sn();
        $arr['add_time'] = TIMESTAMP;
        $arr['update_time'] = TIMESTAMP;
        $arr['register_lat'] = isset($data['register_lat']) ? $data['register_lat'] : '';
        $arr['register_lng'] = isset($data['register_lng']) ? $data['register_lng'] : '';
        $arr['platform'] = isset($data['from']) ? $data['from'] : 'android';
        if (isset($data['register_region_id'])) $arr['register_region_id'] = $data['register_region_id'];
        if (isset($data['cooperator_id'])) $arr['cooperator_id'] = $data['cooperator_id'];
        $user = $this->sys_model_user->getUserInfo(['email'=>$data['email']]);
        if($user){
            //如果还没有设置密码 则需要重新激活 用户可能是 在设置密码阶段退出了 所以这个激活是失效的 再次注册的时候 需要重新激活一次
            if($user['is_active']){
                return callback(false, '此号码已经被注册!');
            }
            $this->sys_model_user->updateUser(['user_id'=>$user['user_id']],$user);
            $return_data = ['user_id'=>$user['user_id'],'user_sn'=>$user['user_sn']];
        }else{
            $insert_id = $this->sys_model_user->addUser($arr);
            if($insert_id)
                $return_data = ['user_id'=>$insert_id,'user_sn'=>$arr['user_sn']];
            else
                $return_data = [];
        }

        if ($return_data) {
            return callback(true, '注册成功', $return_data);
        } else {
            return callback(false, '注册失败，写入数据库失败');
        }
    }


    /**
     * 写入实名认证
     * @param $user_id
     * @param $data
     * @return mixed
     */
    public function verify_identity($user_id, $data) {
        $where = array('user_id' => $user_id);

        $arr['real_name'] = $data['real_name'];
        $arr['identification'] = $data['identity'];
        $arr['credit_point'] = CREDIT_POINT; //信用分数
        $arr['cert_time'] = TIMESTAMP;
        $arr['verify_state'] = '1';
        return $this->sys_model_user->updateUser($where, $arr);
    }

    /**
     * 检测是否已注册
     * @param $mobile
     * @return array
     */
    public function existMobile($mobile) {
        $result = $this->sys_model_user->existsMobile($mobile);
        if ($result) {
            return callback(true, '', $result);
        }
        return callback(false);
    }

    public function make_sn() {
        return mt_rand(10, 99)
            . sprintf('%010', time() - 946656000)
            . sprintf('%03d', (float) microtime() * 1000)
            . sprintf('%03d', (float) microtime() * 1000);
    }

    /**
     * 登录
     * @param $mobile
     * @param $device_id
     * @return array
     */
    public function login($mobile, $device_id) {
        $result = $this->sys_model_user->getUserInfo(array('mobile' => $mobile));
        if (!$result) {
            return callback(false, '不存在此号码，登录失败');
        }
        $update = $this->sys_model_user->updateUser(array('mobile' => $mobile), array('ip' => getIP(), 'uuid' => $device_id, 'login_time' => time(), 'platform' => isset($this->registry->get('request')->get['fromApi']) ? $this->registry->get('request')->get['fromApi'] : 'android'));
        if (!$update) {
            return callback(false, '更新用户登录信息失败');
        }

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
            'credit_point' => $result['credit_point'],
            'real_name' => $result['real_name'],
            'identification' => $result['identification'],
            'verify_state' => $result['verify_state'],
            'available_state' => $result['available_state'],
            'recommend_num' => $result['recommend_num']
        );
        return callback(true, '登录成功', $info);
    }

    /**
     * 检测是否可交押金
     * @param $user_id
     * @return array
     */
    public function checkDeposit($user_id) {
        $result = $this->sys_model_user->getUserInfo(array('user_id' => $user_id), 'deposit, deposit_state');
        if (empty($result)) {
            return callback(false, '参数错误');
        }
        if ($result['deposit_state'] == 1) {
            return callback(false, '用户已交押金');
        }
        return callback(true);
    }

    /**
     * 检测是否可退押金
     * @param $user_id
     * @return array
     */
    public function checkCashDeposit($user_id) {
        $result = $this->sys_model_user->getUserInfo(array('user_id' => $user_id), 'deposit,deposit_state,available_deposit,freeze_deposit');
        if (!$result) {
            return callback(false, '参数错误');
        }

        if ($result['deposit_state'] != 1) {
            return callback(false, '押金未交不可退');
        }

        if ($result['freeze_deposit'] > 0) {
            return callback(false, '您还有欠费未结清，不可退押金');
        }

        return callback(true);
    }

    /**
     * 更新用户信息
     * @param $user_id
     * @param $data
     * @return array
     */
    public function updateUserInfo($user_id, $data) {
        $data['update_time'] = TIMESTAMP;
        $update = $this->sys_model_user->updateUser(array('user_id'=>$user_id), $data);
        return $update ? callback(true) : callback(false);
    }

    public function logout($user_id) {
        return $this->updateUserInfo($user_id, array('uuid' => ''));
    }

    /**
     * 设置密码
     * @param $user_id
     * @param $password
     * @return mixed
     */
    public function setPassword($user_id,$password){
        $password = md5($password.'thisispassword');
        return $this->sys_model_user->updateUser(['user_id'=>$user_id],['password'=>$password]);
    }

    /**
     * 检查密码
     * @param $password
     * @param $username string  email 或者 mobile
     */
    public function passwordLogin($username,$password,$uuid){
        $password = md5($password.'thisispassword');
        $user = $this->sys_model_user->getUserInfo(['mobile'=>$username,'password'=>$password]);
        if(!$user){
            $user = $this->sys_model_user->getUserInfo(['email'=>$username,'password'=>$password,'is_active'=>1]);
        }
        if(!$user){
            return callback(false, 'login failure');
        }
        $user['uuid'] = $uuid;
        $this->sys_model_user->updateUser(['user_id'=>$user['user_id']],$user);
        $info = array(
            'user_id' => $user['user_id'],
            'user_sn' => $user['user_sn'],
            'mobile' => $user['mobile'],
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
            'deposit' => $user['deposit'],
            'deposit_state' => $user['deposit_state'],
            'available_deposit' => $user['available_deposit'],
            'freeze_deposit' => $user['freeze_deposit'],
            'credit_point' => $user['credit_point'],
            'real_name' => $user['real_name'],
            'identification' => $user['identification'],
            'verify_state' => $user['verify_state'],
            'available_state' => $user['available_state'],
            'recommend_num' => $user['recommend_num'],
            'email' => $user['email'],
        );

        return callback(true, '登录成功', $info);
    }
}