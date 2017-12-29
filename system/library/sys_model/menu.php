<?php
namespace Sys_Model;

class Menu {
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
    public function addMenu($data) {
        return $this->db->table('rbac_menu')->insert($data);
    }

    /**
     * 更新菜单
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateMenu($where, $data) {
        return $this->db->table('rbac_menu')->where($where)->update($data);
    }

    /**
     * 删除菜单
     * @param $where
     * @return mixed
     */
    public function deleteMenu($where) {
        return $this->db->table('rbac_menu')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取菜单列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getMenuList($where = array(), $order = '', $limit = '') {
        return $this->db->table('rbac_menu')->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取菜单信息
     * @param $where
     * @return mixed
     */
    public function getMenuInfo($where) {
        return $this->db->table('rbac_menu')->where($where)->limit(1)->find();
    }

    /**
     * 统计菜单信息
     * @param $where
     * @return mixed
     */
    public function getTotalMenus($where) {
        return $this->db->table('rbac_menu')->where($where)->limit(1)->count(1);
    }

    /**
     * 获取菜单位置信息
     */
    public function getMenuLockMarker($where = array(), $field = '', $limit = '') {
        $field .= 'b.menu_id,b.menu_sn,b.type,b.fee,b.scenic_spot_id,b.scenic_spot_name,';
        $field .= 'l.lock_sn,l.lat,l.lng';
        $on = 'b.lock_sn=l.lock_sn';
        $result = $this->db->table('menu as b,lock as l')->where($where)->field($field)->join('left')->on($on)->limit($limit)->select();
        return $result;
    }
}
