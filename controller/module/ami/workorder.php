<?php
require_once( __DIR__ . '/OcHelperTrait.php');

require_once( DIR_SYSTEM . '/library/ami/User.php' );
require_once( DIR_SYSTEM . '/library/ami/WorkOrder.php' );


use \wbparts\ami\User as User;
use \wbparts\ami\Workorder as Workorder;

class ControllerModuleamiWorkorder extends Controller {
    
        use OcHelperTrait;
        
        public function __construct( ) {
            global $registry;
            parent::__construct($registry);
            
            if ( !$registry->get('amiuser') ) {
                $this->user = new User( $this->customer, $registry->get('db') );
            }
            else {
                $this->user = $registry->get('amiuser');
                $registry->set('amiuser', $this->user);
            }
            
            $this->wo = new Workorder( $this->customer, $registry->get('db') );
        }
        
	public function index() {
            
                $data = '';
                $module_name = 'wbwoworkorder';
                
		$language = $this->load->language('module/ami/workorder');
                                
                $data = $this->makeVariables($language, $module_name, $data);
		//$data['heading_title'] = $data['text_' . $module_name . '_heading_title'];

                $this->document->setTitle($this->language->get('text_heading_title'));
                
                $this->document->addStyle( 'catalog/view/theme/wb/stylesheet/workorder.css');
                $this->document->addScript( 'catalog/view/theme/wb/js/workorder.js');
        
                
                

                $data['header'] = $this->load->controller('module/ami/common/header');
                $data['column_left'] = $this->load->controller('module/ami/common/column_left');
                $data['footer'] = $this->load->controller('module/ami/common/footer');


		$this->response->setOutput($this->load->view('wb/template/workorder/index.tpl', $data));
	}
        
        /** This is a widget function 
         * outputs an html tile with performance data
         * data can be loaded from the caller so any one function doesn't have to do everything
         * @param array $data
         * @return string [html]
         */
        public function tile( $data ) {
            
            //$ami_customer = new Squawks( $this->db, $this->user );
            $data['tile_name'] = 'Work Order';
            $data['counts'] = array("count"=>23);
            $data['performance'] = " 23%";
            $data['caret_dir'] = "up";
            $data['fa_icon'] = 'table';
            $data['url'] = '?route=module/ami/workorder';
            
            return $this->load->view('wb/template/common/tile.tpl', $data);
        }
        
        /** get a widget for work order activity 
         */
        public function recentActivityWidget() {
            
            $data['fields'] =array('name,date,action');
            $data['activity_title'] = 'Work Order';
            $data['fa_icon'] = 'calendar';

            $data['activity_rows'] = $this->wo->getRecentActivity( $this->user_id, 10, 'desc' );
                    
            return $this->load->view('wb/template/widgets/recentactivity.tpl', $data);
            
        }
}