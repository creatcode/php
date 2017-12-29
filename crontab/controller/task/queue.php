<?php
class ControllerTaskQueue extends Controller {
    public function index() {
        echo 'hello';exit();
        if (ob_get_level()) ob_end_clean();

        $this->load->library('logic/queue', true);
        $this->load->library('queue/queue_server');

        $queues = $this->queue_queue_server->scan();

        while (true) {
            $content = $this->queue_queue_server->pop($queues, 1800);
            if (is_array($content)) {
                $method = key($content);
                $arg = current($content);
                //只带一个参数，最好用数组
                $result = $this->logic_queue->$method($arg);
                if (!$result['state']) {
                    //记录错误信息
                    //$this
                }
            } else {

            }
        }
    }
}