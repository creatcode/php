<?php
class ControllerStartupEvent extends Controller {
    public function index() {
        $results = $this->db->table('event')->where("`trigger` like 'admin/%'")->order('event_id ASC')->select();
        foreach ($results as $result) {
            $this->event->register(substr($result['trigger'], strpos($result['trigger'], '/') + 1), new Action($result['action']));
        }
    }
}