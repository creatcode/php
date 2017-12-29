<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/1/3
 * Time: 17:38
 */
class ControllerWechatDevice extends Controller {
    // 设备上限
    const DEVICE_LIMIT = 100;

    private $wx_appid;
    private $wx_appsecret;
    private $product_id = 34522;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->wx_appid = 'wxcbfa44fc0c22072f';
        $this->wx_appsecret = 'dfa95aa9409e9c8586c7d256e851ad83';

        $this->load->library('sys_model/wechat_device', true);
        $this->load->library('sys_model/lock', true);
        $this->load->library('sys_model/bicycle', true);
    }

    public function openLock() {
        $bicycle_sn = $this->request->post('bicycle_sn');

        // 单车信息
        $condition = array(
            'full_bicycle_sn' => $bicycle_sn
        );
        $bicycleInfo = $this->sys_model_bicycle->getBicycleInfo($condition);

        if (!$bicycleInfo) {
            $this->response->showErrorResult('单车不存在');
        }

        if (!isset($bicycleInfo['lock_sn']) || empty($bicycleInfo['lock_sn'])) {
            $this->response->showErrorResult('单车没有绑定锁');
        }

        $lock_sn = $bicycleInfo['lock_sn'];
        // 锁信息
        $condition = array(
            'lock_sn' => $lock_sn
        );
        $lockInfo = $this->sys_model_lock->getLockInfo($condition);

        // 是否有设备绑定锁
        $condition = array(
            'lock_sn' => $lock_sn
        );
        $deviceInfo = $this->sys_model_wechat_device->getWechatDeviceInfo($condition);

        $device_id = '';
        // 锁已经绑定设备
        if (is_array($deviceInfo) && !empty($deviceInfo)) {
            $device_id = $deviceInfo['device_id'];
        } else {
            // 是否超过设备上限
            $total = $this->sys_model_wechat_device->getTotalWechatDevice();
            // 超过设备上限
            if ($total >= self::DEVICE_LIMIT) {
                // 获取不在使用中的设备id
                $condition = array(
                    'is_using' => 0
                );
                $usedDevice = $this->sys_model_wechat_device->getWechatDeviceInfo($condition);
                $device_id = $usedDevice['device_id'];

                // 更改设备绑定的锁sn
                $wechatDevicebj = new \wechat\WechatDevice($this->wx_appid, $this->wx_appsecret);
                $res = $wechatDevicebj->authorizeDevice($device_id, $lockInfo['mac_address'], bin2hex($lockInfo['encrypt_key']));
                if ($res['state']) {
                    // 更改数据库信息
                    $condition = array(
                        'device_id' => $device_id
                    );
                    $data = array(
                        'lock_sn' => $lock_sn,
                        'is_using' => 1
                    );
                    $this->sys_model_wechat_device->updateWechatDevice($condition, $data);
                }
            } else {
                $wechatDevicebj = new \wechat\WechatDevice($this->wx_appid, $this->wx_appsecret);
                // 添加微信设备
                $res = $wechatDevicebj->getqrcode($this->product_id);
                if ($res['state']) {
                    $device_id = $res['data']['deviceid'];
                    $res = $wechatDevicebj->authorizeDevice($device_id, $lockInfo['mac_address'], bin2hex($lockInfo['encrypt_key']));
                    if ($res['state']) {
                        // 入库
                        $data = array(
                            'device_id' => $device_id,
                            'lock_sn' => $lock_sn,
                            'is_using' => 1
                        );
                        $this->sys_model_wechat_device->addWechatDevice($data);
                    }
                }
            }
        }
        $responseData = array(
            'device_id' => $device_id
        );
        $this->response->showSuccessResult($responseData);
    }

    /**
     * 绑定设备
     */
    public function bind() {
        $input = $this->request->post(array('ticket', 'device_id', 'openid'));
        $wechatDevicebj = new \wechat\WechatDevice($this->wx_appid, $this->wx_appsecret);
        $res = $wechatDevicebj->bind($input['ticket'], $input['device_id'], $input['openid']);
        $res['state'] ? $this->response->showSuccessResult() : $this->response->showErrorResult($res['msg']);
    }

    /**
     * 解绑设备
     */
    public function unbind() {
        $input = $this->request->post(array('ticket', 'device_id', 'openid'));
        $wechatDevicebj = new \wechat\WechatDevice($this->wx_appid, $this->wx_appsecret);
        $res = $wechatDevicebj->unbind($input['ticket'], $input['device_id'], $input['openid']);
        $res['state'] ? $this->response->showSuccessResult() : $this->response->showErrorResult($res['msg']);
    }
}
