<?php
namespace Sys_Model;

class Gift {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ------------------------------------------------- 礼品表 -------------------------------------------------
    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加礼品
     * @param $data
     * @return mixed
     */
    public function addGift($data) {
        return $this->db->table('gift')->insert($data);
    }

    /**
     * 更新礼品
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateGift($where, $data) {
        return $this->db->table('gift')->where($where)->update($data);
    }

    /**
     * 删除礼品
     * @param $where
     * @return mixed
     */
    public function deleteGift($where) {
        return $this->db->table('gift')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取礼品列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getGiftList($where = array(), $order = '', $limit = '', $field = 'gift.*', $join = array()) {
        $table = 'gift as gift';
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

        return $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取礼品信息
     * @param $where
     * @return mixed
     */
    public function getGiftInfo($where) {
        return $this->db->table('gift')->where($where)->limit(1)->find();
    }

    /**
     * 统计礼品信息
     * @param $where
     * @return mixed
     */
    public function getTotalGifts($where, $join = array()) {
        $table = 'gift as gift';
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


    // ------------------------------------------------- 礼品订单表 -------------------------------------------------
    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加礼品订单
     * @param $data
     * @return mixed
     */
    public function addGiftOrder($data) {
        return $this->db->table('gift_orders')->insert($data);
    }

    /**
     * 更新礼品订单
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateGiftOrder($where, $data) {
        return $this->db->table('gift_orders')->where($where)->update($data);
    }

    /**
     * 删除礼品订单
     * @param $where
     * @return mixed
     */
    public function deleteGiftOrder($where) {
        return $this->db->table('gift_orders')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取礼品订单列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getGiftOrderList($where = array(), $order = '', $limit = '', $field = 'gift_orders.*', $join = array()) {
        $table = 'gift_orders as gift_orders';
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

        return $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取礼品订单信息
     * @param $where
     * @return mixed
     */
    public function getGiftOrderInfo($where) {
        return $this->db->table('gift_orders')->where($where)->limit(1)->find();
    }

    /**
     * 统计礼品订单信息
     * @param $where
     * @return mixed
     */
    public function getTotalGiftOrders($where, $join = array()) {
        $table = 'gift_orders as gift_orders';
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


    // ------------------------------------------------- 礼品活动表 -------------------------------------------------
    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加礼品活动
     * @param $data
     * @return mixed
     */
    public function addGiftActivity($data) {
        return $this->db->table('gift_activity')->insert($data);
    }

    /**
     * 更新礼品活动
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateGiftActivity($where, $data) {
        return $this->db->table('gift_activity')->where($where)->update($data);
    }

    /**
     * 删除礼品活动
     * @param $where
     * @return mixed
     */
    public function deleteGiftActivity($where) {
        return $this->db->table('gift_activity')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取礼品活动列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getGiftActivityList($where = array(), $order = '', $limit = '', $field = 'gift_activity.*', $join = array()) {
        $table = 'gift_activity as gift_activity';
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

        return $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取礼品活动信息
     * @param $where
     * @return mixed
     */
    public function getGiftActivityInfo($where) {
        return $this->db->table('gift_activity')->where($where)->limit(1)->find();
    }

    /**
     * 统计礼品活动信息
     * @param $where
     * @return mixed
     */
    public function getTotalGiftActivities($where, $join = array()) {
        $table = 'gift_activity as gift_activity';
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

    // ------------------------------------------------- 参与活动的礼品表 -------------------------------------------------
    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加参与活动的礼品
     * @param $data
     * @return mixed
     */
    public function addGiftActivityToGift($data) {
        return $this->db->table('gift_activity_to_gift')->insert($data);
    }

    /**
     * 更新参与活动的礼品
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateGiftActivityToGift($where, $data) {
        return $this->db->table('gift_activity_to_gift')->where($where)->update($data);
    }

    /**
     * 删除参与活动的礼品
     * @param $where
     * @return mixed
     */
    public function deleteGiftActivityToGift($where) {
        return $this->db->table('gift_activity_to_gift')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------

    /**
     * 获取参与活动的礼品列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getGiftActivityToGiftList($where = array(), $field = '*') {
        return $this->db->table('gift_activity_to_gift')->field($field)->where($where)->select();
    }
}
