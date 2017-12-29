<?php
namespace Sys_Model;

class User {
	public function __construct($registry) {
		$this->db = $registry->get('db');
	}

    // -------------------------------------------- 写 --------------------------------------------
    /**
     * 添加用户信息
     * @param $data
     * @return mixed
     */
	public function addUser($data) {
        return $this->db->table('user')->insert($data);
    }

    /**
     * 更新用户信息
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateUser($where, $data) {
        return $this->db->table('user')->where($where)->update($data);
    }

    // -------------------------------------------- 读 --------------------------------------------
    /**
     * 用户列表
     * @param string $where
     * @param string $fields
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getUserList($where = '', $fields = '*', $order = '', $limit = '') {
        return $this->db->table('user')->where($where)->field($fields)->order($order)->limit($limit)->select();
    }

    /**
     * 读取用户信息
     * @param $where
     * @param string $field
     * @return mixed
     */
    public function getUserInfo($where, $field = '*') {
        if (!isset($where['user_id'])) {
            $this->db->limit(1);
        }
        return $this->db->table('user')->field($field)->where($where)->find();
    }

    /**
     * 统计用户信息
     * @param $where
     * @return mixed
     */
    public function getTotalUsers($where) {
        return $this->db->table('user')->where($where)->limit(1)->count(1);
    }

    /**
     * 根据手机号读取用户信息
     * @param $mobile
     * @return mixed
     */
    public function existsMobile($mobile) {
        return $this->db->table('user')->field('mobile, deposit_state, verify_state, available_deposit')->where(array('mobile' => $mobile))->find();
    }
    // -------------------------------------------- 其他 --------------------------------------------
    /**
     * 生成16位的编码
     */
    public function makeSn() {
        
    }
}