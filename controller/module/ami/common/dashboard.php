<?php
class ControllerModuleAmiCommonDashboard extends Controller {
	public function index( $data ) {
            
		$this->load->language('module/ami/common/dashboard');

		$this->document->setTitle($this->language->get('heading_title'));
		$data['heading_title'] = $this->language->get('heading_title');

                $data['header'] = $this->load->controller('module/ami/common/header');
                $data['column_left'] = $this->load->controller('module/ami/common/column_left');
                $data['footer'] = $this->load->controller('module/ami/common/footer');


		$this->response->setOutput($this->load->view('wb/template/common/dashboard.tpl', $data));
	}
}