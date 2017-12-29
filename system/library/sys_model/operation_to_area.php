<?php

namespace Sys_Model;

class Operation_To_Area
{

    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    public function add($data) {
        return $this->db->table('operations_to_region')->insert($data);
    }


    public function update($where, $data)
    {
        return $this->db->table('operations_to_region')->where($where)->update($data);
    }

    public function getInfo($where, $field = '*', $join = array())
    {
        $table = 'operations_to_region as operations_to_region';
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
        return $this->db->table($table)->field($field)->where($where)->limit(1)->find();
    }


}
