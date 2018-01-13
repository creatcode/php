<?php
namespace Sys_Model;

class Advertisement {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加景区
     * @param $data
     * @return mixed
     */
    public function addAdvertisement($data) {
        return $this->db->table('advertisement')->insert($data);
    }

    /**
     * 更新景区
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateAdvertisement($where, $data) {
        return $this->db->table('advertisement')->where($where)->update($data);
    }

    /**
     * 删除景区
     * @param $where
     * @return mixed
     */
    public function deleteAdvertisement($where) {
        return $this->db->table('advertisement')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取景区列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getAdvertisementList($where = array(), $order = '', $limit = '', $field = 'r.*', $join = array()) {
        $table = 'advertisement as r';
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
       // return $this->db->getLastSql();
    }

    /**
     * 获取景区信息
     * @param $where
     * @return mixed
     */
    public function getAdvertisementInfo($where) {
        return $this->db->table('advertisement')->where($where)->limit(1)->find();
    }

    /**
     * 统计合伙人景区信息
     * @param $where
     * @return mixed
     */
    public function getTotalAdvertisement($where) {
        return $this->db->table('advertisement')->where($where)->limit(1)->count(1);
    }

}
