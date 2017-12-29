<?php
namespace Sys_Model;

class Admin_Log {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加管理员操作日志
     * @param $data
     * @return mixed
     */
    public function addAdminLog($data) {
        return $this->db->table('admin_log')->insert($data);
    }

    /**
     * 更新管理员操作日志
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateAdminLog($where, $data) {
        return $this->db->table('admin_log')->where($where)->update($data);
    }

    /**
     * 删除管理员操作日志
     * @param $where
     * @return mixed
     */
    public function deleteAdminLog($where) {
        return $this->db->table('admin_log')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取管理员操作日志列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getAdminLogList($where = array(), $order = '', $limit = '', $field = 'admin_log.*', $join = array()) {
        $table = 'admin_log as admin_log';
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
     * 获取管理员操作日志信息
     * @param $where
     * @return mixed
     */
    public function getAdminLogInfo($where) {
        return $this->db->table('admin_log')->where($where)->limit(1)->find();
    }

    /**
     * 统计管理员操作日志信息
     * @param $where
     * @return mixed
     */
    public function getTotalAdminLogs($where, $join = array()) {
        $table = 'admin_log as admin_log';
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
