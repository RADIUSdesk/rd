<?php
App::uses('AppController', 'Controller');

class MeshesController extends AppController {

    public $name        = 'Meshes';
    public $components  = array('Aa','GridFilter');
    public $uses        = array('Mesh','User','DynamicClient','DynamicPair','DynamicClientRealm','OpenvpnServer','OpenvpnServerClient');
    protected $base     = "Access Providers/Controllers/Meshes/";
    protected $itemNote = 'MeshNote';

//------------------------------------------------------------------------

	//====== MESHES OVERVIEW =========

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
            foreach($i['MeshNote'] as $nn){
                if(!$this->_test_for_private_parent($nn['Note'],$user)){
                    $notes_flag = true;
                    break;
                }
            }

            $owner_id       = $i['Mesh']['user_id'];
            $owner_tree     = $this->_find_parents($owner_id);
            $action_flags   = $this->_get_action_flags($owner_id,$user);
			$mesh_id		= $i['Mesh']['id'];

			//Get the 'dead_after' value
			$dead_after = $this->_get_dead_after($mesh_id);
			$now		= time();

			$node_count 	= 0;
			$nodes_up		= 0;
			$nodes_down		= 0;
			foreach($i['Node'] as $node){
				$l_contact  = $node['last_contact'];
				//===Determine when last did we saw this node (never / up / down) ====
				$last_timestamp = strtotime($l_contact);
	            if($last_timestamp+$dead_after <= $now){
	                $nodes_down++;
	            }else{
					$nodes_up++;  
	            }
				$node_count++;
			}

            array_push($items,array(
                'id'                    => $i['Mesh']['id'], 
                'name'                  => $i['Mesh']['name'],
                'ssid'                  => $i['Mesh']['ssid'],
                'bssid'                 => $i['Mesh']['bssid'],
				'available_to_siblings' => $i['Mesh']['available_to_siblings'],
                'node_count'            => $node_count,
                'nodes_up'              => $nodes_up,
                'nodes_down'            => $nodes_down,
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
            $item           = $this->{$this->modelClass}->findById($this->data['id']);
            $owner_id       = $item['Mesh']['user_id'];
            $profile_name   = $item['Mesh']['name'];
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
                $owner_id       = $item['Mesh']['user_id'];
                $profile_name   = $item['Mesh']['name'];
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
                    'conditions'    => array('MeshNote.mesh_id' => $pc_id)
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
            $d['MeshNote']['mesh_id']   = $this->request->data['for_id'];
            $d['MeshNote']['note_id'] = $this->{$this->modelClass}->MeshNote->Note->id;
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
                $this->{$this->modelClass}->MeshNote->Note->id = $this->data['id'];
                $this->{$this->modelClass}->MeshNote->Note->delete($this->data['id'],true);
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

//___________________________________________________

    //======= MESH entries ============
    public function mesh_entries_index(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $items      = array();
        $total      = 0;
        $entry      = ClassRegistry::init('MeshEntry');
        $entry->contain('MeshExitMeshEntry');
        $mesh_id    = $this->request->query['mesh_id'];
        $q_r        = $entry->find('all',array('conditions' => array('MeshEntry.mesh_id' => $mesh_id)));
        
        foreach($q_r as $m){
            $connected_to_exit = true;   
            if(count($m['MeshExitMeshEntry']) == 0){
                $connected_to_exit = false;
            }
            array_push($items,array( 
                'id'            => $m['MeshEntry']['id'],
                'mesh_id'       => $m['MeshEntry']['mesh_id'],
                'name'          => $m['MeshEntry']['name'],
                'hidden'        => $m['MeshEntry']['hidden'],
                'isolate'       => $m['MeshEntry']['isolate'],
                'apply_to_all'  => $m['MeshEntry']['apply_to_all'],
                'encryption'    => $m['MeshEntry']['encryption'],
                'special_key'   => $m['MeshEntry']['special_key'],
                'auth_server'   => $m['MeshEntry']['auth_server'],
                'auth_secret'   => $m['MeshEntry']['auth_secret'],
                'dynamic_vlan'  => $m['MeshEntry']['dynamic_vlan'],
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

    public function mesh_entry_add(){
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $entry = ClassRegistry::init('MeshEntry'); 
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

    public function mesh_entry_edit(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if ($this->request->is('post')) {

            $check_items = array('hidden','isolate','apply_to_all');
            foreach($check_items as $i){
                if(isset($this->request->data[$i])){
                    $this->request->data[$i] = 1;
                }else{
                    $this->request->data[$i] = 0;
                }
            }

            $entry = ClassRegistry::init('MeshEntry');
            // If the form data can be validated and saved...
            if ($entry->save($this->request->data)) {
                   $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }
        } 
    }

    public function mesh_entry_view(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $entry = ClassRegistry::init('MeshEntry');

        $id    = $this->request->query['entry_id'];
        $q_r   = $entry->findById($id);
        
        if($q_r['MeshEntry']['macfilter'] != 'disable'){ 
            $pu = ClassRegistry::init('PermanentUser');
            $pu->contain();
            $q = $pu->findById($q_r['MeshEntry']['permanent_user_id']);
            if($q){
                $q_r['MeshEntry']['username'] = $q['PermanentUser']['username'];    
            }else{
                $q_r['MeshEntry']['username'] = "!!!User Missing!!!";
            }
        }

        $this->set(array(
            'data'     => $q_r['MeshEntry'],
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
    }

    public function mesh_entry_delete(){

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
        $entry      = ClassRegistry::init('MeshEntry'); 

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

    //======= MESH settings =======
    public function mesh_settings_view(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $id         = $this->request->query['mesh_id']; 
		Configure::load('MESHdesk'); 
        $data       = Configure::read('mesh_settings'); //Read the defaults
        $setting    = ClassRegistry::init('MeshSetting');
        $setting->contain();

        $q_r = $setting->find('first', array('conditions' => array('MeshSetting.mesh_id' => $id)));
        if($q_r){  
            $data = $q_r['MeshSetting'];  
        }

        $this->set(array(
            'data'      => $data,
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
    }

    public function mesh_settings_edit(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if ($this->request->is('post')) {

            //Unfortunately there are many check items which means they will not be in the POST if unchecked
            //so we have to check for them
            $check_items = array(
				'aggregated_ogms',
				'ap_isolation',
				'bonding',
				'fragmentation',
				'bridge_loop_avoidance',
				'distributed_arp_table',
				'encryption'
			);
            foreach($check_items as $i){
                if(isset($this->request->data[$i])){
                    $this->request->data[$i] = 1;
                }else{
                    $this->request->data[$i] = 0;
                }
            }

            $mesh_id = $this->request->data['mesh_id'];
            //See if there is not already a setting entry
            $setting    = ClassRegistry::init('MeshSetting');
            $q_r        = $setting->find('first', array('conditions' => array('MeshSetting.mesh_id' => $mesh_id)));
            if($q_r){
                $this->request->data['id'] = $q_r['MeshSetting']['id']; //Set the ID
            }

            if ($setting->save($this->request->data)) {
                   $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }
        }
    }

    //======= MESH exits ============
    public function mesh_exits_index(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $items      = array();
        $total      = 0;
        $exit      = ClassRegistry::init('MeshExit');
        $exit->contain('MeshExitMeshEntry.MeshEntry.name');
        $mesh_id    = $this->request->query['mesh_id'];
        $q_r        = $exit->find('all',array('conditions' => array('MeshExit.mesh_id' => $mesh_id)));
       // print_r($q_r);

        foreach($q_r as $m){
            $exit_entries = array();

            foreach($m['MeshExitMeshEntry'] as $m_e_ent){
                array_push($exit_entries,array('name' => $m_e_ent['MeshEntry']['name']));
            }

            array_push($items,array( 
                'id'            => $m['MeshExit']['id'],
                'mesh_id'       => $m['MeshExit']['mesh_id'],
                'name'          => $m['MeshExit']['name'],
                'type'          => $m['MeshExit']['type'],
                'connects_with' => $exit_entries,
                'auto_detect'   => $m['MeshExit']['auto_detect'],

            ));
        }
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    public function mesh_exit_add(){
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $entry_point    = ClassRegistry::init('MeshExitMeshEntry');
        $exit           = ClassRegistry::init('MeshExit'); 
        $exit->create();
        
        
        if($this->request->data['type'] == 'captive_portal'){ 
            if(isset($this->request->data['auto_dynamic_client'])){
                $this->request->data['auto_dynamic_client'] = 1; 
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
            $new_id = $exit->id;
            
            //---- openvpn_bridge -----
            if($this->request->data['type'] == 'openvpn_bridge'){
                
                $server_id  = $this->request->data['openvpn_server_id'];
                $q_r        = $this->OpenvpnServer->findById($server_id);
                if($q_r){    
                    $d_vpn_c                    = array();
                    $d_vpn_c['mesh_ap_profile'] = 'mesh';
                    $d_vpn_c['mesh_id']         = $this->request->data['mesh_id'];
                    $d_vpn_c['openvpn_server_id'] = $this->request->data['openvpn_server_id'];
                    $d_vpn_c['mesh_exit_id']    = $new_id;
                
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
                    $this->OpenvpnServerClient->create();
                    $this->OpenvpnServerClient->save($d_vpn_c);   
                }           
            }
            //---- END openvpn_bridge ------
            
            

            //===== Captive Portal ==========
            if($this->request->data['type'] == 'captive_portal'){

                $this->request->data['mesh_exit_id'] = $new_id;
                
                //----------- Easy to use enhancement --------------------
                //See if we have to formulate the value of the 'radius_nasid' if the user chose to auto add it 
                if(
                    ($this->request->data['auto_dynamic_client'] == 1)||
                    ($this->request->data['auto_login_page'] == 1)
                ){
                
                    //Get the Mesh so we can get the user_id and available_to_siblings for the said mesh
                    $mesh_id  = $this->request->data['mesh_id'];
                    $this->Mesh->contain();   
                    $mesh       = $this->Mesh->findById($mesh_id);
                    $user_id    = $mesh['Mesh']['user_id'];
                    $a_to_s     = $mesh['Mesh']['available_to_siblings'];
                    $mesh_name  = $mesh['Mesh']['name'];
                    $mesh_name  = preg_replace('/\s+/', '_', $mesh_name);
                    
                                       
                    $dc_data                            = array();       	            
	                $dc_data['user_id']                 = $user_id;
	                $dc_data['available_to_siblings']   = $a_to_s;
	                $dc_data['nasidentifier']           = $mesh_name.'_mcp_'.$new_id;
	                
	                //Get a list of realms if the person selected a list - If it is empty that's fine
                    $count      = 0;
                    $dc_data['realm_list'] = ""; //Prime it
                    if (array_key_exists('realm_ids', $this->request->data)) {
                        foreach($this->request->data['realm_ids'] as $r){
                            if($count == 0){
                                $dc_data['realm_list'] = $this->request->data['realm_ids'][$count]; 
                            }else{
                                $dc_data['realm_list'] = $dc_data['realm_list'].",".$this->request->data['realm_ids'][$count];
                            }  
                            $count++;
                        }
                    }
                    
	                if($this->request->data['auto_dynamic_client'] == 1){    	                
                        $this->_add_dynamic($dc_data);
                    }
                    
                    if($this->request->data['auto_login_page'] == 1){ 
	                    $dc_data['dynamic_detail_id'] = $this->request->data['dynamic_detail_id'];
	                    $this->_add_dynamic_pair($dc_data);
	                }
                                      
                    //Set the radius_nasid
                    $this->request->data['radius_nasid'] = $dc_data['nasidentifier'];  
                }
                //----------- END Easy to use enhancement --------------------
                
                $captive_portal = ClassRegistry::init('MeshExitCaptivePortal');
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
                    $data['MeshExitMeshEntry']['mesh_exit_id']  = $new_id;
                    $data['MeshExitMeshEntry']['mesh_entry_id'] = $id;
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

    public function mesh_exit_edit(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if ($this->request->is('post')) {

            $entry_point    = ClassRegistry::init('MeshExitMeshEntry');
            $exit           = ClassRegistry::init('MeshExit');
            
            
            //---- openvpn_bridge -----
            if($this->request->data['type'] == 'openvpn_bridge'){
                
                $server_id  = $this->request->data['openvpn_server_id'];
                
                //We will only do the following if the selected OpenvpnServer changed
                $exit->contain();
                $q_exit             = $exit->findById($this->request->data['id']);
                $current_server_id  = $q_exit['MeshExit']['openvpn_server_id'];
                $server_id      = $this->request->data['openvpn_server_id'];
                
                if($current_server_id !== $server_id){
                    //Delete old one 
                    $this->OpenvpnServerClient->deleteAll(
                        array('OpenvpnServerClient.openvpn_server_id' => $current_server_id), 
                        false
                    );
                    
                    $q_r        = $this->OpenvpnServer->findById($server_id);
                    if($q_r){    
                        $d_vpn_c                        = array();
                        $d_vpn_c['mesh_ap_profile']     = 'mesh';
                        $d_vpn_c['mesh_id']             = $this->request->data['mesh_id'];
                        $d_vpn_c['openvpn_server_id']   = $this->request->data['openvpn_server_id'];
                        $d_vpn_c['mesh_exit_id']        = $this->request->data['id'];
                    
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
                        $this->OpenvpnServerClient->create();
                        $this->OpenvpnServerClient->save($d_vpn_c);   
                    }
                    
                }           
            }
            //---- END openvpn_bridge ------
            


            //===== Captive Portal ==========
            //== First see if we can save the captive portal data ====
            if($this->request->data['type'] == 'captive_portal'){

                $captive_portal = ClassRegistry::init('MeshExitCaptivePortal');
                $cp_data        = $this->request->data;
                $mesh_exit_id   = $this->request->data['id'];
                $q_r            = $captive_portal->find(
                                    'first',array('conditions' => array('MeshExitCaptivePortal.mesh_exit_id' => $mesh_exit_id)));               
                if($q_r){
                    $cp_id = $q_r['MeshExitCaptivePortal']['id'];
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

            // If the form data can be validated and saved...
            if ($exit->save($this->request->data)) {

                //Add the entry points
                $count      = 0;
                $entry_ids  = array();
                $empty_flag = false;
                $new_id     = $this->request->data['id'];

                //Clear previous ones first:
                $entry_point->deleteAll(array('MeshExitMeshEntry.mesh_exit_id' => $new_id), false);

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
                        $data['MeshExitMeshEntry']['mesh_exit_id']  = $new_id;
                        $data['MeshExitMeshEntry']['mesh_entry_id'] = $id;
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
    
    public function mesh_exit_add_defaults(){
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        
        Configure::load('MESHdesk'); 
        $data = Configure::read('Meshes.captive_portal'); //Read the defaults
        $this->set(array(
            'data'     => $data,
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
    }
    
    public function mesh_experimental_check(){
        Configure::load('RadiusDesk'); 
        $active = Configure::read('experimental.active'); //Read the defaults
        $this->set(array(
            'active'     => $active,
            'success'   => true,
            '_serialize'=> array('success', 'active')
        ));
    }

    public function mesh_exit_view(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $exit = ClassRegistry::init('MeshExit');
        $exit->contain('MeshExitMeshEntry','MeshExitCaptivePortal');

        $id    = $this->request->query['exit_id'];
        $q_r   = $exit->findById($id);

        //entry_points
        $q_r['MeshExit']['entry_points'] = array();
        foreach($q_r['MeshExitMeshEntry'] as $i){
            array_push($q_r['MeshExit']['entry_points'],intval($i['mesh_entry_id']));
        }

        if($q_r['MeshExitCaptivePortal']){
            $q_r['MeshExit']['radius_1']        = $q_r['MeshExitCaptivePortal']['radius_1'];
            $q_r['MeshExit']['radius_2']        = $q_r['MeshExitCaptivePortal']['radius_2'];
            $q_r['MeshExit']['radius_secret']   = $q_r['MeshExitCaptivePortal']['radius_secret'];
            $q_r['MeshExit']['radius_nasid']    = $q_r['MeshExitCaptivePortal']['radius_nasid'];
            $q_r['MeshExit']['uam_url']         = $q_r['MeshExitCaptivePortal']['uam_url'];
            $q_r['MeshExit']['uam_secret']      = $q_r['MeshExitCaptivePortal']['uam_secret'];
            $q_r['MeshExit']['walled_garden']   = $q_r['MeshExitCaptivePortal']['walled_garden'];
            $q_r['MeshExit']['swap_octets']     = $q_r['MeshExitCaptivePortal']['swap_octets'];
			$q_r['MeshExit']['mac_auth']        = $q_r['MeshExitCaptivePortal']['mac_auth'];

            //Proxy settings
            $q_r['MeshExit']['proxy_enable']    = $q_r['MeshExitCaptivePortal']['proxy_enable'];
            $q_r['MeshExit']['proxy_ip']        = $q_r['MeshExitCaptivePortal']['proxy_ip'];
            $q_r['MeshExit']['proxy_port']      = intval($q_r['MeshExitCaptivePortal']['proxy_port']);
            $q_r['MeshExit']['proxy_auth_username']      = $q_r['MeshExitCaptivePortal']['proxy_auth_username'];
            $q_r['MeshExit']['proxy_auth_password']      = $q_r['MeshExitCaptivePortal']['proxy_auth_password'];
            $q_r['MeshExit']['coova_optional']  = $q_r['MeshExitCaptivePortal']['coova_optional'];
            
            //DNS settings
            $q_r['MeshExit']['dns_manual']      = $q_r['MeshExitCaptivePortal']['dns_manual'];
            $q_r['MeshExit']['dns1']            = $q_r['MeshExitCaptivePortal']['dns1'];
            $q_r['MeshExit']['dns2']            = $q_r['MeshExitCaptivePortal']['dns2'];
            $q_r['MeshExit']['uamanydns']       = $q_r['MeshExitCaptivePortal']['uamanydns'];
            $q_r['MeshExit']['dnsparanoia']     = $q_r['MeshExitCaptivePortal']['dnsparanoia'];
            $q_r['MeshExit']['dnsdesk']         = $q_r['MeshExitCaptivePortal']['dnsdesk'];

        }

        $data = $q_r['MeshExit'];

      //  print_r($q_r);

        $this->set(array(
            'data'     => $data,
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
    }

	public function mesh_exit_view_eth_br(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $exit = ClassRegistry::init('MeshExit');
        $exit->contain();

        $id    = $this->request->query['mesh_id'];
        $q_r   = $exit->findAllByMeshId($id);
		$data  = array();
		array_push($data,array('id' => 0, 'name' => 'LAN')); //First entry

		foreach($q_r as $i){
			$id 	= $i['MeshExit']['id'];
			$name 	= $i['MeshExit']['name'];
			array_push($data, array('id'=> $id,'name' => $name));
		}

        $this->set(array(
            'items'     => $data,
            'success'   => true,
            '_serialize'=> array('success', 'items')
        ));
    }
    
     public function mesh_exit_upstream_list(){
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

    public function mesh_exit_delete(){

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
        $exit       = ClassRegistry::init('MeshExit'); 

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id']; 
            $exit->id = $this->data['id'];
            $exit->delete($exit->id, true);
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                    $exit->id = $d['id'];
                    $exit->delete($exit->id, true);
            }
        }  
        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }


    //===== Mesh nodes ======

    public function timezone_index(){
        Configure::load('MESHdesk');      
        $timezones   = Configure::read('MESHdesk.timezones');
        $this->set(array(
            'items' => $timezones,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

     public function country_index(){
        Configure::load('MESHdesk');      
        $timezones   = Configure::read('MESHdesk.countries');
        $this->set(array(
            'items' => $timezones,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    public function advanced_settings_for_model(){
        $data = array();
        Configure::load('MESHdesk');
        $hw   = Configure::read('hardware');
        $model= $this->request->query['model'];

        $no_overrides = true;

        //Check if there is a node_id in the request and if so check the current hardware type
        //If the same as the model requested, check if we have overrides
        if(array_key_exists('node_id', $this->request->query)){

            $node   = ClassRegistry::init('Node');
            $node->contain('NodeWifiSetting');
            $q_r    = $node->findById($this->request->query['node_id']);
            
            $data['radio0_band'] = $q_r['Node']['radio0_band'];
            $data['radio1_band'] = $q_r['Node']['radio1_band'];
            
            if($q_r){
                $current_model = $q_r['Node']['hardware'];
                if($current_model == $model){ //Its the same so lets check if there are any custom settings
                    if(count($q_r['NodeWifiSetting'])>0){

                        $radio1_flag    = false;
                        $r0_ht_capab    = array();
                        $r1_ht_capab    = array();

                        foreach($q_r['NodeWifiSetting'] as $s){
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
                    $device_type = 'standard';
                    $radio0_band = '24';
                    $radio1_band = false;
                    foreach(array_keys($h) as $key){
                    
                        //AC Device or not
                        if($key == 'device_type'){
                            $device_type = $h["$key"];
                        }
                    
                        //Radio zero band adjust
                        if($key == 'five'){
                            if($h["$key"]){
                                $radio0_band = '5';
                            }
                        }
                        
                        //Radio one band adjust
                        if($key == 'five1'){
                            if($h["$key"]){
                                $radio1_band = '5';
                            }
                        }
                        
                        if($key == 'two1'){
                            if($h["$key"]){
                                $radio1_band = '24';
                            }
                        }
                        
                        if(preg_match('/^radio\d+_/',$key)){
                            if(preg_match('/^radio\d+_ht_capab/',$key)){
                                $data["$key"] = implode("\n",$h["$key"]);
                            }else{
                                $data["$key"] = $h["$key"];
                            }
                        }
                    }
                       
                    $data['radio0_band'] = $radio0_band;
                    if($radio1_band){
                        $data['radio1_band'] = $radio1_band;
                    }
                    $data['device_type'] = $device_type;
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


    public function mesh_nodes_index(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $items      = array();
        $total      = 0;
        $node       = ClassRegistry::init('Node');
        $node->contain(array('NodeMeshEntry.MeshEntry','NodeMeshExit.MeshExit'));
        $mesh_id    = $this->request->query['mesh_id'];
        $q_r        = $node->find('all',array('conditions' => array('Node.mesh_id' => $mesh_id)));

        //Create a hardware lookup for proper names of hardware
        $hardware = array();  
		Configure::load('MESHdesk');      
        $hw   = Configure::read('hardware');
        foreach($hw as $h){
            $id     = $h['id'];
            $name   = $h['name']; 
            $hardware["$id"]= $name;
        }

		//Check if we need to show the override on the power
		$node_setting	= ClassRegistry::init('NodeSetting');
		$node_setting->contain();

		$power_override = false;

		$ns				= $node_setting->find('first', array('conditions' => array('NodeSetting.mesh_id' => $mesh_id)));
		if($ns){
			if($ns['NodeSetting']['all_power'] == 1){
				$power_override = true;
				$power 			= $ns['NodeSetting']['power'];
			}
		}else{
			$data       = Configure::read('common_node_settings'); //Read the defaults
			if($data['all_power'] == true){
				$power_override = true;
				$power 			= $data['power'];
			}
		}

        foreach($q_r as $m){
            $static_entries = array();
            $static_exits   = array();
            foreach($m['NodeMeshEntry'] as $m_e_ent){
                array_push($static_entries,array('name' => $m_e_ent['MeshEntry']['name']));
            }

            foreach($m['NodeMeshExit'] as $m_e_exit){
                array_push($static_exits,array('name'   => $m_e_exit['MeshExit']['name']));
            }

			if($power_override){
				$p = $power;
			}else{
				$p = $m['Node']['power'];
			}
			

            $hw_id = $m['Node']['hardware'];
            array_push($items,array( 
                'id'            => $m['Node']['id'],
                'mesh_id'       => $m['Node']['mesh_id'],
                'name'          => $m['Node']['name'],
                'description'   => $m['Node']['description'],
                'mac'           => $m['Node']['mac'],
                'hardware'      => $hardware["$hw_id"],
                'power'         => $p,
				'ip'			=> $m['Node']['ip'],
				'last_contact'	=> $m['Node']['last_contact'],
				'lat'			=> $m['Node']['lat'],
				'lng'			=> $m['Node']['lon'],
                'static_entries'=> $static_entries,
                'static_exits'  => $static_exits,
                'ip'            => $m['Node']['ip'],
            ));
        }
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    public function mesh_node_add(){
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $static_entry   = ClassRegistry::init('NodeMeshEntry');
        $static_exit    = ClassRegistry::init('NodeMeshExit');
        $node           = ClassRegistry::init('Node');

        $wifi_setting   = ClassRegistry::init('NodeWifiSetting');
 
        $node->create();
        if ($node->save($this->request->data)) {

            $new_id = $node->id;

			//Check if it was submitted through the attach  node window - then remove the unknown_node with mac = mac
			if(array_key_exists('rem_unknown', $this->request->data)) {
				$unknown_node   = ClassRegistry::init('UnknownNode');
				$mac			= $this->request->data['mac'];
 				$unknown_node->deleteAll(array('UnknownNode.mac' => $mac), true);
			}

            //Add the entry points
            $count      = 0;
            $entry_ids  = array();
            $empty_flag = false;

            if (array_key_exists('static_entries', $this->request->data)) {

                foreach($this->request->data['static_entries'] as $e){
                    if($this->request->data['static_entries'][$count] == 0){
                        $empty_flag = true;
                        break;
                    }else{
                        array_push($entry_ids,$this->request->data['static_entries'][$count]);
                    }
                    $count++;
                }
            }

            //Only if empty was not specified
            if((!$empty_flag)&&(count($entry_ids)>0)){  
                foreach($entry_ids as $id){
                	$data = array();
                    $data['NodeMeshEntry']['node_id']       = $new_id;
                    $data['NodeMeshEntry']['mesh_entry_id'] = $id;
					$static_entry->create();
                    $static_entry->save($data);
					$static_entry->id = null;	
                }
            }

            //Add the exit points
            $count      = 0;
            $exit_ids  = array();
            $e_flag = false;

            if (array_key_exists('static_exits', $this->request->data)) {
                foreach($this->request->data['static_exits'] as $e){
                    if($this->request->data['static_exits'][$count] == 0){
                        $e_flag = true;
                        break;
                    }else{
                        array_push($entry_ids,$this->request->data['static_exits'][$count]);
                    }
                    $count++;
                }
            }

            //Only if empty was not specified
            if((!$e_flag)&&(count($exit_ids)>0)){
                foreach($entry_ids as $id){
                    $data = array();
                    $data['NodeMeshExit']['node_id']       = $new_id;
                    $data['NodeMeshExit']['mesh_exit_id']  = $id;
					$static_exit->create();
                    $static_exit->save($data);
					$static_exit->id = null;
                }
            }

			//____ Do a check if it is a MP2 type of device and send it away to be delt with
			if(
				($this->request->data['hardware'] == 'mp2_phone')||
				($this->request->data['hardware'] == 'mp2_basic')
			){
				$this->_add_or_edit_mp_settings($new_id); //$this->request will be available in that method we only send the new node_id
			}

			//___ Do a check if the device is a dual radio device and if so; send it way to be delt with
			if($this->_get_radio_count_for($this->request->data['hardware']) == 2){
				$this->_add_or_edit_dual_radio_settings($new_id); //$this->request will be available in that method we only send the new node_id
			}

            //---------Add WiFi settings for this node ------
            //--Clean up--
            $n_id = $new_id;
            foreach(array_keys($this->request->data) as $key){
                if(preg_match('/^radio\d+_(htmode|txpower|diversity|distance|noscan|ht_capab|ldpc|beacon_int|disable_b)/',$key)){            
                    if(preg_match('/^radio\d+_ht_capab/',$key)){
                        $pieces = explode("\n", $this->request->data["$key"]);
                        foreach($pieces as $p){
                            $wifi_setting->create();
                            $d_setting = array();
                            $d_setting['NodeWifiSetting']['node_id']   = $n_id;
                            $d_setting['NodeWifiSetting']['name']      = $key;
                            $d_setting['NodeWifiSetting']['value']     = $p;
                            $wifi_setting->save($d_setting);
                            $wifi_setting->id = null;
                        }
                    }else{
                        $wifi_setting->create();
                        $d_setting = array();
                        $d_setting['NodeWifiSetting']['node_id']   = $n_id;
                        $d_setting['NodeWifiSetting']['name']      = $key;
                        $d_setting['NodeWifiSetting']['value']     = $this->request->data["$key"];
                        $wifi_setting->save($d_setting);
                        $wifi_setting->id = null;
                    }
                }
                
                if($key == 'device_type'){
                    $wifi_setting->create();
                    $d_setting = array();
                    $d_setting['NodeWifiSetting']['node_id']   = $n_id;
                    $d_setting['NodeWifiSetting']['name']      = $key;
                    $d_setting['NodeWifiSetting']['value']     = $this->request->data["$key"];
                    $wifi_setting->save($d_setting);
                    $wifi_setting->id = null;
                }  
            }
            //------- END Add settings for this node ---

            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }else{
            $message = 'Error';
            $this->set(array(
                'errors'    => $node->validationErrors,
                'success'   => false,
                'message'   => array('message' => __('Could not create item')),
                '_serialize' => array('errors','success','message')
            ));
        }
    }

    public function mesh_node_edit(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if ($this->request->is('post')) {

			$static_entry   = ClassRegistry::init('NodeMeshEntry');
            $static_exit    = ClassRegistry::init('NodeMeshExit');
            $node           = ClassRegistry::init('Node');
            $neighbors      = ClassRegistry::init('NodeNeighbor');

            $wifi_setting   = ClassRegistry::init('NodeWifiSetting');

			$move_meshes	= false;

			if (array_key_exists('mesh_id', $this->request->data)) {
				$new_mesh_id 	= $this->request->data['mesh_id'];
				$node->contain();
				$q_r 			= $node->findById($this->request->data['id']);
				$current_id 	= $q_r['Node']['mesh_id'];
				if($current_id != $new_mesh_id){	//Delete it if the mesh changed
					$node->delete($current_id, true);
                    $neighbors->deleteAll(array('NodeNeighbor.neighbor_id' => $node->id), true);
					$move_meshes = true;
				}
			}

			//We are moving meshes - we need the ip
            if($move_meshes){
			    $ip = $node->get_ip_for_node($this->request->data['mesh_id']);
			    $this->request->data['ip'] = $ip;
            }  
     
            if ($node->save($this->request->data)) {
                $new_id = $node->id;

				//Clear previous ones first:
                $static_entry->deleteAll(array('NodeMeshEntry.node_id' => $new_id), false);

                //Add the entry points
                $count      = 0;
                $entry_ids  = array();
                $empty_flag = false;

                if (array_key_exists('static_entries', $this->request->data)) {
                    foreach($this->request->data['static_entries'] as $e){
                        if($this->request->data['static_entries'][$count] == 0){
                            $empty_flag = true;
                            break;
                        }else{
                            array_push($entry_ids,$this->request->data['static_entries'][$count]);
                        }
                        $count++;
                    }
                }

                //Only if empty was not specified
                if((!$empty_flag)&&(count($entry_ids)>0)){
                    foreach($entry_ids as $id){
                        $data = array();
                        $data['NodeMeshEntry']['node_id']       = $new_id;
                        $data['NodeMeshEntry']['mesh_entry_id'] = $id;
						$static_entry->create();
                        $static_entry->save($data);
						$static_entry->id = null;
                    }
                }

				//Clear previous ones first:
                $static_exit->deleteAll(array('NodeMeshExit.node_id' => $new_id), false);

                //Add the exit points
                $count      = 0;
                $exit_ids  = array();
                $e_flag = false;

                if (array_key_exists('static_exits', $this->request->data)) {
                    foreach($this->request->data['static_exits'] as $e){
                        if($this->request->data['static_exits'][$count] == 0){
                            $e_flag = true;
                            break;
                        }else{
                            array_push($entry_ids,$this->request->data['static_exits'][$count]);
                        }
                        $count++;
                    }
                }

                //Only if empty was not specified
                if((!$e_flag)&&(count($exit_ids)>0)){
                    foreach($entry_ids as $id){
                    	$data = array();
                        $data['NodeMeshExit']['node_id']       = $new_id;
                        $data['NodeMeshExit']['mesh_exit_id']  = $id;
						$static_exit->create();
                        $static_exit->save($data);
						$static_exit->id = null;
                    }
                }

				if(
					($this->request->data['hardware'] == 'mp2_phone')||
					($this->request->data['hardware'] == 'mp2_basic')
				){
					$this->_add_or_edit_mp_settings($new_id); //$this->request will be available in that method we only send the new node_id
				}

				//___ Do a check if the device is a dual radio device and if so; send it way to be delt with

				if($this->_get_radio_count_for($this->request->data['hardware']) == 2){
					$this->_add_or_edit_dual_radio_settings($new_id); //$this->request will be available in that method we only send the new node_id
				}


                //---------Add WiFi settings for this node ------
                //--Clean up--
                $n_id = $this->request->data['id'];
                $wifi_setting->deleteAll(array('NodeWifiSetting.node_id' => $n_id), true);
                foreach(array_keys($this->request->data) as $key){
                    if(preg_match('/^radio\d+_(htmode|txpower|diversity|distance|noscan|ht_capab|ldpc|beacon_int|disable_b)/',$key)){
                        
                        if(preg_match('/^radio\d+_ht_capab/',$key)){
                            $pieces = explode("\n", $this->request->data["$key"]);
                            foreach($pieces as $p){
                                $wifi_setting->create();
                                $d_setting = array();
                                $d_setting['NodeWifiSetting']['node_id']   = $n_id;
                                $d_setting['NodeWifiSetting']['name']      = $key;
                                $d_setting['NodeWifiSetting']['value']     = $p;
                                $wifi_setting->save($d_setting);
                                $wifi_setting->id = null;
                            }
                        }else{
                            $wifi_setting->create();
                            $d_setting = array();
                            $d_setting['NodeWifiSetting']['node_id']   = $n_id;
                            $d_setting['NodeWifiSetting']['name']      = $key;
                            $d_setting['NodeWifiSetting']['value']     = $this->request->data["$key"];
                            $wifi_setting->save($d_setting);
                            $wifi_setting->id = null;
                        }
                    }
                    
                    if($key == 'device_type'){
                        $wifi_setting->create();
                        $d_setting = array();
                        $d_setting['NodeWifiSetting']['node_id']   = $n_id;
                        $d_setting['NodeWifiSetting']['name']      = $key;
                        $d_setting['NodeWifiSetting']['value']     = $this->request->data["$key"];
                        $wifi_setting->save($d_setting);
                        $wifi_setting->id = null;
                    }
                    
                }
                //------- END Add settings for this node ---




                $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }else{
                $message = 'Error';
                $this->set(array(
                    'errors'    => $node->validationErrors,
                    'success'   => false,
                    'message'   => array('message' => __('Could not create item')),
                    '_serialize' => array('errors','success','message')
                ));
            }
        } 
    }

    public function mesh_node_view(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $node = ClassRegistry::init('Node');
		$node->contain('NodeMpSetting','NodeWifiSetting','NodeMeshEntry');

        $id    = $this->request->query['node_id'];
        $q_r   = $node->findById($id);
        
        $hardware = $q_r['Node']['hardware'];
 
        //print_r($q_r);
		if(
			($q_r['Node']['hardware'] == 'mp2_phone')||
			($q_r['Node']['hardware'] == 'mp2_basic')
		){

			foreach($q_r['NodeMpSetting'] as $nms){
				$key 	= $nms['name'];
				$value	= $nms['value'];
				$q_r['Node']["$key"] = $value; 
			}
		}
		
		$nme_list = [];
		
		foreach($q_r['NodeMeshEntry'] as $nme){
		    array_push($nme_list,$nme['mesh_entry_id']);
		}
		$q_r['Node']['static_entries[]'] = $nme_list;

        //Return the Advanced WiFi Settings...
        if(count($q_r['NodeWifiSetting'])>0){

            $radio1_flag    = false;
            $r0_ht_capab    = array();
            $r1_ht_capab    = array();

            foreach($q_r['NodeWifiSetting'] as $s){
                $s_name     = $s['name'];
                $s_value    = $s['value'];
                if($s_name == 'radio1_txpower'){
                    $radio1_flag = true;
                }

                if(!(preg_match('/^radio\d+_ht_capab/',$s_name))){
                    $q_r['Node']["$s_name"] = "$s_value";
                }else{
                    if($s_name == 'radio0_ht_capab'){
                        array_push($r0_ht_capab,$s_value);
                    }
                    if($s_name == 'radio1_ht_capab'){
                        array_push($r1_ht_capab,$s_value);
                    }
                }
            }

            $q_r['Node']['radio0_ht_capab'] = implode("\n",$r0_ht_capab);
            if($radio1_flag){
                $q_r['Node']['radio1_ht_capab'] = implode("\n",$r1_ht_capab);
            }
        }else{
            
            Configure::load('MESHdesk'); 
            $hardware_list 	= Configure::read('hardware'); //Read the defaults
		    foreach($hardware_list as $i){
			    if($i['id'] == $hardware){
				    foreach(array_keys($i) as $key){
                        if(preg_match('/^radio\d+_/',$key)){
                            if(preg_match('/^radio\d+_ht_capab/',$key)){
                                $q_r['Node']["$key"] = implode("\n",$i["$key"]);
                            }else{
                                $q_r['Node']["$key"] = $i["$key"];
                            }
                        }
                    }
                    break;
			    }
		    }
        }

		$q_r['Node']['mesh_id'] = intval($q_r['Node']['mesh_id']);

        $this->set(array(
            'data'      => $q_r['Node'],
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
    }

    public function mesh_node_delete(){

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
        $node       = ClassRegistry::init('Node'); 
        $neighbors  = ClassRegistry::init('NodeNeighbor');

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id']; 
            $node->id = $this->data['id'];
            $node->delete($node->id, true);
            $neighbors->deleteAll(array('NodeNeighbor.neighbor_id' => $node->id), true);
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                    $node->id = $d['id'];
                    $node->delete($node->id, true);
                    $neighbors->deleteAll(array('NodeNeighbor.neighbor_id' => $node->id), true);
            }
        }  
        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    //==== END Mesh nodes ======

   public function mesh_entry_points(){
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        //Get the mesh id
        $mesh_id    = $this->request->query['mesh_id'];

        $exit_id = false;

        //Check if the exit_id was included
        if(isset($this->request->query['exit_id'])){
            $exit_id = $this->request->query['exit_id'];
        }

        $exit       = ClassRegistry::init('MeshExit');
        $entry      = ClassRegistry::init('MeshEntry');

        $entry->contain('MeshExitMeshEntry');
        $ent_q_r    = $entry->find('all',array('conditions' => array('MeshEntry.mesh_id' => $mesh_id))); 
        //print_r($ent_q_r);

        $items = array();
        array_push($items,array('id' => 0, 'name' => "(None)")); //Allow the user not to assign at this stage
        foreach($ent_q_r as $i){

            //If this entry point is already associated; we will NOT add it
            if(count($i['MeshExitMeshEntry'])== 0){
                $id = intval($i['MeshEntry']['id']);
                $n  = $i['MeshEntry']['name'];
                array_push($items,array('id' => $id, 'name' => $n));
            }

            //if $exit_id is set; we add it 
            if($exit_id){
                if(count($i['MeshExitMeshEntry'])> 0){
                    if($i['MeshExitMeshEntry'][0]['mesh_exit_id'] == $exit_id){
                        $id = intval($i['MeshEntry']['id']);
                        $n  = $i['MeshEntry']['name'];
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

    //====== Common Node settings ================
    //-- View common node settings --
    public function node_common_settings_view(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $id         = $this->request->query['mesh_id']; 
		Configure::load('MESHdesk'); 
        $data       = Configure::read('common_node_settings'); //Read the defaults
        $setting    = ClassRegistry::init('NodeSetting');
        $setting->contain();

        //Timezone lists
        $tz_list    = Configure::read('MESHdesk.timezones'); 

        $q_r = $setting->find('first', array('conditions' => array('NodeSetting.mesh_id' => $id)));
        if($q_r){  
            //print_r($q_r);
            $data = $q_r['NodeSetting']; 
            //We need to find if possible the number for the timezone
            foreach($tz_list as $i){
                if($q_r['NodeSetting']['tz_name'] == $i['name']){
                    $data['timezone'] = intval($i['id']);
                    break;
                }
            }
            $data['eth_br_with']= intval($data['eth_br_with']); 
        }

        $this->set(array(
            'data'      => $data,
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
    }

    public function node_common_settings_edit(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if ($this->request->is('post')) {

            //Unfortunately there are many check items which means they will not be in the POST if unchecked
            //so we have to check for them
            $check_items = array('all_power','eth_br_chk','eth_br_for_all', 'gw_use_previous','gw_auto_reboot');
            foreach($check_items as $i){
                if(isset($this->request->data[$i])){
                    $this->request->data[$i] = 1;
                }else{
                    $this->request->data[$i] = 0;
                }
            }

            //Try to find the timezone and its value
            Configure::load('MESHdesk');
            $tz_list    = Configure::read('MESHdesk.timezones'); 
            foreach($tz_list as $j){
                if($j['id'] == $this->request->data['timezone']){
                    $this->request->data['tz_name'] = $j['name'];
                    $this->request->data['tz_value']= $j['value'];
                    break;
                }
            }
            
            $mesh_id = $this->request->data['mesh_id'];
            //See if there is not already a setting entry
            $setting    = ClassRegistry::init('NodeSetting');
			$setting->contain();
            $q_r        = $setting->find('first', array('conditions' => array('NodeSetting.mesh_id' => $mesh_id)));

            if($q_r){
                $this->request->data['id'] = $q_r['NodeSetting']['id']; //Set the ID
				//Check if the value of 
				////if($this->request->data['password'] != $q_r['NodeSetting']['password']){   //!!Create a new has regardless!!
					//Create a new hash
					$new_pwd = $this->_make_linux_password($this->request->data['password']);
					$this->request->data['password_hash'] = $new_pwd;

				////}
            }

            if ($setting->save($this->request->data)) {
                   $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }
        }
    }


    //-- List static entry point options for mesh --
    public function static_entry_options(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        
        $conditions = ['MeshEntry.apply_to_all' => 0];

        if(isset($this->request->query['mesh_id'])){
            $mesh_id = $this->request->query['mesh_id'];
            array_push($conditions,['MeshEntry.mesh_id' => $mesh_id]);
        }

        $entry  = ClassRegistry::init('MeshEntry');
        $entry->contain();
        $q_r    = $entry->find('all',['conditions' => $conditions]);
        $items = array();
        foreach($q_r as $i){
            $id = $i['MeshEntry']['id'];
            $n  = $i['MeshEntry']['name'];
            array_push($items,array('id' => $id, 'name' => $n));
        }

        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    public function static_exit_options(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if(isset($this->request->query['mesh_id'])){
            $mesh_id = $this->request->query['mesh_id'];
        }

        $exit  = ClassRegistry::init('MeshExit');
        $exit->contain();
        $q_r    = $exit->find('all',array('conditions' => array('MeshExit.auto_detect' => 0)));
        $items = array();
        array_push($items,array('id' => 0, 'name' => "(None)")); //Allow the user not to assign at this stage
        foreach($q_r as $i){
            $id = $i['MeshExit']['id'];
            $n  = $i['MeshExit']['name'];
            array_push($items,array('id' => $id, 'name' => $n));
        }

        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));

    }


    //-- List available encryption options --
    public function encryption_options(){

        $items = array();
		Configure::load('MESHdesk');
        $ct = Configure::read('encryption');
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

    //-- List available hardware options --
    public function hardware_options(){

        $items = array();
		Configure::load('MESHdesk');
        $ct = Configure::read('hardware');
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

	public function map_pref_view(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $user_id    = $user['id'];
        $items		= array();

		if(!isset($this->request->query['mesh_id'])){
			$this->set(array(
		        'message'	=> array("message"	=>"Mesh ID (mesh_id) missing"),
		        'success' => false,
		        '_serialize' => array('success','message')
		    ));
			return;
		}

		$mesh_id = $this->request->query['mesh_id'];

    	$this->MeshSpecific = ClassRegistry::init('MeshSpecific');
		Configure::load('MESHdesk');
    	$zoom = Configure::read('mesh_specifics.map.zoom');
    	//Check for personal overrides
    	$q_r = $this->MeshSpecific->find('first',array('conditions' => array('MeshSpecific.mesh_id' => $mesh_id,'MeshSpecific.name' => 'map_zoom')));
        if($q_r){
            $zoom = intval($q_r['MeshSpecific']['value']);
        }

        $type = Configure::read('mesh_specifics.map.type');
        //Check for overrides
        $q_r = $this->MeshSpecific->find('first',array('conditions' => array('MeshSpecific.mesh_id' => $mesh_id,'MeshSpecific.name' => 'map_type')));
        if($q_r){
            $type = $q_r['MeshSpecific']['value'];
        }

        $lat = Configure::read('mesh_specifics.map.lat');
        //Check for overrides
        $q_r = $this->MeshSpecific->find('first',array('conditions' => array('MeshSpecific.mesh_id' => $mesh_id,'MeshSpecific.name' => 'map_lat')));
        if($q_r){
            $lat = $q_r['MeshSpecific']['value']+0;
        }

        $lng = Configure::read('mesh_specifics.map.lng');
        //Check for overrides
        $q_r = $this->MeshSpecific->find('first',array('conditions' => array('MeshSpecific.mesh_id' => $mesh_id,'MeshSpecific.name' => 'map_lng')));
        if($q_r){
            $lng = $q_r['MeshSpecific']['value']+0;
        }

        $items['zoom'] = $zoom;
        $items['type'] = $type;
        $items['lat']  = $lat;
        $items['lng']  = $lng;

        $this->set(array(
            'data'   => $items, //For the form to load we use data instead of the standard items as for grids
            'success' => true,
            '_serialize' => array('success','data')
        ));
    }

	public function map_pref_edit(){
		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $this->MeshSpecific = ClassRegistry::init('MeshSpecific');

		$mesh_id 	= $this->request->data['mesh_id'];

        if(array_key_exists('zoom',$this->request->data)){        
            $q_r = $this->MeshSpecific->find('first',
				array('conditions' => array('MeshSpecific.mesh_id' => $mesh_id,'MeshSpecific.name' => 'map_zoom'))
			);
            if(!empty($q_r)){
                $this->MeshSpecific->id = $q_r['MeshSpecific']['id'];    
                $this->MeshSpecific->saveField('value', $this->request->data['zoom']);
            }else{
                $d['MeshSpecific']['mesh_id']	= $mesh_id;
                $d['MeshSpecific']['name']   	= 'map_zoom';
                $d['MeshSpecific']['value']  	= $this->request->data['zoom'];
                $this->MeshSpecific->create();
                $this->MeshSpecific->save($d);
                $this->MeshSpecific->id = null;
            }
        }

        if(array_key_exists('type',$this->request->data)){        
            $q_r = $this->MeshSpecific->find('first',
				array('conditions' => array('MeshSpecific.mesh_id' => $mesh_id,'MeshSpecific.name' => 'map_type'))
			);
            if(!empty($q_r)){
                $this->MeshSpecific->id = $q_r['MeshSpecific']['id'];    
                $this->MeshSpecific->saveField('value', $this->request->data['type']);
            }else{
                $d['MeshSpecific']['mesh_id']	= $mesh_id;
                $d['MeshSpecific']['name']   	= 'map_type';
                $d['MeshSpecific']['value']  	= $this->request->data['type'];
                $this->MeshSpecific->create();
                $this->MeshSpecific->save($d);
                $this->MeshSpecific->id = null;
            }
        }

        if(array_key_exists('lat',$this->request->data)){        
            $q_r = $this->MeshSpecific->find('first',
				array('conditions' => array('MeshSpecific.mesh_id' => $mesh_id,'MeshSpecific.name' => 'map_lat'))
			);
            if(!empty($q_r)){
                $this->MeshSpecific->id = $q_r['MeshSpecific']['id'];    
                $this->MeshSpecific->saveField('value', $this->request->data['lat']);
            }else{
                $d['MeshSpecific']['mesh_id']	= $mesh_id;
                $d['MeshSpecific']['name']   	= 'map_lat';
                $d['MeshSpecific']['value']  	= $this->request->data['lat'];

                $this->MeshSpecific->create();
                $this->MeshSpecific->save($d);
                $this->MeshSpecific->id = null;
            }
        }

        if(array_key_exists('lng',$this->request->data)){        
            $q_r = $this->MeshSpecific->find('first',
				array('conditions' => array('MeshSpecific.mesh_id' => $mesh_id,'MeshSpecific.name' => 'map_lng'))
			);
            if(!empty($q_r)){
                $this->MeshSpecific->id = $q_r['MeshSpecific']['id'];    
                $this->MeshSpecific->saveField('value', $this->request->data['lng']);
            }else{
                $d['MeshSpecific']['mesh_id']= $mesh_id;
                $d['MeshSpecific']['name']   = 'map_lng';
                $d['MeshSpecific']['value']  = $this->request->data['lng'];
                $this->MeshSpecific->create();
                $this->MeshSpecific->save($d);
                $this->MeshSpecific->id = null;
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
	}

	public function map_node_save(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
		if(isset($this->request->query['id'])){
			$this->Node = ClassRegistry::init('Node');
			$this->Node->contain();
            $this->Node->id = $this->request->query['id'];
            $this->Node->saveField('lat', $this->request->query['lat']);
            $this->Node->saveField('lon', $this->request->query['lon']);
        }

		$this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
	}

	public function map_node_delete(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

		if(isset($this->request->query['id'])){
			$this->Node = ClassRegistry::init('Node');
			$this->Node->contain();
            $this->Node->id = $this->request->query['id'];
            $this->Node->saveField('lat', null);
            $this->Node->saveField('lon', null);
        }

		$this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
	}


	public function nodes_avail_for_map(){
		//List all the nodes that has not yet been assigned a lat (and lon) value for a mesh

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $user_id    = $user['id'];
        $items		= array();

		if(!isset($this->request->query['mesh_id'])){
			$this->set(array(
		        'message'	=> array("message"	=>"Mesh ID (mesh_id) missing"),
		        'success' => false,
		        '_serialize' => array('success','message')
		    ));
			return;
		}
		$mesh_id = $this->request->query['mesh_id'];

		$this->Node = ClassRegistry::init('Node');
		$this->Node->contain();
		$q_r		= $this->Node->find('all',array('conditions' => array('Node.mesh_id' => $mesh_id,'Node.lat' => null)));
		$items 		= array();
		foreach($q_r as $i){
			array_push($items,array(
				'id' 			=> $i['Node']['id'],
				'name'			=> $i['Node']['name'],
				'description'	=> $i['Node']['description']
			));
		}

		$this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
	}

//_______________________________________________________

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
                    array('xtype' => 'button', 'iconCls' => 'b-add',     'glyph' => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'iconCls' => 'b-delete',  'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'iconCls' => 'b-edit',    'glyph' => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')),
                    array('xtype' => 'button', 'iconCls' => 'b-view',    'glyph' => Configure::read('icnView'),'scale' => 'large', 'itemId' => 'view',     'tooltip'=> __('View'))
                )),
                array('xtype' => 'buttongroup','title' => __('Document'), 'width' => 100, 'items' => array(
                    array('xtype' => 'button', 'iconCls' => 'b-note',     'glyph' => Configure::read('icnNote'),'scale' => 'large', 'itemId' => 'note',    'tooltip'=> __('Add notes')),
                  //  array('xtype' => 'button', 'iconCls' => 'b-csv',     'scale' => 'large', 'itemId' => 'csv',      'tooltip'=> __('Export CSV')),
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
            	)
			);


            //Add
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base."add")){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-add',
                    'glyph'     => Configure::read('icnAdd'),     
                    'scale'     => 'large', 
                    'itemId'    => 'add',     
                    'tooltip'   => __('Add')));
            }
            //Delete
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'delete')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-delete',
                    'glyph'     => Configure::read('icnDelete'),  
                    'scale'     => 'large', 
                    'itemId'    => 'delete', 
                    'disabled'  => true, 
                    'tooltip'   => __('Delete')));
            }

			//Edit
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'mesh_entry_edit')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnEdit'),  
                    'scale'     => 'large', 
                    'itemId'    => 'edit',
                    'disabled'  => true,  
                    'tooltip'   => __('Edit')));
            }

			//View
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'mesh_entry_view')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnView'),  
                    'scale'     => 'large', 
                    'itemId'    => 'view',
                    'disabled'  => true, 
                    'tooltip'   => __('View')));
            }

            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'note_index')){ 
                array_push($document_group,array(
                        'xtype'     => 'button', 
                        'iconCls'   => 'b-note',
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

    public function menu_for_nodes_grid(){

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
                    array('xtype' => 'button', 'iconCls' => 'b-add',     'glyph'     => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'iconCls' => 'b-delete',  'glyph'     => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'iconCls' => 'b-edit',    'glyph'     => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')),
					array('xtype' => 'button', 'iconCls' => 'b-map',     'glyph'     => Configure::read('icnMap'),'scale' => 'large', 'itemId' => 'map',      'tooltip'=> __('Map'))
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
                    array('xtype' => 'button', 'iconCls' => 'b-add',     'glyph'     => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'iconCls' => 'b-delete',  'glyph'     => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'iconCls' => 'b-edit',    'glyph'     => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')),
					array('xtype' => 'button', 'iconCls' => 'b-map',     'glyph'     => Configure::read('icnMap'),'scale' => 'large', 'itemId' => 'map',      'tooltip'=> __('Map'))
                ))
    
            );
        }


        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

	 public function menu_for_node_details_grid(){

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
                    array('xtype' => 'button',  'glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload', 'tooltip'=> __('Reload')),
					array('xtype' => 'button', 	'glyph' => Configure::read('icnMap'),'scale' => 'large', 'itemId' => 'map', 'tooltip'=> __('Map')),
                  
                    array('xtype' => 'button',  'glyph' => Configure::read('icnSpanner'),'scale' => 'large', 'itemId' => 'execute','tooltip'=> __('Execute')),
					array('xtype' => 'button',  'glyph' => Configure::read('icnWatch'),'scale' => 'large', 'itemId' => 'history','tooltip'=> __('View execute history')),
					array('xtype' => 'button',  'glyph' => Configure::read('icnPower'),'scale' => 'large', 'itemId' => 'restart','tooltip'=> __('Restart')),
                ))
            );
        }

		 //Access provider
        if($user['group_name'] == Configure::read('group.ap')){  //FIXME fine tune the rights later

            $menu = array(
                array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                    array('xtype' => 'button',  'glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload', 'tooltip'=> __('Reload')),
					array('xtype' => 'button', 	'glyph' => Configure::read('icnMap'),'scale' => 'large', 'itemId' => 'map', 'tooltip'=> __('Map')),
                  
                    array('xtype' => 'button',  'glyph' => Configure::read('icnSpanner'),'scale' => 'large', 'itemId' => 'execute','tooltip'=> __('Execute')),
					array('xtype' => 'button',  'glyph' => Configure::read('icnWatch'),'scale' => 'large', 'itemId' => 'history','tooltip'=> __('View execute history')),
					array('xtype' => 'button',  'glyph' => Configure::read('icnPower'),'scale' => 'large', 'itemId' => 'restart','tooltip'=> __('Restart')),
                ))
            );
        }


        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
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
                            'MeshNote'    => array('Note.note','Note.id','Note.available_to_siblings','Note.user_id'),
                            'User',
                            'Node'
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'Mesh.name';
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
                    array_push($tree_array,array('Mesh.user_id' => $i_id));
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

                    //Here we do a special thing to see if the owner of the mesh perhaps allowed the person beneath him to edit and view the mesh
                    if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $user_id), $this->base.'mesh_entry_edit')){
                        $edit = true;
                    }

                    if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $user_id), $this->base.'mesh_entry_view')){
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

	private function _get_dead_after($mesh_id){
		Configure::load('MESHdesk');
		$data 		= Configure::read('common_node_settings'); //Read the defaults
		$dead_after	= $data['heartbeat_dead_after'];
		$n_s = $this->Mesh->NodeSetting->find('first',array(
            'conditions'    => array(
                'NodeSetting.mesh_id' => $mesh_id
            )
        )); 
        if($n_s){
            $dead_after = $n_s['NodeSetting']['heartbeat_dead_after'];
        }
		return $dead_after;
	}

	private function _add_or_edit_mp_settings($node_id){

/*

config secn 'asterisk'
    option codec1 'gsm'
    option codec2 'ulaw'
    option codec3 'alaw'
    option host 'sip.myhost.com'
    option fromdomain 'sip.myhost.com'
    option dialout '#'
    option externip '0.0.0.0'
    option reghost 'sip.myhost.com'
    option softph 'OFF'
    option username 'myuser'
    option fromusername 'myuser'
    option enable '0'
    option register '0'
    option enablenat '0'
    option enable_ast '0'
*/

		$settings_to_add = array();

		//Some defaults
		$settings_to_add['enablenat']	= '0';
		$settings_to_add['externip']	= '0.0.0.0';
		$settings_to_add['codec1']		= 'gsm';
		$settings_to_add['codec2']		= 'ulaw';
		$settings_to_add['codec2']		= 'alaw';
		$settings_to_add['softph']		= 'OFF';


		$sip_check = false;
		//We have tou build in quite a lot of 'logic' on this one to assume some things based on what the user chose
		if (
			(array_key_exists('enable', $this->request->data))&&
			(!array_key_exists('enable_ast', $this->request->data))
		){
			//We assume the person did not touch the advanced settings thus we will make some descisions for them
			$sip_check 			= true;
			$sip_register_check	= '1';
			$settings_to_add['reghost']		= $this->request->data['host'];
		}

		if (
			(array_key_exists('enable', $this->request->data))&&
			(array_key_exists('enable_ast', $this->request->data))
		){
			$sip_check 			= true;
			//We assume the person did choose advanced settings -check for the registrar
			if(array_key_exists('register', $this->request->data)){
				$sip_register_check = "1";
				$sip_registrar		= $this->request->data['reghost'];
			}else{
				$sip_register_check = "0";
			}

			if(array_key_exists('enablenat', $this->request->data)){
				$settings_to_add['externip']	= $this->request->data['externip'];
				$settings_to_add['enablenat']	= '1';
			}

			$settings_to_add['codec1']		= $this->request->data['codec1'];
			$settings_to_add['codec2']		= $this->request->data['codec2'];
			$settings_to_add['codec3']		= $this->request->data['codec3'];
			$settings_to_add['softph']		= $this->request->data['softph'];
		}

		//Now we can add /edit the lot
 		$node_mp_setting	= ClassRegistry::init('NodeMpSetting');
		$node_mp_setting->contain();

		//Clear previous ones first:
        $node_mp_setting->deleteAll(array('NodeMpSetting.node_id' => $node_id), false);


		if(!$sip_check){
			//Silently ignore it
			return;
		}

		$settings_to_add['enable'] 		= 1;
		$settings_to_add['register'] 	= $sip_register_check;
		$settings_to_add['host']		= $this->request->data['host'];
		$settings_to_add['fromdomain']	= $this->request->data['host'];
		$settings_to_add['enable_ast']	= '1';
		$settings_to_add['fromusername']= $this->request->data['username'];
		$settings_to_add['username']    = $this->request->data['username'];
		$settings_to_add['secret']      = $this->request->data['secret'];
		$settings_to_add['dialout']     = $this->request->data['dialout'];
	
		foreach(array_keys($settings_to_add) as $k){
            $data['node_id']  	= $node_id;
            $data['name'] 		= $k;
			$data['value'] 		= $settings_to_add["$k"];
			$node_mp_setting->create();
            $node_mp_setting->save($data);
			$node_mp_setting->id= null;
		}
	}

	private function _get_radio_count_for($hardware){
		$radio_count 	= 1;
		Configure::load('MESHdesk'); 
        $hardware_list 	= Configure::read('hardware'); //Read the defaults

		foreach($hardware_list as $i){
			if($i['id'] == $hardware){
				$radio_count = $i['radios'];
			}
		}
		return $radio_count;
	}

	private function _add_or_edit_dual_radio_settings($node_id){

		$dual_radio = array();
		//Default is off for everything unless enabled
		$set_to_zero = array(
			'radio0_enable','radio0_mesh','radio0_entry',
			'radio1_enable','radio1_mesh','radio1_entry',
		);

		foreach($set_to_zero as $i){
			$dual_radio[$i] = 0;
		}

		$dual_radio['id']	= $node_id;
		
		//Radio0
		if (array_key_exists('radio0_enable', $this->request->data)) {

			$dual_radio['radio0_enable'] = 1; //Enable radio

			if (array_key_exists('radio0_mesh', $this->request->data)) {
				$dual_radio['radio0_mesh'] = 1;
			}

			if (array_key_exists('radio0_entry', $this->request->data)) {
				$dual_radio['radio0_entry'] = 1;
			}

			if (array_key_exists('radio0_band', $this->request->data)) {
				$dual_radio['radio0_band'] 	= $this->request->data['radio0_band'];
			}

			if (array_key_exists('radio0_two_chan', $this->request->data)) {
				$dual_radio['radio0_two_chan'] 	= $this->request->data['radio0_two_chan'];
			}

			if (array_key_exists('radio0_five_chan', $this->request->data)) {
				$dual_radio['radio0_five_chan'] 	= $this->request->data['radio0_five_chan'];
			}
		}

		//Radio1
		if (array_key_exists('radio1_enable', $this->request->data)) {

			$dual_radio['radio1_enable'] = 1; //Enable radio

			if (array_key_exists('radio1_mesh', $this->request->data)) {
				$dual_radio['radio1_mesh'] = 1;
			}

			if (array_key_exists('radio1_entry', $this->request->data)) {
				$dual_radio['radio1_entry'] = 1;
			}

			if (array_key_exists('radio1_band', $this->request->data)) {
				$dual_radio['radio1_band'] 	= $this->request->data['radio1_band'];
			}

			if (array_key_exists('radio1_two_chan', $this->request->data)) {
				$dual_radio['radio1_two_chan'] 	= $this->request->data['radio1_two_chan'];
			}

			if (array_key_exists('radio1_five_chan', $this->request->data)) {
				$dual_radio['radio1_five_chan'] 	= $this->request->data['radio1_five_chan'];
			}
		}

		$n      = ClassRegistry::init('Node');
		$n->create();
		$n->save($dual_radio);
	}
	
	private function _add_dynamic($dc_data){
    
        //--Formulate a name
        $dc_data['name'] = 'MESHdesk_'.$dc_data['nasidentifier'];
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
