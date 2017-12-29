<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/1/3
 * Time: 17:38
 */
class ControllerSystemTest extends Controller {
    public function index() {
        $input = $this->request->post(array('MAC', 'keySource'));
//        $file_content = file_get_contents("php://input");
//        $input = json_decode($file_content, true);
        if (empty($input['keySource']) || (strlen($input['keySource']) != 8)) {
            $this->response->showErrorResult('非法keySource');
        }
        $this->load->library('sys_model/lock', true);
        $condition = array(
            'lock_sn' => $input['MAC']
        );
        $lock_info = $this->sys_model_lock->getLockInfo($condition);

        // 随机索引，相当于加密的key
        $rnd = mt_rand(0, strlen($lock_info['password']) - 16);
        // 以索引作开始取16个字符串
        $pwd = substr($lock_info['password'], $rnd, 16);
        // 填充原字符
        $keySource = strtoupper($input['keySource'] . '00000000');

        $aes = new \Tool\Crypt_AES();
        $aes->set_key($pwd);
        $encryptResult = $aes->encrypt($keySource);

        $data['encryptionKey'] = $rnd + 128;
        $data['keys'] = strtoupper($encryptResult);
        $data['serverTime'] = time();
        $this->response->showSuccessResult($data);
    }
}