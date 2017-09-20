<?php
error_reporting( E_ALL );
ini_set('display_errors', 1);

require_once( __DIR__ . '/OcHelperTrait.php');

require_once( DIR_SYSTEM . '/library/ami/User.php' );
require_once( DIR_SYSTEM . '/library/ami/Inventory.php' );
require_once( DIR_SYSTEM . '/library/ami/AppLog.php' );


use \wbparts\ami\User as User;
use \wbparts\ami\Inventory as Inventory;
use \wbparts\ami\AppLog as Log;

class ControllerModuleamiInventory extends Controller {
    
        use OcHelperTrait;
        
        public function __construct( ) {
            global $registry;
            parent::__construct($registry);
            
            $this->module_name = 'inventory';
            
            if ( !$this->customer->isLogged() ) {
                $this->response->redirect($this->url->link('module/ami/dashboard') );
            }
            
            if ( !$registry->get('amiuser') ) {
                $this->user = new User( $this->customer, $registry->get('db') );
            }
            else {
                $this->user = $registry->get('amiuser');
            }
            
            $this->inv = new Inventory( $this->user, $registry->get('db') );
            
            $this->sortable_columns = array("partnum","quantity","price");
            
            $default_sort[] = array( "field" =>"partnum","order"=>"1","dir"=>"asc" );
            $default_sort[] = array( "field" =>"serial_number","order"=>"2","dir"=>"desc" );
            $default_sort[] = array( "field" =>"quantity","order"=>"3","dir"=>"desc" );
            
            $this->default_sort = $default_sort;
            
            $this->log = new Log( $registry->get('db') );
            
        }
        
        
        
        /** home page for inventory module.  
         * This should be like the products index page
         */
	public function index() {
            
            $data = $this->bootstrap('list');

            if ( isset( $this->request->get['message'] ) ) {
                $notification['type'] = 'success';
                $notification['fa_icon'] = 'exclamation-circle';
                $notification['message'] = $this->request->get['message'];

                $data['messages'] = $this->load->view('wb/template/common/messages.tpl', $notification);
            }
            
            
            $data['columns'] = $this->buildSortParams();
            $data['filter_params'] = '';
            
            
            $filter_fields =  $this->prepareFilterFields( $this->request->get );

            $data['filter_params'] = $this->buildUrlParams( $filter_fields );
            $data['defaults'] = $this->buildDefaultsFormValues( $filter_fields );

            $filters['filter'] =  $filter_fields;
            $filters['paging'] = array("start"=>0, "limit" => 100);
            $filters['sort'] = $this->buildSortFromGet();

            

            if ( isset( $this->request->get['export']) ) {
                
                $filters['paging'] = array("start"=>0, "limit" => 10000);
                
                $this->inv->setIsSearch( true );
                $inventory['inventory'] = $this->inv->getInventoryRows( $filters );
                
                $columns = array('guid','inventory_dtl_id','partnum', 'description', 'variation','quantity','reorder_quantity','cost','price','location','part_condition');
                
                $this->exportCsv( $inventory['inventory'], $columns );
                die();

            }
            else {
                /* do a search */
                if ( isset( $this->request->get['search']) ) {

                    $data['search'] = true;
                    $data['search_string'] = "";
                    
                    $inventory['inventory'] = $this->inv->search( $filters );

                /** List all inventory **/    
                } else {

                    $data['search'] = false;
                    $data['search_string'] = "&search=1";
                    
                    // set dropdown defaults
                    $data['defaults']['active'] = 1;

                    if ( isset( $this->request->get['export']) ) {
                        $inventory['inventory'] = $this->inv->getInventoryRows( $filters );

                    } else {
                        $inventory['inventory'] = $this->inv->getInventoryList( $filters );
                    }

                }
                
                $data['inventory'] = $this->load->view('wb/template/inventory/partials/inventory_table.tpl', $inventory );
                $this->response->setOutput($this->load->view('wb/template/inventory/index.tpl', $data));

            }

	}
        
        /** form for adding new inventory and detail **/
        public function add ( ) {
            
            try {
                
                $data = $this->bootstrap('Add');
                $data['action'] = 'add';
                
		$this->response->setOutput($this->load->view('wb/template/inventory/inventory_form.tpl', $data));
                
            } catch ( Exception $ex ) {

                $this->renderError ( $ex, 'inventory - add', "add to inventory failed while loading inventory add form.php " . __FILE__ . " "  .__METHOD__);
            }
            
        }

        public function edit() {
            try {
                
                $data = $this->bootstrap('Edit');
                
                // redirect if invalid argument
                if ( !isset( $this->request->get['inventory_id'] ) ) {
                    $this->response->redirect($this->url->link('module/ami/inventory&message=Error - No inventory detail id provided', 'token=' . $this->session->data['token'], 'SSL'));
                }
                
                // set inventory object
                //$ami_inventory = new InvCustomer( $this->db, $this->user );
                // prepare a specific filter for one inventory lookup
                $filter_fields =  $this->prepareFilterFields( array("filter_inventory_id"=>$this->request->get['inventory_id']) );
                $filters['filter'] = $filter_fields;
                
                // do the search
                $inventory = $this->inv->getInventoryRows( $filters );

                $data['inventory'] = $inventory[0];

                $data['inventory_details'] = $inventory;
                $data['action'] = 'update';                

		$this->response->setOutput($this->load->view('wb/template/inventory/inventory_form.tpl', $data));
                
                
                
                
            } catch ( Exception $ex ) {

                $this->renderError ( $ex, 'inventory - edit', "update to inventory failed while loading inventory edit form.php " . __FILE__ . " "  .__METHOD__);
               
                
            }
        }
        
        public function getImportTemplate () {

                //$filters['paging'] = array("start"=>0, "limit" => 10000);
                
                //$this->inv->setIsSearch( true );
                $inventory['inventory'] = null;
                
                $columns = array('guid','inventory_dtl_id','partnum', 'description', 'variation','quantity','reorder_quantity','cost','price','location','part_condition');
                
                $this->exportCsv( $inventory['inventory'], $columns );
                die();
            
        }
        
        /** import form
         * 
         * @throws Exception
         */
        public function import() {
            try {
                $data = $this->bootstrap();
                
                $uploadOk = 1;
                
                
                if ( $this->validateUpload() ) {
                    
                    $handle = fopen( $this->request->files['import_file']['tmp_name'], "r" );

                    $this->inv->importInventoryFromFile( $handle );

                    $data['messages'] = "Upload Complete - go to stock to view";
                    
                    if ( isset( $data['messages'] ) ) {
                        $notification['type'] = 'success';
                        $notification['fa_icon'] = 'exclamation-circle';
                        $notification['message'] = $data['messages'];

                        $data['messages'] = $this->load->view('wb/template/common/messages.tpl', $notification);
                    }
                    
                } else {
                    
                }
                
                //$data['messages'] = array("message"=>"successful upload", "type"=>"success");
                
                $this->response->setOutput($this->load->view('wb/template/inventory/import_form.tpl', $data));
                
            } catch ( Exception $ex ) {
               $title="Import Validation Error";
               $data['messages'] = $this->inv->getMessages();
               $this->renderError ( $ex, 'inventory - import', "import failed on " . __FILE__ . " "  .__METHOD__ . " inventory.php", $data['messages'], $title );
               
            }    
        }
        
        /** This is an action page, we should be directed back to edit, or add **/
        public function update () {
            try {
                $data = $this->bootstrap();
                
                if ( !isset( $this->request->post['action']) ) {
                    throw new Exception("No action provided");
                } else {
                    // add a new record(s)
                    if ( $this->request->post['action'] == 'add' ) {
                        
                        $args = $this->request->post;
                        $args['owner_id'] = $this->user->getOwnerId();
                    
                        $messages = $this->inv->makeInventoryItem( $args );
                        
                        if ( is_numeric( $messages ) ) { 
                            $message = 'successfully added inventory';
                        } else { 
                            $message = 'Error adding inventory';
                        }
                        
                    // update the inventory and details
                    } else {
                        
                        try {

                            // update the inventory header
                            $this->inv->changeInventoryItem( $this->request->post );
                            
                            // update the inventory detail records
                            $this->inv->changeInventoryDetail( $this->request->post );

                            $message = "inventory updated";
                        } catch ( Exception $ex ) {
                            echo 'eroeriuerouier';
                            $message = $ex->getMessage();
                        }
                        
                    }
                    
                    $this->response->redirect($this->url->link('module/ami/inventory&message=' . $message, 'token=' . $this->session->data['token'], 'SSL'));

                }
            
             } catch ( Exception $ex ) {
                
                $this->renderError ( $ex, 'inventory - update', "update failed on " . __FILE__ . " "  .__METHOD__ . " inventory.php" );
               
            }
            
        }
        
        private function validateUpload ( ) {
        
            if ( isset( $this->request->post['action'] ) ) {
                    // check import
                    if ( $this->request->post['action'] == 'import' ) {
                        
                        if ( count( $this->request->files ) > 0 ) {
                            return true;
                        } else {
                            throw new Exception ( "Unexpected upload error - no file found to upload." );
                        }
                    }
            }
            
            return false;
        }
        
        
        
        
        /** This is a widget function 
         * outputs an html tile with performance data
         * data can be loaded from the caller so any one function doesn't have to do everything
         * @param array $data
         * @return string [html]
         */
        public function tile( $data ) {
            
            //$ami_customer = new Inventorys( $this->db, $this->user );
            $data['tile_name'] = 'Inventory';
            $data['counts'] = array("count"=>23);
            $data['performance'] = " 23%";
            $data['caret_dir'] = "up";
            $data['fa_icon'] = 'table';
            $data['url'] = '?route=module/ami/inventory';
            
            return $this->load->view('wb/template/common/tile.tpl', $data);
        }
        
        /** widget data 
         * 
         * @return array
         */
        public function recentTransactionsWidget () {

        $data['transactions'] = $this->inv->getRecentTransactions( $this->user_id, 10, 'desc' );
                    
        return $this->load->view('wb/template/widgets/recenttransactions.tpl', $data);
        
        return $transactions;
        
    }
    
    /** make an array of sort objects
    * 
    * this is pushed into the Customer Object
    * @return array
    */
   /* private function buildSortFromGet() {

        if ( isset( $this->request->get['sort']) ) {

            if ( $this->request->get['sort'] == 'partnum' ) {
                $sort[] = array( "field" =>"company","order"=>"1","dir"=>$this->getSortDir('company') );
            }

            if ( $this->request->get['sort'] == 'create_date' ) {
                $sort[] = array( "field" =>"create_date","order"=>"1","dir"=>$this->getSortDir('create_date') );
            }

            return $sort;
        } else {

            //print_r( $this->default_sort );
            $sort = $this->default_sort;
            return $sort;
        }

    }
    */
    
}