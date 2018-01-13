<?php

namespace Sys_Model;

class Work_Order
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    /**
     * 获取列表
     * @param $where
     * @param string $order
     * @param string $limit
     * @param string $field
     * @param array $join
     * @return mixed
     */
    public function getWorkOrderList($where, $order = '', $limit = '', $field = '*', $join = array())
    {
        $table = 'work_order as work_order';
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


    public function getTotalWorkOrder($where){

        return $this->db->table('work_order')->where($where)->limit(1)->count(1);

    }
    public function AddWorkOrder($data){
        return $this->db->table('work_order')->insert($data);
    }

    /**
     * 更新
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateWorkOrder($where, $data) {
        return $this->db->table('work_order')->where($where)->update($data);
    }

    public function deleteWorkOrder($where){
        return $this->db->table('work_order')->where($where)->delete();
    }


}