<?php
class ControllerModuleAmiCommonColumnLeft extends Controller {
	public function index() {
            
		//if (isset($this->request->get['token']) && isset($this->session->data['token']) && ($this->request->get['token'] == $this->session->data['token'])) {
			//$data['profile'] = $this->load->controller('common/profile');
			$data['menu'] = $this->load->controller('module/ami/common/menu');
			$data['stats'] = $this->load->controller('module/ami/common/stats');

			return $this->load->view('/wb/template/common/column_left.tpl', $data);
		//}
	}
}