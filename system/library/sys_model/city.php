<?php
namespace Sys_Model;
class City
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    public function addCity($data) {
        return $this->db->table('city')->insert($data);
    }

    public function getCityInfo($where) {
        return $this->db->table('city')->where($where)->find();
    }

    public function updateCity($where, $data) {
        return $this->db->table('city')->where($where)->update($data);
    }

    public function deleteCity($where) {
        return $this->db->table('city')->where($where)->delete();
    }
    
    public function getCityList($where, $order = '', $limit = '', $field = '*',$join=array()) {

        $table = 'city as city';
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


    public function getTotalCities($where){

        return $this->db->table('city')->where($where)->limit(1)->count(1);

    }
}