<?php
/** This is an api template for the wbwo system.
 * this template is a wrapper class for the /system/wbwo library or the opencart implementation of that library.  Some 
 * responses may be purely ajax and some may be html... So, I am not sure how we want to manage this.  We probably should not use these routes 
 * if we are returning html
 * 
 * 
 */
require_once ( 'ApiTrait.php');

class ControllerModuleamiApiWorkorder extends Controller {
    
    use ApiTrait;
    
    protected $vars;
    
    public function __construct () {
        
        global $registry;
        parent::__construct($registry);
        
        // for api set put/post/delete variable to the $this->vars object
        $this->request_type = $this->request->server['REQUEST_METHOD'];

        // do not let this throw an ugly error
        try {
            //populate vars object
            $this->setVariables();
        } catch (Exception $e) {
            header("Content-Type: application/json", true);
            http_response_code(500);

            echo json_encode(array("message" => $e->getMessage(), "response" => "error setting values."));
            die();
        }
        
    }
    
    public function index () {
        if ( $this->request_type == 'GET') {
            
            $this->getWorkorders( $this->vars );
            
        } elseif ( $this->request_type == 'PUT') {
            
            $this->add( );
            
        } elseif ( $this->request_type == 'POST') {
            
            $this->update( );
            
        } elseif ( $this->request_type == 'DELETE') {
            
            $this->delete( $this->vars );
            
        } else {
            throw new Exception("invalid submission type for api");
        }
    }
    
    //Add user
    public function add ( $vars ) {

        
    }
    
    //update User
    public function update ( ) {

        //$this->load->controller('module/')
        
    }
    
    //delete User
    public function delete ( ) {
        
        echo 'delete';
        
    }
    
    public function getWorkorders () {
        
    }
    
    
}
