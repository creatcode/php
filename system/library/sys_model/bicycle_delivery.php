<?php
namespace Sys_Model;

class Bicycle_delivery {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    /**
     * 添加锁
     * @param $data
     * @return mixed
     */
    public function addDelivery($data) {
        return $this->db->table('bicycle_delivery')->insert($data);
    }

    /**
     * 更新锁
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateDelivery($where, $data) {
        return $this->db->table('bicycle_delivery')->where($where)->update($data);
    }

    /**
     * 删除锁
     * @param $where
     * @return mixed
     */
    public function deleteDelivery($where) {
        return $this->db->table('bicycle_delivery')->where($where)->delete();
    }

    /**
     * 获取锁列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getDeliveryList($where = array(), $order = '', $limit = '', $field = 'b.*', $join = array()) {

        $table = 'bicycle_delivery as b';
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
     * 获取锁信息
     * @param $where
     * @return mixed
     */
    public function getDeliveryInfo($where) {
        return $this->db->table('bicycle_delivery')->where($where)->limit(1)->find();
    }

    /**
     * 统计锁信息
     * @param $where
     * @return mixed
     */
    public function getTotalDeliverys($where, $join = array()) {
        $table = 'bicycle_delivery as b';
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
     * 删除锁
     * @param $where
     * @return mixed
     */
    public function deleteMacTemp($where) {
        return $this->db->table('mac_temp')->where($where)->update(array('state' => 0));
    }

}
