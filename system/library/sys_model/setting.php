<?php
namespace Sys_Model;

class Setting {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加系统参数
     * @param $data
     * @return mixed
     */
    public function addSetting($data) {
        return $this->db->table('setting')->insert($data);
    }

    /**
     * 更新系统参数
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateSetting($where, $data) {
        return $this->db->table('setting')->where($where)->update($data);
    }

    /**
     * 删除系统参数
     * @param $where
     * @return mixed
     */
    public function deleteSetting($where) {
        return $this->db->table('setting')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取系统参数列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getSettingList($where = array(), $order = '', $limit = '') {
        return $this->db->table('setting')->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取系统参数信息
     * @param $where
     * @return mixed
     */
    public function getSettingInfo($where) {
        return $this->db->table('setting')->where($where)->limit(1)->find();
    }

    /**
     * 统计系统参数信息
     * @param $where
     * @return mixed
     */
    public function getTotalSettings($where) {
        return $this->db->table('setting')->where($where)->limit(1)->count(1);
    }

}
