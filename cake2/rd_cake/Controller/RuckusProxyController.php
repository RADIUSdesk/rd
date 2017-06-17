<?php
class RuckusProxyController extends AppController {

    public $name        = 'RuckusProxy';
    public $uses        = null;
        
    private $Vendor             = 'ruckus';
    private $APIVersion         = '1.0';
    private $RequestCategory    = 'UserOnlineControl';
    private $protocol           = 'http';
    private $port               = 9080; //(For http it is 9080 and https it is 9443)
    private $error_check        = false;
    private $error_message      = '';
    private $northbound         = '';
    
    
    /*
        Vendor: 'ruckus',
        RequestPassword: 'stayoutnow123!',
        APIVersion: '1.0',
        RequestCategory: 'UserOnlineControl',
        RequestType: 'Status',
        UE-MAC: 'ENC57a0d375604154793f3a60784be967037a8dbb9754dde0ab'
    */

	public function status(){
	
	    $this->_query_check();
	    if($this->error_found == true){
	        $this->set(array(
                'items'     => array(),
                'success'   => false,
                'message'   => $this->error_message,
                '_serialize' => array('items','success','message')
            ));
            return;
	    }
	    
	    $this->_get_nbi_password();
	    if($this->error_found == true){
	        $this->set(array(
                'items'     => array(),
                'success'   => false,
                'message'   => $this->error_message,
                '_serialize' => array('items','success','message')
            ));
            return;
	    }
	    

        $nbiIP      = $this->request->query['nbiIP'];
        $client_mac = $this->request->query['client_mac'];

        //We got everything - Now we can do the POST to the controller  
        App::uses('HttpSocket', 'Network/Http');
        $HttpSocket = new HttpSocket();
        $data = array(
            'Vendor'            => $this->Vendor,
            'RequestPassword'   => $this->northbound,
            'APIVersion'        => $this->APIVersion,
            'RequestCategory'   => $this->RequestCategory,
            'RequestType'       => 'Status',
            'UE-MAC'            => $client_mac
        );
         
        $data       = json_encode($data); 
        $request    = array(
            'header' => array('Content-Type' => 'application/json',
            ),
        );     
       
        $results        = $HttpSocket->post($this->protocol.'://'.$nbiIP.':'.$this->port.'/portalintf', $data,$request);
        $return_array   = (array) json_decode($results->body()); 

        $this->set(array(
            'data' => $return_array,
            'success' => true,
            '_serialize' => array('data','success')
        ));
	}
	
	/*
        Vendor: 'ruckus',
        RequestPassword: 'stayoutnow123!',
        APIVersion: '1.0',
        RequestCategory: 'UserOnlineControl',
        RequestType: 'Logout',
        UE-MAC: 'ENC57a0d375604154793f3a60784be967037a8dbb9754dde0ab'	
	*/

	public function logout(){
	
	    $this->_query_check();
	    if($this->error_found == true){
	        $this->set(array(
                'items'     => array(),
                'success'   => false,
                'message'   => $this->error_message,
                '_serialize' => array('items','success','message')
            ));
            return;
	    }
	    
	    $this->_get_nbi_password();
	    if($this->error_found == true){
	        $this->set(array(
                'items'     => array(),
                'success'   => false,
                'message'   => $this->error_message,
                '_serialize' => array('items','success','message')
            ));
            return;
	    }
	    

        $nbiIP      = $this->request->query['nbiIP'];
        $client_mac = $this->request->query['client_mac'];

        //We got everything - Now we can do the POST to the controller  
        App::uses('HttpSocket', 'Network/Http');
        $HttpSocket = new HttpSocket();
        $data = array(
            'Vendor'            => $this->Vendor,
            'RequestPassword'   => $this->northbound,
            'APIVersion'        => $this->APIVersion,
            'RequestCategory'   => $this->RequestCategory,
            'RequestType'       => 'Logout',
            'UE-MAC'            => $client_mac
        );
         
        $data       = json_encode($data); 
        $request    = array(
            'header' => array('Content-Type' => 'application/json',
            ),
        );     
       
        $results        = $HttpSocket->post($this->protocol.'://'.$nbiIP.':'.$this->port.'/portalintf', $data,$request); 
        $return_array   = (array) json_decode($results->body()); 

        $this->set(array(
            'data' => $return_array,
            'success' => true,
            '_serialize' => array('data','success')
        ));
	}
	
	/*
        Vendor: 'ruckus',
        RequestPassword: 'stayoutnow123!',
        APIVersion: '1.0',
        RequestCategory: 'UserOnlineControl',
        RequestType: 'Login',
        UE-MAC: 'ENC57a0d375604154793f3a60784be967037a8dbb9754dde0ab',
        UE-Proxy: '0',
        UE-Username: 'dvdwalt',
        UE-Password: 'dvdwalt'
	*/

	public function login(){
	
	    $this->_query_check();
	    if($this->error_found == true){
	        $this->set(array(
                'items'     => array(),
                'success'   => false,
                'message'   => $this->error_message,
                '_serialize' => array('items','success','message')
            ));
            return;
	    }
	    
	    $this->_get_nbi_password();
	    if($this->error_found == true){
	        $this->set(array(
                'items'     => array(),
                'success'   => false,
                'message'   => $this->error_message,
                '_serialize' => array('items','success','message')
            ));
            return;
	    }
	    

        $nbiIP      = $this->request->query['nbiIP'];
        $client_mac = $this->request->query['client_mac'];
        
        if(
	     (isset($this->request->query['username']))&&
	     (isset($this->request->query['pwd']))
	     ){
	        //No unless in php 
	         
	    }else{
	        $this->error_message = "Username and / or Password missing";
	        $this->set(array(
                'items'     => array(),
                'success'   => false,
                'message'   => $this->error_message,
                '_serialize' => array('items','success','message')
            ));
            return;    
	    }
	    
	    $username   = $this->request->query['username'];
	    $pwd        = $this->request->query['pwd'];
        

        //We got everything - Now we can do the POST to the controller  
        App::uses('HttpSocket', 'Network/Http');
        $HttpSocket = new HttpSocket();
        $data = array(
            'Vendor'            => $this->Vendor,
            'RequestPassword'   => $this->northbound,
            'APIVersion'        => $this->APIVersion,
            'RequestCategory'   => $this->RequestCategory,
            'RequestType'       => 'Login',
            'UE-MAC'            => $client_mac,
            'UE-Proxy'          => '0',
            'UE-Username'       => $username,
            'UE-Password'       => $pwd
        );
         
        $data       = json_encode($data); 
        $request    = array(
            'header' => array('Content-Type' => 'application/json',
            ),
        );     
       
        $results        = $HttpSocket->post($this->protocol.'://'.$nbiIP.':'.$this->port.'/portalintf', $data,$request);  
        $return_array   = (array) json_decode($results->body()); 

        $this->set(array(
            'data' => $return_array,
            'success' => true,
            '_serialize' => array('data','success')
        ));
	}
	
	
	//_____________ PRIVATE FUNCTIONS________
	
	private function _query_check(){
	    //We need the following to be in the query string
	    if(
	     (isset($this->request->query['nbiIP']))&&
	     (isset($this->request->query['client_mac']))
	     ){
	        //No unless in php 
	         
	    }else{
	        $this->error_found     = true;
	        $this->error_message   = "Missing items in query string";
	    }
	}
	
	private function _get_nbi_password(){
	    //Ruckus Northbound interface - Later we might add a field to the DynamicDetail entry for this
		Configure::load('DynamicLogin');
		if(Configure::read('DynamicLogin.ruckus.northbound.password')){
		    $this->northbound = Configure::read('DynamicLogin.ruckus.northbound.password');
		}else{	
		    $this->error_found     = true;
	        $this->error_message   = 'Northbound Portal Interface password not configured';
		}
	}	
}
?>
