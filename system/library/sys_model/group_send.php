<?php

namespace Sys_Model;

/**
 * 短信群发
 * Class Group_Send
 * @package Sys_Model
 */
class Group_Send
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }


    public function add($data)
    {
        return $this->db->table('group_send')->insert($data);
    }


    public function update($where, $data)
    {
        $table = 'group_send as group_send';
        return $this->db->table($table)->where($where)->update($data);
    }


    public function delete($where)
    {
        return $this->db->table('group_send')->where($where)->delete();
    }

    public function getGroupSendList($where, $order = '', $limit = '', $field = '*', $join = array())
    {
        $table = 'group_send as group_send';
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

    public function getGroupSendCount($where, $field = '*')
    {
        return $this->db->table('group_send')->where($where)->field($field)->limit(1)->count(1);
    }


    public function getGroupSendInfo($where, $field = '*', $join = [])
    {
        $table = 'group_send as group_send';
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
        return $this->db->table($table)->where($where)->field($field)->limit(1)->find();
    }

}
