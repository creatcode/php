<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2016/12/8
 * Time: 17:40
 */

namespace SysModel;


class Locationrecords
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    public function addLogs($data) {
        return $this->db->table('location_records')->insert($data);
    }

    public function deleteLogs($where) {
        return $this->db->table('location_records')->where($where)->delete();
    }

    public function getLogList($where = array(), $fields = '*', $order = '', $limit = '') {
        return $this->db->table('location_records')->where($where)->field($fields)->order($order)->limit($limit)->select();
    }
}