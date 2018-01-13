<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/12/20
 * Time: 15:29
 */
namespace Enum;

class PushCode {

    /**
     * 邮件激活成功
     */
    const EMAIL_ACTIVATION_SUCCESS = 1;

    /**
     * 订单成功结束
     */
    const ORDER_FINISH_SUCCESS = 2;

    /**
     * 订单开锁成功
     */
    const ORDER_OPEN_SUCCESS = 3;

    /**
     * 相同账号在不同设备登录
     */
    const DIFFERENT_DEVICE=4;
    /**
     * 邮箱确认修改密码
     */
    const EMAIL_SET_PASSWORD=5;
}