<?php
namespace Sys_Model;
class Instruction {

    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    public function addInstructionRecord($data) {
        return $this->db->table('instruct_records')->insert($data);
    }

    public function getInstructionRecord($where, $order = '', $limit = '') {
        return $this->db->table('instruct_records')->where($where)->order($order)->limit($limit)->select();
    }
}