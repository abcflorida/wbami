<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once( __DIR__ . '/OcHelperTrait.php');

require_once( DIR_SYSTEM . '/library/ami/Customer.php' );
require_once( DIR_SYSTEM . '/library/ami/User.php' );
require_once( DIR_SYSTEM . '/library/ami/Aircraft.php' );

use \wbparts\ami\User as User;
use \wbparts\ami\Customer as InvCustomer;
use \wbparts\ami\Aircraft as Aircraft;

class ControllerModuleamiAircraft extends Controller {

        use OcHelperTrait;
        
        public function __construct( ) {
            global $registry;
            parent::__construct($registry);
            
            $this->user = new User( $this->customer, $registry->get('db') );
        }
        
	public function index() {
            
                $data = '';
                $module_name = 'amiplane';
                
		$language = $this->load->language('module/ami/aircraft');
                
                $data = $this->makeVariables($language, $module_name, $data);
                
                $this->document->setTitle($data['heading_title']);
                
                $this->document->addStyle( 'catalog/view/theme/wb/stylesheet/ami_aircraft.css');
                $this->document->addScript( 'catalog/view/theme/wb/js/ami_aircraft.js');

                $data['header'] = $this->load->controller('module/ami/common/header');
                $data['column_left'] = $this->load->controller('module/ami/common/column_left');
                $data['footer'] = $this->load->controller('module/ami/common/footer');

		$this->response->setOutput($this->load->view('wb/template/aircraft.tpl', $data));
	}
        
        public function tile( $data ) {
            
            $ami_customer = new Aircraft( $this->db, $this->user );
            
            $data['tile_name'] = 'Airplanes';
            $data['counts'] = $ami_customer->getAircraftSummary();
            $data['performance'] = " 4%";
            $data['caret_dir'] = "up";
            $data['fa_icon'] = 'user';
            $data['url'] = '?route=/module/ami/aircraft';
            
            return $this->load->view('wb/template/common/tile.tpl', $data);
        }
}