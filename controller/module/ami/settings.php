<?php
require_once( __DIR__ . '/OcHelperTrait.php');
require_once( DIR_SYSTEM . '/library/ami/User.php' );

use \wbparts\ami\User as User;

class ControllerModuleamiSettings extends Controller {

        use OcHelperTrait;
        
        public function __construct( ) {
            global $registry;
            parent::__construct($registry);
            
            // must be logged in 
            if ( !$this->customer->isLogged() ) {
                $this->response->redirect($this->url->link('module/ami/common/login') );
            }
                        
            if ( !$registry->get('amiuser') ) {
                $this->user = new User( $this->customer, $registry->get('db') );
                $registry->set('amiuser', $this->user);
            }
            else {
                $this->user = $registry->get('amiuser');
                
            }
            
            $this->default_sort[] = array( "field" =>"create_date","order"=>"1","dir"=>"desc" );
            $this->sortable_columns = array("username");
        }
        
	public function index() {
            
            try { 
                $data = '';

                /** OC LOGIC **/
                $language = $this->load->language('module/ami/settings');

                $module_name = 'wbwosettings';

                $data = $this->makeVariables($language, $module_name, $data);            

                $this->document->setTitle($this->language->get('text_heading_title'));

                $data['heading_title'] = $this->language->get('text_heading_title');

                $this->document->addScript( 'catalog/view/theme/wb/js/forms.js');
                $this->document->addScript( 'catalog/view/theme/wb/js/settings.js');

                $data['header'] = $this->load->controller('module/ami/common/header');
                $data['column_left'] = $this->load->controller('module/ami/common/column_left');
                $data['footer'] = $this->load->controller('module/ami/common/footer');
                /** END OC LOGIC **/
                
                /** WBWO LOGIC **/
                $filters['paging'] = array("start"=>1, "limit" => 10);
                $filters['sort'] = $this->buildSortFromGet();
                
                $data['columns'] = $this->buildSortParams();
                
                $users['users'] = $this->user->getUsers( $filters );

                $data['users'] = $this->load->view('wb/template/partials/user_table.tpl', $users );

                $this->response->setOutput($this->load->view('wb/template/settings/index.tpl', $data));
            } catch ( Exception $ex ) {
                
                $data['error_title'] = "Error Encountered";
                $data['error_message'] = $ex->getMessage();
                $data['error_details'] = "Standard error message";
                
                
                $this->response->setOutput($this->load->view( 'wb/template/error_handler.tpl', $data ));
            }
            
            
	}
        
        public function usertable () {
            
            $filters['sort'][] = array( "field" =>"create_date","order"=>"1","dir"=>"desc" );
            $filters['paging'] = array("start"=>1, "limit" => 500);
            //$user_filter = array("")
            
            //print_r ( $filters );
            
            $users['users'] = $this->user->getUsers( $filters );
            
            //print_r ( $users );
                    
        }
        
        
        /** API Responses - you can only call these methods from another method, not as an endpoint 
        **/
        public function addUser ( $args ) {
            
            $access_type = ( !isset( $args['access_type'] ) ) ? 'standard'  : $args['access_type'];
            
            try {
                // you can only add a standard user ( employee ) 
                $new_user_id = $this->user->makeUser( $args );
                
                if ( is_numeric( $new_user_id ) )  {
                    
                    $user_title = ( $this->user->getCompanyName() !== '' ) ? $this->user->getCompanyName() : $this->user->getCustomerName();
                    
                    $mail_params['email'] = $args['email'];
                    $mail_params['subject'] = "WBExpress AMI Management Control Notification";
                    $mail_params['message'] = $args['firstname'] . " " . $args['lastname'] . "<br/>"
                            . $user_title . " wants you to be part of the company in the WBParts AMI Inventory Management Solution.  Please login "
                            . "or register at wbpartsexpress.com to participate";
                    
                    
                    $this->sendEmail( $mail_params );
                }
                
                
                $filters['sort'][] = array( "field" =>"create_date","order"=>"1","dir"=>"desc" );
                $filters['paging'] = array("start"=>1, "limit" => 10);

                $data['users'] = $this->user->getUsers( $filters );
                                
                if ( $access_type == 'standard' ) {

                    $this->load->view('wb/template/partials/user_table.tpl', $data );

                } elseif ( $access_type == 'api' ) {
                    
                    return  $this->load->view('wb/template/partials/user_table.tpl', $data );
                }
            } catch (Exception $ex) {
                
                throw new Exception("Error Processing Request Add User:" . $ex->getMessage(), 400);
            }
                
        }
        
        
        private function buildSortFromGet() {
            
            if ( isset( $this->request->get['sort']) ) {
                
                if ( $this->request->get['sort'] == 'username' ) {
                    $sort[] = array( "field" =>"lastname","order"=>"1","dir"=>$this->getSortDir('username') );
                }
                
                return $sort;
            } else {
                return $this->default_sort;
            }
            
        }
        
        
}