<?php
App::uses('AppController', 'Controller');

class ApProfilesController extends AppController {

    public $name        = 'ApProfiles';
    public $components  = array('Aa','GridFilter','TimeCalculations');
    public $uses        = array('ApProfile','User','DynamicClient','DynamicPair','DynamicClientRealm','Realm','OpenvpnServer','OpenvpnServerClient');
    protected $base     = "Access Providers/Controllers/ApProfiles/";
    protected $itemNote = 'ApProfileNote';
    
    //____ BASIC CRUD Manager ________
    public function index(){

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

        $total  = $this->{$this->modelClass}->find('count',$c);       
        $q_r    = $this->{$this->modelClass}->find('all',$c_page);

        $items      = array();

        foreach($q_r as $i){
            //Create notes flag
            $notes_flag  = false;
            foreach($i['ApProfileNote'] as $nn){
                if(!$this->_test_for_private_parent($nn['Note'],$user)){
                    $notes_flag = true;
                    break;
                }
            }

            $owner_id       = $i['ApProfile']['user_id'];
            $owner_tree     = $this->_find_parents($owner_id);
            $action_flags   = $this->_get_action_flags($owner_id,$user);
			$ap_profile_id  = $i['ApProfile']['id'];

			$now		= time();

			$ap_count 	= 0;
			$aps_up		= 0;
			$aps_down   = 0;
			foreach($i['Ap'] as $ap){
			    //Get the 'dead_after' value
			    $dead_after = $this->_get_dead_after($ap['ap_profile_id']);
			
				$l_contact  = $ap['last_contact'];
				//===Determine when last did we saw this ap (never / up / down) ====
				$last_timestamp = strtotime($l_contact);
	            if($last_timestamp+$dead_after <= $now){
	                $aps_down++;
	            }else{
					$aps_up++;  
	            }
				$ap_count++;
			}

            array_push($items,array(
                'id'                    => $i['ApProfile']['id'], 
                'name'                  => $i['ApProfile']['name'],
				'available_to_siblings' => $i['ApProfile']['available_to_siblings'],
                'ap_count'              => $ap_count,
                'aps_up'                => $aps_up,
                'aps_down'              => $aps_down,
                'owner'                 => $owner_tree, 
                'notes'                 => $notes_flag,
                'update'                => $action_flags['update'],
                'delete'                => $action_flags['delete'],
                'view'                  => $action_flags['view'],
            ));
        }
       
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            'totalCount' => $total,
            '_serialize' => array('items','success','totalCount')
        ));
    }
    
    
    public function add() {

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

        $this->{$this->modelClass}->create();
        if ($this->{$this->modelClass}->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }else {
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
            $item           = $this->{$this->modelClass}->findById($this->data['id']);
            $owner_id       = $item['ApProfile']['user_id'];
            $profile_name   = $item['ApProfile']['name'];
            if($owner_id != $user_id){
                if($this->_is_sibling_of($user_id,$owner_id)== true){
                    $this->{$this->modelClass}->id = $this->data['id'];
                    $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->{$this->modelClass}->id = $this->data['id'];
                $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                $item           = $this->{$this->modelClass}->findById($d['id']);
                $owner_id       = $item['ApProfile']['user_id'];
                $profile_name   = $item['ApProfile']['name'];
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($user_id,$owner_id) == true){
                        $this->{$this->modelClass}->id = $d['id'];
                        $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->{$this->modelClass}->id = $d['id'];
                    $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
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
	
	//====== Notes ===============
	 public function note_index(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();
        if(isset($this->request->query['for_id'])){
            $pc_id = $this->request->query['for_id'];
            $q_r    = $this->{$this->modelClass}->{$this->itemNote}->find('all', 
                array(
                    'contain'       => array('Note'),
                    'conditions'    => array('ApProfileNote.ap_profile_id' => $pc_id)
                )
            );
            foreach($q_r as $i){
                if(!$this->_test_for_private_parent($i['Note'],$user)){
                    $owner_id   = $i['Note']['user_id'];
                    $owner      = $this->_find_parents($owner_id);
                    $afs        = $this->_get_action_flags($owner_id,$user);
                    array_push($items,
                        array(
                            'id'        => $i['Note']['id'], 
                            'note'      => $i['Note']['note'], 
                            'available_to_siblings' => $i['Note']['available_to_siblings'],
                            'owner'     => $owner,
                            'delete'    => $afs['delete']
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
        $this->{$this->modelClass}->{$this->itemNote}->Note->create(); 
        //print_r($this->request->data);
        if ($this->{$this->modelClass}->{$this->itemNote}->Note->save($this->request->data)) {
            $d                      = array();
            $d['ApProfileNote']['ap_profile_id']   = $this->request->data['for_id'];
            $d['ApProfileNote']['note_id'] = $this->{$this->modelClass}->ApProfileNote->Note->id;
            $this->{$this->modelClass}->{$this->itemNote}->create();
            if ($this->{$this->modelClass}->{$this->itemNote}->save($d)) {
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
            $item       = $this->{$this->modelClass}->{$this->itemNote}->Note->findById($this->data['id']);
            $owner_id   = $item['Note']['user_id'];
            if($owner_id != $user_id){
                if($this->_is_sibling_of($user_id,$owner_id)== true){
                    $this->{$this->modelClass}->{$this->itemNote}->Note->id = $this->data['id'];
                    $this->{$this->modelClass}->{$this->itemNote}->Note->delete($this->data['id'],true);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->{$this->modelClass}->ApProfileNote->Note->id = $this->data['id'];
                $this->{$this->modelClass}->ApProfileNote->Note->delete($this->data['id'],true);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                $item       = $this->{$this->modelClass}->{$this->itemNote}->Note->findById($d['id']);
                $owner_id   = $item['Note']['user_id'];
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($user_id,$owner_id) == true){
                        $this->{$this->modelClass}->{$this->itemNote}->Note->id = $d['id'];
                        $this->{$this->modelClass}->{$this->itemNote}->Note->delete($d['id'],true);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->{$this->modelClass}->{$this->itemNote}->Note->id = $d['id'];
                    $this->{$this->modelClass}->{$this->itemNote}->Note->delete($d['id'],true);
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
	
	
	
	//======= AP Profile entries ============
    public function ap_profile_entries_index(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $items          = array();
        $total          = 0;
        $entry          = ClassRegistry::init('ApProfileEntry');
        $entry->contain('ApProfileExitApProfileEntry');
        $ap_profile_id  = $this->request->query['ap_profile_id'];
        $q_r            = $entry->find('all',array('conditions' => array('ApProfileEntry.ap_profile_id' => $ap_profile_id)));

        foreach($q_r as $m){
            $connected_to_exit = true;   
            if(count($m['ApProfileExitApProfileEntry']) == 0){
                $connected_to_exit = false;
            }
   
            array_push($items,array( 
                'id'            => $m['ApProfileEntry']['id'],
                'ap_profile_id' => $m['ApProfileEntry']['ap_profile_id'],
                'name'          => $m['ApProfileEntry']['name'],
                'hidden'        => $m['ApProfileEntry']['hidden'],
                'isolate'       => $m['ApProfileEntry']['isolate'],
                'encryption'    => $m['ApProfileEntry']['encryption'],
                'special_key'   => $m['ApProfileEntry']['special_key'],
                'auth_server'   => $m['ApProfileEntry']['auth_server'],
                'auth_secret'   => $m['ApProfileEntry']['auth_secret'],
                'dynamic_vlan'  => $m['ApProfileEntry']['dynamic_vlan'],
                'frequency_band'  => $m['ApProfileEntry']['frequency_band'],
                'connected_to_exit' => $connected_to_exit
            ));
        }
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            'totalCount' => $total,
            '_serialize' => array('items','success','totalCount')
        ));
    }

    public function ap_profile_entry_add(){
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $entry = ClassRegistry::init('ApProfileEntry'); 
        $entry->create();
        if ($entry->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        } else {
            $message = 'Error';
            $this->set(array(
                'errors'    => $entry->validationErrors,
                'success'   => false,
                'message'   => array('message' => __('Could not create item')),
                '_serialize' => array('errors','success','message')
            ));
        }
    }

    public function ap_profile_entry_edit(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if ($this->request->is('post')) {

            $check_items = array('hidden','isolate','apply_to_all','chk_maxassoc');
            foreach($check_items as $i){
                if(isset($this->request->data[$i])){
                    $this->request->data[$i] = 1;
                }else{
                    $this->request->data[$i] = 0;
                }
            }

            $entry = ClassRegistry::init('ApProfileEntry');
            // If the form data can be validated and saved...
            if ($entry->save($this->request->data)) {
                   $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }
        } 
    }

    public function ap_profile_entry_view(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $entry = ClassRegistry::init('ApProfileEntry');

        $id    = $this->request->query['entry_id'];
        $q_r   = $entry->findById($id);
        
        if($q_r['ApProfileEntry']['macfilter'] != 'disable'){ 
            $pu = ClassRegistry::init('PermanentUser');
            $pu->contain();
            $q = $pu->findById($q_r['ApProfileEntry']['permanent_user_id']);
            if($q){
                $q_r['ApProfileEntry']['username'] = $q['PermanentUser']['username'];    
            }else{
                $q_r['ApProfileEntry']['username'] = "!!!User Missing!!!";
            }
        }

        $this->set(array(
            'data'     => $q_r['ApProfileEntry'],
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
    }

    public function ap_profile_entry_delete(){

       if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $user_id    = $user['id'];
        $fail_flag  = false;
        $entry      = ClassRegistry::init('ApProfileEntry'); 

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id']; 
            $entry->id = $this->data['id'];
            $entry->delete($entry->id, true);
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                    $entry->id = $d['id'];
                    $entry->delete($entry->id, true);
            }
        }  
        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }
    
    //======= AP Profile exits ============
    public function ap_profile_exits_index(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $items          = array();
        $total          = 0;
        $exit           = ClassRegistry::init('ApProfileExit');
        $exit->contain('ApProfileExitApProfileEntry.ApProfileEntry.name');
        $ap_profile_id  = $this->request->query['ap_profile_id'];
        $q_r            = $exit->find('all',array('conditions' => array('ApProfileExit.ap_profile_id' => $ap_profile_id)));
       // print_r($q_r);

        foreach($q_r as $m){
            $exit_entries = array();

            foreach($m['ApProfileExitApProfileEntry'] as $m_e_ent){
                array_push($exit_entries,array('name' => $m_e_ent['ApProfileEntry']['name']));
            }

            array_push($items,array( 
                'id'            => $m['ApProfileExit']['id'],
                'ap_profile_id' => $m['ApProfileExit']['ap_profile_id'],
                'type'          => $m['ApProfileExit']['type'],
                'connects_with' => $exit_entries

            ));
        }
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    public function ap_profile_exit_add(){
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

       // print_r($this->request->data);
       // exit;

        $entry_point    = ClassRegistry::init('ApProfileExitApProfileEntry');
        $exit           = ClassRegistry::init('ApProfileExit'); 
        $exit->create();
        
        if($this->request->data['type'] == 'captive_portal'){ 
            if(isset($this->request->data['auto_dynamic_client'])){
                $this->request->data['auto_dynamic_client'] = 1;
                
                //Get a list of realms if the person selected a list - If it is empty that's fine
                $count      = 0;
                $this->request->data['realm_list'] = ""; //Prime it
                if (array_key_exists('realm_ids', $this->request->data)) {
                    foreach($this->request->data['realm_ids'] as $r){
                        if($count == 0){
                            $this->request->data['realm_list'] = $this->request->data['realm_ids'][$count]; 
                        }else{
                            $this->request->data['realm_list'] = $this->request->data['realm_list'].",".$this->request->data['realm_ids'][$count];
                        }  
                        $count++;
                    }
                }
                
            }else{
                $this->request->data['auto_dynamic_client'] = 0;
            }
            
            if(isset($this->request->data['auto_login_page'])){
                $this->request->data['auto_login_page'] = 1;
            }else{
                $this->request->data['auto_login_page'] = 0;
            }
        }
        
        if ($exit->save($this->request->data)) {
            $new_id         = $exit->id;     
            $ap_profile_id  = $this->request->data['ap_profile_id'];
            
            //---- openvpn_bridge -----
            if($this->request->data['type'] == 'openvpn_bridge'){
                
                $server_id  = $this->request->data['openvpn_server_id'];
                $q_s        = $this->OpenvpnServer->findById($server_id);
                $next_ip    = $q_s['OpenvpnServer']['vpn_bridge_start_address'];
                
                $ap         = ClassRegistry::init('Ap');
                $ap->contain();
                $q_aps      = $ap->find('all',array('conditions' =>array('Ap.ap_profile_id' =>$ap_profile_id )));
 
                //We need to add a VPN entry for all the existing ones so we do not need to add them again
                foreach($q_aps as $ap){
                    $ap_id = $ap['Ap']['id'];
                    $not_available      = true;
                    while($not_available){
                        if($this->_check_if_available($server_id,$next_ip)){
                            $d_vpn_c['ip_address'] = $next_ip;
                            $not_available = false;
                            break;
                        }else{
                            $next_ip = $this->_get_next_ip($next_ip);
                        }
                    }
                    $d_new                      = array();
                    $d_new['mesh_ap_profile']   = 'ap_profile';
                    $d_new['openvpn_server_id'] = $server_id;
                    $d_new['ip_address']        = $next_ip;
                    $d_new['ap_profile_id']     = $ap_profile_id;
                    $d_new['ap_profile_exit_id']= $new_id;
                    $d_new['ap_id']             = $ap['Ap']['id'];
                    
                    $this->OpenvpnServerClient->create();
                    $this->OpenvpnServerClient->save($d_new);
                }
            }
            //---- END openvpn_bridge ------
            
            
            

            //===== Captive Portal ==========
            if($this->request->data['type'] == 'captive_portal'){

                $this->request->data['ap_profile_exit_id'] = $new_id;

                $captive_portal = ClassRegistry::init('ApProfileExitCaptivePortal');
                $captive_portal->create();

				$check_items = array(
					'swap_octets',
					'mac_auth',
                    'proxy_enable',
                    'dns_manual',
                    'uamanydns',
                    'dnsparanoia',
                    'dnsdesk'
				);
			    foreach($check_items as $i){
			        if(isset($this->request->data[$i])){
			            $this->request->data[$i] = 1;
			        }else{
			            $this->request->data[$i] = 0;
			        }
			    }
                if(!($captive_portal->save($this->request->data))){
                    $exit->delete($new_id, true); //Remove the newly created exit point since the captive portal add failed
                    $this->set(array(
                        'errors'    => $captive_portal->validationErrors,
                        'success'   => false,
                        'message'   => array('message' => __('Could not create item')),
                        '_serialize' => array('errors','success','message')
                    ));
                    return;
                }
            }
            //==== End of Captive Portal ====

            //Add the entry points
            $count      = 0;
            $entry_ids  = array();
            $empty_flag = false;

            if (array_key_exists('entry_points', $this->request->data)) {
                foreach($this->request->data['entry_points'] as $e){
                    if($this->request->data['entry_points'][$count] == 0){
                        $empty_flag = true;
                        break;
                    }else{
                        array_push($entry_ids,$this->request->data['entry_points'][$count]);
                    }
                    $count++;
                }
            }


            //Only if empty was not specified
            if((!$empty_flag)&&(count($entry_ids)>0)){
                foreach($entry_ids as $id){	
                    $data = array();
                    $data['ApProfileExitApProfileEntry']['ap_profile_exit_id']  = $new_id;
                    $data['ApProfileExitApProfileEntry']['ap_profile_entry_id'] = $id;
					$entry_point->create();
                    $entry_point->save($data);
					$entry_point->id = null;
                }
            }

            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }else{
            $message = 'Error';
            $this->set(array(
                'errors'    => $exit->validationErrors,
                'success'   => false,
                'message'   => array('message' => __('Could not create item')),
                '_serialize' => array('errors','success','message')
            ));
        }
    }

    public function ap_profile_exit_edit(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if ($this->request->is('post')) {

            $entry_point    = ClassRegistry::init('ApProfileExitApProfileEntry');
            $exit           = ClassRegistry::init('ApProfileExit');
            
            
            //---- openvpn_bridge -----
            if($this->request->data['type'] == 'openvpn_bridge'){
                
                $server_id  = $this->request->data['openvpn_server_id'];
                
                //We will only do the following if the selected OpenvpnServer changed
                $exit->contain();
                $q_exit             = $exit->findById($this->request->data['id']);
                $current_server_id  = $q_exit['ApProfileExit']['openvpn_server_id'];
                $server_id          = $this->request->data['openvpn_server_id'];
                $ap_profile_exit_id = $this->request->data['id'];
                
                if($current_server_id !== $server_id){
                    //Update current list 
                    $this->OpenvpnServerClient->contain();
                    $current_aps = $this->OpenvpnServerClient->find('all',
                        array('conditions' =>array(
                            'OpenvpnServerClient.openvpn_server_id'     => $current_server_id,
                            'OpenvpnServerClient.mesh_ap_profile'       => 'ap_profile',
                            'OpenvpnServerClient.ap_profile_exit_id'    => $ap_profile_exit_id,
                        ))
                    );
                    
                    $q_r        = $this->OpenvpnServer->findById($server_id);
                        
                    foreach($current_aps as $ap){
                        $next_ip            = $q_r['OpenvpnServer']['vpn_bridge_start_address'];
                        $not_available      = true;
                        while($not_available){
                            if($this->_check_if_available($server_id,$next_ip)){
                                $d_vpn_c['ip_address'] = $next_ip;
                                $not_available = false;
                                break;
                            }else{
                                $next_ip = $this->_get_next_ip($next_ip);
                            }
                        }      
                        //Update the record
                        $d_apdate                       = array();
                        $d_update['id']                 = $ap['OpenvpnServerClient']['id'];
                        $d_update['openvpn_server_id']  = $server_id;
                        $d_update['ip_address']         = $next_ip;
                        $this->OpenvpnServerClient->save($d_update);
                    }          
                }            
            }
            //---- END openvpn_bridge ------
            

            //===== Captive Portal ==========
            //== First see if we can save the captive portal data ====
            if($this->request->data['type'] == 'captive_portal'){

                $captive_portal = ClassRegistry::init('ApProfileExitCaptivePortal');
                $cp_data        = $this->request->data;
                $ap_profile_exit_id   = $this->request->data['id'];
                $q_r            = $captive_portal->find(
                                    'first',array('conditions' => 
                                        array('ApProfileExitCaptivePortal.ap_profile_exit_id' => $ap_profile_exit_id)
                                ));               
                if($q_r){
                    $cp_id = $q_r['ApProfileExitCaptivePortal']['id'];
                    $cp_data['id'] = $cp_id;
                    $captive_portal->id = $cp_id;

					$check_items = array(
						'swap_octets',
						'mac_auth',
                        'proxy_enable',
                        'dns_manual',
                        'uamanydns',
                        'dnsparanoia',
                        'dnsdesk'
					);
					foreach($check_items as $i){
					    if(isset($this->request->data[$i])){
					        $cp_data[$i] = 1;
					    }else{
					        $cp_data[$i] = 0;
					    }
					}

                   // print_r($cp_data);
                    if(!($captive_portal->save($cp_data))){
                        $this->set(array(
                            'errors'    => $captive_portal->validationErrors,
                            'success'   => false,
                            'message'   => array('message' => __('Could not create item')),
                            '_serialize' => array('errors','success','message')
                        ));
                        return;
                    }
                }
            }
            //==== End of Captive Portal ====
            
            $this->request->data['realm_list'] = ""; //Prime it
            
            if($this->request->data['type'] == 'captive_portal'){ 
                if(isset($this->request->data['auto_dynamic_client'])){
                    $this->request->data['auto_dynamic_client'] = 1;
                    
                    //Get a list of realms if the person selected a list - If it is empty that's fine
                    $count      = 0;
                    if (array_key_exists('realm_ids', $this->request->data)) {
                        foreach($this->request->data['realm_ids'] as $r){
                            if($count == 0){
                                $this->request->data['realm_list'] = $this->request->data['realm_ids'][$count]; 
                            }else{
                                $this->request->data['realm_list'] = $this->request->data['realm_list'].",".$this->request->data['realm_ids'][$count];
                            }  
                            $count++;
                        }
                    }   
                    
                }else{
                    $this->request->data['auto_dynamic_client'] = 0;
                }
                
                if(isset($this->request->data['auto_login_page'])){
                    $this->request->data['auto_login_page'] = 1;
                }else{
                    $this->request->data['auto_login_page'] = 0;
                }
            }
            

            // If the form data can be validated and saved...
            if ($exit->save($this->request->data)) {

                //Add the entry points
                $count      = 0;
                $entry_ids  = array();
                $empty_flag = false;
                $new_id     = $this->request->data['id'];

                //Clear previous ones first:
                $entry_point->deleteAll(array('ApProfileExitApProfileEntry.ap_profile_exit_id' => $new_id), false);

                if (array_key_exists('entry_points', $this->request->data)) {
                    foreach($this->request->data['entry_points'] as $e){
                        if($this->request->data['entry_points'][$count] == 0){
                            $empty_flag = true;
                            break;
                        }else{
                            array_push($entry_ids,$this->request->data['entry_points'][$count]);
                        }
                        $count++;
                    }
                }

                //Only if empty was not specified
                if((!$empty_flag)&&(count($entry_ids)>0)){
                    foreach($entry_ids as $id){
						$data = array();
                        $data['ApProfileExitApProfileEntry']['ap_profile_exit_id']  = $new_id;
                        $data['ApProfileExitApProfileEntry']['ap_profile_entry_id'] = $id;
						$entry_point->create();
                        $entry_point->save($data);
						$entry_point->id = null;
                    }
                }

                $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }
        } 
    }
    
    public function ap_profile_exit_add_defaults(){
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        
        Configure::load('ApProfiles'); 
        $data = Configure::read('ApProfiles.captive_portal'); //Read the defaults
        $this->set(array(
            'data'     => $data,
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
    }
    
    public function ap_experimental_check(){
        Configure::load('RadiusDesk'); 
        $active = Configure::read('experimental.active'); //Read the defaults
        $this->set(array(
            'active'     => $active,
            'success'   => true,
            '_serialize'=> array('success', 'active')
        ));
    }

    public function ap_profile_exit_view(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $exit = ClassRegistry::init('ApProfileExit');
        $exit->contain('ApProfileExitApProfileEntry','ApProfileExitCaptivePortal','DynamicDetail');

        $id    = $this->request->query['exit_id'];
        $q_r   = $exit->findById($id);

       
        //Get the realm list
        if($q_r['ApProfileExit']['realm_list'] != ''){
            $pieces = explode(",", $q_r['ApProfileExit']['realm_list']);
            $q_r['ApProfileExit']['realm_records'] = array();
            $q_r['ApProfileExit']['realm_ids'] = array();
            foreach($pieces as $p){
                if(is_numeric($p)){
                    //Get the name and id of this realm
                    $this->Realm->contain();
                    $q_realm = $this->Realm->findById($p);
                    if($q_realm){
                        $r_name = $q_realm['Realm']['name'];
                        array_push($q_r['ApProfileExit']['realm_records'],array('id' => $p, 'name' => $r_name));
                        array_push($q_r['ApProfileExit']['realm_ids'],$p);
                    }
                }
            }
        }

        //entry_points
        $q_r['ApProfileExit']['entry_points'] = array();
        foreach($q_r['ApProfileExitApProfileEntry'] as $i){
            array_push($q_r['ApProfileExit']['entry_points'],intval($i['ap_profile_entry_id']));
        }

        if($q_r['ApProfileExitCaptivePortal']){
            $q_r['ApProfileExit']['radius_1']        = $q_r['ApProfileExitCaptivePortal']['radius_1'];
            $q_r['ApProfileExit']['radius_2']        = $q_r['ApProfileExitCaptivePortal']['radius_2'];
            $q_r['ApProfileExit']['radius_secret']   = $q_r['ApProfileExitCaptivePortal']['radius_secret'];
            $q_r['ApProfileExit']['uam_url']         = $q_r['ApProfileExitCaptivePortal']['uam_url'];
            $q_r['ApProfileExit']['uam_secret']      = $q_r['ApProfileExitCaptivePortal']['uam_secret'];
            $q_r['ApProfileExit']['walled_garden']   = $q_r['ApProfileExitCaptivePortal']['walled_garden'];
            $q_r['ApProfileExit']['swap_octets']     = $q_r['ApProfileExitCaptivePortal']['swap_octets'];
			$q_r['ApProfileExit']['mac_auth']        = $q_r['ApProfileExitCaptivePortal']['mac_auth'];

            //Proxy settings
            $q_r['ApProfileExit']['proxy_enable']    = $q_r['ApProfileExitCaptivePortal']['proxy_enable'];
            $q_r['ApProfileExit']['proxy_ip']        = $q_r['ApProfileExitCaptivePortal']['proxy_ip'];
            $q_r['ApProfileExit']['proxy_port']      = intval($q_r['ApProfileExitCaptivePortal']['proxy_port']);
            $q_r['ApProfileExit']['proxy_auth_username']      = $q_r['ApProfileExitCaptivePortal']['proxy_auth_username'];
            $q_r['ApProfileExit']['proxy_auth_password']      = $q_r['ApProfileExitCaptivePortal']['proxy_auth_password'];
            $q_r['ApProfileExit']['coova_optional']  = $q_r['ApProfileExitCaptivePortal']['coova_optional'];
            
            //DNS settings
            $q_r['ApProfileExit']['dns_manual']      = $q_r['ApProfileExitCaptivePortal']['dns_manual'];
            $q_r['ApProfileExit']['dns1']            = $q_r['ApProfileExitCaptivePortal']['dns1'];
            $q_r['ApProfileExit']['dns2']            = $q_r['ApProfileExitCaptivePortal']['dns2'];
            $q_r['ApProfileExit']['uamanydns']       = $q_r['ApProfileExitCaptivePortal']['uamanydns'];
            $q_r['ApProfileExit']['dnsparanoia']     = $q_r['ApProfileExitCaptivePortal']['dnsparanoia'];
            $q_r['ApProfileExit']['dnsdesk']         = $q_r['ApProfileExitCaptivePortal']['dnsdesk'];

        }
        
        if($q_r['DynamicDetail']){
           $q_r['ApProfileExit']['dynamic_detail'] =  $q_r['DynamicDetail']['name'];
        }

        $data = $q_r['ApProfileExit'];

      //  print_r($q_r);

        $this->set(array(
            'data'     => $data,
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
    }
    
    public function ap_profile_exit_upstream_list(){
        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }
          
        $items  = [
            ['name'=> 'LAN (Ethernet0)', 'id' => 0 ]
        ];
        
        $this->set(array(
            'items'     => $items,
            'success'   => true,
            '_serialize'=> array('success', 'items')
        ));
    }


    public function ap_profile_exit_delete(){

       if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $user_id    = $user['id'];
        $fail_flag  = false;
        $exit       = ClassRegistry::init('ApProfileExit'); 

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id']; 
            
            $exit->contain('ApProfile');
            $id                 = $this->data['id'];
            $q_r                = $exit->findById($this->data['id']);
            if($q_r){
                if($q_r['ApProfileExit']['type'] == 'captive_portal'){
                    $ap_profile_name    = $q_r['ApProfile']['name'];
                    $ap_profile_name    = preg_replace('/\s+/', '_', $ap_profile_name);
                    $this->DynamicClient->deleteAll(array('DynamicClient.nasidentifier LIKE' => "$ap_profile_name"."_%_cp_".$id), true);
                    $this->DynamicPair->deleteAll(
                        array(
                            'DynamicPair.value LIKE' => "$ap_profile_name"."_%_cp_".$id,
                            'DynamicPair.name' => 'nasid',
                        ), true);
                }
            }     
            
            $exit->id = $this->data['id'];
            $exit->delete($exit->id, true);
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                $exit->contain('ApProfile');
                $id                 = $d['id'];
                $q_r                = $exit->findById($d['id']);
                if($q_r){
                    if($q_r['ApProfileExit']['type'] == 'captive_portal'){
                        $ap_profile_name    = $q_r['ApProfile']['name'];
                        $ap_profile_name    = preg_replace('/\s+/', '_', $ap_profile_name);
                        $this->DynamicClient->deleteAll(array('DynamicClient.nasidentifier LIKE' => "$ap_profile_name"."_%_cp_".$id), true);
                        $this->DynamicPair->deleteAll(
                            array(
                                'DynamicPair.value LIKE' => "$ap_profile_name"."_%_cp_".$id,
                                'DynamicPair.name' => 'nasid',
                            ), true);
                    }
                }          
                $exit->id = $d['id'];
                $exit->delete($exit->id, true);
            }
        }  
        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    public function ap_profile_entry_points(){
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        //Get the ap profile id
        $ap_profile_id    = $this->request->query['ap_profile_id'];

        $exit_id = false;

        //Check if the exit_id was included
        if(isset($this->request->query['exit_id'])){
            $exit_id = $this->request->query['exit_id'];
        }

        $exit       = ClassRegistry::init('ApProfileExit');
        $entry      = ClassRegistry::init('ApProfileEntry');

        $entry->contain('ApProfileExitApProfileEntry');
        $ent_q_r    = $entry->find('all',array('conditions' => array('ApProfileEntry.ap_profile_id' => $ap_profile_id))); 
        //print_r($ent_q_r);

        $items = array();
        array_push($items,array('id' => 0, 'name' => "(None)")); //Allow the user not to assign at this stage
        foreach($ent_q_r as $i){

            //If this entry point is already associated; we will NOT add it
            if(count($i['ApProfileExitApProfileEntry'])== 0){
                $id = intval($i['ApProfileEntry']['id']);
                $n  = $i['ApProfileEntry']['name'];
                array_push($items,array('id' => $id, 'name' => $n));
            }

            //if $exit_id is set; we add it 
            if($exit_id){
                if(count($i['ApProfileExitApProfileEntry'])> 0){
                    if($i['ApProfileExitApProfileEntry'][0]['ap_profile_exit_id'] == $exit_id){
                        $id = intval($i['ApProfileEntry']['id']);
                        $n  = $i['ApProfileEntry']['name'];
                        array_push($items,array('id' => $id, 'name' => $n));
                    }
                }
            }
        }
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }
    
    
    //====== Common AP settings ================
    //-- View common node settings --
    public function ap_common_settings_view(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $id         = $this->request->query['ap_profile_id']; 
		Configure::load('ApProfiles'); 
        $data       = Configure::read('common_ap_settings'); //Read the defaults
        $setting    = ClassRegistry::init('ApProfileSetting');
        $setting->contain();

        //Timezone lists
        $tz_list    = Configure::read('ApProfiles.timezones'); 
        $q_r = $setting->find('first', array('conditions' => array('ApProfileSetting.ap_profile_id' => $id)));
        if($q_r){  
            //print_r($q_r);
            $data = $q_r['ApProfileSetting']; 
            //We need to find if possible the number for the timezone
            foreach($tz_list as $i){
                if($q_r['ApProfileSetting']['tz_name'] == $i['name']){
                    $data['timezone'] = intval($i['id']);
                    break;
                }
            }
        }

        $this->set(array(
            'data'      => $data,
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
    }

    public function ap_common_settings_edit(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if ($this->request->is('post')) {

            $check_items = array('gw_use_previous','gw_auto_reboot');
            foreach($check_items as $i){
                if(isset($this->request->data[$i])){
                    $this->request->data[$i] = 1;
                }else{
                    $this->request->data[$i] = 0;
                }
            }

            //Try to find the timezone and its value
            Configure::load('ApProfiles');
            $tz_list    = Configure::read('ApProfiles.timezones'); 
            foreach($tz_list as $j){
                if($j['id'] == $this->request->data['timezone']){
                    $this->request->data['tz_name'] = $j['name'];
                    $this->request->data['tz_value']= $j['value'];
                    break;
                }
            }
            
            $ap_profile_id = $this->request->data['ap_profile_id'];
            //See if there is not already a setting entry
            $setting    = ClassRegistry::init('ApProfileSetting');
			$setting->contain();
            $q_r        = $setting->find('first', array('conditions' => array('ApProfileSetting.ap_profile_id' => $ap_profile_id)));

            if($q_r){
                $this->request->data['id'] = $q_r['ApProfileSetting']['id']; //Set the ID
				//Check if the value of 
				////if($this->request->data['password'] != $q_r['ApProfileSetting']['password']){ //!!Create a new has regardless!!
					//Create a new hash
					$new_pwd = $this->_make_linux_password($this->request->data['password']);
					$this->request->data['password_hash'] = $new_pwd;

				///}
            }

            if ($setting->save($this->request->data)) {
                   $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }
        }
    }
    
    //=== APs Special ====
     public function advanced_settings_for_model(){
        $data = array();
        Configure::load('ApProfiles');
        $hw   = Configure::read('ApProfiles.hardware');
        $model= $this->request->query['model'];

        $no_overrides = true;

        //Check if there is a ap_id in the request and if so check the current hardware type
        //If the same as the model requested, check if we have overrides
        if(array_key_exists('ap_id', $this->request->query)){

            $ap   = ClassRegistry::init('Ap');
            $ap->contain('ApWifiSetting');
            $q_r    = $ap->findById($this->request->query['ap_id']);
            if($q_r){
                $current_model = $q_r['Ap']['hardware'];
                if($current_model == $model){ //Its the same so lets check if there are any custom settings
                    if(count($q_r['ApWifiSetting'])>0){

                        $radio1_flag    = false;
                        $r0_ht_capab    = array();
                        $r1_ht_capab    = array();

                        foreach($q_r['ApWifiSetting'] as $s){
                            $s_name     = $s['name'];
                            $s_value    = $s['value'];
                            if($s_name == 'radio1_txpower'){
                                $radio1_flag = true;
                            }

                            if(!(preg_match('/^radio\d+_ht_capab/',$s_name))){
                                $data["$s_name"] = "$s_value";
                            }else{
                                if($s_name == 'radio0_ht_capab'){
                                    array_push($r0_ht_capab,$s_value);
                                }
                                if($s_name == 'radio1_ht_capab'){
                                    array_push($r1_ht_capab,$s_value);
                                }
                            }
                        }

                        $data['radio0_ht_capab'] = implode("\n",$r0_ht_capab);
                        if($radio1_flag){
                            $data['radio1_ht_capab'] = implode("\n",$r1_ht_capab);
                        }
                        $no_overrides = false;
                        //After the loop we
                    }
                }
            }
        }

        if($no_overrides){

            foreach($hw as $h){
                $id     = $h['id'];
                if($model == $id){
                    foreach(array_keys($h) as $key){
                        if(preg_match('/^radio\d+_/',$key)){
                            if(preg_match('/^radio\d+_ht_capab/',$key)){
                                $data["$key"] = implode("\n",$h["$key"]);
                            }else{
                                $data["$key"] = $h["$key"];
                            }
                        }
                    }
                    break;
                }
            }
        }
        
        $this->set(array(
            'data' => $data,
            'success' => true,
            '_serialize' => array('data','success')
        ));
    }

    
    //=== APs CRUD ===
    public function ap_profile_ap_index(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $items          = array();
        $total          = 0;
        $ap             = ClassRegistry::init('Ap');
        $ap_profile_id  = $this->request->query['ap_profile_id'];
        $q_r            = $ap->find('all',array('conditions' => array('Ap.ap_profile_id' => $ap_profile_id)));

        //Create a hardware lookup for proper names of hardware
        $hardware = array();  
		Configure::load('ApProfiles');      
        $hw   = Configure::read('ApProfiles.hardware');
        foreach($hw as $h){
            $id     = $h['id'];
            $name   = $h['name']; 
            $hardware["$id"]= $name;
        }

		//Check if we need to show the override on the power
		$ap_setting	= ClassRegistry::init('ApProfileSetting');
		$ap_setting->contain();
		
		App::uses('GeoIpLocation', 'GeoIp.Model');
        $GeoIpLocation = new GeoIpLocation();

        foreach($q_r as $m){
        
            $m['Ap']['last_contact_human']  = $this->TimeCalculations->time_elapsed_string($m['Ap']["last_contact"]);
            
            //----              
            //Some defaults:
            $country_code = '';
            $country_name = '';
            $city         = '';
            $postal_code  = '';
            
            if($m['Ap']['last_contact_from_ip'] != null){
          
                $location = $GeoIpLocation->find($m['Ap']['last_contact_from_ip']);
                
                if(array_key_exists('GeoIpLocation',$location)){
                    if($location['GeoIpLocation']['country_code'] != ''){
                        $country_code = utf8_encode($location['GeoIpLocation']['country_code']);
                    }
                    if($location['GeoIpLocation']['country_name'] != ''){
                        $country_name = utf8_encode($location['GeoIpLocation']['country_name']);
                    }
                    if($location['GeoIpLocation']['city'] != ''){
                        $city = utf8_encode($location['GeoIpLocation']['city']);
                    }
                    if($location['GeoIpLocation']['postal_code'] != ''){
                        $postal_code = utf8_encode($location['GeoIpLocation']['postal_code']);
                    }
                }
            }   
            //----  
            
            

            array_push($items,array( 
                'id'                    => $m['Ap']['id'],
                'ap_profile_id'         => $m['Ap']['ap_profile_id'],
                'name'                  => $m['Ap']['name'],
                'description'           => $m['Ap']['description'],
                'mac'                   => $m['Ap']['mac'],      
                'hardware'	            => $m['Ap']['hardware'],
                'last_contact_from_ip'	=> $m['Ap']['last_contact_from_ip'],
                'on_public_maps'	    => $m['Ap']['on_public_maps'],
				'last_contact'	        => $m['Ap']['last_contact'],
				"last_contact_human"    => $m['Ap']['last_contact_human'],
				'lat'			        => $m['Ap']['lat'],
				'lng'			        => $m['Ap']['lon'],
				'photo_file_name'	    => $m['Ap']['photo_file_name'],
				'country_code'          => $country_code,
                'country_name'          => $country_name,
                'city'                  => $city,
                'postal_code'           => $postal_code,
            ));
        }
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    public function ap_profile_ap_add(){
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
     
        $ap             = ClassRegistry::init('Ap');
        $wifi_setting   = ClassRegistry::init('ApWifiSetting');
        
        //Get the ApProfile so we can get the user_id nd available_to_siblings for the said ap_profile
        $ap_profile_id  = $this->request->data['ap_profile_id'];
        $this->ApProfile->contain();   
        $ap_profile     = $this->ApProfile->findById($ap_profile_id);
        $user_id        = $ap_profile['ApProfile']['user_id'];
        $a_to_s         = $ap_profile['ApProfile']['available_to_siblings'];
        $ap_profile_name= $ap_profile['ApProfile']['name'];
        $ap_profile_name= preg_replace('/\s+/', '_', $ap_profile_name);
        
        $ap->create();
        if ($ap->save($this->request->data)) {

            $new_id = $ap->id;
            
			//Check if it was submitted through the attach  ap window - then remove the unknown_ap with mac = mac
			if(array_key_exists('rem_unknown', $this->request->data)) {
				$unknown_ap   = ClassRegistry::init('UnknownAp');
				$mac			= $this->request->data['mac'];
 				$unknown_ap->deleteAll(array('UnknownAp.mac' => $mac), true);
			}
			
			//__ OpenVPN Bridges _____	
			$exit  = ClassRegistry::init('ApProfileExit');
			//Find out if there are Exit points that is of type 'openvpn_bridge' for this ap_profile
			$exit->contain('OpenvpnServer');
			$q_e_vpnb = $exit->find('all',
			    array('conditions' => array(
			        'ApProfileExit.ap_profile_id'   => $ap_profile_id,
			        'ApProfileExit.type'            => 'openvpn_bridge',
			    ))
		    );
		    
		    foreach($q_e_vpnb as $e){
		        //Get the OpenvpnServer's detail of reach
		        $vpn_server_id  = $e['OpenvpnServer']['id'];
		        $exit_id        = $e['ApProfileExit']['id'];
		        
		        $d_vpn_c                        = array();
                $d_vpn_c['mesh_ap_profile']     = 'ap_profile';
                $d_vpn_c['openvpn_server_id']   = $vpn_server_id;
                $d_vpn_c['ap_profile_id']       = $ap_profile_id;
                $d_vpn_c['ap_profile_exit_id']  = $exit_id;
                $d_vpn_c['ap_id']               = $new_id;
            
                $next_ip        = $e['OpenvpnServer']['vpn_bridge_start_address'];
                $not_available  = true;
                
                while($not_available){
                    if($this->_check_if_available($vpn_server_id,$next_ip)){
                        $d_vpn_c['ip_address'] = $next_ip;
                        $not_available = false;
                        break;
                    }else{
                        $next_ip = $this->_get_next_ip($next_ip);
                    }
                }   
                $this->OpenvpnServerClient->create();
                $this->OpenvpnServerClient->save($d_vpn_c); 
		    }	
			//__ END OpenVPN Bridges ___
			
			
			//______________________________________________________________________			
	        //We need to see if there are captive portals defined on the ap_profile
	        //_______________________________________________________________________   
	        $this->ApProfile->ApProfileExit->contain('ApProfileExitCaptivePortal');    
	        $q_exits    = $this->ApProfile->ApProfileExit->find('all',
	            array('conditions' => array(
	                'ApProfileExit.ap_profile_id'   => $ap_profile_id,
	                'ApProfileExit.type'            => 'captive_portal'
	            ))
	        );
	        foreach($q_exits as $qe){
	            	        
	            $exit_id = $qe['ApProfileExit']['id'];
	            
	            $name_no_spaces = $this->request->data['name'];
	            $name_no_spaces = preg_replace('/\s+/', '_', $name_no_spaces);
	            
	            
	            $dc_data                            = array();       	            
	            $dc_data['user_id']                 = $user_id;
	            $dc_data['available_to_siblings']   = $a_to_s;
	            $dc_data['nasidentifier']           = $ap_profile_name.'_'.$name_no_spaces.'_cp_'.$exit_id;
	            $dc_data['realm_list']              = $qe['ApProfileExit']['realm_list'];
	            
	            if($qe['ApProfileExit']['auto_dynamic_client'] == 1){  //It has to be enabled
	                $this->_add_dynamic($dc_data);
	            }
	            
	            if($qe['ApProfileExit']['auto_login_page'] == 1){  //It has to be enabled
	                $dc_data['dynamic_detail_id'] = $qe['ApProfileExit']['dynamic_detail_id'];
	                $this->_add_dynamic_pair($dc_data);
	            }
	        }
	        //_______________________________________________________________________
	
			
   
            //---------Add WiFi settings for this ap ------
            //--Clean up--
            $n_id = $new_id;
            
            //Check if the radio0_enable is perhaps missing
            if(array_key_exists('radio0_enable', $this->request->data)) {
                $this->request->data['radio0_disabled'] = 0;
            }else{
                $this->request->data['radio0_disabled'] = 1;
            }
        
            //Check for radio1 -> First we need to be sure there are a radio1!
            if(array_key_exists('radio1_band', $this->request->data)) {
                if(array_key_exists('radio1_enable', $this->request->data)) {
                    $this->request->data['radio1_disabled'] = 0;
                }else{
                    $this->request->data['radio1_disabled'] = 1;
                }
            }
            
            foreach(array_keys($this->request->data) as $key){
                if(preg_match('/^radio\d+_(disabled|band|channel|htmode|txpower|diversity|distance|noscan|ht_capab|ldpc|beacon_int|disable_b)/',$key)){            
                    if(preg_match('/^radio\d+_ht_capab/',$key)){
                        $pieces = explode("\n", $this->request->data["$key"]);
                        foreach($pieces as $p){
                            $wifi_setting->create();
                            $d_setting = array();
                            $d_setting['ApWifiSetting']['ap_id']   = $n_id;
                            $d_setting['ApWifiSetting']['name']    = $key;
                            $d_setting['ApWifiSetting']['value']   = $p;
                            $wifi_setting->save($d_setting);
                            $wifi_setting->id = null;
                        }
                        
                    }else{
                        $wifi_setting->create();
                        $d_setting = array();
                        $d_setting['ApWifiSetting']['ap_id']   = $n_id;
                        $d_setting['ApWifiSetting']['name']      = $key;
                        $d_setting['ApWifiSetting']['value']     = $this->request->data["$key"];
                        $wifi_setting->save($d_setting);
                        $wifi_setting->id = null;
                    }
                }
            }
            //------- END Add settings for this ap ---

            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }else{
            $message = 'Error';
            $this->set(array(
                'errors'    => $ap->validationErrors,
                'success'   => false,
                'message'   => array('message' => __('Could not create item')),
                '_serialize' => array('errors','success','message')
            ));
        }
    }
    
    public function ap_profile_ap_delete(){

       if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $user_id    = $user['id'];
        $fail_flag  = false;
        $ap       = ClassRegistry::init('Ap'); 

	    if(isset($this->data['id'])){   //Single item delete
            $message            = "Single item ".$this->data['id']; 
            $ap->id             = $this->data['id'];
            $ap->contain('ApProfile');
            $q_r                = $ap->findById($this->data['id']);
            if($q_r){
                $ap_profile_name    = $q_r['ApProfile']['name'];
                $ap_profile_name    = preg_replace('/\s+/', '_', $ap_profile_name);
                $ap_name            = $q_r['Ap']['name'];
                $ap_name            = preg_replace('/\s+/', '_', $ap_name);
                $this->DynamicClient->deleteAll(array('DynamicClient.nasidentifier LIKE' => "$ap_profile_name".'_'.$ap_name."_cp_%"), true);
                $this->DynamicPair->deleteAll(
                            array(
                                'DynamicPair.value LIKE' => "$ap_profile_name".'_'.$ap_name."_cp_%",
                                'DynamicPair.name' => 'nasid',
                            ), true);
            }       
            $ap->delete($ap->id, true);
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                    $ap->id = $d['id'];
                    $ap->contain('ApProfile');
                    $q_r                = $ap->findById($d['id']);
                    if($q_r){
                        $ap_profile_name    = $q_r['ApProfile']['name'];
                        $ap_profile_name    = preg_replace('/\s+/', '_', $ap_profile_name);
                        $ap_name            = $q_r['Ap']['name'];
                        $ap_name            = preg_replace('/\s+/', '_', $ap_name);
                        $this->DynamicClient->deleteAll(array('DynamicClient.nasidentifier LIKE' => "$ap_profile_name".'_'.$ap_name."_cp_%"), true);
                        $this->DynamicPair->deleteAll(
                            array(
                                'DynamicPair.value LIKE' => "$ap_profile_name".'_'.$ap_name."_cp_%",
                                'DynamicPair.name' => 'nasid',
                            ), true);
                    }       
                    $ap->delete($ap->id, true);
            }
        }  
        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }
    
    
    public function ap_profile_ap_edit(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if ($this->request->is('post')) {

            $ap                 = ClassRegistry::init('Ap');
            $wifi_setting       = ClassRegistry::init('ApWifiSetting');
			$move_ap_profiles	= false;

			if (array_key_exists('ap_profile_id', $this->request->data)) {
				$new_ap_profile_id 	= $this->request->data['ap_profile_id'];
				$ap->contain('ApProfile');
				$q_r 			= $ap->findById($this->request->data['id']);
				$current_id 	= $q_r['Ap']['ap_profile_id'];
				$current_ap_id  = $q_r['Ap']['id'];
				print_r($q_r);
				if($current_id != $new_ap_profile_id){	//Delete it if the ap profile changed
					$ap->delete($current_ap_id, true);
					$move_ap_profiles = true;
				}
				
				//See if we have a change in the name of the AP
				$old_name = $q_r['Ap']['name'];
				$new_name = $this->request->data['name'];
				if($old_name != $new_name){
				    $ap_profile_name = $q_r['ApProfile']['name'];
				    $ap_profile_name = preg_replace('/\s+/', '_', $ap_profile_name);
				    $this->_change_dynamic_shortname($ap_profile_name,$old_name,$new_name); //This is on the NAS Devices
			    }
			    		
			}
     
            if(true){
           // if ($ap->save($this->request->data)) {
          /*      $new_id = $ap->id;

				////if($this->_get_radio_count_for($this->request->data['hardware']) == 2){
				////	$this->_add_or_edit_dual_radio_settings($new_id); //$this->request will be available in that method we only send the new ap_id
				////}


                //---------Add WiFi settings for this ap ------
                //--Clean up--
                $a_id = $this->request->data['id'];
                $wifi_setting->deleteAll(array('ApWifiSetting.ap_id' => $a_id), true);
                
                //Check if the radio0_enable is perhaps missing
                if(array_key_exists('radio0_enable', $this->request->data)) {
                    $this->request->data['radio0_disabled'] = 0;
                }else{
                    $this->request->data['radio0_disabled'] = 1;
                }
            
                //Check for radio1 -> First we need to be sure there are a radio1!
                if(array_key_exists('radio1_band', $this->request->data)) {
                    if(array_key_exists('radio1_enable', $this->request->data)) {
                        $this->request->data['radio1_disabled'] = 0;
                    }else{
                        $this->request->data['radio1_disabled'] = 1;
                    }
                }
                            
                foreach(array_keys($this->request->data) as $key){
                    if(preg_match('/^radio\d+_(disabled|band|channel|htmode|txpower|diversity|distance|noscan|ht_capab|ldpc|beacon_int|disable_b)/',$key)){
                        
                        if(preg_match('/^radio\d+_ht_capab/',$key)){
                            $pieces = explode("\n", $this->request->data["$key"]);
                            foreach($pieces as $p){
                                $wifi_setting->create();
                                $d_setting = array();
                                $d_setting['ApWifiSetting']['ap_id']    = $a_id;
                                $d_setting['ApWifiSetting']['name']     = $key;
                                $d_setting['ApWifiSetting']['value']    = $p;
                                $wifi_setting->save($d_setting);
                                $wifi_setting->id = null;
                            }
                        }else{
                            $wifi_setting->create();
                            $d_setting = array();
                            $d_setting['ApWifiSetting']['ap_id']   = $a_id;
                            $d_setting['ApWifiSetting']['name']    = $key;
                            $d_setting['ApWifiSetting']['value']   = $this->request->data["$key"];
                            $wifi_setting->save($d_setting);
                            $wifi_setting->id = null;
                        }
                    }
                }
                //------- END Add settings for this ap ---
                */

                $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }else{
                $message = 'Error';
                $this->set(array(
                    'errors'    => $ap->validationErrors,
                    'success'   => false,
                    'message'   => array('message' => __('Could not create item')),
                    '_serialize' => array('errors','success','message')
                ));
            }
        } 
    }
    
     public function ap_profile_ap_view(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $ap = ClassRegistry::init('Ap');
		$ap->contain('ApWifiSetting');

        $id    = $this->request->query['ap_id'];
        $q_r   = $ap->findById($id);
 

        //Return the Advanced WiFi Settings...
        if(count($q_r['ApWifiSetting'])>0){

            $radio1_flag    = false;
            $r0_ht_capab    = array();
            $r1_ht_capab    = array();
            
            $r0_enable      = true;
            $r1_enable      = true;
            
            foreach($q_r['ApWifiSetting'] as $s){
                $s_name     = $s['name'];
                $s_value    = $s['value'];
                if($s_name == 'radio1_txpower'){
                    $radio1_flag = true;
                }
                
                if(($s_name == "radio0_disabled")&&($s_value == '1')){
                    $r0_enable = false;   
                }
                
                if(($s_name == "radio1_disabled")&&($s_value == '1')){
                    $r1_enable= false;   
                }

                if(!(preg_match('/^radio\d+_ht_capab/',$s_name))){
                    $q_r['Ap']["$s_name"] = "$s_value";
                }else{
                    if($s_name == 'radio0_ht_capab'){
                        array_push($r0_ht_capab,$s_value);
                    }
                    if($s_name == 'radio1_ht_capab'){
                        array_push($r1_ht_capab,$s_value);
                    }
                }
            }

            $q_r['Ap']['radio0_enable'] = $r0_enable;

            $q_r['Ap']['radio0_ht_capab'] = implode("\n",$r0_ht_capab);
            if($radio1_flag){
                $q_r['Ap']['radio1_ht_capab'] = implode("\n",$r1_ht_capab);
                $q_r['Ap']['radio1_enable'] = $r1_enable;
            }
        }else{
        
            Configure::load('ApProfiles'); 
            $hardware_list 	= Configure::read('ApProfiles.hardware'); //Read the defaults
		    foreach($hardware_list as $i){
			    if($i['id'] == $hardware){
				    foreach(array_keys($i) as $key){
                        if(preg_match('/^radio\d+_/',$key)){
                            if(preg_match('/^radio\d+_ht_capab/',$key)){
                                $q_r['Ap']["$key"] = implode("\n",$i["$key"]);
                            }else{
                                $q_r['Ap']["$key"] = $i["$key"];
                            }
                        }
                    }
                    break;
			    }
		    }
        }

		$q_r['Ap']['ap_profile_id'] = intval($q_r['Ap']['ap_profile_id']);

        $this->set(array(
            'data'      => $q_r['Ap'],
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
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

  
    function _build_common_query($user){

        //Empty to start with
        $c                  = array();
        $c['joins']         = array(); 
        $c['conditions']    = array();

        //What should we include....
        $c['contain']   = array(
                            'ApProfileNote'    => array('Note.note','Note.id','Note.available_to_siblings','Note.user_id'),
                            'User',
                            'Ap'
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'ApProfile.name';
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
        //====== END REQUEST FILTER =====

        //====== AP FILTER =====
        //If the user is an AP; we need to add an extra clause to only show the Profiles which he is allowed to see.
        if($user['group_name'] == Configure::read('group.ap')){  //AP
            $tree_array = array();
            $user_id    = $user['id'];

            //**AP and upward in the tree**
            $this->parents = $this->User->getPath($user_id,'User.id');
            //So we loop this results asking for the parent nodes who have available_to_siblings = true
            foreach($this->parents as $i){
                $i_id = $i['User']['id'];
                if($i_id != $user_id){ //upstream
                    array_push($tree_array,array($this->modelClass.'.user_id' => $i_id,$this->modelClass.'.available_to_siblings' => true));
                }else{
                    array_push($tree_array,array('ApProfile.user_id' => $i_id));
                }
            }
            //** ALL the AP's children
            $this->children    = $this->User->find_access_provider_children($user['id']);
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $id = $i['id'];
                    array_push($tree_array,array($this->modelClass.'.user_id' => $id));
                }       
            }       
            //Add it as an OR clause
            array_push($c['conditions'],array('OR' => $tree_array));  
        }       
        //====== END AP FILTER =====      
        return $c;
    }
    
    private function _get_action_flags($owner_id,$user){
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            return array('update' => true, 'delete' => true, 'view' => true);
        }

        

        if($user['group_name'] == Configure::read('group.ap')){  //AP
            $user_id = $user['id'];

            //test for self
            if($owner_id == $user_id){
                return array('update' => true, 'delete' => true, 'view' => true );
            }
            //Test for Parents
            foreach($this->parents as $i){
                if($i['User']['id'] == $owner_id){

                    $edit = false;
                    $view = false;

                    //Here we do a special thing to see if the owner of the ap profile perhaps allowed the person beneath him to edit and view the ap_profile
                    if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $user_id), $this->base.'ap_profile_entry_edit')){
                        $edit = true;
                    }

                    if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $user_id), $this->base.'ap_profile_entry_view')){
                        $view = true;
                    }
                    return array('update' => $edit, 'delete' => false, 'view' => $view );
                }
            }

            //Test for Children
            foreach($this->children as $i){
                if($i['id'] == $owner_id){
                    return array('update' => true, 'delete' => true, 'view' => true);
                }
            }  
        }
    }
    
    private function _make_linux_password($pwd){
		return exec("openssl passwd -1 $pwd");
	}
    
    private function _get_dead_after($ap_profile_id){
		Configure::load('ApProfiles');
		$data 		= Configure::read('common_ap_settings'); //Read the defaults
		$dead_after	= $data['heartbeat_dead_after'];
		$ap_s = $this->ApProfile->ApProfileSetting->find('first',array(
            'conditions'    => array(
                'ApProfileSetting.ap_profile_id' => $ap_profile_id
            )
        )); 
        if($ap_s){
            $dead_after = $ap_s['ApProfileSetting']['heartbeat_dead_after'];
        }
		return $dead_after;
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
                     array( 
                        'xtype'     =>  'splitbutton',  
                        'glyph'     => Configure::read('icnReload'),   
                        'scale'     => 'large', 
                        'itemId'    => 'reload',   
                        'tooltip'   => __('Reload'),
                            'menu'  => array( 
                                'items' => array( 
                                    '<b class="menu-title">'.__('Reload every').':</b>',
                                    array( 'text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                                    array( 'text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                                    array( 'text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                                    array( 'text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )
                                   
                                )
                            )
                    ),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete', 'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')),
                 ////   array('xtype' => 'button', 'iconCls' => 'b-view',    'glyph' => Configure::read('icnView'),'scale' => 'large', 'itemId' => 'view',     'tooltip'=> __('View'))
                )),
                array('xtype' => 'buttongroup','title' => __('Document'), 'width' => 100, 'items' => array(
                    array('xtype' => 'button', 'glyph' => Configure::read('icnNote'),'scale' => 'large', 'itemId' => 'note',    'tooltip'=> __('Add notes')),
                ))
                
            );
        }

        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $action_group   = array();
            $document_group = array();
            $specific_group = array();

            array_push($action_group,array( 
                'xtype'     =>  'splitbutton',  
                'glyph'     => Configure::read('icnReload'),   
                'scale'     => 'large', 
                'itemId'    => 'reload',   
                'tooltip'   => __('Reload'),
                    'menu'  => array( 
                        'items' => array( 
                            '<b class="menu-title">'.__('Reload every').':</b>',
                            array( 'text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                            array( 'text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                            array( 'text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                            array( 'text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )
                           
                        )
                    )
            	)
			);


            //Add
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base."add")){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnAdd'),     
                    'scale'     => 'large', 
                    'itemId'    => 'add',     
                    'tooltip'   => __('Add')));
            }
            //Delete
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'delete')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnDelete'),  
                    'scale'     => 'large', 
                    'itemId'    => 'delete', 
                    'disabled'  => true, 
                    'tooltip'   => __('Delete')));
            }

			//Edit
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'ap_profile_entry_edit')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnEdit'),  
                    'scale'     => 'large', 
                    'itemId'    => 'edit',
                    'disabled'  => true,  
                    'tooltip'   => __('Edit')));
            }


            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'note_index')){ 
                array_push($document_group,array(
                        'xtype'     => 'button', 
                        'glyph'     => Configure::read('icnNote'),     
                        'scale'     => 'large', 
                        'itemId'    => 'note',      
                        'tooltip'   => __('Add Notes')));
            }

            $menu = array(
                        array('xtype' => 'buttongroup','title' => __('Action'),        'items' => $action_group),
                        array('xtype' => 'buttongroup','title' => __('Document'), 'width' => 100,   'items' => $document_group)
                   );
        }
        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }
    
     public function menu_for_entries_grid(){

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
                    array('xtype' => 'button', 'iconCls' => 'b-reload',  'glyph'     => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload',   'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'iconCls' => 'b-add',     'glyph'     => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'iconCls' => 'b-delete',  'glyph'     => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'iconCls' => 'b-edit',    'glyph'     => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')),
                ))
                
            );
        }

		//Access Provider
        if($user['group_name'] == Configure::read('group.ap')){  //FIXME fine tune the rights later

            $menu = array(
                array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                    array('xtype' => 'button', 'iconCls' => 'b-reload',  'glyph'     => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload',   'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'iconCls' => 'b-add',     'glyph'     => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'iconCls' => 'b-delete',  'glyph'     => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'iconCls' => 'b-edit',    'glyph'     => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')),
                ))
                
            );
        }

        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

    public function menu_for_exits_grid(){

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
                    array('xtype' => 'button', 'glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload', 'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')),
                ))
                
            );
        }

		//Access Provider
        if($user['group_name'] == Configure::read('group.ap')){  //FIXME fine tune the rights later

            $menu = array(
            array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
            array('xtype' => 'button', 'glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload', 'tooltip'=> __('Reload')),
            array('xtype' => 'button', 'glyph' => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
            array('xtype' => 'button', 'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
            array('xtype' => 'button', 'glyph' => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')),
                ))
                
            );
        }

        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }
    
    public function menu_for_aps_grid(){

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
                 array( 
                        'xtype'     =>  'splitbutton',  
                        'iconCls'   => 'b-reload',
                        'glyph'     => Configure::read('icnReload'),   
                        'scale'     => 'large', 
                        'itemId'    => 'reload',   
                        'tooltip'   => __('Reload'),
                            'menu'  => array( 
                                'items' => array( 
                                    '<b class="menu-title">'.__('Reload every').':</b>',
                                    array( 'text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                                    array( 'text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                                    array( 'text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                                    array( 'text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )
                                   
                                )
                            )
                    ),
            array('xtype' => 'button', 'glyph' => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
            array('xtype' => 'button', 'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
            array('xtype' => 'button', 'glyph' => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')),
			array('xtype' => 'button', 'glyph' => Configure::read('icnView'),'scale' => 'large', 'itemId' => 'view',      'tooltip'=> __('View')),
			array('xtype' => 'button', 'glyph' => Configure::read('icnView'),'scale' => 'large', 'itemId' => 'view',      'tooltip'=> __('View')),
			array('xtype' => 'button', 'glyph' => Configure::read('icnSpanner'),'scale' => 'large', 'itemId' => 'execute','tooltip'=> __('Execute')),	
			array('xtype' => 'button', 'glyph' => Configure::read('icnPower'),'scale' => 'large', 'itemId' => 'restart','tooltip'=> __('Restart')),
                ))
    
            );
        }

		//Access Provider
		if($user['group_name'] == Configure::read('group.ap')){  //FIXME fine tune the rights later

            $menu = array(
                array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
             array( 
                        'xtype'     =>  'splitbutton',  
                        'iconCls'   => 'b-reload',
                        'glyph'     => Configure::read('icnReload'),   
                        'scale'     => 'large', 
                        'itemId'    => 'reload',   
                        'tooltip'   => __('Reload'),
                            'menu'  => array( 
                                'items' => array( 
                                    '<b class="menu-title">'.__('Reload every').':</b>',
                                    array( 'text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                                    array( 'text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                                    array( 'text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                                    array( 'text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )
                                   
                                )
                            )
                    ),
            array('xtype' => 'button', 'glyph' => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
            array('xtype' => 'button', 'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
            array('xtype' => 'button', 'glyph' => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')),
			array('xtype' => 'button', 'glyph' => Configure::read('icnView'),'scale' => 'large', 'itemId' => 'view',      'tooltip'=> __('View')),
			array('xtype' => 'button', 'glyph' => Configure::read('icnView'),'scale' => 'large', 'itemId' => 'view',      'tooltip'=> __('View')),
			array('xtype' => 'button', 'glyph' => Configure::read('icnSpanner'),'scale' => 'large', 'itemId' => 'execute','tooltip'=> __('Execute')),	
			array('xtype' => 'button', 'glyph' => Configure::read('icnPower'),'scale' => 'large', 'itemId' => 'restart','tooltip'=> __('Restart')),
                ))
    
            );
        }


        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

    
     public function menu_for_devices_grid(){

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
                    array('xtype' => 'button','glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload',   'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')),
			////		array('xtype' => 'button','glyph'     => Configure::read('icnMap'),'scale' => 'large', 'itemId' => 'map',      'tooltip'=> __('Map'))
                ))
    
            );
        }

		//Access Provider
		if($user['group_name'] == Configure::read('group.ap')){  //FIXME fine tune the rights later

             $menu = array(
                array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                    array('xtype' => 'button','glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload',   'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')),
			////		array('xtype' => 'button','glyph'     => Configure::read('icnMap'),'scale' => 'large', 'itemId' => 'map',      'tooltip'=> __('Map'))
                ))
    
            );
        }
        
        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }
    
    private function _add_dynamic($dc_data){
    
        //--Formulate a name
        $dc_data['name'] = 'APdesk_'.$dc_data['nasidentifier'];
        $this->DynamicClient->create();
        if ($this->DynamicClient->save($dc_data)) {
            //After this we can add the Realms if there are any
            $new_id = $this->DynamicClient->id;
            $pieces = explode(",", $dc_data['realm_list']);
            foreach($pieces as $p){
                if(is_numeric($p)){
                    $this->DynamicClientRealm->create();
                    $dcr = array();
                    $dcr['dynamic_client_id'] = $new_id;
                    $dcr['realm_id'] = $p;
                    $this->DynamicClientRealm->save($dcr);
                    $this->DynamicClientRealm->id = null;
                }
            }   
        }
    }
    
    private function _add_dynamic_pair($nas_data){
        $this->DynamicPair->create();
        $data = array();
        $data['name']               = 'nasid';
        $data['value']              = $nas_data['nasidentifier'];
        $data['dynamic_detail_id']  = $nas_data['dynamic_detail_id'];
        $data['priority']           = 1;  
        $this->DynamicPair->save($data);
    }
    
    private function _change_dynamic_shortname($ap_profile_name,$old_name,$new_name){ 
        $search_for = $ap_profile_name.'_'.$old_name.'_cp_';
        $this->DynamicClient->contain();
        $q_r = $this->DynamicClient->find('all',array('conditions' => array('DynamicClient.nasidentifier LIKE' => "$search_for%")));
        foreach($q_r as $n){  
            $current_name   = $n['DynamicClient']['nasidentifier'];
            $id             = $n['DynamicClient']['id'];
            $newname        = str_replace("$old_name","$new_name","$current_name");
            $d              = array();
            $d['id']        = $id;
            $d['nasidentifier'] = $newname;
            $this->DynamicClient->save($d);            
        }    
    }
    
     private function _check_if_available($openvpn_server_id,$ip){
        $count = $this->OpenvpnServerClient->find('count',
            array('conditions' => 
                array(
                    'OpenvpnServerClient.openvpn_server_id' => $openvpn_server_id,
                    'OpenvpnServerClient.ip_address' => $ip,
                )
            ));
        if($count == 0){
            return true;
        }else{
            return false;
        }
    }

    private function _get_next_ip($ip){
        $pieces     = explode('.',$ip);
        $octet_1    = $pieces[0];
        $octet_2    = $pieces[1];
        $octet_3    = $pieces[2];
        $octet_4    = $pieces[3];

        if($octet_4 >= 254){
            $octet_4 = 1;
            $octet_3 = $octet_3 +1;
        }else{

            $octet_4 = $octet_4 +1;
        }
        $next_ip = $octet_1.'.'.$octet_2.'.'.$octet_3.'.'.$octet_4;
        return $next_ip;
    }
    
}
