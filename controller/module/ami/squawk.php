<?php
require_once( __DIR__ . '/OcHelperTrait.php');

require_once( DIR_SYSTEM . '/library/ami/User.php' );
require_once( DIR_SYSTEM . '/library/ami/Squawk.php' );


use \wbparts\ami\User as User;
use \wbparts\ami\Squawk as Squawk;

class ControllerModuleamiSquawk extends Controller {

        use OcHelperTrait;
        
        public function __construct( ) {
            global $registry;
            parent::__construct($registry);
            
            $this->squawks = new Squawk( $this->customer, $registry->get('db') );
            
            if ( !$registry->get('amiuser') ) {
                $this->user = new User( $this->customer, $registry->get('db') );
            }
            else {
                $this->user = $registry->get('amiuser');
            }
        }
        
        /** There is no index, but we might want to put our squawk forms in here NOT in wo because it might get confusing
         * 
         */
        
        /** This is a widget function 
         * outputs an html tile with performance data
         * data can be loaded from the caller so any one function doesn't have to do everything
         * @param array $data
         * @return string [html]
         */
        public function tile( $data ) {
            
            //$ami_customer = new Squawks( $this->db, $this->user );
            $data['tile_name'] = 'Squawks';
            $data['counts'] = array("count"=>23);
            $data['performance'] = " 23%";
            $data['caret_dir'] = "up";
            $data['fa_icon'] = 'plane';
            $data['url'] = '?route=module/ami/squawk';
            
            return $this->load->view('wb/template/common/tile.tpl', $data);
        }
}