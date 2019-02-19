<?php
App::uses('AppController', 'Controller');

class DynamicClientsController extends AppController {

    public $name        = 'DynamicClients';
    public $components  = array('Aa','GridFilter','TimeCalculations');
    public $uses        = array('DynamicClient','UnknownDynamicClient','User');
    protected $base     = "Access Providers/Controllers/DynamicClients/";

//------------------------------------------------------------------------


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
        
        App::uses('GeoIpLocation', 'GeoIp.Model');
        $GeoIpLocation = new GeoIpLocation();

        foreach($q_r as $i){
        
            $location = array();
            if($i['DynamicClient']['last_contact_ip'] != ''){
                $location = $GeoIpLocation->find($i['DynamicClient']['last_contact_ip']);
            }
                   
            //Some defaults:
            $country_code = '';
            $country_name = '';
            $city         = '';
            $postal_code  = '';
            
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
        
        
            $realms     = array();
            //Realms
            foreach($i['DynamicClientRealm'] as $dcr){ 
                if(!$this->_test_for_private_parent($dcr['Realm'],$user)){
		            if(!array_key_exists('id',$dcr['Realm'])){
			            $r_id = "undefined";
			            $r_n = "undefined";
			            $r_s =  false;
		        }else{
			            $r_id= $dcr['Realm']['id'];
			            $r_n = $dcr['Realm']['name'];
			            $r_s = $dcr['Realm']['available_to_siblings'];
		       }
               array_push($realms, 
                    array(
                        'id'                    => $r_id,
                        'name'                  => $r_n,
                        'available_to_siblings' => $r_s
                    ));
                }
            } 

            $owner_id       = $i['DynamicClient']['user_id'];
            $owner_tree     = $this->_find_parents($owner_id);
            $action_flags   = $this->_get_action_flags($owner_id,$user);
            
            
            $i['DynamicClient']['country_code'] = $country_code;
            $i['DynamicClient']['country_name'] = $country_name;
            $i['DynamicClient']['city']         = $city;
            $i['DynamicClient']['postal_code']  = $postal_code;   
            if($i['DynamicClient']['last_contact'] != null){
                $i['DynamicClient']['last_contact_human']    = $this->TimeCalculations->time_elapsed_string($i['DynamicClient']['last_contact']);
            }
            
            //Create notes flag
            $notes_flag  = false;
            foreach($i['DynamicClientNote'] as $dcn){
                if(!$this->_test_for_private_parent($dcn['Note'],$user)){
                    $notes_flag = true;
                    break;
                }
            }
            
             
            $i['DynamicClient']['notes']        = $notes_flag;
             
            $i['DynamicClient']['owner']        = $owner_tree;
            $i['DynamicClient']['realms']       = $realms;
            $i['DynamicClient']['update']       = $action_flags['update'];
            $i['DynamicClient']['delete']       = $action_flags['delete'];
         
            array_push($items,$i['DynamicClient']);
        }
       
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            'totalCount' => $total,
            '_serialize' => array('items','success','totalCount')
        ));
    }
    
    public function clients_avail_for_map() {
    
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
        $items  = array();
        foreach($q_r as $i){
            $id     = $i['DynamicClient']['id'];
            $name   = $i['DynamicClient']['name'];  
            $item = array('id' => $id,'name' => $name);
            array_push($items,$item);
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
         
        $check_items = array('active', 'available_to_siblings', 'on_public_maps', 'session_auto_close','data_limit_active');
        foreach($check_items as $ci){
            if(isset($this->request->data[$ci])){
                $this->request->data[$ci] = 1;
            }else{
                $this->request->data[$ci] = 0;
            }
        }
        
        $unknown_flag = false;
        //Check if it was an attach!
        if(array_key_exists('unknown_dynamic_client_id',$this->request->data)){
            //Now we need to do a lookup
            $u = $this->UnknownDynamicClient->findById($this->request->data['unknown_dynamic_client_id']);
            if($u){
                $unknown_flag   = true;
                $nas_id         = $u['UnknownDynamicClient']['nasidentifier'];
                $called         = $u['UnknownDynamicClient']['calledstationid'];
                
                $this->request->data['nasidentifier']   = $nas_id;
                $this->request->data['calledstationid'] = $called;
            }
        }
        

        $this->{$this->modelClass}->create();
        if ($this->{$this->modelClass}->save($this->request->data)) {
        
            //Check if we need to add na_realms table
            if(isset($this->request->data['avail_for_all'])){
            //Available to all does not add any dynamic_client_realm entries
            }else{
                foreach(array_keys($this->request->data) as $key){
                    if(preg_match('/^\d+/',$key)){
                        //----------------
                        $this->_add_dynamic_client_realm($this->{$this->modelClass}->id,$key);
                        //-------------
                    }
                }
            }   
            $this->request->data['id'] = $this->{$this->modelClass}->id;
            
            //If it was an unknown attach - remove the unknown
            if($unknown_flag){
                $this->UnknownDynamicClient->id = $this->request->data['unknown_dynamic_client_id'];
                $this->UnknownDynamicClient->delete($this->request->data['unknown_dynamic_client_id'], true);
            }
            
            
            $this->set(array(
                'success' => true,
                'data'      => $this->request->data,
                '_serialize' => array('success','data')
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
        $fail_flag  = false;

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id'];
            $this->{$this->modelClass}->id = $this->data['id'];
            $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                $this->{$this->modelClass}->id = $d['id'];
                $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
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

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if ($this->request->is('post')) {

            //Unfortunately there are many check items which means they will not be in the POST if unchecked
            //so we have to check for them
            $check_items = array(
				'active', 'available_to_siblings', 'on_public_maps', 'session_auto_close','data_limit_active'
			);
			
            foreach($check_items as $i){
                if(isset($this->request->data[$i])){
                    $this->request->data[$i] = 1;
                }else{
                    $this->request->data[$i] = 0;
                }
            }

            if ($this->{$this->modelClass}->save($this->request->data)) {
                   $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }
        }
    }
    
    
     public function view(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $items = array();
        
        if(isset($this->request->query['dynamic_client_id'])){

            $this->{$this->modelClass}->contain();
            $q_r = $this->{$this->modelClass}->findById($this->request->query['dynamic_client_id']);
           // print_r($q_r);
            if($q_r){
                $items = $q_r['DynamicClient'];
            }
        }

        $this->set(array(
            'data'   => $items, //For the form to load we use data instead of the standard items as for grids
            'success' => true,
            '_serialize' => array('success','data')
        ));

    }
    
    
    public function view_photo(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $items = array();

        if(isset($this->request->query['id'])){
            $this->DynamicClient->contain();
            $q_r = $this->{$this->modelClass}->findById($this->request->query['id']);
            if($q_r){
                $items['photo_file_name'] = $q_r['DynamicClient']['photo_file_name'];
            }
        }

        $this->set(array(
            'data'   => $items, //For the form to load we use data instead of the standard items as for grids
            'success' => true,
            '_serialize' => array('success','data')
        ));
    }

    public function upload_photo($id = null){

        //This is a deviation from the standard JSON serialize view since extjs requires a html type reply when files
        //are posted to the server.
        $this->layout = 'ext_file_upload';

        $path_parts     = pathinfo($_FILES['photo']['name']);
        $unique         = time();
        $dest           = IMAGES."nas/".$unique.'.'.$path_parts['extension'];
        $dest_www       = "/cake2/rd_cake/webroot/img/nas/".$unique.'.'.$path_parts['extension'];

        //Now add....
        $data['photo_file_name']  = $unique.'.'.$path_parts['extension'];
       
        $this->{$this->modelClass}->id = $this->request->data['id'];
       // $this->{$this->modelClass}->saveField('photo_file_name', $unique.'.'.$path_parts['extension']);
        if($this->{$this->modelClass}->saveField('photo_file_name', $unique.'.'.$path_parts['extension'])){
            move_uploaded_file ($_FILES['photo']['tmp_name'] , $dest);
            $json_return['id']                  = $this->{$this->modelClass}->id;
            $json_return['success']             = true;
            $json_return['photo_file_name']     = $unique.'.'.$path_parts['extension'];
        }else{
            $json_return['errors']      = $this->{$this->modelClass}->validationErrors;
            $json_return['message']     = array("message"   => __('Problem uploading photo'));
            $json_return['success']     = false;
        }
        $this->set('json_return',$json_return);
    }

    
    
    //____ Notes ______
     public function note_index(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();
        if(isset($this->request->query['for_id'])){
            $na_id  = $this->request->query['for_id'];
            $q_r    = $this->DynamicClient->DynamicClientNote->find('all', 
                array(
                    'contain'       => array('Note'),
                    'conditions'    => array('DynamicClientNote.dynamic_client_id' => $na_id)
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
        $this->DynamicClient->DynamicClientNote->Note->create(); 
        //print_r($this->request->data);
        if ($this->DynamicClient->DynamicClientNote->Note->save($this->request->data)) {
            $d                      = array();
            $d['DynamicClientNote']['dynamic_client_id']   = $this->request->data['for_id'];
            $d['DynamicClientNote']['note_id'] = $this->DynamicClient->DynamicClientNote->Note->id;
            $this->DynamicClient->DynamicClientNote->create();
            if ($this->DynamicClient->DynamicClientNote->save($d)) {
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
            $item       = $this->DynamicClient->DynamicClientNote->Note->findById($this->data['id']);
            $owner_id   = $item['Note']['user_id'];
            if($owner_id != $user_id){
                if($this->_is_sibling_of($user_id,$owner_id)== true){
                    $this->DynamicClient->DynamicClientNote->Note->id = $this->data['id'];
                    $this->DynamicClient->DynamicClientNote->Note->delete($this->data['id'],true);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->DynamicClient->DynamicClientNote->Note->id = $this->data['id'];
                $this->DynamicClient->DynamicClientNote->Note->delete($this->data['id'],true);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                $item       = $this->DynamicClient->DynamicClientNote->Note->findById($d['id']);
                $owner_id   = $item['Note']['user_id'];
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($user_id,$owner_id) == true){
                        $this->DynamicClient->DynamicClientNote->Note->id = $d['id'];
                        $this->DynamicClient->DynamicClientNote->Note->delete($d['id'],true);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->DynamicClient->DynamicClientNote->Note->id = $d['id'];
                    $this->DynamicClient->DynamicClientNote->Note->delete($d['id'],true);
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

     public function view_map_pref(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $items = array();

        $this->UserSetting = ClassRegistry::init('UserSetting');

        $zoom = Configure::read('user_settings.dynamic_client_map.zoom');
        //Check for personal overrides
        $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'dynamic_client_map_zoom')));
        if($q_r){
            $zoom = intval($q_r['UserSetting']['value']);
        }

        $type = Configure::read('user_settings.dynamic_client_map.type');
        //Check for personal overrides

        $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'dynamic_client_map_type')));
        if($q_r){
            $type = $q_r['UserSetting']['value'];
        }

        $lat = Configure::read('user_settings.dynamic_client_map.lat');
        //Check for personal overrides

        $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'dynamic_client_map_lat')));
        if($q_r){
            $lat = $q_r['UserSetting']['value']+0;
        }

        $lng = Configure::read('user_settings.dynamic_client_map.lng');
        //Check for personal overrides

        $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'dynamic_client_map_lng')));
        if($q_r){
            $lng = $q_r['UserSetting']['value']+0;
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

     public function edit_map_pref(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $this->UserSetting = ClassRegistry::init('UserSetting');

        if(array_key_exists('zoom',$this->request->data)){        
            $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'dynamic_client_map_zoom')));
            if(!empty($q_r)){
                $this->UserSetting->id = $q_r['UserSetting']['id'];    
                $this->UserSetting->saveField('value', $this->request->data['zoom']);
            }else{
                $d['UserSetting']['user_id']= $user_id;
                $d['UserSetting']['name']   = 'dynamic_client_map_zoom';
                $d['UserSetting']['value']  = $this->request->data['zoom'];
                $this->UserSetting->create();
                $this->UserSetting->save($d);
                $this->UserSetting->id = null;
            }
        }

        if(array_key_exists('type',$this->request->data)){        
            $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'dynamic_client_map_type')));
            if(!empty($q_r)){
                $this->UserSetting->id = $q_r['UserSetting']['id'];    
                $this->UserSetting->saveField('value', $this->request->data['type']);
            }else{
                $d['UserSetting']['user_id']= $user_id;
                $d['UserSetting']['name']   = 'dynamic_client_map_type';
                $d['UserSetting']['value']  = $this->request->data['type'];
                $this->UserSetting->create();
                $this->UserSetting->save($d);
                $this->UserSetting->id = null;
            }
        }

        if(array_key_exists('lat',$this->request->data)){        
            $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'dynamic_client_map_lat')));
            if(!empty($q_r)){
                $this->UserSetting->id = $q_r['UserSetting']['id'];    
                $this->UserSetting->saveField('value', $this->request->data['lat']);
            }else{
                $d['UserSetting']['user_id']= $user_id;
                $d['UserSetting']['name']   = 'dynamic_client_map_lat';
                $d['UserSetting']['value']  = $this->request->data['lat'];

                $this->UserSetting->create();
                $this->UserSetting->save($d);
                $this->UserSetting->id = null;
            }
        }

        if(array_key_exists('lng',$this->request->data)){        
            $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'dynamic_client_map_lng')));
            if(!empty($q_r)){
                $this->UserSetting->id = $q_r['UserSetting']['id'];    
                $this->UserSetting->saveField('value', $this->request->data['lng']);
            }else{
                $d['UserSetting']['user_id']= $user_id;
                $d['UserSetting']['name']   = 'dynamic_client_map_lng';
                $d['UserSetting']['value']  = $this->request->data['lng'];
                $this->UserSetting->create();
                $this->UserSetting->save($d);
                $this->UserSetting->id = null;
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    public function delete_map(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        if(isset($this->request->query['id'])){
            $this->DynamicClient->id = $this->request->query['id'];
            $this->DynamicClient->saveField('lat', null);
            $this->DynamicClient->saveField('lon', null);
        }

        $this->set(array(
                'success' => true,
                '_serialize' => array('success')
        ));
    }

   public function edit_map(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        if(isset($this->request->query['id'])){
            $this->DynamicClient->id = $this->request->query['id'];
            $this->DynamicClient->saveField('lat', $this->request->query['lat']);
            $this->DynamicClient->saveField('lon', $this->request->query['lon']);
        }

        $this->set(array(
                'success' => true,
                '_serialize' => array('success')
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
       
        $shared_secret = "(Please specify one)"; 
        if(Configure::read('DynamicClients.shared_secret')){
            $shared_secret = Configure::read('DynamicClients.shared_secret');
        }

        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin

            $menu = array(
                array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                   array( 'xtype' =>  'splitbutton',  'glyph'     => Configure::read('icnReload'),'scale'   => 'large', 'itemId'    => 'reload',   'tooltip'    => __('Reload'),
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
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnAdd'), 'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnEdit'), 'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit'))
                )),
                array('xtype' => 'buttongroup','title' => __('Other'), 'items' => array(
                    array('xtype' => 'button','glyph'=> Configure::read('icnNote'),'scale' => 'large', 'itemId' => 'note', 'tooltip'=> __('Add notes')),
                    array('xtype' => 'button','glyph'=> Configure::read('icnCsv'),'scale' => 'large', 'itemId' => 'csv', 'tooltip'=> __('Export CSV')),
                    array('xtype' => 'button','glyph'=> Configure::read('icnGraph'),'scale' => 'large', 'itemId' => 'graph','tooltip'=> __('Graphs')),
                    array('xtype' => 'button','glyph'=> Configure::read('icnMap'),'scale' => 'large', 'itemId' => 'map',   'tooltip'=> __('Map'))
                )),
                array('xtype' => 'buttongroup', 'width'=> 300,'title' => '<span class="txtBlue"><i class="fa  fa-lightbulb-o"></i> Site Wide Shared Secret</span>', 'items' => array(
                    array('xtype' => 'tbtext', 'html' => "<h1>$shared_secret</h1>")
                )),
            );
        }

        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $action_group   = array();

            array_push($action_group,array( 'xtype' =>  'splitbutton',  'glyph'     => Configure::read('icnReload'),'scale'   => 'large', 'itemId'    => 'reload',   'tooltip'    => __('Reload'),
                            'menu'  => array( 
                                'items' => array( 
                                    '<b class="menu-title">'.__('Reload every').':</b>',
                                    array( 'text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                                    array( 'text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                                    array( 'text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                                    array( 'text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )
                                   
                                )
                            )
                    ));

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
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'edit')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-edit',
                    'glyph'     => Configure::read('icnEdit'),     
                    'scale'     => 'large', 
                    'itemId'    => 'edit',
                    'disabled'  => true,     
                    'tooltip'   => __('Edit')));
            }

            $menu = array(
                array('xtype' => 'buttongroup','title' => __('Action'),        'items' => $action_group),
                array('xtype' => 'buttongroup','title' => __('Other'), 'items' => array(
                    array('xtype' => 'button','glyph'=> Configure::read('icnNote'),'scale' => 'large', 'itemId' => 'note', 'tooltip'=> __('Add notes')),
                    array('xtype' => 'button','glyph'=> Configure::read('icnCsv'),'scale' => 'large', 'itemId' => 'csv', 'tooltip'=> __('Export CSV')),
                    array('xtype' => 'button','glyph'=> Configure::read('icnGraph'),'scale' => 'large', 'itemId' => 'graph','tooltip'=> __('Graphs')),
                    array('xtype' => 'button','glyph'=> Configure::read('icnMap'),'scale' => 'large', 'itemId' => 'map',   'tooltip'=> __('Map')),
                )),
                array(
                    'xtype'     => 'buttongroup', 
                    'width'     => 300,
                    'title'     => '<span class="txtBlue"><i class="fa  fa-lightbulb-o"></i> Site Wide Shared Secret</span>', 
                    'items'     => array(
                        array('xtype' => 'tbtext', 'html' => "<h1>$shared_secret</h1>")
                )),
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
                            'User',
                            'DynamicClientRealm'   => array('Realm.name','Realm.id','Realm.available_to_siblings','Realm.user_id'),
                            'DynamicClientNote'    => array('Note.note','Note.id','Note.available_to_siblings','Note.user_id'),
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = $this->modelClass.'.last_contact';
        $dir    = 'DESC';

        if(isset($this->request->query['sort'])){
            if($this->request->query['sort'] == 'username'){
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
        //If the user is an AP; we need to add an extra clause to only show the LicensedDevices which he is allowed to see.
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
                    array_push($tree_array,array($this->modelClass.'.user_id' => $i_id));
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
            return array('update' => true, 'delete' => true);
        }

        if($user['group_name'] == Configure::read('group.ap')){  //AP
            $user_id = $user['id'];

            //test for self
            if($owner_id == $user_id){
                return array('update' => true, 'delete' => true );
            }
            //Test for Parents
            foreach($this->parents as $i){
                if($i['User']['id'] == $owner_id){
                    return array('update' => false, 'delete' => false );
                }
            }

            //Test for Children
            foreach($this->children as $i){
                if($i['id'] == $owner_id){
                    return array('update' => true, 'delete' => true);
                }
            }  
        }
    }
    
    private function _add_dynamic_client_realm($dynamic_client_id,$realm_id){
        $d                                              = array();
        $d['DynamicClientRealm']['id']                  = '';
        $d['DynamicClientRealm']['dynamic_client_id']   = $dynamic_client_id;
        $d['DynamicClientRealm']['realm_id']            = $realm_id;

        $this->DynamicClient->DynamicClientRealm->create();
        $this->DynamicClient->DynamicClientRealm->save($d);
        $this->DynamicClient->DynamicClientRealm->id      = false;
    }
}
