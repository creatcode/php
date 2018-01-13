<?php

/**
 * Created by PhpStorm.
 * User: h
 * Date: 2016/12/26
 * Time: 14:55
 */
/**
 * 单车类型
 */
if (!function_exists('get_bicycle_type')) {

    function get_bicycle_type() {
        return array(
            '1' => '普通单车',
            '2' => '助力单车',
            '3' => '普通桩车',
            '4' => '助力桩车',
        );
    }

}

/**
 * 用户类型
 */
if (!function_exists('get_user_type')) {

    function get_user_type() {
        return array(
            '1' => '注册金用户',
            '2' => '临时用户'
        );
    }

}

/**
 * 站点状态
 */
if (!function_exists('get_station_state')) {

    function get_station_state() {
        return array(
            '1' => '正常',
            '2' => '在建中',
            '3' => '维修中',
            '4' => '拆除中',
            '5' => '停用',
            '6' => '故障',
        );
    }

}


/**
 * 站点电量状态
 */
if (!function_exists('get_station_power_state')) {

    function get_station_power_state() {
        return array(
            '1' => '满储',
            '2' => '高储',
            '3' => '低储',
            '4' => '零储',
        );
    }

}

/**
 * 站点电量状态
 */
if (!function_exists('get_stake_state')) {

    function get_stake_state() {
        return array(
            '1' => '正常',
            '2' => '故障',
            '3' => '停用',
        );
    }

}

/**
 * 单车类型
 */
if (!function_exists('get_bicycle_using_state')) {

    function get_bicycle_using_state() {
        return array(
            '0' => '未使用',
            '1' => '故障',
            '2' => '使用中'
        );
    }

}

/**
 * 锁状态
 */
if (!function_exists('get_lock_status')) {

    function get_lock_status() {
        return array(
            '0' => '已关锁',
            '1' => '开锁',
            '2' => '异常'
        );
    }

}


/**
 * 充值类型
 */
if (!function_exists('get_recharge_type')) {

    function get_recharge_type() {
        return array(
            '0' => '余额充值',
            '1' => '押金充值',
            '2' => '充值卡充值',
            '3' => '注册金充值'
        );
    }

}


/**
 * 时间区间选择
 */
if (!function_exists('get_time_type')) {

    function get_time_type() {
        return array(
            '1' => '按年',
            '2' => '按月',
            '3' => '按天'
        );
    }

}

/**
 * 充值优惠启用状态
 */
if (!function_exists('get_present_type')) {

    function get_present_type() {
        return array(
            '0' => '停用',
            '1' => '启用'
        );
    }

}

/**
 * 文章语言种类
 */
if (!function_exists('get_lan_type')) {

    function get_lan_type() {
        return array(
            '0' => '中文',
            '1' => 'Enlish',
            '2' => '意大利语'
        );
    }

}

/**
 * 退款类型
 */
if (!function_exists('get_cashapply_type')) {

    function get_cashapply_type() {
        return array(
            '0' => '余额退款',
            '1' => '押金退款',
            '2' => '注册金退款'
        );
    }

}

/**
 * 结算状态
 */
if (!function_exists('get_payoff_state')) {

    function get_payoff_state() {
        return array(
            '0' => '未支付',
            '1' => '已支付',
        );
    }

}

/**
 * 支付状态
 */
if (!function_exists('get_payment_state')) {

    function get_payment_state() {
        return array(
            '0' => '未支付',
            '1' => '已支付',
            '-1' => '已退款',
            '-2' => '部分已退款',
        );
    }

}

/**
 * 支付类型
 */
if (!function_exists('get_payment_type')) {

    function get_payment_type() {
        return array(
            'app' => 'APP',
            'web' => '刷卡',
            // 'mini_app' => '小程序',
        );
    }

}


/**
 * 订单状态
 */
if (!function_exists('get_order_state')) {

    function get_order_state() {
        return array(
            '-1' => '已取消',
            '0' => '未生效',
            '1' => '进行中',
            '2' => '已完成',
            '-2' => '等待开锁',
            '-3' => '等待结束',
            '3' => '蓝牙锁待计费'
        );
    }

}

/**
 * 订单状态
 */
if (!function_exists('get_parking_type')) {

    function get_parking_type() {
        return array(
            '1' => '违停上报',
            '2' => '其他上报'
        );
    }

}

/**
 * 订单状态
 */
if (!function_exists('get_cooperator_state')) {

    function get_cooperator_state() {
        return array(
            '0' => '禁用',
            '1' => '启用',
        );
    }

}

/**
 * 工单状态
 */
if (!function_exists('get_work_order_state')) {

    function get_work_order_state() {
        return array(
            1 => '已处理',
            2 => '待处理',
            3 => '待处理',
        );
    }

}

/**
 * 反馈类型
 */
if (!function_exists('get_feedback_type')) {

    function get_feedback_type() {
        return array(
            1 => '计费信息',
            2 => 'App用户/卡用户状态处理',
            3 => '租还车记录修改',
            4 => '单车故障反馈',
        );
    }

}

/**
 * 设置类型状态
 */
if (!function_exists('get_setting_boolean')) {

    function get_setting_boolean() {
        return array(
            '0' => '禁用',
            '1' => '启用',
        );
    }

}

/**
 * 常规布尔型
 */
if (!function_exists('get_common_boolean')) {

    function get_common_boolean() {
        return array(
            '1' => '是',
            '0' => '否',
        );
    }

}

/**
 * 常规布尔型
 */
if (!function_exists('get_fault_status')) {

    function get_fault_status() {
        return array(
            '1' => '二维码脱落',
            '2' => '车铃不响',
            '3' => '刹车失灵',
            '4' => '龙头歪斜',
            '5' => '车胎漏气',
            '6' => '链条坏了',
            '7' => '踏板坏了',
            '8' => '其他',
            '9' => '开不了锁',
            '10' => '关不了锁',
            '11' => '违规停车',
            '12' => '结束不了订单',
        );
    }

}

/**
 * 申请结果
 */
if (!function_exists('get_common_result')) {

    function get_common_result() {
        return array(
            '1' => '通过',
            '0' => '不通过',
        );
    }

}

if (!function_exists('get_apply_states')) {

    function get_apply_states() {
        return array(
            '2' => '不通过',
            '1' => '通过',
            '0' => '待处理',
        );
    }

}

if (!function_exists('get_apply_states_deposit')) {

    function get_apply_states_deposit() {
        return array(
            '0' => '待处理',
            '1' => '财务审批',
            '2' => '通过',
            '-1' => '不通过',
        );
    }

}

/**
 * 故障处理
 */
if (!function_exists('get_fault_processed')) {

    function get_fault_processed() {
        return array(
            '1' => '已处理',
            '0' => '未处理',
        );
    }

}

/**
 * 维修方式
 */
if (!function_exists('get_repair_type')) {

    function get_repair_type() {
        return array(
            '1' => '现场维修',
            '2' => '返仓维修',
            '3' => '报废回收',
            '4' => '其他',
        );
    }

}

/**
 * 优惠券类型
 */
if (!function_exists('get_coupon_type')) {

    function get_coupon_type() {
        return array(
            '1' => '按时间',
            '2' => '按次数',
            '3' => '按金额',
            '4' => '按折扣',
        );
    }

}

/**
 * 优惠券获取方式
 */
if (!function_exists('get_coupon_obtain')) {

    function get_coupon_obtain() {
        return array(
            '0' => '后台发放',
            '1' => '通过邀请码',
            '2' => '通过分享行程',
            '4' => '其他途径',
        );
    }

}

/**
 * 锁类型
 */
if (!function_exists('get_lock_type')) {

    function get_lock_type() {
        return array(
            '1' => 'GPRS',
            '2' => '蓝牙',
            '3' => '机械',
            '4' => 'GPRS+蓝牙',
            '5' => '亦强蓝牙',
            '6' => '亦强GPRS'
        );
    }

}


/**
 * 广告类型
 */
if (!function_exists('get_adv_type')) {

    function get_adv_type() {
        return array(
            '0' => '常规',
            '1' => '启动页',
        );
    }

}

/**
 * 广告类型
 */
if (!function_exists('get_adv_type')) {

    function get_adv_type() {
        return array(
            '0' => '常规',
            '1' => '启动页',
        );
    }

}

/**
 * 礼品活动状态
 */
if (!function_exists('get_gift_orders_state')) {

    function get_gift_orders_state() {
        return array(
            '-1' => '已取消',
            '0' => '审核中',
            '1' => '已发货',
        );
    }

}

/**
 * 押金退款类型
 */
if (!function_exists('get_apply_payment_type')) {

    function get_apply_payment_type() {
        return array(
            '0' => '--退款方式--',
            '1' => 'Stripe收款',
            // '2' => '支付宝收款',
            // '3' => '银行卡收款',
        );
    }

}

/*
 * 后台日志内容分类标签
 * */

if (!function_exists('get_admin_log_constant')) {

    function get_admin_log_constant() {
        return array(
            array(
                'type_id' => 1,
                'type_name' => '锁管理',
                'type_keyword' => '操作锁',
            ),
            array(
                'type_id' => 2,
                'type_name' => '系统消息管理',
                'type_keyword' => '操作系统消息',
            ),
            array(
                'type_id' => 3,
                'type_name' => '版本号管理',
                'type_keyword' => '操作版本号',
            ),
            array(
                'type_id' => 4,
                'type_name' => '运维人员管理',
                'type_keyword' => '操作运维人员',
            ),
            array(
                'type_id' => 5,
                'type_name' => '管理员管理',
                'type_keyword' => '操作优惠券',
            ),
            array(
                'type_id' => 6,
                'type_name' => '合伙人管理',
                'type_keyword' => '操作合伙人',
            ),
            array(
                'type_id' => 7,
                'type_name' => '单车管理',
                'type_keyword' => '操作单车',
            ),
            array(
                'type_id' => 8,
                'type_name' => '文章管理',
                'type_keyword' => '操作文章',
            ),
            array(
                'type_id' => 9,
                'type_name' => '优惠券管理',
                'type_keyword' => '操作优惠券',
            ),
            array(
                'type_id' => 10,
                'type_name' => '结束订单',
                'type_keyword' => '操作后台结束订单',
            ),
        );
    }

    /**
     * 单车类型
     */
    if (!function_exists('get_bicycle_status')) {

        function get_bicycle_status() {
            return array(
                '1' => '在库',
                '2' => '可租用',
                '3' => '租用中',
                '4' => '维修中',
                '5' => '低电量（助力）',
                '6' => '报障',
                '7' => '故障',
                '8' => '遗失',

            );
        }

    }

    function build_order_no()
    {
        return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }
}
