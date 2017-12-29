<?php
class ControllerErrorPermission extends Controller {
	public function index() {
		$this->response->setOutput($this->load->view('error/permission'));
	}
}
