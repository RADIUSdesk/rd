<?php
App::uses('AppController', 'Controller');

class VouchersController extends AppController {

    public $name       = 'Vouchers';
    public $components = array('Aa','VoucherGenerator','GridFilter','VoucherCsv');
    public $uses       = array('Voucher','User','Profile','Realm','EmailMessage');
    protected $base    = "Access Providers/Controllers/Vouchers/"; //Required for AP Rights

    protected  $read_only_attributes = array(
            'Rd-User-Type', 'Rd-Device-Owner', 'Rd-Account-Disabled', 'User-Profile', 'Expiration',
            'Rd-Account-Activation-Time', 'Rd-Not-Track-Acct', 'Rd-Not-Track-Auth', 'Rd-Auth-Type', 
            'Rd-Cap-Type-Data', 'Rd-Cap-Type-Time' ,'Rd-Realm', 'Cleartext-Password', 'Rd-Voucher'
        );

	private $singleField	= true;
	
	protected $AclCache = array();

    //-------- BASIC CRUD -------------------------------

	public function pdf_export_settings(){

		Configure::load('Vouchers'); 
        $data       = Configure::read('voucher_dafaults'); //Read the defaults

		$this->set(array(
            'data'     => $data,
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
	}

  	public function export_csv(){

        set_time_limit(60); //Double it 

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

            $columns    = array();
            $csv_line   = array();
            if(isset($this->request->query['columns'])){
                $columns = json_decode($this->request->query['columns']);
                foreach($columns as $c){
                    $column_name = $c->name;
                    if($column_name =='owner'){
                        $owner_id       = $i['User']['id'];
                        $owner_tree     = $this->_find_parents($owner_id);
                        array_push($csv_line,$owner_tree);
                    }else{
                        array_push($csv_line,$i['Voucher']["$column_name"]);  
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


  
    public function export_pdf(){

        set_time_limit(60); //Double it 

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

		App::import('Vendor', 'voucher_pdf');
        App::import('Vendor', 'label_pdf');

        
        $this->response->type('application/pdf');
        $this->layout = 'pdf';

		//We improve this function by also allowing the user to specify certain values
		//which in turn will influence the outcome of the PDF
		Configure::load('Vouchers');
 
        $output_instr  	= Configure::read('voucher_dafaults'); //Read the defaults

		foreach(array_keys($output_instr) as $k){
			if(isset($this->request->query[$k])){
				if(($k == 'language')||($k == 'format')||($k == 'orientation')){
					$output_instr["$k"] 	= $this->request->query["$k"];
				}else{
					$output_instr["$k"] = true;
				}    
		    }else{
				if(!(($k == 'language')||($k == 'format')||($k == 'orientation'))){
					$output_instr["$k"] = false;
				}
			}
		}

        $pieces = explode('_',$output_instr['language']);
        $l      = ClassRegistry::init('Language');
        $l->contain();
        $l_q    = $l->findById($pieces[1]);
        //print_r($l_q);
        if($l_q['Language']['rtl'] == '1'){
			$output_instr['rtl'] = true;
        }else{
			$output_instr['rtl'] = false;
        }

		$this->set('output_instr',$output_instr);

        //==== Selected items is the easiest =====
        //We need to see if there are a selection:
        if(isset($this->request->query['selected'])){
            $selected = json_decode($this->request->query['selected']);
            $sel_condition = array();
            foreach($selected as $i){
                array_push($sel_condition, array("Voucher.id" => $i)); 
            }

            $voucher_data = array();
            $q_r = $this->{$this->modelClass}->find('all', array('conditions' => array('OR' => $sel_condition)));
            foreach($q_r as $i){
                $v                  = array();
                $v['username']      = $i['Voucher']['name'];
                $v['password']      = $i['Voucher']['password'];
                $v['expiration']    = $i['Voucher']['expire'];
                $v['days_valid']    = $i['Voucher']['time_valid'];
                $v['profile']       = $i['Voucher']['profile'];
                $v['extra_name']    = $i['Voucher']['extra_name'];
                $v['extra_value']   = $i['Voucher']['extra_value'];

                $realm_id           = $i['Voucher']['realm_id'];
                $realm              = $i['Voucher']['realm'];
                if(!array_key_exists($realm,$voucher_data)){
                    $r = $this->Realm->findById($realm_id);
                    $voucher_data[$realm] = $r['Realm'];
                    $voucher_data[$realm]['vouchers'] = array();
                }

                array_push($voucher_data[$realm]['vouchers'],$v);   
            }
            $this->set('voucher_data',$voucher_data);
        }else{
            //Check if there is a filter applied
           
     
            $c          = $this->_build_common_query($user);
            $q_r        = $this->{$this->modelClass}->find('all', $c);

            $voucher_data = array();

            foreach($q_r as $i){
                $v                  = array();
                $v['username']      = $i['Voucher']['name'];
                $v['password']      = $i['Voucher']['password'];
                $v['expiration']    = $i['Voucher']['expire'];
                $v['days_valid']    = $i['Voucher']['time_valid'];
                $v['profile']       = $i['Voucher']['profile'];
                $v['extra_name']    = $i['Voucher']['extra_name'];
                $v['extra_value']   = $i['Voucher']['extra_value'];

                $realm_id           = $i['Voucher']['realm_id'];
                $realm              = $i['Voucher']['realm'];
                if(!array_key_exists($realm,$voucher_data)){
                    $r = $this->Realm->findById($realm_id);
                    $voucher_data[$realm] = $r['Realm'];
                    $voucher_data[$realm]['vouchers'] = array();
                }
                array_push($voucher_data[$realm]['vouchers'],$v); 
                
            }
            $this->set('voucher_data',$voucher_data);
        }
    }

    public function index(){
        //-- Required query attributes: token;
        //-- Optional query attribute: sel_language (for i18n error messages)
        //-- also LIMIT: limit, page, start (optional - use sane defaults)
        //-- FILTER 
        //-- AND SORT ORDER 

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $c = $this->_build_common_query($user); 

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

        $total      = $this->{$this->modelClass}->find('count'  , $c);  
        $q_r        = $this->{$this->modelClass}->find('all'    , $c_page);
        $items      = array();
        $af_hash    = array();

        foreach($q_r as $i){            
            $owner_id   = $i['Voucher']['user_id'];

            if(!array_key_exists($owner_id,$af_hash)){ //Avoid duplicate queries
                $af_hash["$owner_id"] = $this->_get_action_flags($user,$owner_id,$i['Realm']);
            }
           
            if($af_hash["$owner_id"]['read']){     
                array_push($items,
                    array(
                        'id'                    => $i['Voucher']['id'], 
                        'owner'                 => $i['User']['username'],
                        'user_id'               => $i['User']['id'],
                        'batch'                 => $i['Voucher']['batch'],
                        'name'                  => $i['Voucher']['name'],
                        'password'              => $i['Voucher']['password'],
                        'realm'                 => $i['Voucher']['realm'],
                        'realm_id'              => $i['Voucher']['realm_id'],
                        'profile'               => $i['Voucher']['profile'],
                        'profile_id'            => $i['Voucher']['profile_id'],
                        'perc_time_used'        => $i['Voucher']['perc_time_used'],
                        'perc_data_used'        => $i['Voucher']['perc_data_used'],
                        'status'                => $i['Voucher']['status'],
                        'last_accept_time'      => $i['Voucher']['last_accept_time'],
                        'last_accept_nas'       => $i['Voucher']['last_accept_nas'],
                        'last_reject_time'      => $i['Voucher']['last_reject_time'],
                        'last_reject_nas'       => $i['Voucher']['last_reject_nas'],
                        'last_reject_message'   => $i['Voucher']['last_reject_message'],
                        'update'                => $af_hash["$owner_id"]['update'],
                        'delete'                => $af_hash["$owner_id"]['delete'],
                        'extra_name'            => $i['Voucher']['extra_name'],
                        'extra_value'           => $i['Voucher']['extra_value'],
                        'expire'                => $i['Voucher']['expire'],
                        'time_valid'            => $i['Voucher']['time_valid']
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

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        //Get the owner's id
        if($this->request->data['user_id'] == '0'){ //This is the holder of the token - override '0'
            $this->request->data['user_id'] = $user_id;
        }
   
        //___Two fields should be tested for first___:
        if(array_key_exists('activate_on_login',$this->request->data)){
            $this->request->data['activate_on_login'] = 1;
        }

        if(array_key_exists('never_expire',$this->request->data)){
            $this->request->data['never_expire'] = 1;
        }
        //____ END OF TWO FIELD CHECK ___
    
        //_____We need the profile name / if and the realm name / id before we can continue___
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
        $suffix          = '';
        $suffix_vouchers = false;
        
        
        if(array_key_exists('realm',$this->request->data)){
            $realm      = $this->request->data['realm'];
            $this->Realm->contain();
            $q_r        = $this->Realm->findByName($realm);
            $realm_id   = $q_r['Realm']['id']; 
            $this->request->data['realm_id'] = $realm_id; 
            
            $suffix     =  $q_r['Realm']['suffix']; 
            $suffix_vouchers = $q_r['Realm']['suffix_vouchers']; 
        }

        if(array_key_exists('realm_id',$this->request->data)){
            $realm_id   = $this->request->data['realm_id'];
            $this->Realm->contain();
            $q_r        = $this->Realm->findById($realm_id);
            $realm      = $q_r['Realm']['name'];
            $this->request->data['realm'] = $realm; 
            
            $suffix     =  $q_r['Realm']['suffix']; 
            $suffix_vouchers = $q_r['Realm']['suffix_vouchers'];    
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

		//Check if this is a single field voucher or not
		$single_field = true; //Default = true
		if(array_key_exists('single_field',$this->request->data)){
			if($this->request->data['single_field'] == 'false'){
				$single_field = false;
			}else{
				//Source a list of all the voucher names
				$this->Voucher->contain();
				$t_v_names = $this->Voucher->find('all',array('fields' => array('Voucher.name')));
				foreach($t_v_names as $n){
					$v_name = $n['Voucher']['name'];
					array_push($this->VoucherGenerator->voucherNames, $v_name);
				}	
			}
		}

        //The rest of the attributes should be same as the form.

        if(array_key_exists('quantity',$this->request->data)){
            $qty = $this->request->data['quantity'];
            $counter = 0;
            while($counter < $qty){
				//Set the voucher's name and password
				$pwd = false;
				if($single_field){
					//if(
					//	(array_key_exists('name',$this->request->data))&& //Both name and password has to be present
					//	(array_key_exists('password',$this->request->data))
					//){
					//	$this->log('Add a voucher with name and password specified', 'debug');
					//}else{
						$pwd = $this->VoucherGenerator->generateVoucher();
						
						$this->request->data['name']      = $pwd; 
		        		$this->request->data['password']  = $pwd;		        		
		        		
		        		//Update the auto add of the suffix if it is specified and enabled
                        if(($suffix != '')&&($suffix_vouchers)){
                            $this->request->data['name'] = $pwd.'@'.$suffix;
                            $this->request->data['password'] = $pwd.'@'.$suffix;
                        }
		        		
					//}	
				}

                $this->{$this->modelClass}->create();
                if ($this->{$this->modelClass}->save($this->request->data)) {
                    $success_flag = true;
                    $this->{$this->modelClass}->id = null;
                } else {
                    $message = 'Error';
                    $this->set(array(
                        'errors'    => $this->{$this->modelClass}->validationErrors,
                        'success'   => false,
                        'message'   => array('message' => __('Could not create item')),
                        '_serialize' => array('errors','success','message')
                    ));
                    return; //Get out of here!
                }
                $counter = $counter + 1;
            }

            //The loop completed fine
            $data = $this->{$this->modelClass}->CreatedVouchers;
            $this->set(array(
                'success' => true,
                'data'    => $data,
                '_serialize' => array('success','data')
            ));
        }  
    }
    
    
    public function add_csv(){
    
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        //Get the owner's id
        if($this->request->data['user_id'] == '0'){ //This is the holder of the token - override '0'
            $this->request->data['user_id'] = $user_id;
        }
   
        //___Two fields should be tested for first___:
        if(array_key_exists('activate_on_login',$this->request->data)){
            $this->request->data['activate_on_login'] = 1;
        }

        if(array_key_exists('never_expire',$this->request->data)){
            $this->request->data['never_expire'] = 1;
        }
        //____ END OF TWO FIELD CHECK ___
    
        //_____We need the profile name / if and the realm name / id before we can continue___
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
        if(array_key_exists('realm',$this->request->data)){
            $realm      = $this->request->data['realm'];
            $this->Realm->contain();
            $q_r        = $this->Realm->findByName($realm);
            $realm_id   = $q_r['Realm']['id']; 
            $this->request->data['realm_id'] = $realm_id;  
        }

        if(array_key_exists('realm_id',$this->request->data)){
            $realm_id   = $this->request->data['realm_id'];
            $this->Realm->contain();
            $q_r        = $this->Realm->findById($realm_id);
            $realm      = $q_r['Realm']['name'];
            $this->request->data['realm'] = $realm;    
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
        
        
        //___ Email message ____
        $message_id = false;
        //If the email_title and email_message is not empty we will create an entry into the email_messages table
        if(
        (array_key_exists('email_title',$this->request->data))&&
        (array_key_exists('email_message',$this->request->data))
        ){
            if(
                ($this->request->data['email_title'] != '')&&
                ($this->request->data['email_message'] != '')
            ){
                $e_d            = array();
                $e_d['name']    = $this->request->data['batch'];
                $e_d['title']   = $this->request->data['email_title'];
                $e_d['message'] = $this->request->data['email_message'];
                $this->EmailMessage->save($e_d);
                $message_id = $this->EmailMessage->id;
            }
        }
          
        
        $this->layout   = 'ext_file_upload'; 
        $temp_file      = "/tmp/csv_file.csv";
        
        
        move_uploaded_file ($_FILES['csv_file']['tmp_name'] , $temp_file);
        $batch          = $this->request->data['batch'];   
        $voucher_list   = $this->VoucherCsv->generateVoucherList($temp_file,$batch,$message_id);
        
        $success_flag = true;
        foreach($voucher_list as $v){
        
            $this->request->data['name']        = $v['name']; 
		    $this->request->data['password']    = $v['password'];
		    $this->request->data['extra_name']  = $v['extra_name'];
		    $this->request->data['extra_value'] = $v['extra_value'];
            if (!$this->{$this->modelClass}->save($this->request->data)) {
               $success_flag = false; 
            }
            $this->{$this->modelClass}->id = null;
        }
          
        $json_return            = array();   
        $json_return['success'] = true;
        $json_return['t']       = $voucher_list;
        $this->set('json_return',$json_return);
    
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

        $user_id            = $user['id'];
        $fail_flag          = false;
        $this->Radcheck     = ClassRegistry::init('Radcheck');
        $this->Radreply     = ClassRegistry::init('Radreply');
        $this->Radacct      = ClassRegistry::init('Radacct');
        $this->Radpostauth  = ClassRegistry::init('Radpostauth');
		$this->UserStat  	= ClassRegistry::init('UserStat');


	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id'];
            //NOTE: we first check of the user_id is the logged in user OR a sibling of them:
            $this->{$this->modelClass}->contain('User');
            $item       = $this->{$this->modelClass}->findById($this->data['id']);
            $ap_id      = $item['User']['id'];
            $username   = $item['Voucher']['name'];
            if($ap_id != $user_id){
                if($this->_is_sibling_of($user_id,$ap_id)== true){
                    $this->{$this->modelClass}->id = $this->data['id'];
                    $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                    $this->_delete_clean_up_voucher($username);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->{$this->modelClass}->id = $this->data['id'];
                $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                $this->_delete_clean_up_voucher($username);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                $item       = $this->{$this->modelClass}->findById($d['id']);
                $ap_id      = $item['User']['id'];
                $username   = $item['Voucher']['name'];
                if($ap_id != $user_id){
                    if($this->_is_sibling_of($user_id,$ap_id) == true){
                        $this->{$this->modelClass}->id = $d['id'];
                        $this->{$this->modelClass}->delete($this->{$this->modelClass}->id,true);
                        $this->_delete_clean_up_voucher($username);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->{$this->modelClass}->id = $d['id'];
                    $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                    $this->_delete_clean_up_voucher($username);
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

    public function view_basic_info(){

        //We need the voucher_id;
        //We supply the profile_id; realm_id; cap; always_active; from_date; to_date

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $user_id    = $user['id'];
        $items      = array();

        //TODO Check if the owner of this voucher is in the chain of the APs
        if(isset($this->request->query['voucher_id'])){
        
            $this->{$this->modelClass}->contain();
            $q_r = $this->{$this->modelClass}->findById($this->request->query['voucher_id']);
            if($q_r){
                //"profile_id":7,"realm_id":34,"activate_on_login":"activate_on_login","days_valid":"2","never_expire":false,"expire":"5\/31\/2015"}
                $items['profile_id']    = intval($q_r['Voucher']['profile_id']);
                $items['realm_id']      = intval($q_r['Voucher']['realm_id']);
				$items['profile_id']    = intval($q_r['Voucher']['profile_id']);
                $items['realm']         = $q_r['Voucher']['realm_id'];
				$items['profile']       = $q_r['Voucher']['realm_id'];
                $items['extra_name']    = $q_r['Voucher']['extra_name'];
                $items['extra_value']   = $q_r['Voucher']['extra_value'];

                if($q_r['Voucher']['time_valid'] != ''){
                    $items['activate_on_login'] = 'activate_on_login';
                    $pieces                     = explode("-", $q_r['Voucher']['time_valid']);
                    $items['days_valid']        = $pieces[0];  
                    $items['hours_valid']       = $pieces[1];
                    $items['minutes_valid']     = $pieces[2]; 

                }

                if($q_r['Voucher']['expire'] != ''){
                    $items['never_expire']  = false;
                    $items['expire']        = $q_r['Voucher']['expire'];
                }else{
                    $items['never_expire'] = true;
                }

				//SSID list
				$ssid_check = false;
				$username 	= $q_r['Voucher']['name'];
		        $rc 		= ClassRegistry::init('Radcheck');
				$ssid_count = $rc->find('count',
					array('conditions' => array(
						'Radcheck.username' 	=> $username,
						'Radcheck.attribute' 	=> 'Rd-Ssid-Check',
						'Radcheck.value' 		=> '1',
					))
				);

		        if($ssid_count > 0){
					$ssid_check = true;
				}

				//---- SSID checking ---
				$items['ssid_only'] = false;
				if($ssid_check){
					$username = $q_r['Voucher']['name'];
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
        }

        $this->set(array(
            'data'   => $items,
            'success' => true,
            '_serialize' => array('success','data')
        ));
    }

    public function edit_basic_info(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        //___Three fields should be tested for first___:
        if(array_key_exists('activate_on_login',$this->request->data)){
            $this->request->data['activate_on_login'] = 1;
        }

        if(array_key_exists('never_expire',$this->request->data)){
            $this->request->data['never_expire'] = 1;
        }

		if(array_key_exists('ssid_only',$this->request->data)){
            $this->request->data['ssid_only'] = 1;
        }
        //____ END OF TWO FIELD CHECK ___
    
        //_____We need the profile name / if and the realm name / id before we can continue___
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
        if(array_key_exists('realm',$this->request->data)){
            $realm      = $this->request->data['realm'];
            $this->Realm->contain();
            $q_r        = $this->Realm->findByName($realm);
            $realm_id   = $q_r['Realm']['id']; 
            $this->request->data['realm_id'] = $realm_id;  
        }

        if(array_key_exists('realm_id',$this->request->data)){
            $realm_id   = $this->request->data['realm_id'];
            $this->Realm->contain();
            $q_r        = $this->Realm->findById($realm_id);
            $realm      = $q_r['Realm']['name'];
            $this->request->data['realm'] = $realm;    
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

		$this->request->data['status'] = 'new'; //Make it new so it changes visibly

        //VERY VERY important to cascade throught to the radcheck entries
        $this->request->data['do_radcheck'] = true;

        $result = $this->{$this->modelClass}->save($this->request->data);
        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    public function private_attr_index(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();

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

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

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

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

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

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $fail_flag = false;

        $rc = ClassRegistry::init('Radcheck');
        $rr = ClassRegistry::init('Radreply');

        if(isset($this->data['id'])){   //Single item delete
            $type_id            = explode( '_', $this->request->data['id']);
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
        }else{                          //Assume multiple item delete
            $fail_flag          = false;
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

	public function voucher_device_index(){

        //__ Authentication + Authorization __
     //   $user = $this->_ap_right_check();
     //   if(!$user){
    //        return;
    //    }
    //    $user_id   	= $user['id'];
        $fail_flag 	= false;
		$items		= array();
		$username   = $this->request->query['username'];

        $rc = ClassRegistry::init('Radcheck');

		$q_r = $rc->find('all', array('conditions' => 
			array(
				'Radcheck.attribute' 	=> 'Rd-Voucher-Device-Owner',
				'Radcheck.value'		=> $username
			)
		));

		if($q_r){
			foreach($q_r as $i){
				$id 	= $i['Radcheck']['id'];
				$mac	= $i['Radcheck']['username'];
				array_push($items,array('id' => $id, 'mac' => $mac));
			}
		}

		$this->set(array(
            'items'         => $items,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
	}

	public function voucher_device_add(){
		$items 		= array();

		//We will first search for any place this MAC might be currently specified and remove it
		$rc 		= ClassRegistry::init('Radcheck');
		$mac 		= $this->request->data['mac'];
		$username	= $this->request->data['username'];

		$rc->deleteAll(
            array('Radcheck.username' => $mac), false
        );

		//Now we search for the intems belonging to the voucher
		$q_r 	= $rc->find('all', array('conditions' => array('Radcheck.username' => $username)));

		if($q_r){ //Found the voucher....

			$profile 	= false;
			$type		= false;
			$realm		= false;

			foreach($q_r as $i){
				//profile
				if($i['Radcheck']['attribute'] == 'User-Profile'){
					$profile = $i['Radcheck']['value'];
				}
				//type
				if($i['Radcheck']['attribute'] == 'Rd-User-Type'){
					$type = $i['Radcheck']['value'];
				}
				//realm
				if($i['Radcheck']['attribute'] == 'Rd-Realm'){
					$realm = $i['Radcheck']['value'];
				}
			}
		
			if(($type == 'voucher')&&($realm)&&($profile)){

				//User-Type = voucher-device
				$data = array('username' => $mac,'attribute' => 'Rd-User-Type', 'op' => ':=', 'value' => 'voucher-device');
				$rc->create();
				$rc->save($data);
				$rc->id = null;

				//Voucher who owns this device
				$data = array('username' => $mac,'attribute' => 'Rd-Voucher-Device-Owner', 'op' => ':=', 'value' => $username);
				$rc->create();
				$rc->save($data);
				$rc->id = null;

				//profile
				$data = array('username' => $mac,'attribute' => 'User-Profile', 'op' => ':=', 'value' => $profile);
				$rc->create();
				$rc->save($data);
				$rc->id = null;

				//realm
				$data = array('username' => $mac,'attribute' => 'Rd-Realm', 'op' => ':=', 'value' => $realm);
				$rc->create();
				$rc->save($data);
				$rc->id = null;

			}
		}


		$this->set(array(
            'items'         => $items,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
	}

	public function voucher_device_delete(){
		$rc 	= ClassRegistry::init('Radcheck');
		if(isset($this->data['mac'])){   //Single item delete
			$mac = $this->data['mac'];
            $rc->deleteAll(
            	array('Radcheck.username' => $mac), false
        	);
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                $mac 	= $d['mac'];
		        $rc->deleteAll(
		        	array('Radcheck.username' => $mac), false
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
            (isset($this->request->data['voucher_id']))||
            (isset($this->request->data['name'])) //Can also change by specifying name
            ){

			$single_field = false;

            if(isset($this->request->data['name'])){
				$this->{$this->modelClass}->contain();
                $q_n = $this->{$this->modelClass}->findByName($this->request->data['name']);
                if($q_n){
                    $this->request->data['voucher_id'] = $q_n['Voucher']['id'];
					if($q_n['Voucher']['name'] == $q_n['Voucher']['password']){	//Test to see if it is not perhaps a single field voucher
						$single_field = true;
					}
                }
            }else{

				$this->{$this->modelClass}->contain();
                $q_n = $this->{$this->modelClass}->findById($this->request->data['voucher_id']);
                if($q_n){
					if($q_n['Voucher']['name'] == $q_n['Voucher']['password']){	//Test to see if it is not perhaps a single field voucher
						$single_field = true;
					}
                }
			}

			//We refuse to change tha password of single field vouchers
			if($single_field){
				$this->set(array(
				    'success' => false,
					'message'	=> array('message' => 'Cannot change the password of a single field voucher'),
				    '_serialize' => array('success','message')
				));
			}else{

		        $this->request->data['id']      = $this->request->data['voucher_id'];
		        $this->{$this->modelClass}->id  = $this->request->data['voucher_id'];
		        $this->{$this->modelClass}->save($this->request->data);


		        //Get the name of this voucher
		        if(!(isset($this->request->data['name']))){
		            $this->{$this->modelClass}->contain();
		            $q_r                            = $this->{$this->modelClass}->findById($this->request->data['voucher_id']);
		            $this->request->data['name']    = $q_r['Voucher']['name'];
		        }

		        if(isset($this->request->data['name'])){
		            $this->_replace_radcheck_item($this->request->data['name'],'Cleartext-Password',$this->request->data['password']);
		            $success    = true; 
		        }

				$this->set(array(
				    'success' => $success,
				    '_serialize' => array('success',)
				));
			}
        }
    }

    public function email_voucher_details(){
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id= $user['id'];

        $id     = $this->request->data['id'];
        $this->{$this->modelClass}->contain();
        $q_r    = $this->{$this->modelClass}->findById($id);
        $to     = $this->request->data['email'];
        $message= $this->request->data['message'];

         if($q_r){
            $username       = $q_r['Voucher']['name'];
            $password       = $q_r['Voucher']['password'];
            $valid_for      = $q_r['Voucher']['time_valid'];
            $profile        = $q_r['Voucher']['profile'];
            $extra_name     = $q_r['Voucher']['extra_name'];
            $extra_value     = $q_r['Voucher']['extra_value'];

            //  print_r("The username is $username and password is $password");
			$email_server = Configure::read('EmailServer');
            App::uses('CakeEmail', 'Network/Email');
            $Email = new CakeEmail();
            $Email->config($email_server);
            $Email->subject('Your voucher detail');
            $Email->to($to);
            $Email->viewVars(compact( 'username', 'password','valid_for','profile','extra_name','extra_value','message'));
            $Email->template('voucher_detail', 'voucher_notify');
            $Email->emailFormat('html');
            $Email->send();

        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }


    //--------- END BASIC CRUD ---------------------------

     public function pdf_voucher_formats(){
        $items = array();
        $ct = Configure::read('voucher_formats');
        foreach($ct as $i){
            if($i['active']){
                array_push($items, $i);
            }
        }
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

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
                        array( 'xtype' =>  'splitbutton',  'glyph' => Configure::read('icnReload'),  'scale'   => 'large', 'itemId'    => 'reload',   'tooltip'    => __('Reload'),
                            'menu'  => array( 
                                'items' => array( 
                                    '<b class="menu-title">Reload every:</b>',
                                    array( 'text'  => _('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                                    array( 'text'  => _('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                                    array( 'text'  => _('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                                    array( 'text'  => _('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )
                                   
                                )
                            )
                    ),
                    array(
						'xtype' 	=> 'splitbutton',   
						'glyph' 	=> Configure::read('icnAdd'),    
						'scale' 	=> 'large', 
						'itemId' 	=> 'add',      
						'tooltip'	=> __('Add'),
						'menu'  => array( 
                                'items' => array( 
                                    array( 'text'  => _('Single field'),      		'itemId'    => 'addSingle', 'group' => 'add', 'checked' => true ),
                                    array( 'text'  => _('Username and Password'),   'itemId'    => 'addDouble', 'group' => 'add' ,'checked' => false), 
                                    array( 'text'  => _('Import CSV List'),         'itemId'    => 'addCsvList','group' => 'add' ,'checked' => false),  
                                )
                            )
					),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnEdit'),   'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit'))
                )),
                array('xtype' => 'buttongroup','title' => __('Document'), 'items' => array(
                    array('xtype' => 'button', 'glyph' => Configure::read('icnPdf'),    'scale' => 'large', 'itemId' => 'pdf',     'tooltip'=> __('Export to PDF')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnCsv'),    'scale' => 'large', 'itemId' => 'csv',      'tooltip'=> __('Export CSV')),
                    array(
                        'xtype'     => 'button', 
                        'glyph'     => Configure::read('icnEmail'),
                        'scale'     => 'large', 
                        'itemId'    => 'email', 
                        'tooltip'   => __('e-Mail voucher')
                    )
                )),
                array('xtype' => 'buttongroup','title' => __('Extra actions'), 'items' => array(
                    array('xtype' => 'button', 'glyph' => Configure::read('icnLock'), 'scale' => 'large', 'itemId' => 'password', 'tooltip'=> __('Change password')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnRadius'),    'scale' => 'large', 'itemId' => 'test_radius',  'tooltip'=> __('Test RADIUS')),
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
                                    '<b class="menu-title">Reload every:</b>',            
                    array( 'text'  => _('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                    array( 'text'  => _('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                    array( 'text'  => _('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                    array( 'text'  => _('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )                                  
                ))));

            //Add
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base."add")){
                array_push($action_group,array(
                    'xtype'     => 'splitbutton',
                    'glyph'     => Configure::read('icnAdd'),   
                    'scale'     => 'large', 
                    'itemId'    => 'add',      
                    'tooltip'   => __('Add'),
                    'menu'  => array(
                                 'items' => array(
                                    array( 'text'  => _('Single field'),   'itemId'    => 'addSingle', 'group' => 'add', 'checked' => true ),
                                    array( 'text'  => _('Username and Password'),   'itemId'    => 'addDouble', 'group' => 'add' ,'checked' => false),
                                    array( 'text'  => _('Import CSV List'),         'itemId'    => 'addCsvList','group' => 'add' ,'checked' => false), 
                                 )
                             )
           			));
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

            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'export_csv')){

                array_push($document_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnPdf'),    
                    'scale'     => 'large', 
                    'itemId'    => 'pdf',      
                    'tooltip'   => __('Export to PDF')));

                array_push($document_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnCsv'),     
                    'scale'     => 'large', 
                    'itemId'    => 'csv',      
                    'tooltip'   => __('Export CSV')));

                 array_push($document_group,array(
                        'xtype'     => 'button', 
                        'glyph'     => Configure::read('icnEmail'),
                        'scale'     => 'large', 
                        'itemId'    => 'email', 
                        'tooltip'   => __('e-Mail voucher')
                    ));

            }

           if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'change_password')){

                array_push($specific_group, array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnLock'),
                    'scale'     => 'large', 
                    'itemId'    => 'password', 
                    'disabled'  => true,
                    'tooltip'   => __('Change password')));


                array_push($specific_group, array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnRadius'),   
                    'scale'     => 'large', 
                    'itemId'    => 'test_radius',  
                    'tooltip'   => __('Test RADIUS')));

            }
            
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
                        array( 'xtype'=>  'button', 'glyph' => Configure::read('icnReload'), 'scale' => 'large', 'itemId' => 'reload',   'tooltip'   => __('Reload')),
                        array('xtype' => 'button',  'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'   => __('Delete')), 
                )) 
            );
        }

        //Access Provider => selected power
        if($user['group_name'] == Configure::read('group.ap')){  //Admin
            $id   = $user['id'];
            $actions = array();
            array_push($actions,
                array( 'xtype'=>  'button', 'glyph' => Configure::read('icnReload'), 'scale' => 'large', 'itemId' => 'reload',   'tooltip'   => __('Reload'))
            );

           
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'delete_accounting_data')){
                array_push($actions,
                    array('xtype' => 'button', 'glyph'  => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'   => __('Delete'))
                );             
            }

            $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => $actions) 
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
                            'User',
                            'Realm'                         
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'Voucher.name';
        $dir    = 'DESC';

        if(isset($this->request->query['sort'])){
            if($this->request->query['sort'] == 'owner'){
                $sort = 'User.username';
            }else{
                $sort = $this->modelClass.'.'.$this->request->query['sort'];
            }
            $dir  = $this->request->query['dir'];
        } 

        $c['order'] = array("$sort $dir");
        //==== END SORT ===

        //======= For a specified owner filter *Usually on the edit permanent user ======
        if(isset($this->request->query['user_id'])){
            $u_id = $this->request->query['user_id'];
            array_push($c['conditions'],array($this->modelClass.".user_id" => $u_id));
        }
        
        //If it is a combobox filter
        if(isset($this->request->query['query'])){
            $query = $this->request->query['query'];
            array_push($c['conditions'],array("Voucher.name LIKE" => '%'.$query.'%'));
        }


        //====== REQUEST FILTER =====
        if(isset($this->request->query['filter'])){
            $filter = json_decode($this->request->query['filter']);
            foreach($filter as $f){
                
                //Clause for the PDF filter style
                if(array_key_exists('data',$f)){
                    print_r($f->data->type);
                    $f->type  = $f->data->type;
                    $f->value = $f->data->value;
                }

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

                 //Lists
                if($f->type == 'list'){
                    $list_array = array();
                    foreach($f->value as $filter_list){
                        $col = $this->modelClass.'.'.$f->field;
                        array_push($list_array,array("$col" => "$filter_list"));
                    }
                    array_push($c['conditions'],array('OR' => $list_array));
                }

                //Bools
                if($f->type == 'boolean'){
                     $col = $this->modelClass.'.'.$f->field;
                     array_push($c['conditions'],array("$col" => $f->value));
                }
            }
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

    private function _delete_clean_up_voucher($username){
        $this->Radcheck->deleteAll(
            array('Radcheck.username' => $username), false
        );
        $this->Radreply->deleteAll( 
            array('Radreply.username' => $username), false
        );
        $this->Radacct->deleteAll( 
            array('Radacct.username' => $username), false
        );
        $this->Radpostauth->deleteAll( 
            array('Radpostauth.username' => $username), false
        );

		$this->UserStat->deleteAll( 
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
        if($q_r){
            foreach($q_r as $i){
                $id = $i['User']['id'];
                if($id == $parent_id){
                    return true;
                }
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
                    $read = $this->Acl->check(
                                array('model' => 'User', 'foreign_key' => $user['id']), 
                                array('model' => 'Realms','foreign_key' => $realm_id), 'read');
                }else{
                    $read = false; //Since the user is not the owner and they can not view other's vouchers we leave it out
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

}
?>
