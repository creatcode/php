<?php
class Util{
	static $log_path 	= '/data/wwwroot/default/bike/admin/alipay_refund_notify_log3.txt';
	static $notify_url 	= 'http://bike.e-stronger.com/bike/admin/payment/alipay_refund.php';
	static $log_arr 	= array();

	static public function get_batch_no($trace_no){
		$log_info 	= self::get_log_info($trace_no);
		return isset($log_info['batch_no'])?$log_info['batch_no']:'';
	}

	static public function get_log_info($trace_no){
		if(empty(self::$log_arr)){
			self::$log_arr 	= self::readLog();
		}
		return isset(self::$log_arr[$trace_no])?self::$log_arr[$trace_no]:'';
	}

	static public function renotify($trace_no){
		if(empty(self::$log_arr)){
			self::readLog();
		}
		if(!isset(self::$log_arr[$trace_no]) || empty(self::$log_arr[$trace_no])){
			return array('status'=>0,'err_msg'=>'undefined trace_no:'.$trace_no);
		}
		$data 			= self::$log_arr[$trace_no];
		$notify_data 	= self::ToUrlParams($data);
		list($status,$content)=self::http_post_curl(self::$notify_url,$notify_data);

		if($status != 200){//请求异常
			return array('status'=>0,'err_msg'=>'request fail.status code:'.$status);
		}
		return array('status'=>1,'return_content'=>$content);
	}

	static public function readLog(){
		if(!file_exists(self::$log_path)){
			throw new \Exception("log file not fond:".self::$log_path, 1);
		}
		$fp 	= fopen(self::$log_path, 'r');
		$i 		= 0;
		$arr 	= array();
		$key_arr= array();
		while( !feof( $fp ) ) {
			$s = fgets( $fp );
			if ( !empty($s) ) {
				preg_match('/:\d{2}({.*})/', $s, $match);
				$_log_json 	= isset($match[1])?$match[1]:'';
				$_log_json 	= trim($_log_json,'"');
				if(empty($_log_json)) continue;

				$_log_arr 	= json_decode($_log_json,true);
				$result_details 	= $_log_arr['result_details'];

				preg_match('/(\d*)\^([0-9.]*)\^(\w*)/', $result_details, $match2);
				$batch_no 			= $_log_arr['batch_no'];
				$trace_no 			= isset($match2[1]) ? $match2[1] : '';

				//丢弃不成功通知
				if($match2[3] != 'SUCCESS' && $match2[3] != 'TRADE_HAS_FINISHED') continue;

				//丢弃重复的通知
				$key 	= $batch_no.'-'.$trace_no;
				if(in_array($key, $key_arr)) continue;

				$key_arr[] 	= $key;
				$arr[$trace_no] = $_log_arr;
			}
			$i++;
		}
		return self::$log_arr=$arr;
	}

	static private function  http_post_curl($url, $data ,$header=array('Content-Type: application/x-www-form-urlencoded; charset= utf-8'),$timeOut=6) {
		$header[] 	= 'Content-Length: '.strlen($data);
		$ch  		= curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT,$timeOut);//超时时间
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		ob_start();
		curl_exec($ch);
		$return_content = ob_get_contents();
		ob_end_clean();

		$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		return array($return_code, $return_content);
	}

	static private function ToUrlParams($data){
		$buff = "";
		foreach ($data as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		$buff = trim($buff, "&");
		return $buff;
	}
}
