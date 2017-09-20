<?php
class ControllerModuleAmiCommonLogout extends Controller {
	public function index() {
		$this->customer->logout();

		unset($this->session->data['token']);

		$this->response->redirect($this->url->link('module/ami/dashboard', '', 'SSL'));
	}
}