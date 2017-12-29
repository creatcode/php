<?php
namespace Sys_Model;

class Bicycle_usage {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    /**
     * 添加使用次数
     * @param $data
     * @return mixed
     */
    public function addUsageCount($data) {
        return $this->db->table('bicycle_usage_count')->insert($data);
    }

    /**
     * 更新使用次数
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateUsageCount($where, $data) {
        return $this->db->table('bicycle_usage_count')->where($where)->update($data);
    }

    /**
     * 统计使用次数
     * @param string $where
     * @param string $field
     * @param string $group
     * @return mixed
     */
    public function getTotalUsageCount($where = '', $field = '*', $group = '') {
        return $this->db->getRows('select '.$field.' from rich_bicycle_usage_count '.$where.$group);
    }

    /**
     * 获取使用次数信息
     * @param $where
     * @return mixed
     */
    public function getUsageCountInfo($where) {
        return $this->db->table('bicycle_usage_count')->where($where)->limit(1)->find();
    }

}
