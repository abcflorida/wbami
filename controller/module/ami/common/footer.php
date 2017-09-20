<?php
class ControllerModuleamiCommonFooter extends Controller {
	public function index() {
		$this->load->language('module/management/common/footer');

		$data['text_footer'] = "WB Work Order Management System";

		if ($this->customer->isLogged() && isset($this->request->get['token']) && ($this->request->get['token'] == $this->session->data['token'])) {
			$data['text_version'] = sprintf($this->language->get('text_version'), VERSION);
		} else {
			$data['text_version'] = '';
		}

		return $this->load->view('wb/template/common/footer.tpl', $data);
	}
}