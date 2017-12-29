<?php
namespace SysModel;
class Sms {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    public function addSms($data) {
        return $this->db->table('sms')->insert($data);
    }

    public function updateSmsStatus($where, $statue = 1) {
        return $this->db->table('sms')->where($where)->update($where);
    }

    public function delete($where) {
        return $this->db->table('sms')->where($where)->delete();
    }
}