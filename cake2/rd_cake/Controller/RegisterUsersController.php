<?php
class RegisterUsersController extends AppController {

    public $name       = 'RegisterUsers';
    public $uses       = array('User','Profile','Realm','PermanentUser','DynamicDetail');

	public function new_permanent_user(){

		//--No login_page_id no reg --
		if(array_key_exists('login_page_id',$this->request->data)){
		    $page_id = $this->request->data['login_page_id'];
		    $this->DynamicDetail->contain();
		    $q_r = $this->DynamicDetail->findById($page_id);
		    if(!$q_r){
		         $this->set(array(
				    'success'   => false,
				    'errors'	=> array('Login Page ID' => 'Page not found in database'),
					    '_serialize' => array('success','errors')
			    ));	
			    return;
		    }
		}else{
		    $this->set(array(
				'success'   => false,
				'errors'	=> array('Login Page ID' => 'Login Page ID missing'),
					'_serialize' => array('success','errors')
			));	
			return;
		}
		
		if(!$q_r['DynamicDetail']['register_users']){
		    $this->set(array(
				'success'   => false,
				'errors'	=> array('Registration forbidden' => 'User Registration not allowed'),
					'_serialize' => array('success','errors')
			));	
			return; 
		}
		

		//--Do the MAC test --
		$mac_name   = '';
		$mac_value  = '';
		
		if($q_r['DynamicDetail']['reg_mac_check']){
		    if(array_key_exists('mac',$this->request->data)){
				$mac = $this->request->data['mac'];
				$mac_value = $mac;
				$mac_name  = 'mac';
				if($mac == ''){//Can't use empty MACs
				    $this->set(array(
				        'success'   => false,
				        'errors'	=> array('Address not specified' => 'MAC Address not specified'),
					        '_serialize' => array('success','errors')
			        ));	
			        return;
				}
				$this->PermanentUser->contain();
				$q	= $this->PermanentUser->find('first',
					array('conditions' => 
						array(
							'PermanentUser.extra_name' 	=> 'mac',
							'PermanentUser.extra_value' => $mac,
						)
					));
				if($q){
					$already_username = $q['PermanentUser']['username'];
					$this->set(array(
						'success'   => false,
						'errors'	=> array('username' => "MAC Address $mac in use by $already_username"),
							'_serialize' => array('success','errors')
					));	
					return;
				}
			}else{

				$this->set(array(
					'success'   => false,
					'errors'	=> array('Device ID Missing' => 'Device MAC not in request'),
						'_serialize' => array('success','errors')
				));	
				return;
			}
		}
		

		//Get the token of the Dynamic Login Page owner
		$this->User->contain();
		$q_u 		= $this->User->findById($q_r['DynamicDetail']['user_id']);
		$token		= $q_u['User']['token'];

		//Realm id
		$realm_id	= $q_r['DynamicDetail']['realm_id'];

		//Profile id
		$profile_id	= $q_r['DynamicDetail']['profile_id'];
		
        $active   	= 'active';
        $cap_data 	= 'hard';
        $language   = '4_4';
        $parent_id  = 0;        
        $url        = 'http://127.0.0.1/cake3/rd_cake/permanent-users/add.json'; 
        $username	= $this->request->data['username'];
		$password	= $this->request->data['password'];
		
		$auto_add   = 0;
		if($q_r['DynamicDetail']['reg_auto_add']){
		    $auto_add = 1;
		}
		
		//--- ADD ON ---- Expire them after 30 days
		/*
		$from_date	=  date("n/j/Y");
		$plus_30  	= mktime(0, 0, 0, date("m"),   date("d")+31,   date("Y")); //We actually put 31 since today is already gone
		$to_date	=  date("n/j/Y",$plus_30);
		
		'from_date'		=> $from_date,
	    'to_date'		=> $to_date,
	    */
         
        // The data to send to the API
        $postData = array(
            'active'        => $active,
            'cap_data'      => $cap_data,
            'language'      => $language,
            'user_id'     	=> $parent_id,
            'profile_id'    => $profile_id,
            'realm_id'      => $realm_id,
            'token'         => $token,
            'username'      => $username,
            'password'      => $password,
            'email'         => $username, //Email and username will be the same / email required
			'extra_name'	=> $mac_name,
			'extra_value'	=> $mac_value,
			'auto_add'      => $auto_add,
			'name'          => $this->request->data['name'],
			'surname'       => $this->request->data['surname'],
			'phone'         => $this->request->data['phone']
        );
     
        // Setup cURL
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
         
            CURLOPT_POST            => TRUE,
            CURLOPT_RETURNTRANSFER  => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($postData)
        ));
         
        // Send the request
        $response = curl_exec($ch);
         
        // Check for errors
        if($response === FALSE){
            die(curl_error($ch));
        }

		$responseData = json_decode($response, TRUE);
		//print_r($responseData);

        if($responseData['success'] == false){
			$this->set(array(
            'success'   => $responseData['success'],
			'errors'	=> $responseData['errors'],
			'message'	=> $responseData['message'],
		        '_serialize' => array('success','errors','message')
		    ));	
		}

		if($responseData['success'] == true){

			//Check if we need to email them
			if($q_r['DynamicDetail']['reg_email']){
				$this->_email_user_detail($username,$password);
			}

			$this->set(array(
            'success'   => $responseData['success'],
			'data'		=> $postData,
		        '_serialize' => array('success','data')
		    ));	
		}
	}

	public function lost_password(){
	
	    $success = false;
	    if(array_key_exists('email',$this->request->data)){
	    
	        $username = $this->request->data['email'];
	         
	        if($this->request->data['auto_suffix_check'] == 'true'){
	            $username = $username.'@'.$this->request->data['auto_suffix'];
	        }
	     
	        $this->PermanentUser->contain('Radcheck'); 
	        $q_r = $this->PermanentUser->find('first',array('conditions' =>array('PermanentUser.username' => $username)));
	       
	        $password = false;       
	        if($q_r){
	            foreach($q_r['Radcheck'] as $rc){
                    if($rc['attribute'] == 'Cleartext-Password'){
                        $un = $this->request->data['email'];
                        $password = $rc['value'];
                        if($this->request->data['auto_suffix_check'] == 'true'){
	                        $un = $un." ($username)";
	                    }
                        $this->_email_lost_password($un,$password);
                        $success = true;
                    }
	            }
	        }        
	    }

		$this->set(array(
            'success'   => $success,
			'data'		=> array(),
		        '_serialize' => array('success','data')
		    ));
	}
	
	
	private function _email_lost_password($username,$password){
	    $email_server = Configure::read('EmailServer');
        App::uses('CakeEmail', 'Network/Email');
        $Email = new CakeEmail();
        $Email->config($email_server);
        $Email->subject('Lost Password Retrieval');
        $Email->to($this->request->data['email']);
        $Email->viewVars(compact( 'username', 'password'));
        $Email->template('user_detail', 'user_notify');
        $Email->emailFormat('html');
        $Email->send();
	}


	private function _email_user_detail($username,$password){

		$email_server = Configure::read('EmailServer');
        App::uses('CakeEmail', 'Network/Email');
        $Email = new CakeEmail();
        $Email->config($email_server);
        $Email->subject('New user registration');
        $Email->to($this->request->data['username']);
        $Email->viewVars(compact( 'username', 'password'));
        $Email->template('user_detail', 'user_notify');
        $Email->emailFormat('html');
        $Email->send();
    }

}
?>
