<?php
/**
 * 东莞市亦强软件有限公司
 * Author: 罗剑波
 * Time: 2017/3/4 13:40
 */
class ControllerSystemTest extends Controller {

    public function index() {
		set_time_limit(0);
        $this->load->library('sys_model/user', true);
		$url = 'https://api.s-bike.cn/index.php';
		
		$condition = array(
			'cooperator_id' => 2
		);
		$fields = 'user_id, uuid';
		$users = $this->sys_model_user->getUserList($condition, $fields);
		if (!empty($users) && is_array($users)) {
			foreach($users as $user) {
				$param = array(
					'route' => 'account/account/cashApply',
					'version' => '35',
					'fromApi' => 'ios',
				);
				$sign = md5($user['user_id'] . $user['uuid']);
				$data = array(
					'user_id' => $user['user_id'],
					'sign' => $sign,
				);
				$this->http($url, $param, $data, 'POST');
			}
		}
		echo 11111;
    }

	private function http($url, $param, $data = '', $method = 'GET'){
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
