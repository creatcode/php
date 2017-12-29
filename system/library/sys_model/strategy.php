<?php
namespace Sys_Model;
class Strategy
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    public function addStrategy($data) {
        return $this->db->table('strategy')->insert($data);
    }

    public function getStrategyInfo($where) {
        return $this->db->table('strategy')->where($where)->find();
    }

    public function updateStrategy($where, $data) {
        return $this->db->table('strategy')->where($where)->update($data);
    }

    public function deleteStrategy($where) {
        return $this->db->table('strategy')->where($where)->delete();
    }

    public function getStrategyList($where, $order = '', $limit = '', $field = '*',$join=array()) {

        $table = 'strategy as strategy';
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


    public function getTotalStrategies($where){

        return $this->db->table('strategy')->where($where)->limit(1)->count(1);

    }
}