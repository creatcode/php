<?php
class Url {
	private $ssl;
	
	private $rewrite = array();
	
	public function __construct($ssl = false) {
		$this->ssl = $ssl;
	}
	
	public function addRewrite($rewrite) {
		$this->rewrite[] = $rewrite; 
	}
	
	public function link($route, $args = '', $secure = true) {
		if ($this->ssl && $secure) {
			$url = 'https://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/.\\') . '/index.php?route=' . $route;
		} else {
			$url = 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/.\\') . '/index.php?route=' . $route;
		}

		if ($args) {
			if (is_array($args)) {
				$args = http_build_query($args);
			}
			if (!empty($args)) {
				$url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
			}
		}

		foreach ($this->rewrite as $rewrite) {
			$url = $rewrite->rewrite($url);
		}

		return $url;
	}
}
