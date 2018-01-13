<?php
namespace Sys_Model;


class Recharge_offer {
    public function __construct($registry) {
        $this->db = $registry->get('db');
    }

    public function updateRechargeOffer($where, $data) {
        return $this->db->table('present_recharge')->where($where)->update($data);
    }

    public function getRechargeOfferInfo($where) {
        return $this->db->table('present_recharge')->where($where)->find();
    }

    public function getRechargeOfferList($where = array(), $order = '', $limit = '') {
        return $this->db->table('present_recharge')->where($where)->order($order)->limit($limit)->select();
        //return $this->db->getLastSql();
    }

    public function getRechargeOfferTotal($where = array()) {
        return $this->db->table('present_recharge')->where($where)->limit(1)->count(1);
    }
}