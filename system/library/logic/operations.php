<?php
namespace Logic;
class Operations {
    private $sys_model_user;
    public $_user_info = array();
    public $_user_id;
    public $_admin_id;
    public $_admin_info;

    public function __construct($registry)
    {
        $this->registry         = $registry;
        $this->sys_model_user  = new \Sys_Model\Admin($registry);
        $this->session          = $registry->get('session');
        $this->sys_model_operations_login = new \Sys_Model\Operations_Login($registry);
        $this->request          = new \Request;
    }

    public function getUserInfo($where = array()) {

        if (!empty($this->_user_info) && empty($where)) {
            return $this->_user_info;
        }
        if (!$this->_user_id) {
            return false;
        }
        $where = !empty($where) ? $where : array('admin_name' => $this->_user_id, 'state' => 1);
        $this->_user_info = $this->sys_model_user->getAdminInfo($where);
        $this->_admin_info = $this->_user_info;
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

    public function adminId() {
        if ($this->_admin_id) {
            return $this->_admin_id;
        }
        return null;
    }

    public function checkUserSign($data, $sign) {
        $user_id          = $data['user_name'];
        $this->_user_id  = $user_id;
        $user_info        = $this->getUserInfo();
        $login_info       = $this->sys_model_operations_login->getOperationsLoginInfo(array("admin_id" => $user_info['admin_id'], "log_state" => 1));
        $this->_admin_id = $login_info['admin_id'];
        $this->_admin_info  = $login_info;
        if (empty($login_info)) {
            return callback(false, '用户尚未登录');
        }
        if ($login_info['uuid'] != $sign) {
            return callback(false, '用户在其他设备登录，请重新登录');
        }
        if($user_info['type'] != 3 ){
            return callback(false, '无权限登录');
        }
        return callback(true, '验证成功');
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
     * @param $admin_user_name
     * @param $device_id
     * @return array
     */
    public function login($admin_user_name, $password, $device_id) {

        $result = $this->sys_model_user->getAdminInfo(array('admin_name' => $admin_user_name,'type' => 3));
        if (!$result) {
            return callback(false, '不存在此账号，登录失败');
        }elseif(isset($result['state']) && $result['state'] != 1){
            return callback(false, '用户已被禁用');
        }
        if (!$this->sys_model_user->checkPassword($password, $result)) {
            return callback(false, '登录密码错误！');
        }

        $log_data['uuid']       = $device_id;
        $log_data['admin_id']   = $result['admin_id'];
        $log_data['login_time'] = time();
        $log_data['log_state']  = 1;
        $log_data['login_ip']   = $this->request->ip_address();

        $this->sys_model_operations_login->updateOperationsLogin(array("admin_id" => $result['admin_id']), array("log_state" => 0));
        $add_result = $this->sys_model_operations_login->addOperationsLogin($log_data);
        if(!$add_result){
            return callback(false, '登录失败100！');
        }
        $this->sys_model_user->updateAdmin(array("admin_id" => $result['admin_id']), array("login_time" => time()));
        $info = array(
            'admin_id'        => $result['admin_id'],
            'role_id'         => $result['role_id'],
            'admin_name'      => $result['admin_name'],
            'type'            => $result['type'],
            'add_time'        => $result['add_time'],
            'state'           => $result['state'],
            'login_ip'       => $result['login_ip'],
            'login_time'     => $result['login_time'],
            'cooperator_id'  => $result['cooperator_id'],
            'operation_ruler'  => $result['operation_ruler'],
            'salt'            => $device_id
        );

        $this->_admin_id = $result['admin_id'];
        $this->_admin_info  = $result;

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

    public function logout() {
        setcookie("user_id", "", time() - 3600, '/');
        setcookie("cookie_id", "", time() - 3600, '/');
        $result = $this->sys_model_operations_login->updateOperationsLogin(array("admin_id" => $this->_admin_id), array("log_state" => 0));
        if(!$result){
            return callback(false, 'error_out_login');
        }
        return callback(true, 'success_out_login');
    }
}