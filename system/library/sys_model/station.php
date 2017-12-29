<?php
namespace Sys_Model;
class Station
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    public function addStation($data) {
        return $this->db->table('station')->insert($data);
    }

    public function getStationInfo($where) {
        return $this->db->table('station')->where($where)->find();
    }

    public function updateStation($where, $data) {
        return $this->db->table('station')->where($where)->update($data);
    }

    public function deleteStation($where) {
        return $this->db->table('station')->where($where)->delete();
    }

    public function getStationList($where, $order = '', $limit = '', $field = '*',$join=array()) {

        $table = 'station as station';
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


    public function getTotalStations($where){

        return $this->db->table('station')->where($where)->limit(1)->count(1);

    }
}