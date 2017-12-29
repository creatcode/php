<?php
class ControllerStartupRouter extends Controller {
    public function index() {
        if (isset($this->request->get['route']) && $this->request->get['route'] != 'startup/route') {
            $route = $this->request->get['route'];
        } else {
            $route = $this->config->get('action_default');
        }

        $data = array();

        $route = str_replace('../', '', (string) $route);

        $result = $this->event->trigger('controller/' . $route . '/before', array(&$route, &$data));

        if (!is_null($result)) {
            return $result;
        }

        $action = new Action($route);

        $output = $action->execute($this->registry, $data);

        $result = $this->event->trigger('controller/' . $route . '/after', array(&$route, &$data));

        if (!is_null($result)) {
            return $result;
        }

        return $output;
    }
}