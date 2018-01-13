<?php

/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2016/12/9
 * Time: 16:03
 */

use Enum\ErrorCode;

class ControllerOperatorOperator extends Controller
{

    /**
     * 开锁
     */
    public function openLock()
    {
        $this->instructions_instructions = new Instructions\Instructions($this->registry);
        $result = $this->instructions_instructions->openLock($this->lock['lock_sn']);
        $return_data = [
            'order_sn' => $this->order_result['data']['order_sn'],
            'lock_type' => $this->order_result['data']['lock_type']
        ];

        if ($result['state']) {
            $this->response->showSuccessResult($return_data, $this->language->get('success_send_open_lock_instruction'));
        }

        $this->response->showErrorResult($this->language->get('open_lock_failure'), ErrorCode::OPEN_LOCK_FAILURE);
    }

    /**
     * 响铃
     */
    public function beepLock()
    {
        $device_id = $this->request->post['device_id'];
        if (empty($device_id)) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        $this->load->library('instructions/instructions', true);
        $this->instructions_instructions->beepLock($device_id);
        $this->response->showSuccessResult('', $this->language->get('success_send_beep_lock_instruction'));
    }

    /**
     * 查找锁的位置
     */
    public function selectLock()
    {
        $device_id = $this->request->post['device_id'];
        if (empty($device_id)) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }
        $this->load->library('instructions/instructions', true);
        $this->instructions_instructions->selectLocks($device_id);
        $this->response->showSuccessResult('', $this->language->get('success_send_select_lock_instruction'));
    }

    /**
     * 查找锁的位置
     */
    public function lockPosition()
    {
        $device_id = $this->request->post['device_id'];
        if (!$device_id) {
            $this->response->showErrorResult($this->language->get('error_missing_parameter'), 1);
        }

        $this->load->library('logic/location', true);
        $result = $this->logic_location->findDeviceCurrentLocation($device_id);
        if ($result) {
            $this->response->showSuccessResult($result, $this->language->get('success_lock_position'));
        }
        $this->response->showErrorResult($this->language->get('error_lock_position'));
    }

    /**
     * 获取开锁密钥
     */
    public function openLockSecretKey()
    {
        $input = $this->request->post(array('order_sn', 'keySource'));
        $user_id = $this->startup_user->userId();

        $this->load->library('sys_model/orders', true);
        // 订单信息
        $condition = array(
            'user_id' => $user_id,
            'order_sn' => $input['order_sn']
        );
        $order_info = $this->sys_model_orders->getOrdersInfo($condition);
        // 订单不存在
        if (!$order_info || !is_array($order_info)) {
            $this->response->showErrorResult('订单不存在');
        }
        // 订单非等待开锁状态
        if ($order_info['order_state'] != -2) {
            $this->response->showErrorResult('订单未生效或已结束');
        }

        $this->load->library('sys_model/lock', true);
        // 锁信息
        $condition = array(
            'lock_sn' => $order_info['lock_sn']
        );
        $lock_info = $this->sys_model_lock->getLockInfo($condition);
        // 车锁不存在
        if (!$lock_info || !is_array($lock_info)) {
            $this->response->showErrorResult('车锁不存在');
        }

        $data = array(
            'encrypt_key' => $lock_info['encrypt_key'],
            'password' => $lock_info['password'],
            'server_time' => time()
        );
        // 泺平锁需要配合临时key加密下
        if ($lock_info['lock_factory'] == 2) {
            if (empty($input['keySource']) || (strlen($input['keySource']) != 8)) {
                $this->response->showErrorResult('非法keySource');
            }
            // 随机索引，相当于加密的key
            $rnd = mt_rand(0, strlen($lock_info['password']) - 16);
            // 以索引作开始取16个字符串
            $pwd = substr($lock_info['password'], $rnd, 16);
            // 填充原字符
            $keySource = strtoupper($input['keySource'] . '00000000');

            $aes = new \Tool\Crypt_AES();
            $aes->set_key($pwd);
            $encryptResult = $aes->encrypt($keySource);

            $data['encrypt_key'] = $rnd + 128;
            $data['password'] = $encryptResult;
        }
        $this->response->showSuccessResult($data, '获取开锁密钥成功');
    }

    /**
     * 获取开锁信息
     */
    public function getOpenLockResource()
    {
        $input = $this->request->post(array('bicycle_sn', 'lat', 'lng'));
        if (!$input['bicycle_sn']) {
            $this->response->showErrorResult('单车编号不能为空', 5222);
        }
        $region_city_code = $region_city_ranking = $bicycle_sn = '';
        if (strlen($input['bicycle_sn']) == 11) {
            sscanf($input['bicycle_sn'], '%03d%02d%06d', $region_city_code, $region_city_ranking, $bicycle_sn);
            $bicycle_sn = sprintf('%06d', $bicycle_sn);
        } else {
            $bicycle_sn = $input['bicycle_sn'];
        }
        $condition = array(
            'bicycle_sn' => $bicycle_sn
        );
        $this->load->library('sys_model/bicycle', true);
        $this->load->library('sys_model/lock', true);
        $bicycle_info = $this->sys_model_bicycle->getBicycleInfo($condition, 'bicycle_id, bicycle_sn, lock_sn');
        if (empty($bicycle_sn)) {
            $this->response->showErrorResult('系统不存在此单车', 5200);
        }
        if (empty($bicycle_info['lock_sn'])) {
            $this->response->showErrorResult('锁未入库', 5230);
        }
        $lock_info = $this->sys_model_lock->getLockInfo(array('lock_sn' => $bicycle_info['lock_sn']));
        if (empty($lock_info)) {
            $this->response->showErrorResult('单车绑定的锁已被移除，请重新入库后再试', 5400);
        }

        $result = array(
            'bicycle_sn' => $bicycle_sn,
            'lock_sn' => $lock_info['lock_sn'],
            'lock_type' => $lock_info['lock_type'],
            'mac_address' => $lock_info['mac_address'],
            'encrypt_key' => $lock_info['encrypt_key'],
            'password' => $lock_info['password']
        );

        $this->response->showSuccessResult($result, '获取开锁信息成功');
    }


}
