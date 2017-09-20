<?php
error_reporting( E_ALL );
ini_set('display_errors', 1);

require_once( __DIR__ . '/OcHelperTrait.php');

require_once( DIR_SYSTEM . '/library/ami/Customer.php' );
require_once( DIR_SYSTEM . '/library/ami/User.php' );

use \wbparts\ami\User as User;
use \wbparts\ami\Customer as InvCustomer;

class ControllerModuleamiCustomer extends Controller {

        use OcHelperTrait;
                
        public function __construct( ) {
            global $registry;
            parent::__construct($registry);
            
            $this->module_name = 'customer';
            if ( !$this->customer->isLogged() ) {
                $this->response->redirect($this->url->link('module/ami/dashboard') );
            }
                        
            if ( !$registry->get('amiuser') ) {
                $this->user = new User( $this->customer, $registry->get('db') );
            }
            else {
                $this->user = $registry->get('amiuser');
            }
            
            $this->sortable_columns = array("company","create_date");
            
            $default_sort[] = array( "field" =>"company","order"=>"1","dir"=>"desc" );
            $default_sort[] = array( "field" =>"create_date","order"=>"1","dir"=>"desc" );
            $this->default_sort = $default_sort;

        }
        
	public function index() {
            
            try {
                                
                $data = $this->bootstrap('list');
                
                if ( isset( $this->request->get['message']) ) {
                    
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
                
                $ami_customer = new InvCustomer( $this->db, $this->user );
                /** Search process **/
                   
                
                $filters['filter'] =  $filter_fields;
                $filters['paging'] = array("start"=>0, "limit" => 100);
                $filters['sort'] = $this->buildSortFromGet();
                
                if ( !isset( $this->request->get['search']) ) {
                    $data['search'] = false;
                    $customers['customers'] = $ami_customer->getCustomerList( $filters );
                } else {
                    $data['search'] = true;
                    $customers['customers'] = $ami_customer->search( $filters );
                }
                                
                if ( isset( $this->request->get['export'])) {
                    $columns = array('first_name', 'last_name','phone_mobile', 'phone_office', 'company','create_date');
                    $this->exportCsv( $customers['customers'], $columns );
                    die();
                } else {
                    
                    $data['customers'] = $this->load->view('wb/template/customer/partials/customer_table.tpl', $customers );
                    $this->response->setOutput($this->load->view('wb/template/customer/index.tpl', $data));
                    
                }
            } catch ( Exception $ex ) {

                $data['error_title'] = "Error Encountered";
                $data['error_message'] = $ex->getMessage();
                $data['error_details'] = "Standard error message";


                $this->response->setOutput($this->load->view( 'wb/template/error_handler.tpl', $data ));
            }
            
	}
        
        public function add() {
            try {
                
                $data = $this->bootstrap('Add');
                $data['action'] = 'add';
                
		$this->response->setOutput($this->load->view('wb/template/customer/customer_form.tpl', $data));
                
            } catch ( Exception $ex ) {

                $data['error_title'] = "Error Encountered";
                $data['error_message'] = $ex->getMessage();
                $data['error_details'] = "Standard error message";


                $this->response->setOutput($this->load->view( 'wb/template/error_handler.tpl', $data ));
            }
        }
        
         public function edit() {
            try {
                
                $data = $this->bootstrap('Edit');
                
                // redirect if invalid argument
                if ( !isset( $this->request->get['customer_id'] ) ) {
                    $this->response->redirect($this->url->link('module/ami/customer&message=Error - No customerid provided', 'token=' . $this->session->data['token'], 'SSL'));
                }
                
                // set customer object
                $ami_customer = new InvCustomer( $this->db, $this->user );
                // prepare a specific filter for one customer lookup
                $filter_fields =  $this->prepareFilterFields( array("filter_customer_id"=>$this->request->get['customer_id']) );
                $filters['filter'] = $filter_fields;
                
                // do the search
                $customer = $ami_customer->search( $filters );

                $data['customer'] = $customer[0];
                $data['action'] = 'update';                
                
                print_r ( $data['customers']);
                
		$this->response->setOutput($this->load->view('wb/template/customer/customer_form.tpl', $data));
                
                
                
                
            } catch ( Exception $ex ) {

                $data['error_title'] = "Error Encountered";
                $data['error_message'] = $ex->getMessage();
                $data['error_details'] = "Standard error message";


                $this->response->setOutput($this->load->view( 'wb/template/error_handler.tpl', $data ));
            }
        }
        
        /** This is an action page, we should be directed back to edit, or add **/
        public function update () {
            try {
                $data = $this->bootstrap();
                
                if ( !isset( $this->request->post['action']) ) {
                    throw new Exception("No action provided");
                } else {
                    
                    $ami_customer = new InvCustomer( $this->db, $this->user );
                    
                    if ( $this->request->post['action'] == 'add' ) {
                    
                        $messages = $ami_customer->makeCustomer( $this->request->post );
                        
                        if ( is_numeric( $messages ) ) { 
                            $message = 'successfully added customer';
                        } else { 
                            $message = 'Error adding customer';
                        }
                        
                    
                    } else {
                        $messages = $ami_customer->changeCustomer( $this->request->post );
                        
                        if ( $messages ) { 
                            $message = 'successfully updated customer';
                        } else { 
                            $message = 'Error updating customer';
                        }

                    }

                    $this->response->redirect($this->url->link('module/ami/customer&message=' . $message, 'token=' . $this->session->data['token'], 'SSL'));

                }
            
             } catch ( Exception $ex ) {
                $data = $this->bootstrap();
                $data['error_title'] = "Error Encountered";
                $data['error_message'] = $ex->getMessage();
                $data['error_details'] = "Standard error message";


                $this->response->setOutput($this->load->view( 'wb/template/error_handler.tpl', $data ));
            }
            
        }
        
        
        /** view customer workorders - short report
         * @output
         * @return void
        */
        public function wo () {
            
            $data['user_id'] = $this->user->getUserId();
            $data['customer'] = "John tradent";
            
            $this->response->setOutput( $this->load->view('wb/template/workorder/list_by_customer.tpl', $data) );
            
        }
        
        /** view customer workorders - short report
         * @output
         * @return void
        */
        public function tn () {
            
            $data['user_id'] = $this->user->getUserId();
            $data['customer'] = "John tradent";
            
            $this->response->setOutput( $this->load->view('wb/template/aircraft/list_by_customer.tpl', $data) );
            
        }
                
        /** modal form for adding customer.
         * good for modals and ajax forms
         * 
         * @return void
        */
        public function form () {
            
            $data['user_id'] = $this->user->getUserId();
  
            $this->response->setOutput( $this->load->view('wb/template/customer/quick_add.tpl', $data) );
            
        }
        
        
        /** This is a widget for the dashboard */
        public function tile( $data ) {
            
            $ami_customer = new InvCustomer( $this->db, $this->user );
            
            $data['tile_name'] = 'Customers';
            $data['counts'] = $ami_customer->getCustomerSummary();
            //$data['performance'] = "";
            $data['caret_dir'] = "up";
            $data['fa_icon'] = 'user';
            $data['url'] = '?route=module/ami/customer';
            
            return $this->load->view('wb/template/common/tile.tpl', $data);
        }
        
        /** build the content for the basic customer page and put it in the $data property 
        private function bootstrap ( $module_name, $submodule = '' ) {
            
            $data = '';
            //$module_name = 'customer';
            $language = $this->load->language('module/ami/' . $module_name);
            
            $data = $this->makeVariables($language, $module_name, $data);
            $data['heading_title'] = $data['text_' . $module_name . '_heading_title'];

            $this->document->setTitle($data['heading_title'] . " " . $submodule );
            $this->loadScripts($module_name);

            $data = $this->loadCommonModules($data);
            
            return $data;

        }
        **/
        /** make an array of sort objects
         * 
         * this is pushed into the Customer Object
         * @return array
         
        private function buildSortFromGet() {
     
            if ( isset( $this->request->get['sort']) ) {
                
                if ( $this->request->get['sort'] == 'company' ) {
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