<?php
namespace Sys_Model;

class Payoff {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加结算
     * @param $data
     * @return mixed
     */
    public function addPayoff($data) {
        return $this->db->table('payoff')->insert($data);
    }

    /**
     * 更新结算
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updatePayoff($where, $data) {
        return $this->db->table('payoff')->where($where)->update($data);
    }

    /**
     * 删除结算
     * @param $where
     * @return mixed
     */
    public function deletePayoff($where) {
        return $this->db->table('payoff')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取结算列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getPayoffList($where = array(), $order = '', $limit = '', $field = 'payoff.*', $join = array()) {
        $table = 'payoff as payoff';
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
     * 获取结算信息
     * @param $where
     * @return mixed
     */
    public function getPayoffInfo($where) {
        return $this->db->table('payoff')->where($where)->limit(1)->find();
    }

    /**
     * 统计结算信息
     * @param $where
     * @return mixed
     */
    public function getTotalPayoffs($where, $join = array()) {
        $table = 'payoff as payoff';
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

    /**
     * 获取结算最大信息
     * @param $where
     * @return mixed
     */
    public function getMaxPayoffs($where, $field) {
        return $this->db->table('payoff')->where($where)->limit(1)->max($field);
    }
}
