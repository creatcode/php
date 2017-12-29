<?php
namespace Sys_Model;
class Language
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    public function addLanguage($data) {
        return $this->db->table('language')->insert($data);
    }

    public function getLanguageInfo($where) {
        return $this->db->table('language')->where($where)->find();
    }

    public function updateLanguage($where, $data) {
        return $this->db->table('language')->where($where)->update($data);
    }

    public function deleteLanguage($where) {
        return $this->db->table('language')->where($where)->delete();
    }

    public function getLanguageList($where, $order = '', $limit = '', $field = '*',$join=array()) {

        $table = 'language as language';
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


    public function getTotalLanguages($where){

        return $this->db->table('language')->where($where)->limit(1)->count(1);
    }
}