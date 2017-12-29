<?php
namespace Sys_Model;
class Admin {
    public function __construct($registry) {
        $this->db = $registry->get('db');
    }

    // --------------------------------------------------- 写 ---------------------------------------------------
    /**
     * 添加管理员
     * @param $data
     * @return mixed
     */
    public function addAdmin($data) {
        return $this->db->table('admin')->insert($data);
    }

    /**
     * 更新管理员信息
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateAdmin($where, $data) {
        return $this->db->table('admin')->where($where)->update($data);
    }

    /**
     * 删除管理员
     * @param $where
     * @return mixed
     */
    public function deleteAdmin($where) {
        return $this->db->table('admin')->where($where)->delete();
    }

    // --------------------------------------------------- 读 ---------------------------------------------------
    /**
     * 获取管理员信息
     * @param $where
     * @return mixed
     */
    public function getAdminInfo($where) {
        return $this->db->table('admin')->where($where)->find();
    }

    /**
     * 根据管理员ID获取管理员信息
     * @param $admin_id
     * @return mixed
     */
    public function getAdminInfoById($admin_id) {
        return $this->getAdminInfo(array('admin_id' => $admin_id));
    }

    /**
     * 获取管理员账号列表
     * @param $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getAdminList($where, $order = '', $limit = '', $field = 'admin.*', $join = array()) {
        $table = 'admin as admin';
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

    /**
     * 统计管理员信息
     * @param $where
     * @return mixed
     */
    public function getTotalAdmins($where, $join = array()) {
        $table = 'admin as admin';
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

    // --------------------------------------------------- 其他 ---------------------------------------------------
    /**
     * 检验 管理员名称格式
     * @param $username
     * @return bool
     */
    public function checkAdminNameFormat($username) {
        return preg_match("/^[0-9a-zA-Z]{6,14}$/", $username) ? true : false;
    }

    /**
     * 检验 会员密码长度
     * @param $password
     * @return bool
     */
    public function checkPasswordFormat($password) {
        return preg_match("/^[0-9a-zA-Z]{6,20}$/", $password) ? true : false;
    }

    /**
     * 检验 会员密码
     * @param $password
     * @param $data
     * @return bool
     */
    public function checkPassword($password, $data) {
        return sha1($data['salt'] . sha1($data['salt'] . sha1($password))) == $data['password'] ? true : false;
    }


}