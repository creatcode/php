<?php
namespace Sys_Model;

class Coupon {
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
    }

    // ------------------------------------------------- 写 -------------------------------------------------
    /**
     * 添加优惠券
     * @param $data
     * @return mixed
     */
    public function addCoupon($data) {
        return $this->db->table('coupon')->insert($data);
    }

    /**
     * 更新优惠券
     * @param $where
     * @param $data
     * @return mixed
     */
    public function updateCoupon($where, $data) {
        return $this->db->table('coupon')->where($where)->update($data);
    }

    /**
     * 删除优惠券
     * @param $where
     * @return mixed
     */
    public function deleteCoupon($where) {
        return $this->db->table('coupon')->where($where)->delete();
    }

    // ------------------------------------------------- 读 -------------------------------------------------
    /**
     * 获取优惠券列表
     * @param array $where
     * @param string $order
     * @param string $limit
     * @return mixed
     */
    public function getCouponList($where = array(), $order = '', $limit = '') {
        $field = 'c.*,u.mobile';
        $on = 'c.user_id=u.user_id';
        $field = 'c.*,u.mobile';
        return $this->db->table('coupon as c,user as u')->where($where)->field($field)->order($order)->limit($limit)->join('left')->on($on)->select();
    }

    public function getSimpleCouponList($where, $order = '', $limit = '') {
        return $this->db->table('coupon')->where($where)->order($order)->limit($limit)->select();
    }

    public function getCouponCount($where) {
        return $this->db->table('coupon')->where($where)->limit(1)->count(1);
    }

    /**
     * 获取优惠券信息
     * @param mixed $where
     * @param string $field
     * @return mixed
     */
    public function getCouponInfo($where, $field = '*') {
        return $this->db->table('coupon')->where($where)->field($field)->limit(1)->find();
    }

    /**
     * 统计优惠券信息
     * @param $where
     * @return mixed
     */
    public function getTotalCoupons($where) {
        return $this->db->table('coupon')->where($where)->limit(1)->count(1);
    }

    /**
     * 获取最合适的
     * @param $where
     * @param $price
     * @return mixed
     */
    public function getRightCoupon($where, $price = 0) {
        if (!isset($where['user_id'])) return array();
        $where = array_merge($where, array('used' => 0, 'failure_time' => array('gt', time())));
        //获取有效的
        $effect_coupon_list = $this->getSimpleCouponList($where, 'failure_time ASC');
        if (empty($effect_coupon_list)) return $effect_coupon_list;
        $coupon_info = array();
        $i = 0;
        foreach ($effect_coupon_list as $coupon) {
            if ($i == 0) {
                $coupon_info = $coupon;
            }
            $i++;
        }
        return $coupon_info;
    }

    /**
     * 处理更新优惠券
     * @param $coupon
     * @return mixed
     */
    public function dealCoupon($coupon) {
        $coupon_type = $coupon['coupon_type'];
        $data = array();
        switch ($coupon_type) {
            //按时间分钟数为单位
            case 1:
                $data['used'] = 1;
                break;
            //按次数
            case 2:
                $data['used'] = 1;                   
                break;
            //按金额
            case 3:
                $data['used'] = 1;
                break;
            default:
                $data['used'] = 1;
                break;
        }
        $data['used_time'] = time();
        $data['order_id'] = isset($coupon['order_id']) ? $coupon['order_id'] : 0;

        return $this->updateCoupon(array('coupon_id' => $coupon['coupon_id']), $data);
    }
}
