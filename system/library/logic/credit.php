<?php
namespace Logic;

define('CREDIT_DESC_ON_REGISTER', '注册完成');
define('CREDIT_POINT_ON_REGISTER', 100);
define('CREDIT_DESC_ON_VERIFICATION', '通过实名验证');
define('CREDIT_POINT_ON_VERIFICATION', 10);
define('CREDIT_DESC_ON_FINISH_CYCLING', '骑行完成');
define('CREDIT_POINT_ON_FINISH_CYCLING', 1);
define('CREDIT_DESC_ON_INVITE_FRIEND', '邀请好友完成注册');
define('CREDIT_POINT_ON_INVITE_FRIEND', 2);
define('CREDIT_DESC_ON_INVITED', '输入邀请码完成注册');
define('CREDIT_POINT_ON_INVITED', 2);
define('CREDIT_DESC_ON_SHARE_TRIP', '首次分享');
define('CREDIT_POINT_ON_SHARE_TRIP', 2);

class Credit {
    private $sys_model_credit_log;

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->sys_model_credit_log = new \Sys_Model\Credit_Log($registry);
    }

    /**
     * 获取一页信用积分记录
     * @param $user_id
     * @param $page
     * @return array
     */
    public function getCreditPoints($user_id, $page) {
        $limit = (empty($page) || $page<1) ? 10 : (10 * ($page-1) . ', 10');
        return $this->sys_model_credit_log->getCreditPoints(array('user_id'=>$user_id), '*', 'add_time DESC', $limit);
    }

    /**
     * 获取某个用户所有信用积分记录的条数
     * @param $user_id
     * @return integer
     */
    public function getCreditPointsCount($user_id) {
        return $this->sys_model_credit_log->getCreditPointsCount(array('user_id'=>$user_id));
    }

    /**
     * 用户完成注册添加信用分
     * @param $user_id
     * @return array
     */
    public function addCreditPointOnRegister($user_id) {
        return $this->addCreditPoint($user_id, CREDIT_POINT_ON_REGISTER, CREDIT_DESC_ON_REGISTER);
    }

    /**
     * 用户通过实名认证添加信用分
     * @param $user_id
     * @return array
     */
    public function addCreditPointOnVerification($user_id) {
        return $this->addCreditPoint($user_id, CREDIT_POINT_ON_VERIFICATION, CREDIT_DESC_ON_VERIFICATION);
    }

    /**
     * 用户完成骑行添加信用分
     * @param $user_id
     * @return array
     */
    public function addCreditPointOnFinishCycling($user_id) {
        return $this->addCreditPoint($user_id, CREDIT_POINT_ON_FINISH_CYCLING, CREDIT_DESC_ON_FINISH_CYCLING);
    }

    /**
     * 用户邀请好友完成注册添加信用分
     * @param $user_id
     * @return array
     */
    public function addCreditPointOnInviteFriend($user_id) {
        return $this->addCreditPoint($user_id, CREDIT_POINT_ON_INVITE_FRIEND, CREDIT_DESC_ON_INVITE_FRIEND);
    }

    /**
     * 用户输入邀请码完成注册添加信用分
     * @param $user_id
     * @return array
     */
    public function addCreditPointOnInvited($user_id) {
        return $this->addCreditPoint($user_id, CREDIT_POINT_ON_INVITED, CREDIT_DESC_ON_INVITED);
    }

    /**
     * 添加一条信用积分记录
     * @param $user_id
     * @param $points
     * @param $point_desc
     * @param int $admin_id
     * @param string $admin_name
     * @return array
     */
    public function addCreditPoint($user_id, $points, $point_desc, $admin_id=0, $admin_name='') {
        $points = intval($points);
        $db_result = $this->sys_model_credit_log->addCreditPoint(array(
            'points' => $points,
            'user_id' => $user_id,
            'add_time' => time(),
            'point_desc' => $point_desc,
            'admin_id' => $admin_id,
            'admin_name' => $admin_name
        ));
        if($db_result > 0) {
            if(!$this->registry->get('logic_user'))
                $this->registry->get('load')->library('logic/user', true);
            $this->registry->get('logic_user')->updateUserInfo($user_id, array('credit_point' => array('exp','`credit_point` + ' . $points)));
        }
        return callback('true');
    }

    /**
     * 信用积分改变，可能是增加，可能是减少，包括写入信用记录,事务单元
     * @param $type
     * @param $data
     */
    public function chargeCreditPoint($type, $data) {
        $data_log = array();
        $data_credit = array();

        $data_log[''] = $data['user_id'];
        $data_log[''] = $data['user_id'];
        $data_log[''] = TIMESTAMP;
    }
}