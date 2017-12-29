<?php

namespace Sys_Model;

class Sms_Templates
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }


    public function getList($where = array(), $order = '', $limit = '')
    {
        $where['is_show'] = 1;
        return $this->db->table('sms_templates')->where($where)->order($order)->limit($limit)->select();
    }


    public function getInfo($where)
    {
        return $this->db->table('sms_templates')->where($where)->limit(1)->find();
    }


    public function getTotal($where)
    {
        return $this->db->table('sms_templates')->where($where)->limit(1)->count(1);
    }
}
