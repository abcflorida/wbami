<?php
class ControllerModuleAmiCommonStats extends Controller {
	public function index() {	
		return $this->load->view('wb/template/common/stats.tpl');
	}
}