<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/12/17
 * Time: 14:36
 * author yangjifang 1427047861@qq.com
 * 错误码应该统一管理 这样子代码就不用改
 */
namespace Enum;

class ErrorCode{

    /**
     * 操作成功
     */
    const SUCCESS = 0;

    /**
     * 缺少参数
     */
    const ERROR_MISSING_PARAMETER = 1;

    /**
     * 手机号码，设备和验证码都不能为空
     */
    const ERROR_EMPTY_LOGIN_PARAM = 2;

    /**
     * 手机错误
     */
    const ERROR_MOBILE = 3;

    /**
     * 邮箱错误
     */
    const ERROR_EMAIL  = 4;

    /**
     * 注册失败
     */
    const REGISTER_FAILURE = 5;

    /**
     * 短信未验证或者已经失效
     */
    const ERROR_INVALID_MESSAGE_CODE = 6;

    /**
     * 数据库错误
     */
    const ERROR_DATABASE_FAILURE = 7;

    /**
     * 找不到单车
     */
    const ERROR_NOT_FIND_BICYCLE = 8;

    /**
     * 找不到锁
     */
    const ERROR_NOT_FIND_LOCK = 9;

    /**
     * 开锁失败
     */
    const OPEN_LOCK_FAILURE = 10;

    /**
     * 不存在的命令 开锁
     */
    const ERROR_UNKNOWN_TYPE_CMD = 11;

    /**
     * 找不到订单
     */
    const ERROR_NOT_FIND_ORDER = 12;

    /**
     * url错误
     */
    const ERROR_INVALID_URL = 13;

    /**
     * url过期了
     */
    const ERROR_URL_OVERTIME = 14;

    /**
     * 发送邮件失败
     */
    const ERROR_SEND_EMAIL_FAILURE = 15;

    /**
     * 用户不存在
     */
    const USER_NOT_EXISTS = 16;

    /**
     * 用户已经存在了
     */
    const USER_ALREADY_ACTIVE = 17;

    /**
     * 激活失败
     */
    const FAILURE_ACTIVE = 18;
}