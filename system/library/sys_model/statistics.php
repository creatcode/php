<?php
/**
 * Created by PhpStorm.
 * User: Luojb
 * Date: 2017/7/31
 * Time: 8:24
 */

namespace Sys_Model;


class Statistics
{
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->CURDATE = date('Y-m-d');
    }

    /**
     * 注册用户数统计
     * @param int $cooperator_id
     * @param int $days
     * @return array
     */
    public function getRegisterSum($cooperator_id = 0, $days = 7) {
        $total = $this->db->table('total_statistics')
            ->where(array('cooperator_id'=>$cooperator_id))
            ->field(array('users'=>'total'))
            ->find();
        $list = $this->db->table('daily_statistics')
            ->where("`cooperator_id`=" . ($cooperator_id+0) . " AND `stat_date`>DATE_SUB('" . $this->CURDATE . "', INTERVAL 7 DAY)")
            ->field(array('users'=>'count', 'stat_date'=> 'days'))
            ->select();
        return array('total'=>$total['total'],'list'=>$list);
    }

    /**
     * 单车数统计
     * @param int $cooperator_id
     * @param int $days
     * @return array
     */
    public function getBicycleSum($cooperator_id = 0, $days = 7) {
        $total = $this->db->table('total_statistics')
            ->where(array('cooperator_id'=>$cooperator_id))
            ->field(array('bikes_has_locations'=>'total'))
            ->find();
        $list = $this->db->table('daily_statistics')
            ->where("`cooperator_id`=" . ($cooperator_id+0) . " AND `stat_date`>DATE_SUB('" . $this->CURDATE . "', INTERVAL 7 DAY)")
            ->field(array('bikes_has_locations'=>'count', 'stat_date'=> 'days'))
            ->select();
        return array('total'=>$total['total'],'list'=>$list);
    }

    /**
     * 使用中单车统计
     * @param int $cooperator_id
     * @param int $days
     * @return array
     */
    public function getUsedBicycleSum($cooperator_id = 0, $days = 7) {
        $total = $this->db->table('total_statistics')
            ->where(array('cooperator_id'=>$cooperator_id))
            ->field(array('uses'=>'total'))
            ->find();
        $list = $this->db->table('daily_statistics')
            ->where("`cooperator_id`=" . ($cooperator_id+0) . " AND `stat_date`>DATE_SUB('" . $this->CURDATE . "', INTERVAL 7 DAY)")
            ->field(array('uses'=>'count', 'stat_date'=> 'days'))
            ->select();
        return array('total'=>$total['total'],'list'=>$list);
    }

    /**
     * 故障单车统计
     * @param int $cooperator_id
     * @param int $days
     * @return array
     */
    public function getFaultBicycleSum($cooperator_id = 0, $days = 7) {
        $total = $this->db->table('total_statistics')
            ->where(array('cooperator_id'=>$cooperator_id))
            ->field(array('faults'=>'total'))
            ->find();
        $list = $this->db->table('daily_statistics')
            ->where("`cooperator_id`=" . ($cooperator_id+0) . " AND `stat_date`>DATE_SUB('" . $this->CURDATE . "', INTERVAL 7 DAY)")
            ->field(array('faults'=>'count', 'stat_date'=> 'days'))
            ->select();
        return array('total'=>$total['total'],'list'=>$list);
    }

    /**
     * 现金收入统计
     * @param int $cooperator_id
     * @param int $days
     * @return array
     */
    public function getRechargeSum($cooperator_id = 0, $days = 7) {
        $total = $this->db->table('total_statistics')
            ->where(array('cooperator_id'=>$cooperator_id))
            ->field(array('order_amount'=>'orderTotal', 'top_ups_amount' => 'rechargeTotal'))
            ->find();
        $today = $this->db->table('daily_statistics')
            ->where("`cooperator_id`=" . ($cooperator_id+0) . " AND `stat_date`='" . $this->CURDATE . "'")
            ->field(array('order_amount'=>'orderToday', 'top_ups_amount' => 'rechargeToday'))
            ->find();
        return array('orderTotal'=>$total['orderTotal'], 'orderToday'=>$today['orderToday'],
            'rechargeTotal'=>$total['rechargeTotal'], 'rechargeToday'=>$today['rechargeToday']);
    }

    /**
     * 押金统计
     * @param int $cooperator_id
     * @param int $days
     * @return array
     */
    public function getDepositSum($cooperator_id = 0, $days = 7) {
        $total = $this->db->table('total_statistics')
            ->where(array('cooperator_id'=>$cooperator_id))
            ->field(array('deposit_amount'=>'rechargeTotal', 'deposit_return_amount' => 'refundTotal'))
            ->find();
        $today = $this->db->table('daily_statistics')
            ->where("`cooperator_id`=" . ($cooperator_id+0) . " AND `stat_date`='" . $this->CURDATE . "'")
            ->field(array('deposit_amount'=>'rechargeToday', 'deposit_return_amount' => 'refundToday'))
            ->find();
        return array('rechargeTotal'=>$total['rechargeTotal'], 'rechargeToday'=>$today['rechargeToday'],
            'refundTotal'=>$total['refundTotal'], 'refundToday'=>$today['refundToday']);
    }

    /**
     * 优惠券统计
     * @param int $cooperator_id
     * @param int $days
     * @return array
     */
    public function getCouponSum($cooperator_id = 0, $days = 7) {
        $total = $this->db->table('total_statistics')
            ->where(array('cooperator_id'=>$cooperator_id))
            ->field(array('coupon_used'=>'countUsedTotal', 'coupon_issued' => 'countAddTotal'))
            ->find();
        $today = $this->db->table('daily_statistics')
            ->where("`cooperator_id`=" . ($cooperator_id+0) . " AND `stat_date`='" . $this->CURDATE . "'")
            ->field(array('coupon_used'=>'countUsedToday', 'coupon_issued' => 'countAddToday'))
            ->find();
        return array('countUsedTotal'=>$total['countUsedTotal'], 'countAddToday'=>$today['countAddToday'],
            'countAddTotal'=>$total['countAddTotal'], 'countUsedToday'=>$today['countUsedToday']);
    }


    /**
     * 获取每页页头的统计数据（总数：bikes_has_locations / bikes台 使用中：uses台 故障：faults台）
     * @param int $cooperator_id
     * @return mixed
     */
    public function getSumForEachPage($cooperator_id = 0) {
        $total = $this->db->table('total_statistics')
            ->where(array('cooperator_id'=>$cooperator_id))
            ->field(array('bikes','bikes_has_locations', 'uses','faults'))
            ->find();
        return $total;
    }
}
