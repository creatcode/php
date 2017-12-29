<?php
namespace Queue;
class Queue_Client {
	private static $queueDb;

    public static function push($key, $value, $registry) {
        if (!QUEUE_OPEN) {
            $queue = new \Logic\Queue($registry);
            $queue->$key($value);return false;
        }
        if (!is_object(self::$queueDb)) {
            self::$queueDb = new QueueDb();
        }
        return self::$queueDb->push(serialize(array($key => $value)));
    }
}