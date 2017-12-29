<?php
class ControllerErrorNotFound extends Controller {
	public function index() {
		$this->response->setOutput($this->load->view('error/not_found'));
	}
}