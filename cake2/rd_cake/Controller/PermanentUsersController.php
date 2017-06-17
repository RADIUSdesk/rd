<?php
App::uses('AppController', 'Controller');

class PermanentUsersController extends AppController {

    public $name       = 'PermanentUsers';
    public $uses       = array('PermanentUser','Profile','Realm','User');
    public $components = array('Aa','Kicker','GridFilter');
    protected $base    = "Access Providers/Controllers/PermanentUsers/";

    protected  $read_only_attributes = array(
            'Rd-User-Type', 'Rd-Device-Owner', 'Rd-Account-Disabled', 'User-Profile', 'Expiration',
            'Rd-Account-Activation-Time', 'Rd-Not-Track-Acct', 'Rd-Not-Track-Auth', 'Rd-Auth-Type', 
            'Rd-Cap-Type-Data', 'Rd-Cap-Type-Time' ,'Rd-Realm', 'Cleartext-Password'
        );
   
    protected $AclCache = array();
    //-------- BASIC CRUD -------------------------------


     public function export_csv(){

        $this->autoRender   = false;

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        //Build query
        $user_id    = $user['id'];
        $c          = $this->_build_common_query($user);
        $q_r        = $this->{$this->modelClass}->find('all', $c);

        //Create file
        $this->ensureTmp();     
        $tmpFilename    = TMP . $this->tmpDir . DS .  strtolower( Inflector::pluralize($this->modelClass) ) . '-' . date('Ymd-Hms') . '.csv';
        $fp             = fopen($tmpFilename, 'w');

        //Headings
        $heading_line   = array();
        if(isset($this->request->query['columns'])){
            $columns = json_decode($this->request->query['columns']);
            foreach($columns as $c){
                array_push($heading_line,$c->name);
            }
        }
        fputcsv($fp, $heading_line,';','"');

        foreach($q_r as $i){

			//print_r($i);

            $columns    = array();
            $csv_line   = array();
            if(isset($this->request->query['columns'])){
                $columns = json_decode($this->request->query['columns']);
                foreach($columns as $c){
                    $column_name = $c->name;
                    if($column_name == 'notes'){
                        $notes   = '';
                        foreach($i['PermanentUserNote'] as $n){
                            if(!$this->_test_for_private_parent($n['Note'],$user)){
                                $notes = $notes.'['.$n['Note']['note'].']';    
                            }
                        }
                        array_push($csv_line,$notes);
                    }elseif($column_name =='owner'){
                        $owner_id       = $i['User']['parent_id']; //FIXME
                        $owner_tree     = $this->_find_parents($owner_id);
                        array_push($csv_line,$owner_tree);
                    }else{
                        array_push($csv_line,$i['PermanentUser']["$column_name"]);  
                    }
                }
                fputcsv($fp, $csv_line,';','"');
            }
        }

        //Return results
        fclose($fp);
        $data = file_get_contents( $tmpFilename );
        $this->cleanupTmp( $tmpFilename );
        $this->RequestHandler->respondAs('csv');
        $this->response->download( strtolower( Inflector::pluralize( $this->modelClass ) ) . '.csv' );
        $this->response->body($data);
    }


    public function index(){
        //-- Required query attributes: token;
        //-- Optional query attribute: sel_language (for i18n error messages)
        //-- also LIMIT: limit, page, start (optional - use sane defaults)
        //-- FILTER <- This will need fine tunning!!!!
        //-- AND SORT ORDER <- This will need fine tunning!!!!

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $c 			= $this->_build_common_query($user); 

        //===== PAGING (MUST BE LAST) ======
        $limit  = 50;   //Defaults
        $page   = 1;
        $offset = 0;
        if(isset($this->request->query['limit'])){
            $limit  = $this->request->query['limit'];
            $page   = $this->request->query['page'];
            $offset = $this->request->query['start'];
        }

        $c_page             = $c;
        $c_page['page']     = $page;
        $c_page['limit']    = $limit;
        $c_page['offset']   = $offset;

        $total  = $this->{$this->modelClass}->find('count'  , $c);  
        $q_r    = $this->{$this->modelClass}->find('all'    , $c_page);

        $items      = array();
        $profiles   = array();
        $realms     = array();
        
      /*  if($user['group_name'] == Configure::read('group.ap')){  //Or AP
            $ap_flag        = true;
            $ap_children    = $this->User->find_access_provider_children($user_id);
        }
        */

        foreach($q_r as $i){ 
            $owner_id       = $i['PermanentUser']['user_id'];
            $owner_tree     = $this->_find_parents($owner_id);

            //Create notes flag
            $notes_flag  = false;
            foreach($i['PermanentUserNote'] as $un){
                if(!$this->_test_for_private_parent($un['Note'],$user)){
                    $notes_flag = true;
                    break;
                }
            }
            
            //if $user_id == realm

            $action_flags = array();
            $action_flags['update'] = false;
            $action_flags['delete'] = false;
            
            $action_flags   = $this->_get_action_flags($user,$owner_id,$i['Realm']);
              
            if($action_flags['read']){
                array_push($items,
                    array(
                        'id'        			=> $i['PermanentUser']['id'], 
                        'owner'     			=> $owner_tree,				//FIXME
					    'owner_id'				=> $i['PermanentUser']['user_id'], //FIXME
                        'username'  			=> $i['PermanentUser']['username'],
                        'name'      			=> $i['PermanentUser']['name'],
                        'surname'   			=> $i['PermanentUser']['surname'], 
                        'phone'     			=> $i['PermanentUser']['phone'], 
                        'email'     			=> $i['PermanentUser']['email'],
                        'address'   			=> $i['PermanentUser']['address'],
                        'auth_type' 			=> $i['PermanentUser']['auth_type'],

                        'perc_time_used'		=> $i['PermanentUser']['perc_time_used'],
                        'perc_data_used'		=> $i['PermanentUser']['perc_data_used'],
                        'active'    			=> $i['PermanentUser']['active'], 
                        'last_accept_time'      => $i['PermanentUser']['last_accept_time'],
                        'last_accept_nas'       => $i['PermanentUser']['last_accept_nas'],
                        'last_reject_time'      => $i['PermanentUser']['last_reject_time'],
                        'last_reject_nas'       => $i['PermanentUser']['last_reject_nas'],
                        'last_reject_message'   => $i['PermanentUser']['last_reject_message'],

                        'data_used'             => $i['PermanentUser']['data_used'],
                        'data_cap'              => $i['PermanentUser']['data_cap'],
					    'time_used'             => $i['PermanentUser']['time_used'],
                        'time_cap'              => $i['PermanentUser']['time_cap'],
					    'time_cap_type'         => $i['PermanentUser']['time_cap_type'],
                        'date_cap_type'         => $i['PermanentUser']['data_cap_type'],
					    'realm'                 => $i['PermanentUser']['realm'],
					    'realm_id'              => $i['PermanentUser']['realm_id'],
					    'profile'               => $i['PermanentUser']['profile'],
					    'profile_id'            => $i['PermanentUser']['profile_id'],
                        'static_ip'             => $i['PermanentUser']['static_ip'],
					    'extra_name'            => $i['PermanentUser']['extra_name'],
					    'extra_value'           => $i['PermanentUser']['extra_value'],
                        'notes'                 => $notes_flag,
                        'update'                => $action_flags['update'],
                        'delete'                => $action_flags['delete']
                    )
                );
            }
            
        }                
        $this->set(array(
            'items'         => $items,
            'success'       => true,
            'totalCount'    => $total,
            '_serialize'    => array('items','success','totalCount')
        ));
    }

  	public function add(){

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        $this->request['active']       = 0;
        $this->request['monitor']      = 0;     


        //Two fields should be tested for first:
        if(array_key_exists('active',$this->request->data)){
            $this->request->data['active'] = 1;
        }

        if(array_key_exists('monior',$this->request->data)){
            $this->request->data['monitor'] = 1;
        }

		//auto_add device
		if(array_key_exists('auto_add',$this->request->data)){
            $this->request->data['auto_add'] = 1;
        }

        if(($this->request->data['user_id'] == '0')||($this->request->data['user_id'] == '')){ //This is the holder of the token
            $this->request->data['user_id'] = $user['id'];
        }

		

        if(!array_key_exists('language',$this->request->data)){
            $this->request->data['language'] = Configure::read('language.default');
        }

        //Get the language and country
        $country_language   = explode( '_', $this->request->data['language'] );

        $country            = $country_language[0];
        $language           = $country_language[1];

        $this->request->data['language_id'] = $language;
        $this->request->data['country_id']  = $country;

         //_____We need the profile name / id and the realm name / id before we can continue___
        $profile    = false;
        $profile_id = false;
           
        if(array_key_exists('profile',$this->request->data)){
            $profile    = $this->request->data['profile'];
            $this->Profile->contain();
            $q_r        = $this->Profile->findByName($profile);
            $profile_id = $q_r['Profile']['id'];
            $this->request->data['profile_id'] = $profile_id;  
            
        }

        if(array_key_exists('profile_id',$this->request->data)){
            $profile_id = $this->request->data['profile_id'];
            $this->Profile->contain();
            $q_r        = $this->Profile->findById($profile_id);
            $profile    = $q_r['Profile']['name'];
            $this->request->data['profile'] = $profile;    
        }

        if(($profile == false)||($profile_id == false)){
            //The loop completed fine
            $this->set(array(
                'success' => false,
                'message'   => array('message' => 'profile or profile_id not found in DB or not supplied'),
                '_serialize' => array('success','message')
            ));
            return;
        }

        $realm      = false;
        $realm_id   = false;
        
        //We also check if we need to add a suffix to the username
        $suffix                 = '';
        $suffix_permanent_users = false;
        
        if(array_key_exists('realm',$this->request->data)){
            $realm      = $this->request->data['realm'];
            $this->Realm->contain();
            $q_r        = $this->Realm->findByName($realm);
            $realm_id   = $q_r['Realm']['id']; 
            $this->request->data['realm_id'] = $realm_id;  
            $suffix     =  $q_r['Realm']['suffix']; 
            $suffix_permanent_users = $q_r['Realm']['suffix_permanent_users'];
        }

        if(array_key_exists('realm_id',$this->request->data)){
            $realm_id   = $this->request->data['realm_id'];
            $this->Realm->contain();
            $q_r        = $this->Realm->findById($realm_id);
            $realm      = $q_r['Realm']['name'];
            $this->request->data['realm'] = $realm;
            $suffix     =  $q_r['Realm']['suffix']; 
            $suffix_permanent_users = $q_r['Realm']['suffix_permanent_users'];    
        }

        if(($realm == false)||($realm_id == false)){
            //The loop completed fine
            $this->set(array(
                'success' => false,
                'message'   => array('message' => 'realm or realm_id not found in DB or not supplied'),
                '_serialize' => array('success','message')
            ));
            return;
        }
        //______ END of Realm and Profile check _____
        
        //Update the auto add of the suffix if it is specified and enabled
        if(($suffix != '')&&($suffix_permanent_users)){
            $this->request->data['username'] = $this->request->data['username'].'@'.$suffix;
        }
        

        //Zero the token to generate a new one for this user:
        $this->request->data['token'] = '';

        //The rest of the attributes should be same as the form..
        $this->{$this->modelClass}->create();
        if ($this->{$this->modelClass}->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        } else {
            $message = 'Error';
            $this->set(array(
                'errors'    => $this->{$this->modelClass}->validationErrors,
                'success'   => false,
                'message'   => array('message' => __('Could not create item')),
                '_serialize' => array('errors','success','message')
            ));
        }
    }

    public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $user_id    = $user['id'];
        $fail_flag = false;

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id'];

            //NOTE: we first check of the user_id is the logged in user OR a sibling of them:  
            $this->{$this->modelClass}->contain('Realm'); 
            $item       = $this->{$this->modelClass}->findById($this->data['id']);

            $owner_id   = $item['PermanentUser']['user_id']; 
            $username   = $item['PermanentUser']['username'];
            if($owner_id != $user_id){
            
                //What if the realm belongs to the $user_id or someone the $user_id created
                $realm_owner    = $item['Realm']['user_id'];
                $ap_children    = $this->User->find_access_provider_children($realm_owner);
                if($ap_children){   //Only if the AP has any children...
                    foreach($ap_children as $i){
                        if($user_id == $i['id']){
                            $this->{$this->modelClass}->id = $this->data['id'];
                            $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                            $this->_delete_clean_up_user($username);
                        }
                    }       
                }  
            
                if($this->_is_sibling_of($user_id,$owner_id)== true){
                    $this->{$this->modelClass}->id = $this->data['id'];
                    $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                    $this->_delete_clean_up_user($username);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->{$this->modelClass}->id = $this->data['id'];
                $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                $this->_delete_clean_up_user($username);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                $this->{$this->modelClass}->contain('Realm');
                $item       = $this->{$this->modelClass}->findById($d['id']);
                $owner_id   = $item['PermanentUser']['user_id']; 
                $username   = $item['PermanentUser']['username'];  
                
                if($owner_id != $user_id){
                
                    //What if the realm belongs to the $user_id or someone the $user_id created
                    $realm_owner    = $item['Realm']['user_id'];
                    $ap_children    = $this->User->find_access_provider_children($realm_owner);
                    if($ap_children){   //Only if the AP has any children...
                        foreach($ap_children as $i){
                            if($user_id == $i['id']){
                                $this->{$this->modelClass}->id = $d['id'];
                                $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                                $this->_delete_clean_up_user($username);
                            }
                        }       
                    }      
                
                    if($this->_is_sibling_of($user_id,$owner_id) == true){
                        $this->{$this->modelClass}->id = $d['id'];
                        $this->{$this->modelClass}->delete($this->{$this->modelClass}->id,true);
                        $this->_delete_clean_up_user($username);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->{$this->modelClass}->id = $d['id'];
                    $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                    $this->_delete_clean_up_user($username);
                }
            }
        }

        if($fail_flag == true){
            $this->set(array(
                'success'   => false,
                'message'   => array('message' => __('Could not delete some items')),
                '_serialize' => array('success','message')
            ));
        }else{
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }
	}


    public function edit(){

    }

    public function view_basic_info(){

        //We need the user_id;
        //We supply the profile_id; realm_id; cap; always_active; from_date; to_date

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $user_id    = $user['id'];

        $items = array();

        //TODO Check if the owner of this user is in the chain of the APs
        if(isset($this->request->query['user_id'])){

            $profile        = false;
            $realm          = false;
            $always_active  = true;
            $to_date        = false;
            $from_date      = false;
            $cap_data       = false;
            $cap_time       = false;
			$static_ip		= false;

            $this->{$this->modelClass}->contain('Radcheck');
            $q_r = $this->{$this->modelClass}->findById($this->request->query['user_id']);

			$items['static_ip'] 	= $q_r['PermanentUser']['static_ip'];
			$items['extra_name'] 	= $q_r['PermanentUser']['extra_name'];
			$items['extra_value'] 	= $q_r['PermanentUser']['extra_value'];
			$items['profile_id'] 	= intval($q_r['PermanentUser']['profile_id']); //VERY VERY VERY IMPORTANT not string
			$items['realm_id'] 		= intval($q_r['PermanentUser']['realm_id']); //VERY VERY VERY IMPORTANT not string
			
			$ssid_check = false;

            foreach($q_r['Radcheck'] as $rc){

                if($rc['attribute'] == 'Rd-Account-Activation-Time'){
                  $from_date =  $rc['value'];
                }

                if($rc['attribute'] == 'Expiration'){
                  $to_date =  $rc['value'];
                }
                
                if($rc['attribute'] == 'Rd-Cap-Type-Data'){
                  $cap_data =  $rc['value'];
                }
                
                if($rc['attribute'] == 'Rd-Cap-Type-Time'){
                  $cap_time =  $rc['value'];
                }

				if($rc['attribute'] == 'Rd-Ssid-Check'){
                	if($rc['value']=='1'){
						$ssid_check = true;
					}
                }

            }

            if($cap_data){
                $items['cap_data'] = $cap_data;
            }

            if($cap_time){
                $items['cap_time'] = $cap_time;
            }

            if(($from_date)&&($to_date)){
                $items['always_active'] = false;
                $items['from_date']     = $this->_extjs_format_radius_date($from_date);
                $items['to_date']       = $this->_extjs_format_radius_date($to_date);
            }else{
                $items['always_active'] = true;
            }

			//---- SSID checking ---
			$items['ssid_only'] = false;
			if($ssid_check){
				$username = $q_r['PermanentUser']['username'];
				$u = ClassRegistry::init('UserSsid');
				$q_us = $u->find('all',array('conditions' => array('UserSsid.username' => $username)));
				$ssids = array();
				foreach($q_us as $i){
					array_push($ssids, array('Ssid.name' => $i['UserSsid']['ssidname']));
				}

				$s = ClassRegistry::init('Ssid');
				$s->contain();
				$q_r = $s->find('all', array('conditions' => array('OR' =>$ssids)));
				$ssid_list = array();
				foreach($q_r as $j){
					array_push($ssid_list , array('id' => $j['Ssid']['id'], 'name' => $j['Ssid']['name']));
				}
				$items['ssid_list'] = $ssid_list;
				$items['ssid_only'] = true;
			}

        }
               // $items = array('realm_id' => 26, 'profile_id' => 2, 'always_active' => false,'cap' => 'soft');

        $this->set(array(
            'data'   => $items, //For the form to load we use data instead of the standard items as for grids
            'success' => true,
            '_serialize' => array('success','data')
        ));
    }

    public function edit_basic_info(){

		//print_r($this->request->data);
		//return;

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        //TODO Check if the owner of this user is in the chain of the APs
        if(isset($this->request->data['id'])){
            $q_r        = $this->{$this->modelClass}->findById($this->request->data['id']);
            $username   = $q_r['PermanentUser']['username'];

            if(isset($this->request->data['profile_id'])){
				$this->Profile->contain();
                $q_r = $this->Profile->findById($this->data['profile_id']);
                $profile_name = $q_r['Profile']['name'];

				//==Set Profile Name==
				$this->request->data['profile'] = $profile_name;

                $this->_replace_radcheck_item($username,'User-Profile',$profile_name);
            }

            if(isset($this->request->data['realm_id'])){
				$this->Realm->contain();
                $q_r = $this->Realm->findById($this->data['realm_id']);
                $realm_name = $q_r['Realm']['name'];

				//==Set Realm Name==
				$this->request->data['realm'] = $realm_name;
                $this->_replace_radcheck_item($username,'Rd-Realm',$realm_name);
            }

            if(isset($this->request->data['to_date'])){
                $expiration = $this->_radius_format_date($this->request->data['to_date']);
                $this->_replace_radcheck_item($username,'Expiration',$expiration);
            }

            if(isset($this->request->data['from_date'])){
                $expiration = $this->_radius_format_date($this->request->data['from_date']);
                $this->_replace_radcheck_item($username,'Rd-Account-Activation-Time',$expiration);
            }

            
            if(isset($this->request->data['always_active'])){ //Clean up if there were previous ones
                ClassRegistry::init('Radcheck')->deleteAll(
                    array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Account-Activation-Time'), false
                );

                ClassRegistry::init('Radcheck')->deleteAll(
                    array('Radcheck.username' => $username,'Radcheck.attribute' => 'Expiration'), false
                );
            }
            
            if(isset($this->request->data['cap_time'])){
                $this->_replace_radcheck_item($username,'Rd-Cap-Type-Time',$this->request->data['cap_time']);
            }else{              //Clean up if there were previous ones
                ClassRegistry::init('Radcheck')->deleteAll(
                    array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Cap-Type-Time'), false
                );
            }

            if(isset($this->request->data['cap_data'])){
                $this->_replace_radcheck_item($username,'Rd-Cap-Type-Data',$this->request->data['cap_data']);
            }else{              //Clean up if there were previous ones
                ClassRegistry::init('Radcheck')->deleteAll(
                    array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Cap-Type-Data'), false
                );
            }

			//What about the IP Address?
			if(isset($this->request->data['static_ip'])){
				if($this->request->data['static_ip']== ''){
					ClassRegistry::init('Radreply')->deleteAll(
		                array('Radreply.username' => $username,'Radreply.attribute' => 'Service-Type'), false
		            );
					ClassRegistry::init('Radreply')->deleteAll(
		                array('Radreply.username' => $username,'Radreply.attribute' => 'Framed-IP-Address'), false
		            );
				}else{
					$this->_replace_radreply_item($username,'Service-Type','Framed-User');
					$this->_replace_radreply_item($username,'Framed-IP-Address',$this->request->data['static_ip']);
				}
			}else{
				ClassRegistry::init('Radreply')->deleteAll(
		                array('Radreply.username' => $username,'Radreply.attribute' => 'Service-Type'), false
		            );
				ClassRegistry::init('Radreply')->deleteAll(
	                array('Radreply.username' => $username,'Radreply.attribute' => 'Framed-IP-Address'), false
	            );
			}

			//Auth Type (for LDAP users)
			if(isset($this->request->data['auth_type'])){
                $this->_replace_radcheck_item($username,'Rd-Auth-Type',$this->request->data['auth_type']);
            }else{              //Clean up if there were previous ones
                ClassRegistry::init('Radcheck')->deleteAll(
                    array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Auth-Type'), false
                );
            }

			//_____ New addition where we can supply SSID ids _____
			$count     = 0;
			$ssid_list = array();
			if (
				(array_key_exists('ssid_only', $this->request->data))&&
				(array_key_exists('ssid_list', $this->request->data))
			) {
				//--We force checking--
				$this->_replace_radcheck_item($username,'Rd-Ssid-Check','1');

				$ssid_list = array();

		        foreach($this->request->data['ssid_list'] as $s){
		            if($this->request->data['ssid_list'][$count] == 0){
		                $empty_flag = true;
		                break;
		            }else{
		                array_push($ssid_list,$this->request->data['ssid_list'][$count]);
		            }
		            $count++;
		        }
				$this->_replace_user_ssids($username,$ssid_list);

		    }else{
				//--We remove checking--
				ClassRegistry::init('Radcheck')->deleteAll(
                    array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Ssid-Check'), false
                );
			}
		
			//Finally update the user's table entry of the permanent user
			$this->PermanentUser->save($this->request->data);

        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    public function view_personal_info(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $items = array();
        //TODO Check if the owner of this user is in the chain of the APs
        if(isset($this->request->query['user_id'])){

            $this->{$this->modelClass}->contain();
            $q_r = $this->{$this->modelClass}->findById($this->request->query['user_id']);
           // print_r($q_r);
            if($q_r){
                $language = $q_r['PermanentUser']['country_id'].'_'.$q_r['PermanentUser']['language_id'];
                $items['language']  = $language;
                $items['name']      = $q_r['PermanentUser']['name'];
                $items['surname']   = $q_r['PermanentUser']['surname'];
                $items['phone']     = $q_r['PermanentUser']['phone'];
                $items['address']   = $q_r['PermanentUser']['address'];
                $items['email']     = $q_r['PermanentUser']['email'];
            }
        }

        $this->set(array(
            'data'   => $items, //For the form to load we use data instead of the standard items as for grids
            'success' => true,
            '_serialize' => array('success','data')
        ));

    }

    public function edit_personal_info(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        //TODO Check if the owner of this user is in the chain of the APs

        unset($this->request->data['token']);
        //Get the language and country
        $country_language   = explode( '_', $this->request->data['language'] );

        $country            = $country_language[0];
        $language           = $country_language[1];

        $this->request->data['language_id'] = $language;
        $this->request->data['country_id']  = $country;

        if ($this->PermanentUser->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));

        }else{
             $this->set(array(
                'success' => false,
                '_serialize' => array('success')
            ));
        }
    }

    public function private_attr_index(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();

       // $exclude_attribues = array(
       //     'Cleartext-Password'
       // )

        //TODO Check if the owner of this user is in the chain of the APs
        if(isset($this->request->query['username'])){
            $username = $this->request->query['username'];
            $q_r = ClassRegistry::init('Radcheck')->find('all',array('conditions' => array('Radcheck.username' => $username)));
            foreach($q_r as $i){
                $edit_flag      = true;
                $delete_flag    = true;
                if(in_array($i['Radcheck']['attribute'],$this->read_only_attributes)){
                    $edit_flag      = false;
                    $delete_flag    = false;
                }     

                array_push($items,array(
                    'id'        => 'chk_'.$i['Radcheck']['id'],
                    'type'      => 'check', 
                    'attribute' => $i['Radcheck']['attribute'],
                    'op'        => $i['Radcheck']['op'],
                    'value'     => $i['Radcheck']['value'],
                    'edit'      => $edit_flag,
                    'delete'    => $delete_flag
                ));
            }

            $q_r = ClassRegistry::init('Radreply')->find('all',array('conditions' => array('Radreply.username' => $username)));
            foreach($q_r as $i){
                $edit_flag      = true;
                $delete_flag    = true;
                if(in_array($i['Radreply']['attribute'],$this->read_only_attributes)){
                    $edit_flag      = false;
                    $delete_flag    = false;
                }     

                array_push($items,array(
                    'id'        => 'rpl_'.$i['Radreply']['id'],
                    'type'      => 'reply', 
                    'attribute' => $i['Radreply']['attribute'],
                    'op'        => $i['Radreply']['op'],
                    'value'     => $i['Radreply']['value'],
                    'edit'      => $edit_flag,
                    'delete'    => $delete_flag
                ));
            }
        }

        $this->set(array(
            'items'         => $items,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

    public function private_attr_add(){

         if(isset($this->request->query['username'])){
            $username = $this->request->query['username'];
            $this->request->data['username'] = $username;

            $this->request->data['id'] = ''; //Wipe it since ExtJs 6 add some random stuff here

            //CHECK
            if($this->request->data['type'] == 'check'){
                $rc = ClassRegistry::init('Radcheck');
                $rc->create();
                if ($rc->save($this->request->data)) {
                    $id = 'chk_'.$rc->id;
                    $this->request->data['id'] = $id;
                    $this->set(array(
                        'items'     => $this->request->data,
                        'success'   => true,
                        '_serialize' => array('success','items')
                    ));
                } else {
                    $message = 'Error';
                    $this->set(array(
                        'errors'    => $rc->validationErrors,
                        'success'   => false,
                        'message'   => array('message' => __('Could not create item')),
                        '_serialize' => array('errors','success','message')
                    ));
                }
            }

            //REPLY
            if($this->request->data['type'] == 'reply'){
                $rr = ClassRegistry::init('Radreply');
                $rr->create();
                if ($rr->save($this->request->data)) {
                    $id = 'rpl_'.$rr->id;
                    $this->request->data['id'] = $id;
                    $this->set(array(
                        'items'     => $this->request->data,
                        'success'   => true,
                        '_serialize' => array('success','items')
                    ));
                } else {
                    $message = 'Error';
                    $this->set(array(
                        'errors'    => $rr->validationErrors,
                        'success'   => false,
                        'message'   => array('message' => __('Could not create item')),
                        '_serialize' => array('errors','success','message')
                    ));
                }
            }
        }
    }

   public function private_attr_edit(){

         if(isset($this->request->query['username'])){
            $username = $this->request->query['username'];
            $this->request->data['username'] = $username;

            //Check if the type check was not changed
            if((preg_match("/^chk_/",$this->request->data['id']))&&($this->request->data['type']=='check')){ //check Type remained the same
                //Get the id for this one
                $type_id            = explode( '_', $this->data['id']);
                $this->request->data['id']   = $type_id[1];
                $rc = ClassRegistry::init('Radcheck');
                if ($rc->save($this->request->data)) {
                    $id = 'chk_'.$rc->id;
                    $this->request->data['id'] = $id;
                    $this->set(array(
                        'items'     => $this->request->data,
                        'success'   => true,
                        '_serialize' => array('success','items')
                    ));
                }else{
                    $message = 'Error';
                    $this->set(array(
                        'errors'    => $rc->validationErrors,
                        'success'   => false,
                        'message'   => array('message' => __('Could not update item')),
                        '_serialize' => array('errors','success','message')
                    ));
                }
            }

            //Check if the type reply was not changed
            if((preg_match("/^rpl_/",$this->request->data['id']))&&($this->data['type']=='reply')){ //reply Type remained the same
                //Get the id for this one
                $type_id            = explode( '_', $this->request->data['id']);
                $this->request->data['id']   = $type_id[1];
                $rr = ClassRegistry::init('Radreply');
                if ($rr->save($this->request->data)) {
                    $id = 'rpl_'.$rr->id;
                    $this->request->data['id'] = $id;
                    $this->set(array(
                        'items'     => $this->request->data,
                        'success'   => true,
                        '_serialize' => array('success','items')
                    ));
                } else {
                    $message = 'Error';
                    $this->set(array(
                        'errors'    => $rr->validationErrors,
                        'success'   => false,
                        'message'   => array('message' => __('Could not update item')),
                        '_serialize' => array('errors','success','message')
                    ));
                }
            }

            //____ Attribute Type changes ______
            if((preg_match("/^chk_/",$this->request->data['id']))&&($this->request->data['type']=='reply')){
                //Delete the check; add a reply
                $type_id            = explode( '_', $this->request->data['id']);
                $rc = ClassRegistry::init('Radcheck');
                $rc->id = $type_id[1];
                $rc->delete();

                //Create
                $rr = ClassRegistry::init('Radreply');
                $rr->create();
                if ($rr->save($this->request->data)) {
                    $id = 'rpl_'.$rr->id;
                    $this->request->data['id'] = $id;
                    $this->set(array(
                        'items'     => $this->request->data,
                        'success'   => true,
                        '_serialize' => array('success','items')
                    ));
                } else {
                    $message = 'Error';
                    $this->set(array(
                        'errors'    => $rr->validationErrors,
                        'success'   => false,
                        'message'   => array('message' => __('Could not update item')),
                        '_serialize' => array('errors','success','message')
                    ));
                }
            }

            if((preg_match("/^rpl_/",$this->request->data['id']))&&($this->request->data['type']=='check')){

                //Delete the check; add a reply
                $type_id            = explode( '_', $this->request->data['id']);
                $rr = ClassRegistry::init('Radreply');
                $rr->id = $type_id[1];
                $rr->delete();

                //Create
                $rc = ClassRegistry::init('Radcheck');
                $rc->create();
                if ($rc->save($this->request->data)) {
                    $id = 'chk_'.$rc->id;
                    $this->request->data['id'] = $id;
                    $this->set(array(
                        'items'     => $this->request->data,
                        'success'   => true,
                        '_serialize' => array('success','items')
                    ));
                } else {
                    $message = 'Error';
                    $this->set(array(
                        'errors'    => $rc->validationErrors,
                        'success'   => false,
                        'message'   => array('message' => __('Could not update item')),
                        '_serialize' => array('errors','success','message')
                    ));
                }
            }
        }
    }

    public function private_attr_delete(){

        $fail_flag = true;

        $rc = ClassRegistry::init('Radcheck');
        $rr = ClassRegistry::init('Radreply');

        if(isset($this->data['id'])){   //Single item delete
            $type_id            = explode( '_', $this->request->data['id']);
            $fail_flag          = false;

            if(preg_match("/^chk_/",$this->request->data['id'])){
                
                //Check if it should not be deleted
                $qr = $rc->findById($type_id[1]);
                if($qr){
                    $name = $qr['Radcheck']['attribute'];
                    if(in_array($name,$this->read_only_attributes)){
                        $fail_flag = true;
                    }else{
                        $rc->id = $type_id[1];
                        $rc->delete();
                    }            
                }
            }

            if(preg_match("/^rpl_/",$this->request->data['id'])){   
                $rr->id = $type_id[1];
                $rr->delete();
            }         
   
        }else{ 
            $fail_flag          = false; 
                        //Assume multiple item delete
            foreach($this->data as $d){
                $type_id            = explode( '_', $d['id']);
                if(preg_match("/^chk_/",$d['id'])){

                    //Check if it should not be deleted
                    $qr = $rc->findById($type_id[1]);
                    if($qr){
                        $name = $qr['Radcheck']['attribute'];
                        if(in_array($name,$this->read_only_attributes)){
                            $fail_flag = true;
                        }else{
                            $rc->id = $type_id[1];
                            $rc->delete();
                        }            
                    }
                }
                if(preg_match("/^rpl_/",$d['id'])){   
                    $rr->id = $type_id[1];
                    $rr->delete();
                }           
            }
        }

        if($fail_flag == true){
            $this->set(array(
                'success'   => false,
                'message'   => array('message' => __('Could not delete some items')),
                '_serialize' => array('success','message')
            ));
        }else{
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }
    }

    public function view_tracking(){

         //We need the user_id;
        //We supply the track_auth, track_acct

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $user_id    = $user['id'];
        $items = array();

        //TODO Check if the owner of this user is in the chain of the APs
        if(isset($this->request->query['user_id'])){

            $acct           = true;
            $auth           = true;

            $this->{$this->modelClass}->contain('Radcheck');
            $q_r = $this->{$this->modelClass}->findById($this->request->query['user_id']);

            foreach($q_r['Radcheck'] as $rc){

                if($rc['attribute'] == 'Rd-Not-Track-Acct'){
                    if($rc['value'] == 1){
                        $acct = false;
                    }
                }

                if($rc['attribute'] == 'Rd-Not-Track-Auth'){
                  if($rc['value'] == 1){
                        $auth = false;
                  }
                } 
            }
            $items['track_auth'] = $auth;
            $items['track_acct'] = $acct;
            
        }

        $this->set(array(
            'data'   => $items, //For the form to load we use data instead of the standard items as for grids
            'success' => true,
            '_serialize' => array('success','data')
        ));
    }

    public function edit_tracking(){

          //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        //TODO Check if the owner of this user is in the chain of the APs
        if(isset($this->request->data['id'])){
            $q_r        = $this->{$this->modelClass}->findById($this->request->data['id']);
            $username   = $q_r['PermanentUser']['username'];
           
            //Not Track auth (Rd-Not-Track-Auth) *By default we will (in post-auth) 
            if(!isset($this->request->data['track_auth'])){
                $this->_replace_radcheck_item($username,'Rd-Not-Track-Auth',1);
            }else{              //Clean up if there were previous ones
                ClassRegistry::init('Radcheck')->deleteAll(
                    array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Not-Track-Auth'), false
                );
            }

            //Not Track acct (Rd-Not-Track-Acct) *By default we will (in pre-acct)
            if(!isset($this->request->data['track_acct'])){
                $this->_replace_radcheck_item($username,'Rd-Not-Track-Acct',1);
            }else{              //Clean up if there were previous ones
                ClassRegistry::init('Radcheck')->deleteAll(
                    array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Not-Track-Acct'), false
                );
            }

        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    //Adds of removes the special Rd-Mac-Check check attribute to a permanent user to restrict the devices they can use
    public function restrict_list_of_devices(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        if((isset($this->request->query['username']))&&(isset($this->request->query['restrict']))){
            $username = $this->request->query['username'];
            if($this->request->query['restrict'] == 'true'){
                $this->_replace_radcheck_item($username,'Rd-Mac-Check',1);       
            }else{
                ClassRegistry::init('Radcheck')->deleteAll(
                    array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Mac-Check'), false
                ); 
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }


    //Enable or disable the Auto-Mac Add functionality for the permanent users
    public function auto_mac_on_off(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        if((isset($this->request->query['username']))&&(isset($this->request->query['auto_mac']))){
            $username = $this->request->query['username'];
            if($this->request->query['auto_mac'] == 'true'){
                $this->_replace_radcheck_item($username,'Rd-Auto-Mac',1);       
            }else{
                ClassRegistry::init('Radcheck')->deleteAll(
                    array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Auto-Mac'), false
                ); 
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }


    public function change_password(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        //For this action to sucees on the User model we need: 
        // id; group_id; password; token should be empty ('')
        $success = false;

        if(
            (isset($this->request->data['user_id']))||
            (isset($this->request->data['username'])) //Can also change by specifying username
            ){

            //If we got the username; we will have to fetch the ID
            if(isset($this->request->data['username'])){
                $q_un = $this->{$this->modelClass}->findByUsername($this->request->data['username']);
                if($q_un){
                    $this->request->data['user_id'] = $q_un['PermanentUser']['id'];
                }
            }

            $d           					= array();
            $d['PermanentUser']['id']       = $this->request->data['user_id'];
            $d['PermanentUser']['password'] = $this->request->data['password'];
            $d['PermanentUser']['token']    = '';
            $this->{$this->modelClass}->id  = $this->request->data['user_id'];
            $this->{$this->modelClass}->save($d);

            //Check if there are auto add devices and wipe them out (These devices will start with "Auto add");
            $this->Device           = ClassRegistry::init('Device');
            $this->Device->contain();
            $q_r = $this->Device->find('all',array('conditions' =>array('Device.permanent_user_id' => $user_id,'Device.description LIKE' => 'Auto add%')));
            
            foreach($q_r as $i){
                $username           = $i['Device']['name'];
                $this->Device->id   = $i['Device']['id'];
                $this->Device->delete($i['Device']['id'], true);
                $this->_delete_clean_up_device($username);
            }

            //Check if we need to add or remove actvation and expiry dates
            $this->PermanentUser->contain();
            $q_user = $this->PermanentUser->findById($this->request->data['user_id']);

            if($q_user){
                $username = $q_user['PermanentUser']['username'];

                if(isset($this->request->data['to_date'])){
                    $expiration = $this->_radius_format_date($this->request->data['to_date']);
                    $this->_replace_radcheck_item($username,'Expiration',$expiration);
                }

                if(isset($this->request->data['from_date'])){
                    $expiration = $this->_radius_format_date($this->request->data['from_date']);
                    $this->_replace_radcheck_item($username,'Rd-Account-Activation-Time',$expiration);
                }

                
                if(isset($this->request->data['always_active'])){ //Clean up if there were previous ones
                    ClassRegistry::init('Radcheck')->deleteAll(
                        array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Account-Activation-Time'), false
                    );

                    ClassRegistry::init('Radcheck')->deleteAll(
                        array('Radcheck.username' => $username,'Radcheck.attribute' => 'Expiration'), false
                    );
                }
                //---End of Expiry and Activation---

                //Also check if the person is not perhaps currently connected and then kick them off
                $this->radacct  = ClassRegistry::init('Radacct');
                $this->radacct->contain();
                $q_acct = $this->radacct->find('all',array('conditions' => array('Radacct.username' => $username,'Radacct.acctstoptime' => 'null')));
                foreach($q_acct as $a){
                    $this->Kicker->kick($a['Radacct']);
                }
            }
            $success               = true;  
        }

        $this->set(array(
            'success' => $success,
            '_serialize' => array('success',)
        ));
    }

    public function view_password(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $success    = false;
        $value      = false;
        $activate   = false;
        $expire     = false;

        if(isset($this->request->query['user_id'])){
            $this->{$this->modelClass}->contain('Radcheck');
            $q_r = $this->{$this->modelClass}->findById($this->request->query['user_id']);
            if($q_r){

                foreach($q_r['Radcheck'] as $i){
                    if($i['attribute'] == 'Cleartext-Password'){
                        $value = $i['value'];
                    }
                    if($i['attribute'] == 'Rd-Account-Activation-Time'){
                        $activate = $this->_extjs_format_radius_date($i['value']);
                    }
                    if($i['attribute'] == 'Expiration'){
                        $expire = $this->_extjs_format_radius_date($i['value']);
                    }
                }
            }
            $success = true;
        }
        $this->set(array(
            'success'   => $success,
            'value'     => $value,
            'activate'  => $activate,
            'expire'    => $expire,
            '_serialize' => array('success','value','activate','expire')
        ));

    }

    public function enable_disable(){
        
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $rb     = $this->request->data['rb'];
        $d      = array();
        //For this action to sucees on the User model we need: 
        // id; group_id; active


        if($rb == 'enable'){
            $d['PermanentUser']['active'] = 1;
        }else{
            $d['PermanentUser']['active'] = 0;
        }

        foreach(array_keys($this->request->data) as $key){
            if(preg_match('/^\d+/',$key)){
                $d['PermanentUser']['id']       = $key;
                $this->{$this->modelClass}->id  = $key;
                $this->{$this->modelClass}->save($d);   
            }
        }
        $this->set(array(
            'success' => true,
            '_serialize' => array('success',)
        ));
    }


    public function note_index(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();
        if(isset($this->request->query['for_id'])){
            $u_id   = $this->request->query['for_id'];
            $q_r    = $this->PermanentUser->PermanentUserNote->find('all', 
                array(
                    'contain'       => array('Note'),
                    'conditions'    => array('PermanentUserNote.permanent_user_id' => $u_id)
                )
            );
            foreach($q_r as $i){
                if(!$this->_test_for_private_parent($i['Note'],$user)){
                    $owner_id   = $i['Note']['user_id'];
                    $owner      = $this->_find_parents($owner_id);
                    array_push($items,
                        array(
                            'id'        => $i['Note']['id'], 
                            'note'      => $i['Note']['note'], 
                            'available_to_siblings' => $i['Note']['available_to_siblings'],
                            'owner'     => $owner,
                            'delete'    => true
                        )
                    );
                }
            }
        } 
        $this->set(array(
            'items'     => $items,
            'success'   => true,
            '_serialize'=> array('success', 'items')
        ));
    }

    public function note_add(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        //Get the creator's id
        if($this->request->data['user_id'] == '0'){ //This is the holder of the token - override '0'
            $this->request->data['user_id'] = $user_id;
        }

        //Make available to siblings check
        if(isset($this->request->data['available_to_siblings'])){
            $this->request->data['available_to_siblings'] = 1;
        }else{
            $this->request->data['available_to_siblings'] = 0;
        }

        $success    = false;
        $msg        = array('message' => __('Could not create note'));
        $this->PermanentUser->PermanentUserNote->Note->create(); 
        //print_r($this->request->data);
        if ($this->PermanentUser->PermanentUserNote->Note->save($this->request->data)) {
            $d                          = array();
            $d['PermanentUserNote']['permanent_user_id']   	= $this->request->data['for_id'];
            $d['PermanentUserNote']['note_id']   			= $this->PermanentUser->PermanentUserNote->Note->id;
            $this->PermanentUser->PermanentUserNote->create();
            if ($this->PermanentUser->PermanentUserNote->save($d)) {
                $success = true;
            }
        }

        if($success){
            $this->set(array(
                'success' => $success,
                '_serialize' => array('success')
            ));
        }else{
             $this->set(array(
                'success' => $success,
                'message' => $message,
                '_serialize' => array('success','message')
            ));
        }
    }

    public function note_del(){

        if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $user_id    = $user['id'];
        $fail_flag  = false;

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id'];

            //NOTE: we first check of the user_id is the logged in user OR a sibling of them:   
            $item       = $this->PermanentUser->PermanentUserNote->Note->findById($this->data['id']);
            $owner_id   = $item['Note']['user_id'];
            if($owner_id != $user_id){
                if($this->_is_sibling_of($user_id,$owner_id)== true){
                    $this->PermanentUser->PermanentUserNote->Note->id = $this->data['id'];
                    $this->PermanentUser->PermanentUserNote->Note->delete($this->data['id'],true);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->PermanentUser->PermanentUserNote->Note->id = $this->data['id'];
                $this->PermanentUser->PermanentUserNote->Note->delete($this->data['id'],true);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                $item       = $this->PermanentUser->PermanentUserNote->Note->findById($d['id']);
                $owner_id   = $item['Note']['user_id'];
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($user_id,$owner_id) == true){
                        $this->PermanentUser->PermanentUserNote->Note->id = $d['id'];
                        $this->PermanentUser->PermanentUserNote->Note->delete($d['id'],true);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->PermanentUser->PermanentUserNote->Note->id = $d['id'];
                    $this->PermanentUser->PermanentUserNote->Note->delete($d['id'],true);
                }
 
            }
        }

        if($fail_flag == true){
            $this->set(array(
                'success'   => false,
                'message'   => array('message' => __('Could not delete some items')),
                '_serialize' => array('success','message')
            ));
        }else{
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }

    }


    //--------- END BASIC CRUD ---------------------------

    //----- Menus ------------------------
    public function menu_for_grid(){

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        //Empty by default
        $menu = array();

        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin

            $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                        array( 'xtype' =>  'splitbutton',  'glyph'     => Configure::read('icnReload'), 'scale'   => 'large', 'itemId'    => 'reload',   'tooltip'    => __('Reload'),
                            'menu'  => array( 
                                'items' => array( 
                                    '<b class="menu-title">'.__('Reload every').':</b>',
                                  //  array( 'text'   => _('Cancel auto reload'),   'itemId' => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true ),
                                    array( 'text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                                    array( 'text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                                    array( 'text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                                    array( 'text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )
                                   
                                )
                            )
                    ),
                   // array('xtype' => 'button', 'scale' => 'large', 'itemId' => 'reload',   'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnAdd'), 'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnEdit'), 'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit'))
                )),
                array('xtype' => 'buttongroup','title' => __('Document'), 'items' => array(
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnNote'),  'scale' => 'large', 'itemId' => 'note',     'tooltip'=> __('Add notes')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnCsv'), 'scale' => 'large', 'itemId' => 'csv',      'tooltip'=> __('Export CSV')),
                )),
                array('xtype' => 'buttongroup','title' => __('Extra actions'), 'items' => array(
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnLock'), 'scale' => 'large', 'itemId' => 'password', 'tooltip'=> __('Change password')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnLight'), 'scale' => 'large', 'itemId' => 'enable_disable','tooltip'=> __('Enable / Disable')),
                
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnRadius'), 'scale' => 'large', 'itemId' => 'test_radius',  'tooltip'=> __('Test RADIUS')),
                    array(
                        'xtype'     => 'button', 
                        'glyph'     => Configure::read('icnGraph'),   
                        'scale'     => 'large', 
                        'itemId'    => 'graph',  
                        'tooltip'   => __('Graphs')
                    )
                )) 
            );
        }

        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)

            $id             = $user['id'];
            $action_group   = array();
            $document_group = array();
            $specific_group = array();
            //Reload
            array_push($action_group,array( 
                'xtype'     =>  'splitbutton',  
                'glyph'     => Configure::read('icnReload'),   
                'scale'     => 'large', 
                'itemId'    => 'reload',   
                'tooltip'   => __('Reload'),
                'menu'      => array(             
                    'items'     => array( 
                                    '<b class="menu-title">'.__('Reload every').':</b>',          
                    array( 'text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                    array( 'text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                    array( 'text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                    array( 'text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )                                  
                ))));

            //Add
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base."add")){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnAdd'),      
                    'scale'     => 'large', 
                    'itemId'    => 'add',      
                    'tooltip'   => __('Add')));
            }
            //Delete
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'delete')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnDelete'),   
                    'scale'     => 'large', 
                    'itemId'    => 'delete',
                    'disabled'  => true,   
                    'tooltip'   => __('Delete')));
            }

            //Edit
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'edit_basic_info')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnEdit'),     
                    'scale'     => 'large', 
                    'itemId'    => 'edit',
                    'disabled'  => true,     
                    'tooltip'   => __('Edit')));
            }

            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'note_index')){ 
                array_push($document_group,array(
                        'xtype'     => 'button', 
                        'glyph'     => Configure::read('icnNote'),      
                        'scale'     => 'large', 
                        'itemId'    => 'note',      
                        'tooltip'   => __('Add Notes')));
            }

            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'export_csv')){ 
                array_push($document_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnCsv'),     
                    'scale'     => 'large', 
                    'itemId'    => 'csv',      
                    'tooltip'   => __('Export CSV')));
            }


            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'change_password')){ 
                  array_push($specific_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnLock'), 
                    'scale'     => 'large', 
                    'itemId'    => 'password', 
                    'tooltip'=> __('Change password')));
            }
           
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'enable_disable')){ 
                  array_push($specific_group, array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnLight'),  
                    'scale'     => 'large', 
                    'itemId'    => 'enable_disable',
                    'tooltip'=> __('Enable / Disable')));
            }
            
            array_push($specific_group, array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnRadius'),     
                    'scale'     => 'large', 
                    'itemId'    => 'test_radius',  
                    'tooltip'   => __('Test RADIUS')));
          
            array_push($specific_group, array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnGraph'),   
                    'scale'     => 'large', 
                    'itemId'    => 'graph',  
                    'tooltip'   => __('Graphs')));

            $menu = array(
                        array('xtype' => 'buttongroup','title' => __('Action'),         'items' => $action_group),
                        array('xtype' => 'buttongroup','title' => __('Document'),       'items' => $document_group),
                        array('xtype' => 'buttongroup','title' => __('Extra actions'),  'items' => $specific_group)
                    );
        }
        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

    function menu_for_accounting_data(){

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        //Empty by default
        $menu = array();

        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                        array( 'xtype'=>  'button', 'glyph'     => Configure::read('icnReload'), 'scale' => 'large', 'itemId' => 'reload',   'tooltip'   => __('Reload')),
                        array('xtype' => 'button',  'glyph'     => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'   => __('Delete')), 
                )) 
            );
        }

        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                        array( 'xtype'=>  'button', 'glyph'     => Configure::read('icnReload'), 'scale' => 'large', 'itemId' => 'reload',   'tooltip'   => __('Reload')),
                        array('xtype' => 'button',  'glyph'     => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'   => __('Delete')), 
                )) 
            );
        }

        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));


    }

     function menu_for_authentication_data(){

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        //Empty by default
        $menu = array();

        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                        array( 'xtype'=>  'button', 'glyph'     => Configure::read('icnReload'), 'scale' => 'large', 'itemId' => 'reload',   'tooltip'   => __('Reload')),
                        array('xtype' => 'button',  'glyph'     => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'   => __('Delete')), 
                )) 
            );
        }

        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                        array( 'xtype'=>  'button', 'glyph'     => Configure::read('icnReload'), 'scale' => 'large', 'itemId' => 'reload',   'tooltip'   => __('Reload')),
                        array('xtype' => 'button',  'glyph'     => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'   => __('Delete')), 
                )) 
            );
        }

        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

    function menu_for_user_devices(){

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        //Empty by default
        $menu = array();

        $checked            = false;
        $checked_add_mac    = false;
        if(isset($this->request->query['username'])){
            $count = $this->{$this->modelClass}->Radcheck->find('count',array('conditions' => 
                array(
                    'Radcheck.username'     => $this->request->query['username'], 
                    'Radcheck.attribute'    => 'Rd-Mac-Check',
                    'Radcheck.value'        => '1',
                )
            ));
            if($count > 0){
                $checked = true;
            }

            //Auto-add MAC check
            $count_add_mac = $this->{$this->modelClass}->Radcheck->find('count',array('conditions' => 
                array(
                    'Radcheck.username'     => $this->request->query['username'], 
                    'Radcheck.attribute'    => 'Rd-Auto-Mac',
                    'Radcheck.value'        => '1',
                )
            ));
            if($count_add_mac > 0){
                $checked_add_mac = true;
            }
        }
        

        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                        array( 'xtype'=>  'button', 'glyph'     => Configure::read('icnReload'), 'scale' => 'large', 'itemId' => 'reload',   'tooltip'   => __('Reload')),
                        array( 
                            'xtype'         => 'checkbox', 
                            'boxLabel'      => 'Connect only from listed devices', 
                            'itemId'        => 'chkListedOnly',
                            'checked'       => $checked, 
                            'cls'           => 'lblRd',
                            'margin'        => 5
                        ),
                        array( 
                            'xtype'         => 'checkbox', 
                            'boxLabel'      => 'Auto-add device after authentication', 
                            'itemId'        => 'chkAutoAddMac',
                            'checked'       => $checked_add_mac, 
                            'cls'           => 'lblRd',
                            'margin'        => 5
                        )
                )) 
            );
        }

        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                        array( 'xtype'=>  'button', 'glyph'     => Configure::read('icnReload'), 'scale' => 'large', 'itemId' => 'reload',   'tooltip'   => __('Reload')),
                        array( 
                            'xtype'         => 'checkbox', 
                            'boxLabel'      => 'Connect only from listed devices', 
                            'itemId'        => 'chkListedOnly',
                            'checked'       => $checked, 
                            'cls'           => 'lblRd',
                            'margin'        => 5
                        ),
                        array( 
                            'xtype'         => 'checkbox', 
                            'boxLabel'      => 'Auto-add device after authentication', 
                            'itemId'        => 'chkAutoAddMac',
                            'checked'       => $checked_add_mac, 
                            'cls'           => 'lblRd',
                            'margin'        => 5
                        )
                )) 
            );
        }
        
        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));

    }

    //______ END EXT JS UI functions ________


     function _build_common_query($user){

        //Empty to start with
        $c                  = array();
        $c['joins']         = array(); 
        $c['conditions']    = array();

        //What should we include....
        $c['contain']   = array(
                            'PermanentUserNote' => array('Note.note','Note.id','Note.available_to_siblings','Note.user_id'),
                            'User',
                            'Realm'
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'PermanentUser.username';
        $dir    = 'DESC';

        if(isset($this->request->query['sort'])){
            if($this->request->query['sort'] == 'owner'){	//FIXME
                $sort = 'User.username';
            }elseif(($this->request->query['sort'] == 'profile')||($this->request->query['sort'] == 'realm')){
                $sort = 'Radcheck.value';
            }else{
                $sort = $this->modelClass.'.'.$this->request->query['sort'];
            }
            $dir  = $this->request->query['dir'];
        } 

        $c['order'] = array("$sort $dir");
        //==== END SORT ===

         //If it is a combobox filter
        if(isset($this->request->query['query'])){
            $query = $this->request->query['query'];
            array_push($c['conditions'],array("PermanentUser.username LIKE" => '%'.$query.'%'));
        }


        //====== REQUEST FILTER =====
        if(isset($this->request->query['filter'])){
            $filter = json_decode($this->request->query['filter']);
            foreach($filter as $f){

                $f = $this->GridFilter->xformFilter($f);

                //Strings
                if($f->type == 'string'){
                    if($f->field == 'owner'){
                        array_push($c['conditions'],array("User.username LIKE" => '%'.$f->value.'%'));   
                    }else{
                        $col = $this->modelClass.'.'.$f->field;
                        array_push($c['conditions'],array("$col LIKE" => '%'.$f->value.'%'));
                    }
                }
                //Bools
                if($f->type == 'boolean'){
                     $col = $this->modelClass.'.'.$f->field;
                     array_push($c['conditions'],array("$col" => $f->value));
                }
            }
        }

		//=== Check if the combobox send us a filter request ===
        if(isset($this->request->query['query'])){
            $un = $this->request->query['query'];
            array_push($c['conditions'],array("PermanentUser.username LIKE" => '%'.$un.'%'));  
        }

        //====== END REQUEST FILTER =====

        //====== AP FILTER =====
        //If the user is an AP; we need to add an extra clause to only show all the AP's downward from its position in the tree
        if($user['group_name'] == Configure::read('group.ap')){  //AP 
        
            $tree_array = array();
            $user_id    = $user['id'];

            //**AP and upward in the tree**
            $this->parents = $this->User->getPath($user_id,'User.id');
            //So we loop this results asking for the parent nodes who have available_to_siblings = true
            foreach($this->parents as $i){
                $i_id = $i['User']['id'];
                if($i_id != $user_id){ //upstream
                    if($this->Acl->check(array(
                        'model'         => 'User', 
                        'foreign_key'   => $user_id), 
                        "Access Providers/Other Rights/View users or vouchers not created self")
                    ){
                        array_push($tree_array,array('Realm.user_id' => $i_id,'Realm.available_to_siblings' => true));
                    }
                }else{
                    array_push($tree_array,array('Realm.user_id' => $i_id));
                }
            }
  
            //** ALL the AP's children
            $ap_children    = $this->User->find_access_provider_children($user_id);
            if($ap_children){   //Only if the AP has any children...
                foreach($ap_children as $i){
                    $id = $i['id'];
                    array_push($tree_array,array('User.id' => $id));
                }       
            } 
            
            //print_r($tree_array); 
                        
            //Add it as an OR clause
            array_push($c['conditions'],array('OR' => $tree_array));  
        }      
        //====== END AP FILTER =====
        return $c;
    }

    private function _find_parents($id){

        $this->User->contain();//No dependencies
        $q_r        = $this->User->getPath($id);
        $path_string= '';
        if($q_r){

            foreach($q_r as $line_num => $i){
                $username       = $i['User']['username'];
                if($line_num == 0){
                    $path_string    = $username;
                }else{
                    $path_string    = $path_string.' -> '.$username;
                }
            }
            if($line_num > 0){
                return $username." (".$path_string.")";
            }else{
                return $username;
            }
        }else{
            return __("orphaned");
        }
    }

    private function _delete_clean_up_user($username){

        $this->{$this->modelClass}->Radcheck->deleteAll(   //Delete a previous one
            array('Radcheck.username' => $username), false
        );

        $this->{$this->modelClass}->Radreply->deleteAll(   //Delete a previous one
            array('Radreply.username' => $username), false
        );

        $acct = ClassRegistry::init('Radacct');
        $acct->deleteAll( 
            array('Radacct.username' => $username), false
        );

        $post_a = ClassRegistry::init('Radpostauth');
        $post_a->deleteAll( 
            array('Radpostauth.username' => $username), false
        );

		$user_s = ClassRegistry::init('UserStat');
        $user_s->deleteAll( 
            array('UserStat.username' => $username), false
        );

		$user_ssid = ClassRegistry::init('UserSsid');
        $user_ssid->deleteAll( 
            array('UserSsid.username' => $username), false
        );

    }

    private function _is_sibling_of($parent_id,$user_id){
        $this->User->contain();//No dependencies
        $q_r        = $this->User->getPath($user_id);
        foreach($q_r as $i){
            $id = $i['User']['id'];
            if($id == $parent_id){
                return true;
            }
        }
        //No match
        return false;
    }

    private function _get_action_flags($user,$owner_id,$realm){
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            return array('update' => true, 'delete' => true, 'read' => true);
        }

        if($user['group_name'] == Configure::read('group.ap')){  //AP

            //If the $user['id'] is the same is the owner; we allow them to update and delete
            if($user['id'] == $owner_id){
                return array('update' => true, 'delete' => true, 'read' => true);
            }
            
            $realm_id = $realm['id'];         
            
            if(array_key_exists($realm_id,$this->AclCache)){
                return $this->AclCache[$realm_id];
            }else{
            
                //If the Realm is owned by the $user or someone owned by the $user we allow them
                $ap_children    = $this->User->find_access_provider_children($user['id']);
                if($ap_children){   //Only if the AP has any children...
                    foreach($ap_children as $i){
                        $c_id = $i['id'];
                        if($c_id == $realm['user_id']){
                            $this->AclCache[$realm_id] =  array('update' => true, 'delete' => true,'read' => true);
                            return array('update' => true, 'delete' => true, 'read' => true); 
                        }
                    }       
                }  
            
                if($this->Acl->check(array(
                    'model'         => 'User', 
                    'foreign_key'   => $user['id']), 
                    "Access Providers/Other Rights/View users or vouchers not created self")
                ){
                    //Only those realms that this user has specified to have read access to
                    $read = $this->Acl->check(
                                array('model' => 'User', 'foreign_key' => $user['id']), 
                                array('model' => 'Realms','foreign_key' => $realm_id), 'read');
                }else{
                    $read = false; //Since the user is not the owner and they can not view other's permanent users we leave it out
                }  
                
                $update = $this->Acl->check(
                                array('model' => 'User', 'foreign_key' => $user['id']), 
                                array('model' => 'Realms','foreign_key' => $realm_id), 'update');
                $delete = $this->Acl->check(
                                array('model' => 'User', 'foreign_key' => $user['id']), 
                                array('model' => 'Realms','foreign_key' => $realm_id), 'delete');
                //Prime it                 
                $this->AclCache[$realm_id] =  array('update' => $update, 'delete' => $delete,'read' => $read);      
                return array('update' => $update, 'delete' => $delete,'read' => $read);
            }
        }
    }

    private function _replace_radcheck_item($username,$item,$value,$op = ":="){
        $rc = ClassRegistry::init('Radcheck');
        $rc->deleteAll(
            array('Radcheck.username' => $username,'Radcheck.attribute' => $item), false
        );
        $rc->create();
        $d['Radcheck']['username']  = $username;
        $d['Radcheck']['op']        = $op;
        $d['Radcheck']['attribute'] = $item;
        $d['Radcheck']['value']     = $value;
        $rc->save($d);
        $rc->id         = null;
    }

	 private function _replace_radreply_item($username,$item,$value,$op = ":="){
        $rr = ClassRegistry::init('Radreply');
        $rr->deleteAll(
            array('Radreply.username' => $username,'Radreply.attribute' => $item), false
        );
        $rr->create();
        $d['Radreply']['username']  = $username;
        $d['Radreply']['op']        = $op;
        $d['Radreply']['attribute'] = $item;
        $d['Radreply']['value']     = $value;
        $rr->save($d);
        $rr->id         = null;
    }

    private function _radius_format_date($d){
        //Format will be month/date/year eg 03/06/2013 we need it to be 6 Mar 2013
        $arr_date   = explode('/',$d);
        $month      = intval($arr_date[0]);
        $m_arr      = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $day        = intval($arr_date[1]);
        $year       = intval($arr_date[2]);
        return "$day ".$m_arr[($month-1)]." $year";
    }

    private function _extjs_format_radius_date($d){
        //Format will be day month year 20 Mar 2013 and need to be month/date/year eg 03/06/2013 
        $arr_date   = explode(' ',$d);
        $month      = $arr_date[1];
        $m_arr      = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $day        = intval($arr_date[0]);
        $year       = intval($arr_date[2]);

        $month_count = 1;
        foreach($m_arr as $m){
            if($month == $m){
                break;
            }
            $month_count ++;
        }
        return "$month_count/$day/$year";
    }

    private function _delete_clean_up_device($username){

        $this->{$this->modelClass}->Radcheck->deleteAll(   //Delete a previous one
            array('Radcheck.username' => $username), false
        );

        $this->{$this->modelClass}->Radreply->deleteAll(   //Delete a previous one
            array('Radreply.username' => $username), false
        );

        $acct = ClassRegistry::init('Radacct'); //With devices we use callingstaton id instead of username
        $acct->deleteAll( 
            array('Radacct.callingstationid' => $username), false
        );

        $post_a = ClassRegistry::init('Radpostauth');
        $post_a->deleteAll( 
            array('Radpostauth.username' => $username), false
        );

		$user_s = ClassRegistry::init('UserStat');
        $user_s->deleteAll( 
            array('UserStat.username' => $username), false
        );
    }

	private function _replace_user_ssids($username,$ssid_list){
		$u = ClassRegistry::init('UserSsid');

		//Clean up previous ones
		$u->deleteAll(
			array('UserSsid.username' => $username), false
		);

		//Get all the SSID names from the $ssid_list
		$s = ClassRegistry::init('Ssid');
		$s->contain();
		$id_list = array();
		foreach($ssid_list as $i){
			array_push($id_list, array('Ssid.id' => strval($i)));
		}

		$q_r = $s->find('all', array('conditions' => array('OR' =>$id_list)));

		foreach($q_r as $j){
			$name = $j['Ssid']['name'];
			$data = array();
			$data['username'] = $username;
			$data['ssidname'] = $name;
			$u->create();
			$u->save($data);
			$u->id = null;			
		}
	}
}
