<?php

namespace Sys_Model;

class Feedback_customer_service
{
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    /**
     * 新增
     * @param $data
     * @return mixed
     */
    public function addFeedbackCustomerService($data) {
        return $this->db->table('feedback_customer_service')->insert($data);
    }

    /**
     * 更新
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateFeedbackCustomerService($where, $data) {
        return $this->db->table('feedback_customer_service')->where($where)->update($data);
    }



    public function getTotalFeedbackCustomerService($where){

        return $this->db->table('feedback_customer_service')->where($where)->limit(1)->count(1);

    }



    public function getFeedbackCustomerServiceList($where, $order = '', $limit = '', $field = '*',$join=array()) {

        $table = 'feedback_customer_service as feedback_customer_service';
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


    public function deleteFeedbackCustomerService($where) {
        return $this->db->table('feedback_customer_service')->where($where)->delete();
    }





}