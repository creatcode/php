<?php
namespace Sys_Model;

class Feedback {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    /**
     * 添加反馈
     * @param $data
     * @return mixed
     */
    public function addFeedback($data) {
        return $this->db->table('feedback')->insert($data);
    }

    /**
     * 更新反馈
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateFeedback($where, $data) {
        return $this->db->table('feedback')->where($where)->update($data);
    }

    /**
     * 删除反馈
     * @param $where
     * @return mixed
     */
    public function deleteFeedback($where) {
        return $this->db->table('feedback')->where($where)->delete();
    }

    /**
     * 获取反馈列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getFeedbackList($where = array(), $order = '', $limit = '') {
        return $this->db->table('feedback')->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 获取反馈信息
     * @param $where
     * @return mixed
     */
    public function getFeedbackInfo($where) {
        return $this->db->table('feedback')->where($where)->limit(1)->find();
    }

    /**
     * 统计反馈信息
     * @param $where
     * @return mixed
     */
    public function getTotalFeedbacks($where) {
        return $this->db->table('feedback')->where($where)->limit(1)->count(1);
    }
    public function addNormalParking($data) {
        return $this->db->table('normal_parking')->insert($data);
    }
}
