<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OcHelperTrait
 *
 * @author Administrator
 */
trait OcHelperTrait {
    //put your code here
    function makeVariables ($language, $module_name, $data) {
        
        if ( count( $language ) > 0 ) {

            foreach (  $language as $k => $v ) {
                
                if ( strstr($k, $module_name) !== false ) {
                    
                    
                    $lang_module = explode("_", $k);
                    $lang_var_type = $lang_module[0];
                    $lang_module_name = $lang_module[1];
                    
                    /* if ( count( $lang_module ) == 3 ) {
                        $lang_variable_name = $lang_module[2];
                    } else {
                        $lang_variable_name = 'test';
                    }*/
                    
                    if ( $module_name == $lang_module_name ) {
                        $data[$k] = $v;
                    }
                }
                
            }

            return $data;
        }
    }
    
    /** build the content for the basic customer page and put it in the $data property
     * Requires this->module_name to be set
     * 
     * @param string $submodule
     * @return array
     */
        private function bootstrap ( $submodule = '' ) {
            
            $data = '';
            //$module_name = 'customer';
            $language = $this->load->language('module/ami/' . $this->module_name);
            
            $data = $this->makeVariables($language, $this->module_name, $data);
            $data['heading_title'] = $data['text_' . $this->module_name . '_heading_title'];

            $this->document->setTitle($data['heading_title'] . " " . $submodule );
            $this->loadScripts($this->module_name);

            $data = $this->loadCommonModules($data);
            
            return $data;

        }
    
    /** Load the scripts that are used on most pages in this app
     * 
     * @param type $module
     */
    private function loadScripts ( $module ) {
            
            $this->document->addStyle( 'catalog/view/theme/wb/stylesheet/' . $module . '.css');
            $this->document->addScript( 'catalog/view/theme/wb/js/forms.js');
            $this->document->addScript( 'catalog/view/theme/wb/js/' . $module . '.js');
            
        }
    
    /** this is a helper to load the common header, menu and footer data content 
     * append the new properties to the data object
     * @param array $data
     * @return array
     */
    function loadCommonModules( $data ) {
        
        $data['header'] = $this->load->controller('module/ami/common/header');
        $data['column_left'] = $this->load->controller('module/ami/common/column_left');
        $data['footer'] = $this->load->controller('module/ami/common/footer');
        
        return $data;
    }
    
    private function buildSortFromGet () {
        
        foreach ( $this->sortable_columns as $k ) {
            
            if ( $this->request->get['sort'] == $k ) {
                $sort[] = array( "field" =>$k,"order"=>"1","dir"=>$this->getSortDir($k) );
            }
            
        }
        
        return $sort;
        
    }
    
    
    /** build the get params for the template column sorts
     * 
     * @return array
     */
    private function buildSortParams() {

        foreach ( $this->sortable_columns as $k ) {

            $data[$k]['sort_order'] = $this->getOppositeSortDir( $k );
            $data[$k]['fa_sort_dir'] = $this->getSortIconDir( $k );
        }

        return $data;
    }

    /** return the sort direction for this field
     * 
     * @param string $column
     * @return string
    */ 
    private function getSortDir($column) {
            return ( isset( $this->request->get[$column . '_dir'] ) ) ? $this->request->get[$column . '_dir'] : $this->default_sort[0]['_dir'];

    }

    /** return the direction needed for the urls on a page.
     * If view is sorted asc, you need to give desc in list
     * 
     * @param string $column
     * @return string
    */
    private function getOppositeSortDir ($column) {
        return ( ( $this->getSortDir($column) == 'asc') ? 'desc' : 'asc' );
    }

    /** return the icon needed for the sorts on a page.
     * If view is sorted asc, you need to give desc icon
     * 
     * @param string $column
     * @return string
    */
    private function getSortIconDir ($column) {
        return ( ( $this->getSortDir($column) == 'asc') ? 'down' : 'up' );
    }
    
    /** build the error message 
     * 
     * @param array $messages
     * @return string
     */
    private function makeNotification ( $messages ) {
        
        $notification['message'] = '&nbsp;&nbsp;';
        
        $notification['type'] = 'info';
        $notification['fa_icon'] = 'exclamation-circle';
            
        foreach ( $messages as $message ) {
            
            $notification['message'] .= $message['message'] . '<br/>';
        }
        
        return $notification;
        
    }
    
    /** loop through the list of params and pick out any that are like "filter_xxxxx".  These are search fields
     * 
     * @param array $array
     * @return array
     */
    private function prepareFilterFields ( $array ) {

        $res_array = '';
        foreach ( $array as $key => $v ) {

            $key_array = explode( "_", $key, '2');
            if ( count( $key_array ) > 1 ) {

                if ( $key_array[0] == 'filter' && strlen( trim( $v ) ) > 0 ) {
                    $res_array[$key_array[1]] = $v;
                }
            }
        }

        if ( is_array( $res_array ) ) {
            return $res_array;
        }
    }
    
    /* This allows autopopulationg of form fields in tpl files
     * @param array $params
    */
    private function buildDefaultsFormValues( $params ) {
        
        $defaults=''; 
        
        foreach ( $params as $k => $v ) {
            
            $defaults[$k] = $v;
            
        }
        
        return $defaults;
    }
    
    /* using the key "default_", build out params needed for making urls
     * @param array $params
     */
    private function buildUrlParams ( $params ) {
        
        $defaults=''; 
        
        foreach ( $params as $k => $v ) {
            
            $defaults .= '&filter_' . $k . "=" . urlencode($v);
            
        }
        
        return $defaults;
    }
    
    
    private function encodeFunc($value) {
        ///remove any ESCAPED double quotes within string.
        $value = str_replace('\\"','"',$value);
        //then force escape these same double quotes And Any UNESCAPED Ones.
        $value = str_replace('"','\"',$value);
        //force wrap value in quotes and return
        return '"'.$value.'"';
    }

    /** CVS Export utility.
     * 
     * @param array $rows
     * @param array $columns
     * @output true
     * @return void
     */
    private function exportCsv ( $rows, $columns ) {
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        
        $new_columns = array();
        
        foreach( $columns as $k ) {
            $column = str_replace("_", " ", $k );
            array_push( $new_columns, $column );
        }

        // output the column headings
        //fputcsv( $output, $new_columns, ";", '"' );
        fputcsv($output, $new_columns, ',', '"', '""' );
        

        // loop over the rows, outputting them
        foreach ( $rows as $row ) {
            
            $therow = array();
            
            $counter = 0;
            // loop over the columns and get these from the data.
            // you might have a data row with 10 fields and you only want 3 of them.  This does that. and you can access this from any model
            foreach( $columns as $column ) {
                array_push( $therow, $row[$columns[$counter]] );
                $counter++;
            }

            //fputcsv($output, $therow);
            fputcsv($output, $therow, ',', '"', '""' );
            
        }

    }
    
    private function sendEmail ( $args ) {

        $message = $args['message'];
        $subject = $args['subject'];

        $email_to = $args['email'];

        $email_text = $message;

        $mail = new Mail();

        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->hostname = $this->config->get('config_smtp_host');
        $mail->username = $this->config->get('config_smtp_username');
        $mail->password = $this->config->get('config_smtp_password');
        $mail->port = $this->config->get('config_smtp_port');
        $mail->timeout = $this->config->get('config_smtp_timeout');
        $mail->setTo($email_to);
        $mail->setFrom("express@wbparts.com");
        $mail->setSender("express@wbparts.com");
        $mail->setSubject( $subject );
        $mail->setHtml($email_text);

        $mail->send();

        return true;
    
    }
    
    /** display a opencart friendly error template
     * 
     * @param string $error
     * @param string $module
     * @param string $trace
     * @param array $user_messages
     * @param string $title
     */
    private function renderError ( $error, $module, $trace, $user_messages = null, $title = "Error Encountered"  ) {
        
        $data = $this->bootstrap();
        
        $data['user_messages'] = $user_messages;
        $data['error_title'] = $title;
        
        $data['error_message'] = $error->getMessage();
        
        //TODO: this might be a template
        $data['error_details'] = "";

        $error = array('oc_customer_id'=>$this->customer->getId(), "message"=> $error->getMessage(), "module"=>$module, "trace"=>$trace );
        $this->log->addLog( $error  );

        $this->response->setOutput($this->load->view( 'wb/template/error_handler.tpl', $data ));
    }
     

    
    
}
