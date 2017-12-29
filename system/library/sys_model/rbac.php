<?php
namespace Sys_Model;

class Rbac {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ************************************* 角色 ************************************
    // ----------------------- 写 -----------------------
    /**
     * 添加角色
     * @param $data
     * @return mixed
     */
    public function addRole($data) {
        return $this->db->table('rbac_role')->insert($data);
    }

    /**
     * 更新角色
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateRole($where, $data) {
        return $this->db->table('rbac_role')->where($where)->update($data);
    }

    /**
     * 删除角色
     * @param $where
     * @return mixed
     */
    public function deleteRole($where) {
        return $this->db->table('rbac_role')->where($where)->delete();
    }

    // ----------------------- 读 -----------------------
    /**
     * 获取角色列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getRoleList($where = array(), $order = '', $limit = '') {
        return $this->db->table('rbac_role')->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取角色信息
     * @param $where
     * @return mixed
     */
    public function getRoleInfo($where) {
        return $this->db->table('rbac_role')->where($where)->limit(1)->find();
    }

    /**
     * 统计角色信息
     * @param $where
     * @return mixed
     */
    public function getTotalRoles($where) {
        return $this->db->table('rbac_role')->where($where)->limit(1)->count(1);
    }

    // ************************************* 权限 ************************************
    // ----------------------- 写 -----------------------
    /**
     * 添加权限
     * @param $data
     * @return mixed
     */
    public function addPermission($data) {
        return $this->db->table('rbac_permission')->insert($data);
    }

    /**
     * 更新权限
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updatePermission($where, $data) {
        return $this->db->table('rbac_permission')->where($where)->update($data);
    }

    /**
     * 删除权限
     * @param $where
     * @return mixed
     */
    public function deletePermission($where) {
        return $this->db->table('rbac_permission')->where($where)->delete();
    }

    // ----------------------- 读 -----------------------
    /**
     * 获取权限列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getPermissionList($where = array(), $order = '', $limit = '') {
        return $this->db->table('rbac_permission')->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取权限信息
     * @param $where
     * @return mixed
     */
    public function getPermissionInfo($where) {
        return $this->db->table('rbac_permission')->where($where)->limit(1)->find();
    }

    /**
     * 统计权限信息
     * @param $where
     * @return mixed
     */
    public function getTotalPermissions($where) {
        return $this->db->table('rbac_permission')->where($where)->limit(1)->count(1);
    }

    // ************************************* 角色权限 ************************************
    // ----------------------- 写 -----------------------
    /**
     * 添加角色权限
     * @param $data
     * @return mixed
     */
    public function addRolePermission($data) {
        return $this->db->table('rbac_role_permission')->insert($data);
    }

    /**
     * 删除角色权限
     * @param $where
     * @return mixed
     */
    public function deleteRolePermission($where) {
        return $this->db->table('rbac_role_permission')->where($where)->delete();
    }

    // ----------------------- 读 -----------------------
    /**
     * 获取角色权限列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getRolePermissionList($where = array(), $order = '', $limit = '') {
        return $this->db->table('rbac_role_permission')->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 统计角色权限信息
     * @param $where
     * @return mixed
     */
    public function getTotalRolePermissions($where) {
        return $this->db->table('rbac_role_permission')->where($where)->limit(1)->count(1);
    }
}
