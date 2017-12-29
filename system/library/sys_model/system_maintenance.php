<?php
namespace Sys_Model;

class System_maintenance {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加系统维护日志
     * @param $data
     * @return mixed
     */
    public function addSystemMaintenance($data) {
        return $this->db->table('system_maintenance')->insert($data);
    }

    /**
     * 更新系统维护日志
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateSystemMaintenance($where, $data) {
        return $this->db->table('system_maintenance')->where($where)->update($data);
    }

    /**
     * 删除系统维护日志
     * @param $where
     * @return mixed
     */
    public function deleteSystemMaintenance($where) {
        return $this->db->table('system_maintenance')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取系统维护日志列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getSystemMaintenanceList($where = array(), $order = '', $limit = '', $field = 'system_maintenance.*', $join = array()) {
        $table = 'system_maintenance as system_maintenance';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }

        return $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取系统维护日志信息
     * @param $where
     * @return mixed
     */
    public function getSystemMaintenanceInfo($where) {
        return $this->db->table('system_maintenance')->where($where)->limit(1)->find();
    }

    /**
     * 统计系统维护日志信息
     * @param $where
     * @return mixed
     */
    public function getTotalSystemMaintenances($where, $join = array()) {
        $table = 'system_maintenance as system_maintenance';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }
        return $this->db->table($table)->where($where)->limit(1)->count(1);
    }
}
