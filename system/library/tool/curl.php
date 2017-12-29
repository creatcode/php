<?php
namespace Tool;
class Curl {
    private $url = '';
    private $data = array();

    public function __construct($url, $curl_option = array())
    {
        if (!function_exists('curl_init')) {
            throw new \Exception('curl未开启');
        }
        $this->url = $url;
    }

    public function setData($data) {
        $this->data =$data;
    }

    public function postData() {
        if (empty($this->data)) {
            throw new \Exception('Data is empty');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //post
        curl_setopt($ch, CURLOPT_POST, 1);
        //data
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function getData() {
        $ch = curl_init();
        if (empty($this->url)) {
            throw new \Exception('curl url is empty');
        }
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}