<?php
namespace Sys_Model;

class Month_Card_Setting {
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->table_name = 'month_card_setting';
    }

    public function getMonthCardSettingList($where = array(), $order = '', $limit = '') {
        return $this->db->table($this->table_name)->where($where)->order($order)->limit($limit)->select();
    }

    public function getMonthCardSettingTotal($where = array()) {
        return $this->db->table($this->table_name)->where($where)->count(1);
    }

    public function updateMonthCardSetting($where, $data) {
        return $this->db->table($this->table_name)->where($where)->update($data);
    }

    public function getMonthCardSetting($where) {
        return $this->db->table($this->table_name)->where($where)->find();
    }
}