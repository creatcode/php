<?php
namespace Sys_Model;

class Credit_Card {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加信用卡
     * @param $data
     * @return mixed
     */
    public function addCreditcard($data) {
        return $this->db->table('credit_card')->insert($data);
    }

    /**
     * 更新信用卡
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateCreditcard($where, $data) {
        return $this->db->table('credit_card')->where($where)->update($data);
    }

    /**
     * 删除信用卡
     * @param $where
     * @return mixed
     */
    public function deleteCreditcard($where) {
        return $this->db->table('credit_card')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取信用卡列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getCreditcardList($where = array(), $order = '', $limit = '', $field = 'r.*', $join = array()) {
        $table = 'credit_card as cc';
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
     * 获取信用卡信息
     * @param $where
     * @return mixed
     */
    public function getCreditcardInfo($where) {
        return $this->db->table('credit_card')->where($where)->limit(1)->find();
    }

    /**
     * 统计信用卡信息
     * @param $where
     * @return mixed
     */
    public function getTotalCreditcards($where, $join = array()) {
        $table = 'credit_card as cc';
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
    public function getLastSql(){
        return $this->db->getLastSql();
    }

  
}
