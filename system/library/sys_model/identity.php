<?php
namespace Sys_Model;

class Identity {
	public function __construct($registry) {
		$this->db = $registry->get('db');
	}

	//添加记录
	public function addIdentity($data) {
        return $this->db->table('identity_log')->insert($data);
    }

    //获取记录列表
    public function getIdentityList($where = '', $fields = '*', $order = '', $limit = '') {
        return $this->db->table('identity_log')->where($where)->field($fields)->order($order)->limit($limit)->select();
    }

    //获取记录数量
    public function getIdentityCount($where) {
        return $this->db->table('identity_log')->where($where)->limit(1)->count(1);
    }

    //获取记录详情
    public function getIdentityInfo($where, $field = '*') {
        if (!isset($where['identity_log'])) {
            $this->db->limit(1);
        }
        return $this->db->table('identity_log')->field($field)->where($where)->find();
    }
}