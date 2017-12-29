<?php
namespace Logic;
class Message {
    private $sys_model_message;

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->sys_model_message = new \Sys_Model\Message($registry);
    }

    /**
     * 获取一页信用积分记录
     * @param $user_id
     * @param $page
     * @return array
     */
    public function getMessages($where = '',$page) {
        $limit = (empty($page) || $page<1) ? 10 : (10 * ($page-1) . ', 10');
        return $this->sys_model_message->getMessageList($where, '*', 'msg_time DESC', $limit);
    }

    /**
     * 获取某个用户所有信用积分记录的条数
     * @param $user_id
     * @return integer
     */
    public function getMessagesCount($where = '') {
        return $this->sys_model_message->getTotalMessages($where);
    }

    /**
     * 添加一条信用积分记录
     * @param $user_id
     * @param $points
     * @param $point_desc
     * @param int $admin_id
     * @param string $admin_name
     * @return bool
     */
    public function addCreditPoint($user_id, $points, $point_desc, $admin_id=0, $admin_name='') {
        return true;
    }

}