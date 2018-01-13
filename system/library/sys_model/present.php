<?php
namespace Sys_Model;

class Present {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加充值优惠
     * @param $data
     * @return mixed
     */
    public function addPresent($data) {
        return $this->db->table('present_recharge')->insert($data);
    }

    /**
     * 更新充值优惠
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updatePresent($where, $data) {
        return $this->db->table('present_recharge')->where($where)->update($data);
    }

    /**
     * 删除充值优惠
     * @param $where
     * @return mixed
     */
    public function deletePresent($where) {
        return $this->db->table('present_recharge')->where($where)->delete();
    }

    /**
     * 获取充值优惠信息
     * @param    [type]     $where [description]
     * @return   [type]            [description]
     */
    public function getPresentInfo($where) {
        return $this->db->table('present_recharge')->where($where)->limit(1)->find();
    }

    


}
