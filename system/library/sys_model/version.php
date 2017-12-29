<?php
namespace Sys_Model;

class Version {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    /**
     * 添加版本
     * @param $data
     * @return mixed
     */
    public function addVersion($data) {
        return $this->db->table('version')->insert($data);
    }

    /**
     * 更新版本
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateVersion($where, $data) {
        return $this->db->table('version')->where($where)->update($data);
    }

    /**
     * 删除版本
     * @param $where
     * @return mixed
     */
    public function deleteVersion($where) {
        return $this->db->table('version')->where($where)->delete();
    }

    /**
     * 获取版本列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getVersionList($where = array(), $order = '', $limit = '') {
        return $this->db->table('version')->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取版本信息
     * @param $where
     * @return mixed
     */
    public function getVersionInfo($where) {
        return $this->db->table('version')->where($where)->limit(1)->find();
    }

    /**
     * 统计版本信息
     * @param $where
     * @return mixed
     */
    public function getTotalVersions($where) {
        return $this->db->table('version')->where($where)->limit(1)->count(1);
    }

    /**
     * 获取最新的版本信息
     * @param $where array
     * @return mixed
     */
    public function getLastestVersionInfo($where = array()) {
        $where = array_merge(array('state' => 1), $where);
        return $this->db->table('version')->where($where)->order('add_time DESC')->limit(1)->find();
    }
}
