<?php
/**
 * 充值送多少的
 * User: estronger
 * Date: 2017/3/3
 * Time: 15:51
 */
namespace Sys_Model;
class Discount {
    public function __construct($registry) {
        $this->db = $registry->get('db');
    }

    public function getDiscountList($where = array(), $limit = '', $order = '') {
        return $this->db->table('activity_delivery')->where($where)->order($order)->limit($limit)->select();
    }

    public function getDiscountInfo($where) {
        return $this->db->table('activity_delivery')->where($where)->find();
    }

    public function getEffectDiscount() {
        //同一个时间段内容，只能有一个优惠生效
        $effect_discount = $this->db->table('activity_delivery')->where(array('start_time' => array('gt', time()), 'end_time' => array('lt', time()), 'open_state' => 1))->find();
        $result = array();
        if (!empty($effect_discount)) {
            $items = $this->db->table('activity_delivery_item')->where(array('rad_id' => $effect_discount['rad_id']))->select();
            if (!empty($items)) {
                $result = $items;
            }
        }
        return $result;
    }

    public function addDiscount($data) {
        return $this->db->table('activity_delivery')->insert($data);
    }

    public function addDiscountItem($data) {
        return $this->db->table('activity_delivery_item')->insertAll($data);
    }

    public function addDelivery($data, $items) {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        if (!is_array($items) || empty($items)) {
            return false;
        }
        $insert_id = $this->addDiscount($data);
        if ($insert_id) {
            foreach ($items as &$value) {
                $value['rad_id'] = $insert_id;
            }
            $this->addDiscountItem($items);
            return true;
        }
        return false;
    }
}