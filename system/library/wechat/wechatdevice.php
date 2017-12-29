<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Wechat;

class WechatDevice
{
    /**
     * 微信开发者申请的appID
     * @var string
     */
    private $appId = '';

    /**
     * 微信开发者申请的appSecret
     * @var string
     */
    private $appSecret = '';

    /**
     * 公众号调用各接口时都需使用access_token
     * @var string
     */
    private $accessToken = '';

    /**
     * 设备id
     * @var string
     */
    private $deviceId = '';

    /**
     * 微信api根路径
     * @var string
     */
    const API_URL = 'https://api.weixin.qq.com/device';

    /**
     * 构造方法，调用微信高级接口时实例化SDK
     * @param string $appid  微信appid
     * @param string $secret 微信appsecret
     * @param string $token  获取到的access_token
     */
    public function __construct($appid, $secret, $token = null){
        if($appid && $secret){
            $this->appId     = $appid;
            $this->appSecret = $secret;

            if(!empty($token)){
                $this->accessToken = $token;
            } else {
                $wechatAuthObj = new WechatAuth($this->appId, $this->appSecret);
                $this->accessToken = $wechatAuthObj->getAccessToken('client');
            }
        } else {
            throw new \Exception('缺少参数 APP_ID 和 APP_SECRET!');
        }
    }

    /**
     * 添加deviceid和二维码
     * @param int $product_id 设备的产品编号（由微信硬件平台分配）。可在公众号设备功能管理页面查询。
     * @return array
     */
    public function getqrcode($product_id) {
        $param = array(
            'product_id' => $product_id
        );

        $res = $this->api('getqrcode', '', 'GET', $param);

        $state = isset($res['base_resp']['errcode']) && ($res['base_resp']['errcode'] == 0) ? true : false;
        $msg = isset($res['base_resp']['errmsg']) ? $res['base_resp']['errmsg'] : '';
        $data = array();
        if ($state) {
            $data = array(
                'deviceid' => $res['deviceid'],
                'qrticket' => $res['qrticket']
            );
        }
        return callback($state, $msg, $data);
    }

    /**
     * 利用deviceid更新设备属性
     * @param $deviceId
     * @param $mac
     * @param $authKey
     * @return array
     */
    public function authorizeDevice($deviceId, $mac, $authKey) {
        $postData = array(
            'device_num' => 1,                                  // 设备id的个数
            'device_list' => array(                            // 设备id的列表
                array(
                    'id' => $deviceId,                          // 设备的deviceid
                    'mac' => $mac,                              // 设备的mac地址 格式采用16进制串的方式（长度为12字节），如： 1234567890AB
                    'auth_key' => $authKey,                    // auth及通信的加密key 格式采用16进制串的方式（长度为32字节），不需要0X前缀，如： 1234567890ABCDEF1234567890ABCDEF
                    'connect_protocol' => '3',               // 连接协议 1：安卓经典协议 2：ios经典协议 3：ble 4：wifi
                    'close_strategy' => '2',                 // 断开策略，1：退出公众号页面时即断开连接 2：退出公众号之后保持连接不断开
                    'conn_strategy' => '5',                  // 连接策略
                    'crypt_method'  => '0',                  // auth加密方法，目前支持两种取值： 0：不加密 1：AES加密
                    'auth_ver' => '0',                        // 0：不加密的version
                    'manu_mac_pos' => '-1',                  // 表示mac地址在厂商广播manufature data里含有mac地址的偏移，取值如下： -1：在尾部、 -2：表示不包含mac地址 其他：非法偏移
                    'ser_mac_pos' => '-2',                   // 表示mac地址在厂商serial number里含有mac地址的偏移，取值如下： -1：表示在尾部 -2：表示不包含mac地址 其他：非法偏移
//                    'ble_simple_protocol' => '0',             // 1：精简协议类型取值
                )
            ),
            'op_type' => 1                                      // 1：设备更新（更新已授权设备的各属性值）
        );
        $res = $this->api('authorize_device', $postData);

        if (isset($res['resp'][0]['errcode']) && ($res['resp'][0]['errcode'] == 0)) {
            $data = isset($res['resp'][0]['base_info']) ? $res['resp'][0]['base_info'] : array();
            return callback(true, 'ok', $data);
        }
        $msg = isset($res['resp'][0]['errmsg']) ? $res['resp'][0]['errmsg'] : '';
        return callback(false, $msg);
    }

    /**
     * 用户与设备绑定
     * @param $ticket   绑定openid
     * @param $deviceId 设备id
     * @param $openid   用户openid
     * @return array
     */
    public function bind($ticket, $deviceId, $openid) {
        $postData = array(
            'ticket' => $ticket,
            'device_id' => $deviceId,
            'openid' => $openid
        );
        $res = $this->api('bind', $postData);
        if (isset($res['base_resp']['errcode'])) {
            return callback(true, 'ok');
        }
        $msg = isset($res['base_resp']['errmsg']) ? $res['base_resp']['errmsg'] : 'bind fail';
        return callback(false, $msg);
    }

    /**
     * 用户与设备解绑
     * @param $ticket   解绑ticket
     * @param $deviceId 设备id
     * @param $openid   用户id
     * @return array
     */
    public function unbind($ticket, $deviceId, $openid) {
        $postData = array(
            'ticket' => $ticket,
            'device_id' => $deviceId,
            'openid' => $openid
        );
        $res = $this->api('unbind', $postData);
        if (isset($res['base_resp']['errcode'])) {
            return callback(true, 'ok');
        }
        $msg = isset($res['base_resp']['errmsg']) ? $res['base_resp']['errmsg'] : 'unbind fail';
        return callback(false, $msg);
    }

    /**
     * 用户与设备强制绑定
     * @param $deviceId 设备id
     * @param $openid   用户openid
     * @return array
     */
    public function compelBind($deviceId, $openid) {
        $postData = array(
            'device_id' => $deviceId,
            'openid' => $openid
        );
        $res = $this->api('compel_bind', $postData);
        if (isset($res['base_resp']['errcode'])) {
            return callback(true, 'ok');
        }
        $msg = isset($res['base_resp']['errmsg']) ? $res['base_resp']['errmsg'] : 'compel bind fail';
        return callback(false, $msg);
    }

    /**
     * 用户与设备强制绑定
     * @param $deviceId 设备id
     * @param $openid   用户openid
     * @return array
     */
    public function compelUnbind($deviceId, $openid) {
        $postData = array(
            'device_id' => $deviceId,
            'openid' => $openid
        );
        $res = $this->api('compel_unbind', $postData);
        if (isset($res['base_resp']['errcode'])) {
            return callback(true, 'ok');
        }
        $msg = isset($res['base_resp']['errmsg']) ? $res['base_resp']['errmsg'] : 'compel unbind fail';
        return callback(false, $msg);
    }

    /**
     * 设备状态查询
     * @param $deviceId 设备id
     * @return array
     */
    public function getStat($deviceId) {
        $param = array(
            'device_id' => $deviceId
        );
        $res = $this->api('compel_unbind', '', 'GET', $param);
        if (isset($res['errcode']) && ($res['errcode'] == 0)) {
            // status 设备状态，目前取值如下： 0：未授权 1：已经授权（尚未被用户绑定） 2：已经被用户绑定 3：属性未设置
            return callback(true, 'ok', $res['status']);
        }
        $msg = isset($res['errmsg']) ? $res['errmsg'] : 'request fail';
        return callback(false, $msg);
    }

    /**
     * 验证二维码
     * @param $qrTicket 二维码ticket
     * @return array
     */
    public function verifyQrcode($qrTicket) {
        $postData = array(
            'ticket' => $qrTicket
        );
        $res = $this->api('verify_qrcode', $postData);
        if (isset($res['errcode']) && ($res['errcode'] == 0)) {
            $data = array(
                'device_type' => isset($res['device_type']) ? $res['device_type'] : '',
                'device_id' => isset($res['device_id']) ? $res['device_id'] : '',
                'mac' => isset($res['mac']) ? $res['mac'] : '',
            );
            return callback(true, 'ok', $data);
        }
        $msg = isset($res['errmsg']) ? $res['errmsg'] : 'request fail';
        return callback(false, $msg);
    }

    /**
     * 获取设备绑定openID
     * @param $deviceType 设备类型，目前为“公众账号原始ID”
     * @param $deviceId 设备的deviceid
     * @return array
     */
    public function getOpenid($deviceType, $deviceId) {
        $param = array(
            'device_type' => $deviceType,
            'device_id' => $deviceId,
        );
        $res = $this->api('get_openid', '', 'GET', $param);
        if (isset($res['resp_msg']['ret_code']) && ($res['resp_msg']['ret_code'] == 0)) {
            return callback(true, 'ok', $res['open_id']);
        }
        $msg = isset($res['errmsg']) ? $res['errmsg'] : 'request fail';
        return callback(false, $msg);
    }

    /**
     * 通过openid获取用户绑定的deviceid
     * @param $openid
     * @return array
     */
    public function getBindDevice($openid) {
        $param = array(
            'openid' => $openid
        );
        $res = $this->api('get_bind_device', '', 'GET', $param);

        $state = isset($res['resp_msg']['ret_code']) && ($res['resp_msg']['ret_code'] == 0) ? true : false;
        $msg = isset($res['resp_msg']['error_info']) ? $res['resp_msg']['error_info'] : '';
        $data = array();
        if ($state) {
            $data = isset($res['device_list']) ? $res['device_list'] : array();
        }
        return callback($state, $msg, $data);
    }

    /**
     * 调用微信api获取响应数据
     * @param  string $name   API名称
     * @param  string $data   POST请求数据
     * @param  string $method 请求方式
     * @param  string $param  GET请求参数
     * @return array          api返回结果
     */
    protected function api($name, $data = '', $method = 'POST', $param = '', $json = true){
        $params = array('access_token' => $this->accessToken);

        if(!empty($param) && is_array($param)){
            $params = array_merge($params, $param);
        }

        $url  = self::API_URL . "/{$name}";
        if($json && !empty($data)){
            //保护中文，微信api不支持中文转义的json结构
            array_walk_recursive($data, function(&$value){
                $value = urlencode($value);
            });
            $data = urldecode(json_encode($data));
        }

        $data = self::http($url, $params, $data, $method);

        return json_decode($data, true);
    }

    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string $url    请求URL
     * @param  array  $param  GET参数数组
     * @param  array  $data   POST的数据，GET请求时该参数无效
     * @param  string $method 请求方法GET/POST
     * @return array          响应数据
     */
    protected static function http($url, $param, $data = '', $method = 'GET'){
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        );

        /* 根据请求类型设置特定参数 */
        $opts[CURLOPT_URL] = $url . '?' . http_build_query($param);

        if(strtoupper($method) == 'POST'){
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $data;

            if(is_string($data)){ //发送JSON数据
                $opts[CURLOPT_HTTPHEADER] = array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($data),
                );
            }
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        //发生错误，抛出异常
        if($error) throw new \Exception('请求发生错误：' . $error);

        return  $data;
    }
}