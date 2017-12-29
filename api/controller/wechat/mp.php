<?php

/**
 * Class ControllerWechatMp
 * 微信公众号接口
 */

class ControllerWechatMp extends Controller {
    public function index() {
        $token = 'estronger';
        $appid = 'wxcbfa44fc0c22072f';
        $key = 'ViDoBPiw3Smae9s4DonZzzABb7pPNUb4p3pQXz9B1Tt';
        $wechatObj = new \Wechat\Wechat($token, $appid, $key);
        $input = $wechatObj->request();
        if ($input && is_array($input)) {
            $content = '';
            $wechatObj->response($content, \Wechat\Wechat::MSG_TYPE_TEXT);
        }
    }
}