<?php

/**
 * Class bikeRedis
 */
class bikeRedis
{
    /**
     * @var Redis
     */
    private $redis;


    /**
     * bikeRedis constructor.
     */
    public function __construct()
    {
        $this->redis = new redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    /**
     * LngLatList 添加一条数据
     *
     * @param $date
     * @return int
     * @internal param $name
     */
    public function addLngLatList($date)
    {
        return $this->redis->rpush('lngLatList',$date);
    }

    /**
     * 输出 LngLatList 数据
     *
     * @param int $f
     * @param int $l
     * @return array
     * @internal param $name
     */
    public function showLngLatList($f = 0, $l = -1)
    {
        return $this->redis->lrange('lngLatList', $f, $l);
    }

    /**
     * 删除 LngLatList 一条数据
     * @return string
     * @internal param $name
     */
    public function delLngLatList()
    {
        return $this->redis->lpop('lngLatList');
    }

    /**
     * 删除一个key，清空那些数据
     *
     * @param $name
     * @return int
     */
    public function del($name)
    {
        return $this->redis->del($name);
    }

    /**
     * 输出redis类
     *
     * @return Redis
     */
    public function getRedis()
    {
        return $this->redis;
    }

}
