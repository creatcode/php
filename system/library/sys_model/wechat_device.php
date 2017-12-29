<?php
namespace Sys_Model;

class Wechat_Device {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ---------------------------------------------- 写 ----------------------------------------------
    /**
     * 添加微信设备
     * @param $data
     * @return mixed
     */
    public function addWechatDevice($data) {
        return $this->db->table('wechat_device')->insert($data);
    }

    /**
     * 更新微信设备
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateWechatDevice($where, $data) {
        return $this->db->table('wechat_device')->where($where)->update($data);
    }

    /**
     * 删除微信设备
     * @param $where
     * @return mixed
     */
    public function deleteWechatDevice($where) {
        return $this->db->table('wechat_device')->where($where)->delete();
    }

    // ---------------------------------------------- 读 ----------------------------------------------

    /**
     * 获取微信设备列表
     * @param $where
     * @param string $order
     * @param string $limit
     * @param string $field
     * @param array $join
     * @return mixed
     */
    public function getWechatDeviceList($where, $order = '', $limit = '', $field = 'wechat_device.*', $join = array()) {
        $table = 'wechat_device as wechat_device';
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
     * 获取微信设备信息
     * @param $where
     * @return mixed
     */
    public function getWechatDeviceInfo($where) {
        return $this->db->table('wechat_device')->where($where)->limit(1)->find();
    }

    /**
     * 统计微信设备信息
     * @param $where
     * @param $join
     * @return mixed
     */
    public function getTotalWechatDevice($where = '', $join = array()) {
        $table = 'wechat_device as wechat_device';
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
}
