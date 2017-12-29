<?php
class ControllerStartupEvent extends Controller {
    public function index() {
        $rows = $this->db->table('event')->where(array('trigger' => array('like', '%' . 'api')))->order('event_id ASC')->select();
        foreach ($rows as $result) {
            $this->event->register(substr($result['trigger'], strpos($result['trigger'], '/') + 1), new Action($result['action']));
        }
    }
}