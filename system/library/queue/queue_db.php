<?php
namespace Queue;

class Queue_Db {
    private $_redis;

    private $_tb_prefix = 'QUEUE_TABLE_';

    private $_tb_num = 2;

    private $_tb_tmp = 'TMP_TABLE';

    public function __construct()
    {
        if (!extension_loaded('redis')) {
            throw new \Exception('Redis failed to load');
        }
        $this->_redis = new \Redis();
        $this->_redis->connect(REDIS_HOST, REDIS_PORT);
    }

    public function push($value) {
        try {
            return $this->_redis->lPush($this->_tb_prefix . rand(1, $this->_tb_num), $value);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     *  取得所有的list key（表）
     */
    public function scan() {
        $list_key = array();
        for ($i = 1; $i <= $this->_tb_num; $i++) {
            $list_key[] = $this->_tb_prefix . $i;
        }
        return $list_key;
    }

    public function pop($key, $time = '') {
        try {
            if ($result = $this->_redis->brPop($key)) {
                return $result[1];
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function clear() {
        $this->_redis->flushAll();
    }
}