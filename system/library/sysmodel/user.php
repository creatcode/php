<?php
namespace SysModel;
class User {
	public function __construct($registry) {
		$this->db = $registry->get('db');
	}
	
	public function addUser($data) {
        return $this->db->table('user')->insert($data);
    }

    public function getUserList($where, $fields = '*', $order = '', $limit = '') {
        return $this->db->table('user')->where($where)->field($fields)->order($order)->limit($limit)->select();
    }

    public function getUserInfo($where) {
        return $this->db->table('user')->where($where)->find();
    }

    public function updateUser($where, $data) {
        return $this->db->table('user')->where($where)->update($data);
    }

    /**
     * 生成16位的编码
     */
    public function makeSn() {
    }
}