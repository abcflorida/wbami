<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Description of newPHPClass
 *
 * @author Administrator
 */
require_once( __DIR__ . '/OcHelperTrait.php');
require_once( DIR_SYSTEM . '/library/ami/User.php' );
use \wbparts\ami\User as User;

class ControllerModuleamiDashboard extends Controller {
    
    use OcHelperTrait;
    
    protected $action;
    protected $module;
    
    public function __construct() {
        
        global $registry;
        parent::__construct($registry);
        
        $login_required = false; 
        
        try {
            $this->user = new User( $this->customer, $registry->get('db') );
            $registry->set('amiuser', $this->user);
            
        } catch (Exception $ex) {
            $login_required = true;
            //echo 'login required';
        }
        
    }
    
    /** This is the workorder dashboard page.
     * This is the entry point for users and should ensure that users are logged in and that they have been initialized
     * Intialized means that they have a user record and are either "admin/owner", or "user of admin/owner"
     * 
     */
    public function index() {
        
        //print_r( $this->request->get );
        
        if ( !$this->customer->isLogged() ) {
            $this->load->controller('module/ami/common/login', $data);
            
        } else {
                    
        $data['needs_initialization'] = $this->user->needsIntialization();
        $data['initialization_widget'] = '';
        
        // no user exists - we need to initialize
        if ( $this->user->needsIntialization() ) {
            $data['initialization_widget'] = $this->load->view('wb/template/widgets/initialization.tpl');
        } 
        
        // logged in data
        else {
            
            $data['customer_tile'] = $this->load->controller('module/ami/customer/tile');
            $data['squawk_tile'] = $this->load->controller('module/ami/squawk/tile');
            $data['workorder_tile'] = $this->load->controller('module/ami/workorder/tile');
            $data['inventory_tile'] = $this->load->controller('module/ami/inventory/tile');
            $data['recent_activity'] = $this->load->controller('module/ami/workorder/recentactivitywidget');
            $data['recent_orders'] = $this->load->controller('module/ami/workorder/recentorderswidget');
            $data['recent_inventory_purchases'] = $this->load->controller('module/ami/inventory/recentTransactionsWidget');
        }
        
        // this could be html
        $this->load->controller('module/ami/common/dashboard', $data);
        }

    }
    
    public function confirmUser( ) {

        $this->request->post['parent_user_id'] = 23;
        $this->user->initializeUser( $this->request->post );
        
    }
   
    
    
    
}
