<?php
/**
 * Created by PhpStorm.
 * User: estronger
 * Date: 2016/12/12
 * Time: 13:23
 */
class ControllerOperatorPropelling extends Controller {
    public function push() {
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $this->load->library('instructions/instructions');

        } else {
            $this->response->showErrorResult('Request require post!');
        }
    }
}