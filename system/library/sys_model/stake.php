<?php
namespace Sys_Model;
class Stake
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    public function addStake($data) {
        return $this->db->table('stake')->insert($data);
    }

    public function getStakeInfo($where) {
        return $this->db->table('stake')->where($where)->find();
    }

    public function updateStake($where, $data) {
        return $this->db->table('stake')->where($where)->update($data);
    }

    public function deleteStake($where) {
        return $this->db->table('stake')->where($where)->delete();
    }

    public function getStakeList($where, $order = '', $limit = '', $field = '*',$join=array()) {

        $table = 'stake as stake';
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


    public function getTotalStakes($where){

        return $this->db->table('stake')->where($where)->limit(1)->count(1);

    }
}