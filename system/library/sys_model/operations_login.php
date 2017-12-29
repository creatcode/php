<?php
namespace Sys_Model;

class Operations_Login {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加菜单
     * @param $data
     * @return mixed
     */
    public function addOperationsLogin($data) {
        return $this->db->table('operations_login')->insert($data);
    }

    /**
     * 更新菜单
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateOperationsLogin($where, $data) {
        return $this->db->table('operations_login')->where($where)->update($data);
    }

    /**
     * 删除菜单
     * @param $where
     * @return mixed
     */
    public function deleteOperationsLogin($where) {
        return $this->db->table('operations_login')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取菜单列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getOperationsLoginList($where = array(), $order = '', $limit = '') {
        return $this->db->table('operations_login')->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取菜单信息
     * @param $where
     * @return mixed
     */
    public function getOperationsLoginInfo($where) {
        return $this->db->table('operations_login')->where($where)->limit(1)->find();
    }

    /**
     * 统计菜单信息
     * @param $where
     * @return mixed
     */
    public function getTotalOperationsLogin($where) {
        return $this->db->table('operations_login')->where($where)->limit(1)->count(1);
    }

}
