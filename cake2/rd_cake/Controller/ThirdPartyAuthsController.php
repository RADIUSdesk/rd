<?php
App::uses('AppController', 'Controller');


class ThirdPartyAuthsController extends AppController {

	//------------------------------------------------------------------------------------
	//--This is the place where we will redirect a user to after they authenticated fine-- 
	//--using an OAuth service------------------------------------------------------------
	//------------------------------------------------------------------------------------
	public function opauth_complete() {

		//During initial authentication we set a session variable which we can then use again here to redirect the user back to their login page
		$qs = CakeSession::read('rd.qs');
		

		//Build the URL	
		$query = $this->_queryToArray($qs);
		$new_query_string = "";
		$following = false;

		$wanted_query_items = array();
		foreach(array_keys($query) as $k){
			//We don't care for :protocol/hostname pathaname and q
			if(
				($k != 'protocol')&&
				($k != 'hostname')&&
				($k != 'pathname')&&
				($k != 'q')&&
				($k != 'sl_type')&&
				($k != 'sl_value')&&
				($k != 'sl_name')
			){ 
				if($following){
					$new_query_string = $new_query_string."&".$k."=".$query["$k"];
				}else{ //First time
					$new_query_string = $new_query_string."?".$k."=".$query["$k"];
				}
				$following = true;

				$wanted_query_items["$k"] = $query["$k"];
			}
		}

		//If the person authenticated fine ($this-data[validated] ==1) then we can search for it among the vouchers or users
		if($this->data['validated'] ==1){

			$record_info = false;

			$conditions = array("OR" =>array());
      
		    foreach(array_keys($wanted_query_items) as $key){
		        array_push($conditions["OR"],
		            array("DynamicPair.name" => $key, "DynamicPair.value" =>  $wanted_query_items[$key])
		        ); //OR query all the keys
		    }

			$this->DynamicDetail= ClassRegistry::init('DynamicDetail');
		    $this->DynamicDetail->DynamicPair->contain(
				array('DynamicDetail' => array('DynamicDetailSocialLogin'))
			);

		    $q_r = $this->DynamicDetail->DynamicPair->find('first', 
		        array('conditions' => $conditions, 'order' => 'DynamicPair.priority DESC')); //Return the one with the highest priority
			//print_r($q_r);

			//Loop through all the DynamicDetailSocialLogin entries and see if one compares with 
			//$this->data[auth][provider]
			$social_login_info = array();
			foreach($q_r['DynamicDetail']['DynamicDetailSocialLogin'] as $i){
				if($i['name'] == $this->data['auth']['provider']){
					$social_login_info = $i;
					$realm_id = $social_login_info['realm_id'];
					break; //No need to go on
				}
			}

			if(array_key_exists('type',$social_login_info)){ 
				//There was a hit now we need to check if there are either a Voucher or Permanent User
				//With extra_name = $this->data['auth']['provider'] and extra_value = ($social_login_info['dynamic_detail_id']."_".
				//$this->data['auth']['uid']
				$extra_name 	= $this->data['auth']['provider'];
				$dd_id			= $social_login_info['dynamic_detail_id'];
				$extra_value	= 'sl_'.$dd_id."_".$this->data['auth']['uid']; //Add social login to work on permanent usernames
				$type			= $social_login_info['type'];

				//Check if this one wants to record / update info
				if($social_login_info['record_info'] == true){
					$record_info = true;
				}

				if($type == 'voucher'){
					$this->Voucher = ClassRegistry::init('Voucher');
					$this->Voucher->contain();
					$q_voucher =  $this->Voucher->find('first', 
						array('conditions' => array('Voucher.extra_name' => $extra_name,'Voucher.extra_value'=> $extra_value)));
					if(!$q_voucher){
						$social_login_info['extra_name'] 	= $extra_name;
						$social_login_info['extra_value'] 	= $extra_value;
						$social_login_info['user_id'] 		= intval($q_r['DynamicDetail']['user_id']);
						$this->_addVoucher($social_login_info);
					}
				}

				if($type == 'user'){
					$this->PermanentUser = ClassRegistry::init('PermanentUser');
					$this->PermanentUser->contain();
					$q_user =  $this->PermanentUser->find('first', 
						array('conditions' => array('PermanentUser.extra_name' => $extra_name,'PermanentUser.extra_value'=> $extra_value)));
					if(!$q_user){
						$social_login_info['extra_name'] 	= $extra_name;
						$social_login_info['extra_value'] 	= $extra_value;
						$social_login_info['user_id'] 		= intval($q_r['DynamicDetail']['user_id']);
						//Some personal info
											
						$social_login_info['name']    = '';
						if(array_key_exists('first_name',$this->data['auth']['info'])){
							$social_login_info['name'] 	= $this->data['auth']['info']['first_name'];
						}
						
						$social_login_info['surname']	    = '';
						if(array_key_exists('last_name',$this->data['auth']['info'])){
							$social_login_info['surname']   = $this->data['auth']['info']['last_name'];
						}

						$social_login_info['email']			= '';
						if(array_key_exists('email',$this->data['auth']['info'])){
							$social_login_info['email'] 	= $this->data['auth']['info']['email'];
						}
						$this->_addPermanentUser($social_login_info);
					}
				}
			}

			//Check if we should record / update this user's detail
			if($record_info){
			    //We get the entry's ID and see if there is an entry for the realm for this user
				$social_login_user_id = $this->_record_or_update_info();
				//Check if there is a realm entry for this user and if not add it
				$this->SocialLoginUserRealm = ClassRegistry::init('SocialLoginUserRealm');
				$this->SocialLoginUserRealm->contain();
				$count = $this->SocialLoginUserRealm->find('count', 
				    array('conditions' => array(
				            'SocialLoginUserRealm.realm_id' => $realm_id,
				            'SocialLoginUserRealm.social_login_user_id' => $social_login_user_id,
				        )  
				    )
		        );
		        if($count == 0){    //If not found we need to add it since we want to tie it to a real in order to filter the list for Access Providers
		            $d = array();
		            $d['realm_id'] = $realm_id;
		            $d['social_login_user_id'] = $social_login_user_id;
		            $this->SocialLoginUserRealm->save($d);
		        }
			}

		}

		$new_query_string=$new_query_string."&sl_type=$type&sl_value=$extra_value"."&sl_name=$extra_name";
		//print_r($new_query_string);
		//$redirect_url = urldecode($query['protocol']).urldecode($query['hostname']).urldecode($query['pathname']).urldecode($new_query_string);
		$redirect_url = 'http://'.urldecode($query['hostname']).urldecode($query['pathname']).urldecode($new_query_string);
//		print($redirect_url);
		$this->redirect("$redirect_url");
//		print_r($this->data);
	}

	public function info_for(){

		if(
			(array_key_exists('sl_type',$this->request->query))&&
			(array_key_exists('sl_name',$this->request->query))&&
			(array_key_exists('sl_value',$this->request->query))
		){

			if($this->request->query['sl_type'] == 'voucher'){
				$voucher_info = $this->_find_vouchername_and_password($this->request->query['sl_name'],$this->request->query['sl_value']);
				$this->set(array(
                    'data'         => $voucher_info,
                    'success'       => true,
                    '_serialize'    => array('data','success')
                ));
				return;
			}
			
			if($this->request->query['sl_type'] == 'user'){
				$user_info = $this->_find_username_and_password($this->request->query['sl_name'],$this->request->query['sl_value']);
				$this->set(array(
                    'data'         => $user_info,
                    'success'       => true,
                    '_serialize'    => array('data','success')
                ));
				return;
			}

		}else{
			$this->set(array(
				'success'   => false,
				'errors'	=> array('errors' => "Missing values in query string"),
				'_serialize' => array('success','errors')
			));	
			return;
		}
	}

	private function _queryToArray($qry){

		//Take the query string and make in an Array

		$result = array();
		//string must contain at least one = and cannot be in first position
		if(strpos($qry,'=')) {

		if(strpos($qry,'?')!==false) {
		$q = parse_url($qry);
		$qry = $q['query'];
		}
		}else {
			return false;
		}
		foreach (explode('&', $qry) as $couple) {
			list ($key, $val) = explode('=', $couple);
			$result[$key] = $val;
		}
		return empty($result) ? false : $result;
	}

	private function _addVoucher($i){
		$url = 'http://127.0.0.1/cake2/rd_cake/vouchers/add.json';
		$this->User 	= ClassRegistry::init('User');
		$this->User->contain();
		$q_r			= $this->User->find('first',array('conditions' => array('User.username' => 'root')));
		$root_token 	= $q_r['User']['token'];

		$postData = array(
			'extra_name'	=>	$i['extra_name'],
			'extra_value'	=>  $i['extra_value'],	
			'never_expire'	=>	'never_expire',
			'profile_id'	=>  intval($i['profile_id']),
			'quantity'		=>	1,
			'realm_id'		=>	intval($i['realm_id']),
			'sel_language'	=>	'4_4',
			'token'			=> 	$root_token,
			'user_id'		=>	intval($i['user_id'])
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
	}

	private function _addPermanentUser($i){
		$url = 'http://127.0.0.1/cake2/rd_cake/permanent_users/add.json';
		$this->User 	= ClassRegistry::init('User');
		$this->User->contain();
		$q_r			= $this->User->find('first',array('conditions' => array('User.username' => 'root')));
		$root_token 	= $q_r['User']['token'];
		$password		= $this->_generatePassword();


		$postData = array(
			'active'			=> 'active',
			'always_active'		=> 'always_active',
			'extra_name'		=> $i['extra_name'],
			'extra_value'		=> $i['extra_value'],
			'language'			=> '4_4',
			'password'			=> $password,
			'profile_id'		=> intval($i['profile_id']),
			'realm_id'			=> intval($i['realm_id']),
			'sel_language'		=> '4_4',
			'token'				=> $root_token,
			'user_id'			=> intval($i['user_id']),
			'username'			=> $i['extra_value'],
			'name'				=> $i['name'],
			'surname'			=> $i['surname'],
			'email'				=> $i['email']
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
	}

	private function _generatePassword ($length = 8){
        // start with a blank password
        $password = "";
        // define possible characters
       // $possible = "!#$%^&*()+=?0123456789bBcCdDfFgGhHjJkmnNpPqQrRstTvwxyz";
        $possible = "0123456789bBcCdDfFgGhHjJkmnNpPqQrRstTvwxyz";
        // set up a counter
        $i = 0; 
        // add random characters to $password until $length is reached
        while ($i < $length) { 

            // pick a random character from the possible ones
            $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
            // we don't want this character if it's already in the password
            if (!strstr($password, $char)) { 
                $password .= $char;
                $i++;
            }
        }
        // done!
        return $password;
    }

	private function _find_username_and_password($extra_name,$extra_value){
		$this->PermanentUser = ClassRegistry::init('PermanentUser');
		$this->PermanentUser->contain();
		$q_r = $this->PermanentUser->find('first',
			array('conditions' => array('PermanentUser.extra_name' => $extra_name,'PermanentUser.extra_value' => $extra_value)));
		$user_data = array('username' => 'notfound','password' => 'notfound');
		if($q_r){
			$un = $q_r['PermanentUser']['username'];
			$user_data['username'] = $un;
			$this->Radcheck = ClassRegistry::init('Radcheck');

			$q_pw = $this->Radcheck->find('first', 
				array('conditions' => array('Radcheck.username' => $un,'Radcheck.attribute' => 'Cleartext-Password'))
			);

			if($q_pw){
				$user_data['password'] = $q_pw['Radcheck']['value'];
			}
		}

		return $user_data;
	}

	private function _find_vouchername_and_password($extra_name,$extra_value){
		$this->Voucher = ClassRegistry::init('Voucher');
		$this->Voucher->contain();
		$q_r = $this->Voucher->find('first',
			array('conditions' => array('Voucher.extra_name' => $extra_name,'Voucher.extra_value' => $extra_value)));
		$voucher_data = array('username' => 'notfound','password' => 'notfound');
		if($q_r){
			$un = $q_r['Voucher']['name'];
			$voucher_data['username'] = $un;
			$this->Radcheck = ClassRegistry::init('Radcheck');

			$q_pw = $this->Radcheck->find('first', 
				array('conditions' => array('Radcheck.username' => $un,'Radcheck.attribute' => 'Cleartext-Password'))
			);

			if($q_pw){
				$voucher_data['password'] = $q_pw['Radcheck']['value'];
			}
		}
		return $voucher_data;
	}

	private function _record_or_update_info(){

		$common_info 	= array('image', 'name', 'first_name', 'last_name', 'email');

		$facebook_info 	= array('gender','locale','timezone');
		$google_info	= array('locale','timezone'); 

		//First check if there are a social_login_user with this uuid from the provider and then update the entry
		$provider 	= $this->data['auth']['provider'];
		$uid		= $this->data['auth']['uid'];

		$data		= array();

		$data['provider']	= $provider;
		$data['uid']        = $uid;

		$this->SocialLoginUser = ClassRegistry::init('SocialLoginUser');
		$q_r = $this->SocialLoginUser->find('first',array('conditions' 
			=> array('SocialLoginUser.provider' => $provider,'SocialLoginUser.uid' =>$uid)
		));

		if($q_r){
			$data['id']     = $q_r['SocialLoginUser']['id'];	
		}

		//Common info
		foreach($common_info as $i){
			if(array_key_exists($i,$this->data['auth']['info'])){
				$data["$i"] = $this->data['auth']['info']["$i"];
			}
		}

		//Gather the data
		if($this->data['auth']['provider'] == 'Facebook'){
			foreach($facebook_info as $f){
				if(array_key_exists($f,$this->data['auth']['raw'])){
					$data["$f"] = $this->data['auth']['raw']["$f"];
				}
			}	
		}

		//Gather the data
		if($this->data['auth']['provider'] == 'Google'){
			foreach($facebook_info as $g){
				if(array_key_exists($g,$this->data['auth']['raw'])){
					$data["$g"] = $this->data['auth']['raw']["$g"];
				}
			}	
		}

		//Update the last_connect_time
		$data['last_connect_time'] =date('Y-m-d H:i:s'); 
		//Save the data
		$this->SocialLoginUser->save($data);
		
		//--Return the ID--
		return $this->SocialLoginUser->getID();
	}

}
