<?php
/**
 * Description of ApiTrait
 *
 * @author wbparts Brian Clinton
 * 
 * Standard Response
 * 
 * To handle this, the variable response_type can be jsonhtml,json,or ''
 * if '' then pure html template is returned
 * if jsonhtml then return "status","results"=>html template
 * if json return "status","results"
 * 
 * JSON Error Response
 * * if there is an error you will get a 400 with status codes for each error
 * {
    "errors": [
      {
        "status": "403",
        "source": { "pointer": "/data/attributes/secret-powers" },
        "detail": "Editing secret powers is not authorized on Sundays."
      },
      {
        "status": "422",
        "source": { "pointer": "/data/attributes/volume" },
        "detail": "Volume does not, in fact, go to 11."
      },
      {
        "status": "500",
        "source": { "pointer": "/data/attributes/reputation" },
        "title": "The backend responded with an error",
        "detail": "Reputation service not responding after three requests."
      }
    ]
  }
 * 
 */
trait ApiTrait {
    
    protected $access_type = 'api';
    
    private function initializeVariables( ) {
    
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
    
    /** just put all the variables in the request into $vars obj
     * 
     * @param array $args
     * @return boolean
     */
    private function setVariables($args = null) {

        // needed to be set on vars 
        $this->vars['access_type'] = $this->access_type;
        
        // if passed in dont overwrite it
        if (count($args) > 0) {
            $this->vars = $args;
            $this->vars['access_type'] = $this->access_type;
            return true;
        }

        // process METHODS
        if ($this->request_type == 'GET') {

            foreach ($this->request->get as $k => $v) {
                $this->vars[$k] = $v;
            }
        }

        if ($this->request_type == 'POST') {

            foreach ($this->request->post as $k => $v) {
                $this->vars[$k] = $v;
            }
        }


        // these params are not available in post,get so we get the input stream and set it from there
        if ($this->request_type == 'PUT' || $this->request_type == 'DELETE') {
            
            parse_str(file_get_contents('php://input'), $params);

            foreach ($params as $k => $v) {
                $this->vars[$k] = $v;
            }
        }
        
        // rules
        // the route is a required param for getting to this resource, but we will never process it in application/business logic
        unset($this->vars['route']);
    }

    /** This */
      private function setRequestType ( $args ) {
        return ( isset( $args['request_type'] ) ) ? $args['request_type'] : 'json';
      }
      
    /** handles output of methods
     * determines whether to format json or return object
     * 
     * @param string $request_type
     * @param string $message
     * @param string|obj|array $output
     * @return array|boolean
    */
    private function render($response_type, $output, $status = 'success') {

        if ($response_type == 'json') {
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json", true);
            echo json_encode(array("status" => "success", "type" => "json", "results" => $output));
            die();
        } elseif ($response_type == 'jsonhtml') {
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json", true);
            echo json_encode(
                    array(
                        "status"=>"success",
                        "type"=>"html",
                        "results"=> $output
                    )
            );
            
        }
        else {

            if (is_null($output)) {
                return true;
            } else {
                header("Content-Type: text/html", true);
                echo $output;
            }
        }
    }
    
    private function renderError( $response_type, $errors ) {
        
        if ($response_type == 'json') {
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json", true);
            http_response_code(400);
            
            echo json_encode(array("status" => "failure", "type" => "json", "errors" => $errors));
            
        } else {
            header("Access-Control-Allow-Origin: *");
            http_response_code(200);
            
            $output = "<div>Error processing page: " . $errors . "</div>";
            
            echo json_encode(array("status" => "failure", "type" => "json", "errors" => $output));
        }
        
        
    }

}
