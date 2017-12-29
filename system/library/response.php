<?php
class Response {
	private $headers = array();
	private $level = 0;
	private $output;

	public function addHeader($header) {
		$this->headers[] = $header;
	}

	public function redirect($url, $status = 302) {
		header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url), true, $status);
		exit();
	}

	public function setCompression($level) {
		$this->level = $level;
	}

	public function getOutput() {
		return $this->output;
	}

	public function setOutput($output) {
		$this->output = $output;
	}

	private function compress($data, $level = 0) {
		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gizp') !== false)) {
			$encoding = 'gzip';
		}

		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false)) {
			$encoding = 'x-gzip';
		}

		if (!isset($encoding) || ($level < -1 || $level > 9)) {
			return $data;
		}

		if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
			return $data;
		}

		if (headers_sent()) {
			return $data;
		}

		if (connection_status()) {
			return $data;
		}

		$this->addHeader('Content-Encoding: ' . $encoding);

		return gzencode($data, (int)$level);
	}

	public function output() {
		if ($this->output) {
			if ($this->level) {
				$output = $this->compress($this->output, $this->level);
			} else {
				$output = $this->output;
			}

			if (!headers_sent()) {
				foreach ($this->headers as $header) {
					header($header, true);
				}
			}
			echo $output;
		}
	}

	public $_error = array(
		'error' => array(
			'errorCode' => 1,
			'msg' => '操作失败',
			'data' => array()
		),
		'success' => array(
			'errorCode' => 0,
			'msg' => '操作成功',
			'data' => array()
		)
	);

	public function showSuccessResult($data = array(), $msg = '操作成功') {
		$this->showJsonResult($msg, 1, $data);
	}

	public function showErrorResult($msg = '操作失败', $code = 0, $data = array()) {
		$this->showJsonResult($msg, 0, $data, $code);
	}

	public function showJsonResult($msg = '', $type = 0, $data = array(), $code = 0) {
		$type = intval($type) ? 'success' : 'error';
		if ($msg) $this->_error[$type]['msg'] = $msg;
		if ($data) $this->_error[$type]['data'] = $data;
		if ($code) $this->_error[$type]['errorCode'] = $code;
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($this->_error[$type],JSON_UNESCAPED_UNICODE);
		exit;
	}
}
