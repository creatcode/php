<?php
class ControllerCommonLogout extends Controller {
	public function __construct($registry)
	{
		parent::__construct($registry);
	}

	public function index() {
		$this->logic_admin->logout();

		unset($this->session->data['token']);

		$this->response->redirect($this->url->link('common/login', '', true));
	}
}