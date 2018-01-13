<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/12/18
 * Time: 21:41
 */
class ControllerEventOrder extends Controller{

    /**
     * 骑行结束计费
     */
    public function afterRiding(){
        $args = func_get_args();
        var_dump($args);
    }

}