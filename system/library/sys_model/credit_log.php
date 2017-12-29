<?php

namespace Sys_Model;


class Credit_Log
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    public function addCreditPoint($data) {
        return $this->db->table('points_log')->insert($data);
    }

    public function getCreditPoints($where = array(), $fields = '*', $order = 'add_time DESC', $limit = '10') {
        return $this->db->table('points_log')->where($where)->field($fields)->order($order)->limit($limit)->select();
    }

    public function getCreditPointsCount($where = array()) {
        $result =  $this->db->table('points_log')->where($where)->field('COUNT(`point_id`) AS count')->select();
        return $result[0]['count'];
    }
}