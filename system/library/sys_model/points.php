<?php
namespace Sys_Model;

class Points {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ---------------------------------------------- 写 ----------------------------------------------
    /**
     * 添加信用积分
     * @param $data
     * @return mixed
     */
    public function addPoints($data) {
        return $this->db->table('points_log')->insert($data);
    }

    /**
     * 更新信用积分
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updatePoints($where, $data) {
        return $this->db->table('points_log')->where($where)->update($data);
    }

    /**
     * 删除信用积分
     * @param $where
     * @return mixed
     */
    public function deletePoints($where) {
        return $this->db->table('points_log')->where($where)->delete();
    }

    // ---------------------------------------------- 读 ----------------------------------------------

    /**
     * 获取信用积分列表
     * @param $where
     * @param string $order
     * @param string $limit
     * @param string $field
     * @param array $join
     * @return mixed
     */
    public function getPointsList($where, $order = '', $limit = '', $field = 'pl.*', $join = array()) {
        $table = 'points_log as pl';
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

        return $this->db->table($table)->where($where)->field($field)->order($order)->limit($limit)->select();
    }


    /**
     * 获取信用积分信息
     * @param $where
     * @return mixed
     */
    public function getPointsInfo($where) {
        return $this->db->table('points_log')->where($where)->limit(1)->find();
    }

    /**
     * 统计信用积分信息
     * @param $where
     * @param $join
     * @return mixed
     */
    public function getTotalPoints($where, $join) {
        $table = 'points_log as pl';
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
