<?php
namespace Sys_Model;

class Move_Bicycle {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加
     * @param $data
     * @return mixed
     */
    public function add($data) {
        return $this->db->table('move_bicycle')->insert($data);
    }

    /**
     * 更新
     * @param $where
     * @param $data
     * @return mixed
     */
    public function update($where, $data) {
        return $this->db->table('move_bicycle')->where($where)->update($data);
    }

    /**
     * 删除
     * @param $where
     * @return mixed
     */
    public function delete($where) {
        return $this->db->table('move_bicycle')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getList($where = array(), $order = '', $limit = '', $field = 'move_bicycle.*', $join = array()) {
        $table = 'move_bicycle as move_bicycle';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $filter = '/(lock)/i';
                    preg_match($filter, $v) ? $table .= sprintf(',%s as %s', $v, '`' .$v . '`') :
                    $table .= sprintf(',%s as %s', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }
        $result = $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->select();
        return $result;
    }

    /**
     * 获取信息
     * @param $where
     * @param string $field
     * @return mixed
     */
    public function getInfo($where, $field = '*', $order = '') {
        return $this->db->table('move_bicycle')->where($where)->field($field)->order($order)->limit(1)->find();
    }

    /**
     * 统计信息
     * @param $where
     * @return mixed
     */
    public function getTotals($where, $join = array()) {
        $table = 'move_bicycle as move_bicycle';
        if (is_array($join) && !empty($join)) {
            $addTables = array_keys($join);
            $joinType = '';
            if (!empty($addTables) && is_array($addTables)) {
                foreach ($addTables as $v) {
                    $table .= sprintf(',%s as `%s`', $v, $v);
                    $joinType .= ',left';
                }
            }
            $on = implode(',', $join);

            $this->db->join($joinType)->on($on);
        }

        return $this->db->table($table)->where($where)->limit(1)->count(1);
    }




}
