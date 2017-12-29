<?php
namespace Sys_Model;
class Sms {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    public function addSms($data) {
        return $this->db->table('sms')->insert($data);
    }

    public function getSmsInfo($where) {
        return $this->db->table('sms')->where($where)->find();
    }

    public function updateSmsStatus($where, $status = 1) {
        return $this->db->table('sms')->where($where)->update(array('state' => $status));
    }

    public function delete($where) {
        return $this->db->table('sms')->where($where)->delete();
    }

    /**
     * [getSmsTotal 获取短信发送汇总数量]
     * @param    [type]                   $where [description]
     * @return   [type]                          [description]
     * @Author   vincent
     * @DateTime 2017-07-27T14:19:32+0800
     */
    public function getSmsTotal($where) {
        return $this->db->table('sms')->where($where)->limit(1)->count(1);
    }

    /**
     * [getLastSql 获取最后执行sql]
     * @return   [type]                   [description]
     * @Author   vincent
     * @DateTime 2017-08-21T18:11:35+0800
     */
    public function getLastSql(){
        return $this->db->getLastSql();
    }
}