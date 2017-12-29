<?php
namespace Sys_Model;

class Repair {
	public function __construct($registry) {
		$this->db = $registry->get('db');
	}

    // -------------------------------------------- 写 --------------------------------------------
    /**
     * 添加信息
     * @param $data
     * @return mixed
     */
	public function addRepair($data) {
        return $this->db->table('repair')->insert($data);
    }

    /**
     * 更新信息
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateRepair($where, $data) {
        return $this->db->table('repair')->where($where)->update($data);
    }

    // -------------------------------------------- 读 --------------------------------------------
    /**
     * 列表
     * @param string $where
     * @param string $fields
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getRepairList($where = '', $fields = '*', $order = '', $limit = '') {
        return $this->db->table('repair')->where($where)->field($fields)->order($order)->limit($limit)->select();
    }

    /**
     * 读取信息
     * @param $where
     * @param string $field
     * @return mixed
     */
    public function getRepairInfo($where, $field = '*') {
        if (!isset($where['repair_id'])) {
            $this->db->limit(1);
        }
        return $this->db->table('repair')->field($field)->where($where)->find();
    }

    /**
     * 统计信息
     * @param $where
     * @return mixed
     */
    public function getTotalRepairs($where) {
        return $this->db->table('repair')->where($where)->limit(1)->count(1);
    }

}