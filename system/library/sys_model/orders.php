<?php
namespace Sys_Model;

class Orders {
    public function __construct($registry) {
        $this->db = $registry->get('db');
    }

    public function addOrders($data) {
        return $this->db->table('orders')->insert($data);
    }

    public function getOrdersList($where, $order = '', $limit = '', $field = '*', $join = array(), $group = '') {
        $table = 'orders as orders';
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
        
        return $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->group($group)->select();
    }

    public function getTotalOrders($where, $join = array()) {
        $table = 'orders as orders';
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
        return $this->db->table($table)->where($where)->count('order_id');
    }

    public function getOrdersInfo($where, $field = '*', $order = 'order_id desc') {
        return $this->db->table('orders')->field($field)->where($where)->order($order)->find();
    }

    public function addOrderLine($data) {
        return $this->db->table('orders_line')->insert($data);
    }

    public function getOrderLine($where, $fields = '*', $join = array(), $order = 'add_time asc', $limit = '', $group = '') {
        $table = 'orders_line as orders_line';
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
        return $this->db->table($table)->field($fields)->where($where)->group($group)->order($order)->limit($limit)->select();
    }

    public function updateOrders($where, $data) {
        return $this->db->table('orders')->where($where)->update($data);
    }

    public function deleteOrders($where) {
        return $this->db->table('orders')->where($where)->delete();
    }

    /**
     * 添加订单修改金额的申请
     * @param $data
     * @return bool
     */
    public function addOrderApply($data) {
        return $this->db->table('orders_modify_apply')->insert($data);
    }

    /**
     * 获取所有订单修改金额申请
     * @param $where
     * @param string $order
     * @param string $limit
     * @param string $field
     * @param array $join
     * @param string $group
     * @return mixed
     */
    public function getOrderApplyList($where, $order = '', $limit = '', $field = '*', $join = array(), $group = ''){
        $table = 'orders_modify_apply as orders_apply';
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

        return $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->group($group)->select();
    }

    /**
     * 获取orders_modify_apply分页
     * @param $where
     * @param array $join
     * @return mixed
     */
    public function getTotalOrderApply($where, $join = array()) {
        $table = 'orders_modify_apply as orders_apply';
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

    /**
     * 获取orders_modify_apply单条信息
     * @param $where
     * @return mixed
     */
    public function getOrderApplyInfo($where, $field = '*', $join = array()) {
        $table = 'orders_modify_apply as orders_modify_apply';
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

    /**
     * 更新orders_modify_apply
     * @param array $where
     * @param array $data
     * @return bool
     */
    public function updateOrderApply($where, $data) {
        return $this->db->table('orders_modify_apply')->where($where)->update($data);
    }

    public function getLastSql() {
        return $this->db->getLastSql();
    }

    public function begin() {
        $this->db->begin();
    }

    public function commit() {
        $this->db->commit();
    }

    public function rollback() {
        $this->db->rollback();
    }
    
    //批量插入轨迹数据
    public function addOrderLines($data) {
        return $this->db->table('orders_line')->insertAll($data);
    }
}
