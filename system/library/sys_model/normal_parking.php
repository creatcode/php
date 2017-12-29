<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2016/12/8
 * Time: 17:40
 */

namespace Sys_Model;


class Normal_Parking
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    public function addNormalParking($data) {
        return $this->db->table('normal_parking')->insert($data);
    }


    public function delete($where) {
        return $this->db->table('normal_parking')->where($where)->delete();
    }

    public function getList($where = array(), $fields = '*', $order = '', $limit = '') {
        return $this->db->table('normal_parking')->where($where)->field($fields)->order($order)->limit($limit)->select();
    }

    public function find($where = array(), $field = '*') {
        return $this->db->table('normal_parking')->where($where)->field($field)->order('time DESC')->limit(1)->find();
    }
}