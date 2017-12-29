<?php
namespace Sys_Model;
/**
 * 统计首页
 * User: 温海滔
 * Date: 2017/3/2
 * Time: 10:14
 */
class Data_sum {
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->CURDATE = date('Y-m-d');
    }

    //用户注册统计
    public function getRegisterSum($condition = array(), $day = 7) {
        $where = $totalWhere = '';
        if (!empty($condition)) {
            $where = ' and ' . substr(_dealWhere($condition), 6);
            $totalWhere = substr(_dealWhere($condition), 6);
        }
        $total_arr = $this->db->table('user')->where($totalWhere)->field('count(user_id) as total')->find();

        $sql = 'select count(user_id) user_count, FROM_UNIXTIME(add_time, \'%Y-%m-%d\') days from rich_user where FROM_UNIXTIME(add_time, \'%Y-%m-%d\')>DATE_SUB(\'' . $this->CURDATE . '\', INTERVAL '.$day.' DAY) '. $where .' group by days';

        $result = $this->db->getRows($sql);

        return array('total'=>$total_arr['total'],'list'=>$this->matchingDate($result, $day));
    }

    //单车统计
    public function getBicycleSum($condition = array(), $day = 7) {
        $total_arr = $this->db->getRow("SELECT count(bicycle.bicycle_id) as total FROM rich_bicycle as bicycle LEFT JOIN rich_lock as `lock` ON bicycle.lock_sn = lock.lock_sn WHERE `lock`.lat <> '' AND `lock`.lng <> ''");

        $where = '';
        if (!empty($condition)) {
            $where = ' and ' . substr(_dealWhere($condition), 6);
        }

        $sql = 'select count(bicycle_id) bicycle_count, FROM_UNIXTIME(add_time, \'%Y-%m-%d\') days from rich_bicycle where FROM_UNIXTIME(add_time, \'%Y-%m-%d\')>DATE_SUB(\'' . $this->CURDATE . '\', INTERVAL '.$day.' DAY) '. $where .' group by days';

        $result = $this->db->getRows($sql);

        return array('total'=>$total_arr['total'],'list'=>$this->matchingDate($result,$day));
    }

    //使用统计
    public function getUsedBicycleSum($condition = array(), $day = 7) {
        $conditions = array(
            'order_state' => 1
        );
        $conditions = array_merge($conditions, $condition);
        $total_arr = $this->db->table('orders')->where($conditions)->field('count(order_id) as total')->find();

        $where = '';
        if (!empty($condition)) {
            $where = ' and ' . substr(_dealWhere($condition), 6);
        }

        $result = $this->db->getRows('select count(order_id) order_count, FROM_UNIXTIME(add_time, \'%Y-%m-%d\') days from rich_orders where order_state=2 and FROM_UNIXTIME(add_time, \'%Y-%m-%d\')>DATE_SUB(\'' . $this->CURDATE . '\', INTERVAL '.$day.' DAY) '. $where .' group by days');

        return array('total'=>$total_arr['total'],'list'=>$this->matchingDate($result,$day));
    }

    //故障统计
    public function getFaultBicycleSum($condition = array(), $day = 7) {
//        $total_arr = $this->db->table('fault')->where('processed = \'0\'')->field('COUNT(DISTINCT bicycle_sn) as total')->find();

        $total_arr = $this->db->getRow("SELECT COUNT(DISTINCT f.`bicycle_sn`) as `total` FROM `rich_fault` f"
            ." LEFT JOIN `rich_lock` l ON l.`lock_sn` = f.`lock_sn`"
            ." LEFT JOIN  `rich_illegal_parking` ip ON ip.`bicycle_sn` = f.`bicycle_sn`"
            ." WHERE f.`processed` = '0' OR abs(l.`battery`) < '15' OR ip.`processed` = '0'");

        $where = '';
        if (!empty($condition)) {
            $where = ' and ' . substr(_dealWhere($condition), 6);
        }

        $result = $this->db->getRows('select count(distinct bicycle_sn) fault_count, FROM_UNIXTIME(add_time, \'%Y-%m-%d\') days from rich_fault where FROM_UNIXTIME(add_time, \'%Y-%m-%d\')>DATE_SUB(\'' . $this->CURDATE . '\', INTERVAL '.$day.' DAY) AND `processed` = \'0\' '. $where .' group by days');

        return array('total'=>$total_arr['total'],'list'=>$this->matchingDate($result, $day));
    }

    //现金收入统计
    public function getRechargeSum() {
        //订单总收入
        $orderTotal = $this->db->table('orders')->where(array('order_state' => 2))->field('sum(pay_amount) as total')->find();
        //今日订单收入
        $orderToday = $this->db->getRow('SELECT SUM(`pay_amount`) `total`,\'' . $this->CURDATE . '\' `date` FROM `rich_orders` WHERE `order_state` = "2" AND FROM_UNIXTIME(`settlement_time`, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\'');
        //今日余额充值
        $rechargeToday = $this->db->getRow('SELECT SUM(`pdr_amount`) `total`,\'' . $this->CURDATE . '\' `date` FROM `rich_deposit_recharge` WHERE find_in_set(`pdr_payment_state`, \'1,-1,-2\') AND `pdr_type` = "0" AND FROM_UNIXTIME(`pdr_payment_time`, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\'');
        //总余额充值
        $rechargeTotal = $this->db->getRow('SELECT SUM(`pdr_amount`) `total` FROM `rich_deposit_recharge` WHERE find_in_set(`pdr_payment_state`, \'1,-1,-2\') AND `pdr_type` = "0"');

        return array('orderTotal'=>$orderTotal, 'orderToday'=>$orderToday, 'rechargeToday'=>$rechargeToday, 'rechargeTotal'=>$rechargeTotal);
    }

    //押金统计
    public function getDepositSum() {
        //押金充值总额
        $rechargeTotal = $this->db->table('deposit_recharge')->where(array('pdr_payment_state' => array('in', array(1,-1,-2)),'pdr_type'=>1))->field('sum(pdr_amount) as total')->find();
        //今日押金充值
        $rechargeToday = $this->db->getRow('SELECT SUM(`pdr_amount`) `total`,\'' . $this->CURDATE . '\' `date` FROM `rich_deposit_recharge` WHERE find_in_set(`pdr_payment_state`, \'1,-1,-2\') AND `pdr_type` ="1" AND FROM_UNIXTIME(`pdr_payment_time`, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\'');
        //今日押金退款
        $refundToday = $this->db->getRow('SELECT SUM(`pdc_amount`) `total`,\'' . $this->CURDATE . '\' `date` FROM `rich_deposit_cash` WHERE `pdc_payment_state`="1" AND pdc_type="1" AND FROM_UNIXTIME(`pdc_payment_time`, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\'');
        //押金退款总额
        $refundTotal = $this->db->getRow('SELECT SUM(`pdc_amount`) `total` FROM `rich_deposit_cash` WHERE `pdc_payment_state`="1" AND pdc_type="1"');

        return array('rechargeTotal'=>$rechargeTotal, 'rechargeToday'=>$rechargeToday, 'refundToday'=>$refundToday, 'refundTotal'=>$refundTotal);
    }

    //优惠券统计(按区域)
    public function getCouponSumandr($arr = array()) {
        //今日已使用
        $countUsedToday = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon` c '.
            'LEFT JOIN rich_user u ON u.user_id = c.user_id'.
            ' WHERE c.used = "1" AND FROM_UNIXTIME(c.used_time, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\' AND u.region_id ='.$arr["region_id"]);

        //全部已使用
        $countUsedTotal = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon` c '.
            'LEFT JOIN rich_user u ON u.user_id = c.user_id '.
            ' WHERE c.used = "1" AND u.region_id = '.$arr["region_id"]);

        //今日已经发放
        $countAddToday = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon` c '.
            'LEFT JOIN rich_user u ON u.user_id = c.user_id'.
            ' WHERE FROM_UNIXTIME(c.add_time, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\' AND u.region_id = '.$arr["region_id"]);

        //全部已发放
        $countAddTotal = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon` c '.
            'LEFT JOIN rich_user u ON u.user_id = c.user_id WHERE  u.region_id = '.$arr["region_id"]);

        //全部已失效
        $countFailTotal = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`, \'' . $this->CURDATE . '\' `date` FROM `rich_coupon` c '.
            'LEFT JOIN rich_user u ON u.user_id = c.user_id'.
            ' WHERE used = 0 AND `failure_time` <= UNIX_TIMESTAMP() AND u.region_id = '.$arr["region_id"]);

        return array('countUsedToday'=>$countUsedToday,'countUsedTotal'=>$countUsedTotal,'countAddToday'=>$countAddToday,'countAddTotal'=>$countAddTotal,'countFailTotal'=>$countFailTotal);
    }

    //优惠券统计(按合伙人)
    public function getCouponSumandw($arr = array()) {
        //今日已使用
        $countUsedToday = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon` c '.
            'LEFT JOIN rich_user u ON u.user_id = c.user_id'.
            ' WHERE c.used = "1" AND FROM_UNIXTIME(c.used_time, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\' AND u.cooperator_id ='.$arr["cooperator_id"]);

        //全部已使用
        $countUsedTotal = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon` c '.
            'LEFT JOIN rich_user u ON u.user_id = c.user_id '.
            ' WHERE c.used = "1" AND u.cooperator_id = '.$arr["cooperator_id"]);

        //今日已经发放
        $countAddToday = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon` c '.
            'LEFT JOIN rich_user u ON u.user_id = c.user_id '.
            ' WHERE FROM_UNIXTIME(c.add_time, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\' AND u.cooperator_id = '.$arr["cooperator_id"]);

        //全部已发放
        $countAddTotal = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon` c '.
            'LEFT JOIN `rich_user` u ON u.user_id = c.user_id WHERE  u.cooperator_id = '.$arr["cooperator_id"]);

        //全部已失效
        $countFailTotal = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`, \'' . $this->CURDATE . '\' `date` FROM `rich_coupon` c '.
            'LEFT JOIN `rich_user` u ON u.user_id = c.user_id '.
            ' WHERE used = 0 AND `failure_time` <= UNIX_TIMESTAMP() AND u.cooperator_id = '.$arr["cooperator_id"]);

        return array('countUsedToday'=>$countUsedToday,'countUsedTotal'=>$countUsedTotal,'countAddToday'=>$countAddToday,'countAddTotal'=>$countAddTotal,'countFailTotal'=>$countFailTotal);
    }


    //优惠券统计
    public function getCouponSum() {
        //今日已使用
        $countUsedToday = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon` WHERE `used` = "1" AND FROM_UNIXTIME(`used_time`, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\'');

        //全部已使用
        $countUsedTotal = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon` WHERE `used` = "1"');

        //今日已经发放
        $countAddToday = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon` WHERE FROM_UNIXTIME(`add_time`, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\'');

        //全部已发放
        $countAddTotal = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon`');
        
        //全部已失效
        $countFailTotal = $this->db->getRow('SELECT COUNT(`coupon_id`) `count`, \'' . $this->CURDATE . '\' `date` FROM `rich_coupon` WHERE used = 0 AND `failure_time` <= UNIX_TIMESTAMP()');

        return array('countUsedToday'=>$countUsedToday,'countUsedTotal'=>$countUsedTotal,'countAddToday'=>$countAddToday,'countAddTotal'=>$countAddTotal,'countFailTotal'=>$countFailTotal);
    }


    //优惠券统计 按天  (合伙人和区域统计)
    public function getCouponSumByDayAndr($where1 = 1, $where2 = 1, $region_id) {
        //按日期统计优惠券使用数量
        $sql = "SELECT COUNT(`coupon_id`) AS `used_count`, FROM_UNIXTIME(`used_time`, '%Y-%m-%d') AS `date` FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            " WHERE u.region_id = ".$region_id." AND {$where1} GROUP BY `date`";
        $usedCount = $this->db->getRows($sql);

        //按日期统计优惠券发放数量
        $sql = "SELECT COUNT(`coupon_id`) `total_count`,FROM_UNIXTIME(c.add_time, '%Y-%m-%d') `date` FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            " WHERE u.region_id = ".$region_id." AND {$where2} GROUP BY `date`";
        $totalCount = $this->db->getRows($sql);

        return array('usedCount' => $usedCount, 'totalCount' => $totalCount);
    }

    //优惠券统计 按天  (合伙人统计)
    public function getCouponSumByDayAndw($where1 = 1, $where2 = 1, $cooperator_id) {
        //按日期统计优惠券使用数量
        $sql = "SELECT COUNT(`coupon_id`) AS `used_count`, FROM_UNIXTIME(c.used_time, '%Y-%m-%d') AS `date` FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            " WHERE u.cooperator_id = ".$cooperator_id." AND {$where1} GROUP BY `date`";
        $usedCount = $this->db->getRows($sql);
        //按日期统计优惠券发放数量
        $sql = "SELECT COUNT(`coupon_id`) `total_count`,FROM_UNIXTIME(c.add_time, '%Y-%m-%d') `date` FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            " WHERE u.cooperator_id = ".$cooperator_id." AND {$where2} GROUP BY `date`";
        $totalCount = $this->db->getRows($sql);

        return array('usedCount' => $usedCount, 'totalCount' => $totalCount);
    }

    //优惠券统计 按天
    public function getCouponSumByDay($where1 = 1, $where2 = 1) {
        //按日期统计优惠券使用数量
        $sql = "SELECT COUNT(`coupon_id`) AS `used_count`, FROM_UNIXTIME(`used_time`, '%Y-%m-%d') AS `date` FROM `rich_coupon` WHERE 1 AND {$where1} GROUP BY `date`";
        $usedCount = $this->db->getRows($sql);

        //按日期统计优惠券发放数量
        $sql = "SELECT COUNT(`coupon_id`) `total_count`,FROM_UNIXTIME(`add_time`, '%Y-%m-%d') `date` FROM `rich_coupon` WHERE 1 AND {$where2} GROUP BY `date`";
        $totalCount = $this->db->getRows($sql);

        return array('usedCount' => $usedCount, 'totalCount' => $totalCount);
    }

    //优惠券统计 按优惠券类型(按区域)
    public function getCouponSumByTypeAndr($where1, $where2, $where3, $region_id) {
        $sql = "SELECT COUNT(`coupon_id`) `total_count`, SUM(IF(used=1,1,0)) AS `used_count`, SUM(IF(used=0 AND failure_time<=UNIX_TIMESTAMP(),1,0)) AS `fail_count`, SUM(number) AS number_sum, coupon_type FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            "WHERE u.region_id= ". $region_id ." AND {$where2} GROUP BY `coupon_type`";
        $total_count = $this->db->getRows($sql);
        // 发放
        $arr['total_count'] = $total_count;
        $arr['number_sum'] = $total_count;

        $sql = "SELECT COUNT(`coupon_id`) `total_count`, SUM(IF(used=1,1,0)) AS `used_count`, SUM(IF(used=0 AND failure_time<=UNIX_TIMESTAMP(),1,0)) AS `fail_count`, SUM(number) AS number_sum, coupon_type FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            "WHERE u.region_id= ". $region_id ." AND {$where1} GROUP BY `coupon_type`";
        $used_count = $this->db->getRows($sql);
        $arr['used_count'] = $used_count;

        $sql = "SELECT COUNT(`coupon_id`) `total_count`, SUM(IF(used=1,1,0)) AS `used_count`, SUM(IF(used=0 AND failure_time<=UNIX_TIMESTAMP(),1,0)) AS `fail_count`, SUM(number) AS number_sum, coupon_type FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            "WHERE u.region_id= ". $region_id ." AND {$where3} GROUP BY `coupon_type`";
        $fail_count = $this->db->getRows($sql);
        $arr['fail_count'] = $fail_count;

        return $arr;
    }

    //优惠券统计 按优惠券类型(按合伙人)
    public function getCouponSumByTypeAndw($where1, $where2, $where3, $cooperator_id) {
        $sql = "SELECT COUNT(`coupon_id`) `total_count`, SUM(IF(c.used=1,1,0)) AS `used_count`, SUM(IF(c.used=0 AND c.failure_time<=UNIX_TIMESTAMP(),1,0)) AS `fail_count`, SUM(c.number) AS number_sum, coupon_type FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            "WHERE u.cooperator_id= ". $cooperator_id ."  AND {$where2} GROUP BY `coupon_type`";
        $total_count = $this->db->getRows($sql);
        // 发放
        $arr['total_count'] = $total_count;
        $arr['number_sum'] = $total_count;

        $sql = "SELECT COUNT(`coupon_id`) `total_count`, SUM(IF(c.used=1,1,0)) AS `used_count`, SUM(IF(c.used=0 AND c.failure_time<=UNIX_TIMESTAMP(),1,0)) AS `fail_count`, SUM(c.number) AS number_sum, coupon_type FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            "WHERE u.cooperator_id= ". $cooperator_id ."  AND {$where1} GROUP BY `coupon_type`";
        $used_count = $this->db->getRows($sql);
        $arr['used_count'] = $used_count;

        $sql = "SELECT COUNT(`coupon_id`) `total_count`, SUM(IF(c.used=1,1,0)) AS `used_count`, SUM(IF(c.used=0 AND c.failure_time<=UNIX_TIMESTAMP(),1,0)) AS `fail_count`, SUM(c.number) AS number_sum, coupon_type FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            "WHERE u.cooperator_id= ". $cooperator_id ."  AND {$where3} GROUP BY `coupon_type`";
        $fail_count = $this->db->getRows($sql);
        $arr['fail_count'] = $fail_count;

        return $arr;
    }

    //优惠券统计 按优惠券类型
    public function getCouponSumByType($where1, $where2, $where3) {
        $sql = "SELECT COUNT(`coupon_id`) `total_count`, SUM(number) AS number_sum, coupon_type FROM `rich_coupon` WHERE 1 AND {$where2} GROUP BY `coupon_type`";
        $total_count = $this->db->getRows($sql);
        // 发放
        $arr['total_count'] = $total_count;
        $arr['number_sum'] = $total_count;
        //使用的
        //used_count
        $sql = "SELECT  SUM(IF(used=1,1,0)) AS `used_count`, SUM(number) AS number_sum, coupon_type FROM `rich_coupon` WHERE 1 AND {$where1} GROUP BY `coupon_type`";
        $used_count = $this->db->getRows($sql);
        $arr['used_count'] = $used_count;

        //失效
        //fail_count
        $sql = "SELECT SUM(IF(used=0 AND failure_time<=UNIX_TIMESTAMP(),1,0)) AS `fail_count`, SUM(number) AS number_sum, coupon_type FROM `rich_coupon` WHERE 1 AND {$where3} GROUP BY `coupon_type`";
        $fail_count = $this->db->getRows($sql);
        $arr['fail_count'] = $fail_count;

        //值
        //number_sum
        return $arr;
    }

    //优惠券统计 按优惠券来源(按区域)
    public function getCouponSumByObtainAndr($where, $region_id) {
        $sql = "SELECT COUNT(c.coupon_id) `total_count`, SUM(IF(c.used=1,1,0)) AS used_count, SUM(IF(c.used=0 AND c.failure_time<=UNIX_TIMESTAMP(),1,0)) AS fail_count, SUM(c.number) AS number_sum, c.obtain FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            " WHERE u.region_id= ". $region_id ." AND {$where} GROUP BY `obtain`";
        $result['total_count'] = $this->db->getRows($sql);

        $sql = "SELECT COUNT(c.coupon_id) `total_count`, SUM(IF(c.used=1,1,0)) AS used_count, SUM(IF(c.used=0 AND c.failure_time<=UNIX_TIMESTAMP(),1,0)) AS fail_count, SUM(c.number) AS number_sum, c.obtain FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            " WHERE u.region_id= ". $region_id ." AND {$where} GROUP BY `obtain`";
        $result['used_count'] = $this->db->getRows($sql);

        $sql = "SELECT COUNT(c.coupon_id) `total_count`, SUM(IF(c.used=1,1,0)) AS used_count, SUM(IF(c.used=0 AND c.failure_time<=UNIX_TIMESTAMP(),1,0)) AS fail_count, SUM(c.number) AS number_sum, c.obtain FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            " WHERE u.region_id= ". $region_id ." AND {$where} GROUP BY `obtain`";
        $result['fail_count'] = $this->db->getRows($sql);

        return $result;
    }

    //优惠券统计 按优惠券来源(按合伙人)
    public function getCouponSumByObtainAndw($where1, $where2, $where3, $cooperator_id) {

        $sql = "SELECT COUNT(c.coupon_id) `total_count`, SUM(IF(c.used=1,1,0)) AS used_count, SUM(IF(c.used=0 AND c.failure_time<=UNIX_TIMESTAMP(),1,0)) AS fail_count, SUM(c.number) AS number_sum, c.obtain FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            " WHERE u.cooperator_id= ". $cooperator_id ."  AND {$where2} GROUP BY `obtain`";

        $result['total_count'] = $this->db->getRows($sql);

        $sql = "SELECT COUNT(c.coupon_id) `total_count`, SUM(IF(c.used=1,1,0)) AS used_count, SUM(IF(c.used=0 AND c.failure_time<=UNIX_TIMESTAMP(),1,0)) AS fail_count, SUM(c.number) AS number_sum, c.obtain FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            " WHERE u.cooperator_id= ". $cooperator_id ."  AND {$where1} GROUP BY `obtain`";
        $result['used_count'] = $this->db->getRows($sql);


        $sql = "SELECT COUNT(c.coupon_id) `total_count`, SUM(IF(c.used=1,1,0)) AS used_count, SUM(IF(c.used=0 AND c.failure_time<=UNIX_TIMESTAMP(),1,0)) AS fail_count, SUM(c.number) AS number_sum, c.obtain FROM `rich_coupon` c ".
            " LEFT JOIN rich_user u ON u.user_id = c.user_id ".
            " WHERE u.cooperator_id= ". $cooperator_id ."  AND {$where3} GROUP BY `obtain`";
        $result['fail_count'] = $this->db->getRows($sql);

        return $result;
    }

    //优惠券统计 按优惠券来源
    public function getCouponSumByObtain($where1, $where2, $where3) {
        $sql = "SELECT COUNT(`coupon_id`) `total_count`,  SUM(number) AS number_sum, obtain FROM `rich_coupon` WHERE 1 AND {$where2} GROUP BY `obtain`";
        $result['total_count'] = $this->db->getRows($sql);

        $sql = "SELECT SUM(IF(used=1,1,0)) AS `used_count`, SUM(number) AS number_sum, obtain FROM `rich_coupon` WHERE 1 AND {$where1} GROUP BY `obtain`";
        $result['used_count'] = $this->db->getRows($sql);

        $sql = "SELECT SUM(IF(used=0 AND failure_time<=UNIX_TIMESTAMP(),1,0)) AS `fail_count`, SUM(number) AS number_sum, obtain FROM `rich_coupon` WHERE 1 AND {$where3} GROUP BY `obtain`";
        $result['fail_count'] = $this->db->getRows($sql);

        return $result;
    }

    /**
     * 按天统计余额/押金支付
     */
    public function getDepositSumForDays($where = '') {
        if (!empty($where)) {
            $where = ' WHERE ' . $where;
        }
        return $this->db->getRows('select sum(pdr_amount) as `total`, FROM_UNIXTIME(`pdr_payment_time`, \'%Y-%m-%d\') as `payment_date`,`pdr_type` FROM rich_deposit_recharge ' . $where . ' group by `payment_date`, `pdr_type`');
    }


    /**
     * 按天统计余额/押金支付(根据城市)
     */
    public function getDepositSumForDaysCity($where = '',$city_id) {
        if (!empty($where)) {
            $where = ' WHERE ' . $where;
        }
        return $this->db->getRows('select sum(r.pdr_amount) as `total`, FROM_UNIXTIME(`pdr_payment_time`, \'%Y-%m-%d\') as `payment_date`,`pdr_type` FROM rich_deposit_recharge r '.
            ' LEFT JOIN rich_user u ON u.user_id = r.pdr_user_id '.$where.' AND u.city_id = '.$city_id . ' group by `payment_date`, `pdr_type`');
    }

    /**
     * 按天统计余额/押金支付(根据合伙人)
     */
    public function getDepositSumForDaysCooperation($where = '',$cooperator_id) {
        if (!empty($where)) {
            $where = ' WHERE ' . $where;
        }
        return $this->db->getRows('select sum(r.pdr_amount) as `total`, FROM_UNIXTIME(`pdr_payment_time`, \'%Y-%m-%d\') as `payment_date`,`pdr_type` FROM rich_deposit_recharge r '.
            ' LEFT JOIN rich_user u ON u.user_id = r.pdr_user_id '.$where.' AND u.cooperator_id = '.$cooperator_id . ' group by `payment_date`, `pdr_type`');
    }


    /**
     * 按天统计消费金额
     */
    public function getOrderAmountForDays($where = '', $join = '',$city_id) {
        if (!empty($where)) {
            $where = ' WHERE ' . $where;
        }
        return $this->db->getRows('select sum(pay_amount) as total, FROM_UNIXTIME(`settlement_time`, \'%Y-%m-%d\') as `order_date` FROM rich_orders ' . $join . $where . ' group by order_date');
    }

    /**
     * 按天统计消费退回金额
     */
    public function getRefundOrderAmountForDays($where = '', $join = '') {
        if (!empty($where)) {
            $where = ' WHERE ' . $where;
        }
        return $this->db->getRows('select sum(apply_cash_amount) as total, FROM_UNIXTIME(`apply_audit_time`, \'%Y-%m-%d\') as `audit_time` FROM rich_orders_modify_apply ' . $join . $where . ' group by audit_time');
    }

    /**
     * 按天统计消费订单数
     */
    public function getOrderCountForDays($where = '', $join = '') {
        if (!empty($where)) {
            $where = ' WHERE ' . $where;
        }
        return $this->db->getRows('select count(order_id) as total, FROM_UNIXTIME(`settlement_time`, \'%Y-%m-%d\') as `order_date` FROM rich_orders ' . $join . $where . ' group by order_date');
    }

   

    /**
     * 按天统计提现
     */
    public function getCashSumForDays($where = '') {
        if (!empty($where)) {
            $where = ' WHERE ' . $where;
        }
        return $this->db->getRows('select sum(pdc_amount) as total, FROM_UNIXTIME(`pdc_payment_time`, \'%Y-%m-%d\') as `payment_date` FROM rich_deposit_cash ' . $where . ' group by payment_date');
    }


    /**
     * 按天统计提现(城市)
     */
    public function getCashSumForDaysCity($where = '', $city_id) {
        if (!empty($where)) {
            $where = ' WHERE ' . $where;
        }
        return $this->db->getRows('select sum(pdc_amount) as total, FROM_UNIXTIME(`pdc_payment_time`, \'%Y-%m-%d\') as `payment_date` FROM rich_deposit_cash d LEFT JOIN rich_user u ON  u.user_id = d.pdc_user_id ' . $where . ' AND u.city_id = '.$city_id.' group by payment_date');
    }

    /**
     * 按天统计提现(合伙人)
     */
    public function getCashSumForDaysCooperation($where = '', $cooperator_id) {
        if (!empty($where)) {
            $where = ' WHERE ' . $where;
        }
        return $this->db->getRows('select sum(pdc_amount) as total, FROM_UNIXTIME(`pdc_payment_time`, \'%Y-%m-%d\') as `payment_date` FROM rich_deposit_cash d LEFT JOIN rich_user u ON  u.user_id = d.pdc_user_id ' . $where . ' AND u.cooperator_id = '.$cooperator_id.' group by payment_date');
    }

    //用户注册统计(合伙人)
    public function getRegisterSumC($condition = array(), $day = 7) {
        $total_arr = $this->db->table('user')->where($condition)->field('count(user_id) as total')->find();

        $sql = 'select count(user_id) user_count, FROM_UNIXTIME(add_time, \'%Y-%m-%d\') days from rich_user where FROM_UNIXTIME(add_time, \'%Y-%m-%d\')>DATE_SUB(\'' . $this->CURDATE . '\', INTERVAL '.$day.' DAY) AND `cooperator_id` = '.$condition['cooperator_id'].' group by days';

        $result = $this->db->getRows($sql);

        return array('total'=>$total_arr['total'],'list'=>$this->matchingDate($result, $day));
    }

    //单车统计(合伙人)
    public function getBicycleSumC($condition = array(), $day = 7) {
        $total_arr = $this->db->getRow("SELECT count(bicycle.bicycle_id) as total FROM rich_bicycle as bicycle LEFT JOIN rich_lock as `lock` ON bicycle.lock_sn = lock.lock_sn WHERE `lock`.lat <> '' AND `lock`.lng <> '' AND `bicycle`.cooperator_id = ".$condition['cooperator_id']);

        $sql = 'select count(bicycle_id) bicycle_count, FROM_UNIXTIME(add_time, \'%Y-%m-%d\') days from rich_bicycle where FROM_UNIXTIME(add_time, \'%Y-%m-%d\')>DATE_SUB(\'' . $this->CURDATE . '\', INTERVAL '.$day.' DAY) AND cooperator_id = '.$condition['cooperator_id'].' group by days';

        $result = $this->db->getRows($sql);

        return array('total'=>$total_arr['total'],'list'=>$this->matchingDate($result,$day));
    }

    //使用统计(合伙人)
    public function getUsedBicycleSumC($condition = array(), $day = 7) {
        $conditions = array(
            'order_state' => 1
        );
        $conditions = array_merge($conditions, $condition);
        $total_arr = $this->db->table('orders')->where($conditions)->field('count(order_id) as total')->find();

        $result = $this->db->getRows('select count(order_id) order_count, FROM_UNIXTIME(add_time, \'%Y-%m-%d\') days from rich_orders where order_state=2 and FROM_UNIXTIME(add_time, \'%Y-%m-%d\')>DATE_SUB(\'' . $this->CURDATE . '\', INTERVAL '.$day.' DAY) AND `cooperator_id` = '.$condition['cooperator_id'].' group by days');

        return array('total'=>$total_arr['total'],'list'=>$this->matchingDate($result,$day));
    }

    //故障统计(合伙人)
    public function getFaultBicycleSumC($condition = array(), $day = 7) {
        $total_arr = $this->db->table('fault')->where('cooperator_id = '.$condition['cooperator_id'].' AND processed = \'0\'')->field('COUNT(DISTINCT bicycle_sn) as total')->find();

        $result = $this->db->getRows('select count(distinct bicycle_sn) fault_count, FROM_UNIXTIME(add_time, \'%Y-%m-%d\') days from rich_fault where FROM_UNIXTIME(add_time, \'%Y-%m-%d\')>DATE_SUB(\'' . $this->CURDATE . '\', INTERVAL '.$day.' DAY) AND `cooperator_id` = '.$condition['cooperator_id'].' AND `processed` = \'0\' group by days');

        return array('total'=>$total_arr['total'],'list'=>$this->matchingDate($result, $day));
    }


    //现金收入统计(合伙人)
    public function getRechargeSumC($condition = array()) {
        //订单总收入
        $orderTotal = $this->db->table('orders')->where(array('order_state' => 2, 'cooperator_id'=> $condition['cooperator_id']))->field('sum(pay_amount) as total')->find();
        //今日订单收入
        $orderToday = $this->db->getRow('SELECT SUM(`pay_amount`) `total`,\'' . $this->CURDATE . '\' `date` FROM `rich_orders` WHERE `order_state` = "2" AND FROM_UNIXTIME(`settlement_time`, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\' AND `cooperator_id` = '.$condition['cooperator_id']);
        //今日余额充值
        $rechargeToday = $this->db->getRow('SELECT SUM(dr.`pdr_amount`) `total`,\'' . $this->CURDATE . '\' `date` FROM `rich_deposit_recharge` dr LEFT JOIN `rich_user` u  ON u.`user_id`= dr.`pdr_user_id` WHERE find_in_set(dr.`pdr_payment_state`, \'1,-1,-2\') AND dr.`pdr_type` = "0" AND FROM_UNIXTIME(dr.`pdr_payment_time`, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\' AND u.`cooperator_id`='.$condition['cooperator_id']);
        //总余额充值
        $rechargeTotal = $this->db->getRow('SELECT SUM(dr.`pdr_amount`) `total` FROM `rich_deposit_recharge` dr LEFT JOIN `rich_user` u  ON u.`user_id`= dr.`pdr_user_id` WHERE find_in_set(dr.`pdr_payment_state`, \'1,-1,-2\') AND dr.`pdr_type` = "0"  AND u.`cooperator_id`='.$condition['cooperator_id']);

        return array('orderTotal'=>$orderTotal, 'orderToday'=>$orderToday, 'rechargeToday'=>$rechargeToday, 'rechargeTotal'=>$rechargeTotal);
    }

    //优惠券统计(合伙人)
    public function getCouponSumC($condition = array()) {
        //今日已使用
        $countUsedToday = $this->db->getRow('SELECT COUNT(c.`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon` c LEFT JOIN `rich_user` u  ON u.`user_id`= c.`user_id` WHERE c.`used` = "1" AND FROM_UNIXTIME(c.`used_time`, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\' AND u.`cooperator_id`='.$condition['cooperator_id']);

        //全部已使用
        $countUsedTotal = $this->db->getRow('SELECT COUNT(c.`coupon_id`) `count` FROM `rich_coupon` c LEFT JOIN `rich_user` u  ON u.`user_id`= c.`user_id` WHERE c.`used` = "1" AND u.`cooperator_id`='.$condition['cooperator_id']);

        //今日已经发放
        $countAddToday = $this->db->getRow('SELECT COUNT(c.`coupon_id`) `count`,\'' . $this->CURDATE . '\' `date` FROM `rich_coupon` c LEFT JOIN `rich_user` u  ON u.`user_id`= c.`user_id` WHERE FROM_UNIXTIME(c.`add_time`, \'%Y-%m-%d\')=\'' . $this->CURDATE . '\' AND u.`cooperator_id`='.$condition['cooperator_id']);

        //全部已发放
        $countAddTotal = $this->db->getRow('SELECT COUNT(c.`coupon_id`) `count` FROM `rich_coupon` c LEFT JOIN `rich_user` u  ON u.`user_id`= c.`user_id` WHERE u.`cooperator_id`='.$condition['cooperator_id']);

        return array('countUsedToday'=>$countUsedToday,'countUsedTotal'=>$countUsedTotal,'countAddToday'=>$countAddToday,'countAddTotal'=>$countAddTotal);
    }

    // --------------------------------------------------- 城市排行 ---------------------------------------------------
    /**
     * 总订单数排行
     */
    public function city_all_orders_ranking($where) {
        $where = $where ? ' AND ' . $where : '';
        $sql = 'SELECT cooperator_name, IFNULL(COUNT(order_id), 0) AS total FROM rich_cooperator LEFT JOIN rich_orders ON rich_orders.cooperator_id=rich_cooperator.cooperator_id AND order_state=\'2\''. $where .' GROUP BY rich_cooperator.cooperator_id ORDER BY total DESC';
        return $this->db->getRows($sql);
    }

    /**
     * 日均订单数排行
     */
    public function daily_orders_ranking($where) {
        $where = $where ? ' AND '.$where : '';
        $sql = 'SELECT cooperator_name, IFNULL(avg_orders, 0) AS avg_orders
FROM rich_cooperator
LEFT JOIN (
SELECT cooperator_id, AVG(total) AS avg_orders
FROM (
SELECT cooperator_id, COUNT(order_id) AS total, FROM_UNIXTIME(settlement_time, \'%Y-%m-%d\') AS date1
FROM rich_orders
WHERE rich_orders.order_state=2' . $where . '
GROUP BY cooperator_id, date1
) AS statistics
GROUP BY cooperator_id
) avg_statistics ON avg_statistics.cooperator_id = rich_cooperator.cooperator_id
ORDER BY avg_orders DESC';
        return $this->db->getRows($sql);
    }

    /**
     * 单车使用率排行
     */
    public function daily_usage_bicycle_ranking($where) {
        $where = $where ? ' AND '.$where : '';
        $sql = 'SELECT cooperator_name, IFNULL(avg_orders, 0) / IFNULL(bicycle_total, 1) AS daily_usage
FROM rich_cooperator
LEFT JOIN (
SELECT cooperator_id, AVG(total) AS avg_orders
FROM (
SELECT cooperator_id, COUNT(order_id) AS total, FROM_UNIXTIME(settlement_time, \'%Y-%m-%d\') AS date1
FROM rich_orders
WHERE rich_orders.order_state=2' . $where . '
GROUP BY cooperator_id, date1
) AS statistics
GROUP BY cooperator_id
) avg_statistics ON avg_statistics.cooperator_id = rich_cooperator.cooperator_id
LEFT JOIN (
SELECT cooperator_id, COUNT(bicycle_id) AS bicycle_total
FROM rich_bicycle
WHERE lock_sn <> \'\'
GROUP BY cooperator_id) AS bicycle_statistics ON bicycle_statistics.cooperator_id=rich_cooperator.cooperator_id
ORDER BY daily_usage DESC';
        return $this->db->getRows($sql);
    }

//    private function _dealData($total_arr, $result, $day) {
//        $items = array();
//        foreach ($result as $value) {
//            $items[$value['days']] = $value;
//        }
//
//        for ($j = $day; $j > 0; $j--) {
//            $day_index = date('Y-m-d', strtotime('-' . $day . ' day'));
//            if (!isset($day_index)) {
//                $items[$day_index] = array('user_count' => 0, 'days' => $day_index);
//            }
//        }
//
//        $result = array_values($items);
//        $arr = array('list' => $result);
//        return array_merge($total_arr, $arr);
//    }

    private function matchingDate($data, $days){
        $arr = array();
        for($i = $days-1; $i >= 0; $i--){
            foreach ($data as $v){
                if(strtotime($v['days']) == (strtotime(date('Y-m-d',time())) - $i * 86400)) {
                    $arr[$days-$i]['days'] = date('Y-m-d',(strtotime(date('Y-m-d',time())) - $i * 86400));
                    $arr[$days-$i]['count'] = array_values($v)[0];
                }
                if(empty($arr[$days-$i])){
                    $arr[$days-$i]['days'] = date('Y-m-d',(strtotime(date('Y-m-d',time())) - $i * 86400));
                    $arr[$days-$i]['count'] = 0;
                }
            }
            if(empty($data)){
                $arr[$days-$i]['days'] = 0;
                $arr[$days-$i]['count'] = 0;
            }
        }
        return $arr;
    }
}