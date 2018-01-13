<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/12/18
 * Time: 14:16
 *
 */
namespace Enum;

class OrderState{

    /**
     * 等待开锁
     */
    const WAITING_OPEN_LOCK = -2;

    /**
     * 已经取消
     */
    const CANCELED = -1;

    /**
     * 未生效
     */
    const INEFFECTIVE = 0;

    /**
     * 骑行中
     */
    const RIDING = 1;


    /**
     * 订单完成 骑行正常结束
     */
    const FINISHED = 2;


}