<?php
require_once( DIR_SYSTEM . '/library/ami/User.php' );

if ( !trait_exists('OcHelperTrait')) {
    require_once  ( __DIR__ . '/../OcHelperTrait.php' );
}

use \wbparts\ami\User as User;

class ControllerModuleAmiCommonMenu extends Controller {
    
        use OcHelperTrait;
        
        public function __construct() {
            
            global $registry;
            parent::__construct($registry);
            
            if ( !$registry->get('amiuser') ) {
               $this->user = new User( $this->customer, $registry->get('db') );
               $registry->set('amiuser', $this->user);
            }
            else {
               $this->user = $registry->get('amiuser');
            }
        }
        
	public function index( $data ) {
            
		$language = $this->language->load('module/ami/common/menu');

                $module_name = 'woadminmenu';
                               
                $data = $this->makeVariables($language, $module_name, $data);
                
                $data['company_name'] = $this->user->getCompanyName();
                $data['customer_name'] = $this->customer->getFirstName() . " " . $this->customer->getLastName();
                
		return $this->load->view('wb/template/common/menu.tpl', $data);
	}
}