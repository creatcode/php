<?php

namespace Sys_Model;


class Message
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加系统消息
     * @param $data
     * @return mixed
     */
    public function addMessage($data) {
        return $this->db->table('message')->insert($data);
    }

    /**
     * 更新系统消息
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateMessage($where, $data) {
        return $this->db->table('message')->where($where)->update($data);
    }

    /**
     * 删除系统消息
     * @param $where
     * @return mixed
     */
    public function deleteMessage($where) {
        return $this->db->table('message')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取系统消息列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getMessageList($where = array(), $fields = 'm.*,user.user_type', $order = 'msg_time DESC', $limit = '10', $join = array()) {
        $table = 'message as m';
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
        return $this->db->table($table)->where($where)->field($fields)->order($order)->limit($limit)->select();
    }

    /**
     * 获取系统消息详情
     * @param mixed $where
     * @param string $field
     * @return mixed
     */
    public function getMessageInfo($where, $field = '*') {
        return $this->db->table('message')->where($where)->field($field)->limit(1)->find();
    }

    /**
     * 统计系统消息
     * @param $where
     * @return mixed
     */
    public function getTotalMessages($where, $join = array()) {
        $table = 'message as m';
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