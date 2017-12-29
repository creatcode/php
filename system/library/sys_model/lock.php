<?php
namespace Sys_Model;

class Lock {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    /**
     * 添加锁
     * @param $data
     * @return mixed
     */
    public function addLock($data) {
        return $this->db->table('lock')->insert($data);
    }

    /**
     * 更新锁
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateLock($where, $data) {
        return $this->db->table('lock')->where($where)->update($data);
    }

    /**
     * 删除锁
     * @param $where
     * @return mixed
     */
    public function deleteLock($where) {
        return $this->db->table('lock')->where($where)->delete();
    }

    /**
     * 获取锁列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getLockList($where = array(), $order = '', $limit = '', $field = 'l.*', $join = array()) {

        $table = 'lock as l';
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
     * 可能废除，暂时保留
     * @param $where
     * @param string $field
     * @param string $limit
     * @return mixed
     */
    public function getLockListByRound($where, $field = '*', $limit = '') {
        return $this->getLockList($where, $field, $limit);
    }

    /**
     * 获取锁信息
     * @param $where
     * @return mixed
     */
    public function getLockInfo($where) {
        return $this->db->table('lock')->where($where)->limit(1)->find();
    }

    /**
     * 统计锁信息
     * @param $where
     * @return mixed
     */
    public function getTotalLocks($where, $join = array()) {
        $table = 'lock as l';
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
