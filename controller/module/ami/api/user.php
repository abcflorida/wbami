<?php
/** This is an api template for the wbwo system.
 * this template is a wrapper class for the /system/wbwo library or the opencart implementation of that library.  Some 
 * responses may be purely ajax and some may be html... reference ApiTrait for implementation
 */

// Reponse Handler
require_once ( 'ApiTrait.php');
require_once( DIR_SYSTEM . '/library/ami/User.php' );

use \wbparts\ami\User as User;

class ControllerModuleamiApiUser extends Controller {
    
    use ApiTrait;
    
    protected $vars;
    
    public function __construct () {
        
        global $registry;
        parent::__construct($registry);
        
        if ( !$registry->get('amiuser') ) {
            $this->user = new User( $this->customer, $registry->get('db') );
            $registry->set('amiuser', $this->user);
        }
        else {
            $this->user = $registry->get('amiuser');

        }
        
        // for api set put/post/delete variable to the $this->vars object
        $this->request_type = $this->request->server['REQUEST_METHOD'];
        
        // set this->vars from METHOD params
        $this->initializeVariables();
        
    }
    
    public function index () {
        if ( $this->request_type == 'GET') {
            
            $this->getUsers();
            
        } elseif ( $this->request_type == 'PUT') {
            
            $this->add();
            
        } elseif ( $this->request_type == 'POST') {
            
            $this->update();
            
        } elseif ( $this->request_type == 'DELETE') {
            
            $this->delete();
            
        } else {
            throw new Exception("invalid submission type for api");
        }
    }
    
    /** 
     * Add user.
     * Response type determines whether to act like a rest api, json formatted html partial template, or pure html partial
     */
    public function add ( ) {
        try { 
            
            if ( $this->vars['response_type'] == 'json' ) {
                $new_user_id = $this->user->makeUser( $this->vars );
                $output = array( "user_id"=>$new_user_id );
            } else {
                //echo 'gethtml output via ajax';
                $output = $this->load->controller('module/ami/settings/adduser', $this->vars );
            }
            
            $this->render( $this->vars['response_type'], $output );
            
        } catch (Exception $ex) {
            echo $this->renderError( $this->vars['response_type'], $ex->getMessage() );
        }
            
        
    }
    
    //update User
    public function update ( ) {

        echo 'update';
        
    }
    
    //delete User
    public function delete ( ) {
        
        try {
            
            
            
        } catch (Exception $ex) {
            
        }
        
        
    }
    
    
    //list users
    public function view ( ) {
        
        $this->user->getUsers();
        
    }
    

    
}
