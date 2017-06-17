<?php
App::uses('AppController', 'Controller');

class NasController extends AppController {


    public $name       = 'Nas';
    public $components = array('Aa','RequestHandler','GridFilter');
    public $uses       = array('Na','User');
    protected $base    = "Access Providers/Controllers/Nas/";

    protected $tmpDir  = 'csvexport';

//------------------------------------------------------------------------

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
        $q_r        = $this->Na->find('all',$c);

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

        //Results
        foreach($q_r as $i){

            $columns    = array();
            $csv_line   = array();
            if(isset($this->request->query['columns'])){
                $columns = json_decode($this->request->query['columns']);
                foreach($columns as $c){
                    //Realms
                    $column_name = $c->name;
                    if($column_name == 'realms'){
                        $realms = '';
                        foreach($i['NaRealm'] as $nr){
                            if(!$this->_test_for_private_parent($nr['Realm'],$user)){
                                $realms = $realms.'['.$nr['Realm']['name'].']';
                            }
                        }
                        array_push($csv_line,$realms);
                    }elseif($column_name == 'tags'){
                        $tags   = '';
                        foreach($i['NaTag'] as $nr){
                            if(!$this->_test_for_private_parent($nr['Tag'],$user)){
                                $tags = $tags.'['.$nr['Tag']['name'].']';    
                            }
                        }
                        array_push($csv_line,$tags);
                    }elseif($column_name == 'notes'){
                        $notes   = '';
                        foreach($i['NaNote'] as $n){
                            if(!$this->_test_for_private_parent($n['Note'],$user)){
                                $notes = $notes.'['.$n['Note']['note'].']';    
                            }
                        }
                        array_push($csv_line,$notes);
                    }elseif($column_name =='owner'){
                        $owner_id       = $i['Na']['user_id'];
                        $owner_tree     = $this->_find_parents($owner_id);
                        array_push($csv_line,$owner_tree); 
                    }elseif($column_name == 'status'){
                        //Status
                        if(empty($i['NaState'])){
                            $status = 'unknown';
                            $status_time = null;
                        }else{
                            if($i['NaState'][0]['state'] == 1){
                                $status         = 'up';
                                $status_time    = time()- strtotime($i['NaState'][0]['modified']);
                            }else{
                                $status         = 'down';
                                $status_time    = time() -strtotime($i['NaState'][0]['modified']);
                            }
                        }
                        array_push($csv_line,$status." ".$status_time);
                    }else{
                        array_push($csv_line,$i['Na']["$column_name"]);  
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

    //____ BASIC CRUD  Manager ________
    public function index(){
        //Display a list of items with their owners
        //This will be dispalyed to the Administrator as well as Access Providers who has righs

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

      //  print_r($c);

        $c_page             = $c;
        $c_page['page']     = $page;
        $c_page['limit']    = $limit;
        $c_page['offset']   = $offset;

        $total  = $this->Na->find('count',$c);       
        $q_r    = $this->Na->find('all',$c_page);

        $items = array();
        foreach($q_r as $i){

            //Status
            if(empty($i['NaState'])){
                $status = 'unknown';
                $status_time = null;
            }else{
                if($i['NaState'][0]['state'] == 1){
                    $status         = 'up';
                    $status_time    = time()- strtotime($i['NaState'][0]['modified']);
                }else{
                    $status         = 'down';
                    $status_time    = time() -strtotime($i['NaState'][0]['modified']);
                }
            }

            $realms     = array();
            //Realms
            foreach($i['NaRealm'] as $nr){
                if(!$this->_test_for_private_parent($nr['Realm'],$user)){
		    if(!array_key_exists('id',$nr['Realm'])){
			$r_id = "undefined";
			$r_n = "undefined";
			$r_s =  false;
		    }else{
			$r_id = $nr['Realm']['id'];
			$r_n = $nr['Realm']['name'];
			$r_s = $nr['Realm']['available_to_siblings'];
		   }
                    array_push($realms, 
                        array(
                            'id'                    => $r_id,
                            'name'                  => $r_n,
                            'available_to_siblings' => $r_s
                        ));
                }
            } 

            //Create tags list
            $tags       = array();
            foreach($i['NaTag'] as $nr){
                if(!$this->_test_for_private_parent($nr['Tag'],$user)){
                    array_push($tags, 
                        array(
                            'id'                    => $nr['Tag']['id'],
                            'name'                  => $nr['Tag']['name'],
                            'available_to_siblings' => $nr['Tag']['available_to_siblings']
                    ));
                }
            }

            //Create notes flag
            $notes_flag  = false;
            foreach($i['NaNote'] as $nn){
                if(!$this->_test_for_private_parent($nn['Note'],$user)){
                    $notes_flag = true;
                    break;
                }
            }

            $owner_id       = $i['Na']['user_id'];
            $owner_tree     = $this->_find_parents($owner_id);
            $action_flags   = $this->_get_action_flags($owner_id,$user);
      
            array_push($items,array(
                'id'                    => $i['Na']['id'], 
                'nasname'               => $i['Na']['nasname'],
                'shortname'             => $i['Na']['shortname'],
                'nasidentifier'         => $i['Na']['nasidentifier'],
                'secret'                => $i['Na']['secret'],
                'type'                  => $i['Na']['type'],
                'ports'                 => $i['Na']['ports'],
                'community'             => $i['Na']['community'],
                'server'                => $i['Na']['server'],
                'description'           => $i['Na']['description'],
                'connection_type'       => $i['Na']['connection_type'],
                'record_auth'           => $i['Na']['record_auth'],
                'dynamic_attribute'     => $i['Na']['dynamic_attribute'],
                'dynamic_value'         => $i['Na']['dynamic_value'],
                'monitor'               => $i['Na']['monitor'],
                'ping_interval'         => $i['Na']['ping_interval'],
                'heartbeat_dead_after'  => $i['Na']['heartbeat_dead_after'],
                'session_auto_close'    => $i['Na']['session_auto_close'],
                'session_dead_time'     => $i['Na']['session_dead_time'],
                'on_public_maps'        => $i['Na']['on_public_maps'],
                'lat'                   => $i['Na']['lat'],
                'lon'                   => $i['Na']['lon'],
                'photo_file_name'       => $i['Na']['photo_file_name'],
                'owner'                 => $owner_tree, 
                'user_id'               => $i['Na']['user_id'],
                'available_to_siblings' => $i['Na']['available_to_siblings'],
                'notes'                 => $notes_flag,
                'realms'                => $realms,
                'tags'                  => $tags,
                'connection_type'       => $i['Na']['connection_type'],
                'status'                => $status,
                'status_time'           => $status_time,
                'update'                => $action_flags['update'],
                'delete'                => $action_flags['delete'],
                'manage_tags'           => $action_flags['manage_tags']
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

    public function add_direct(){

         //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $conn_type = 'direct';
        $this->request->data['connection_type'] = $conn_type;

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

        //Then we add the rest.....
        $this->{$this->modelClass}->create();
        //print_r($this->request->data);
        if ($this->{$this->modelClass}->save($this->request->data)) {

            //Check if we need to add na_realms table
            if(isset($this->request->data['avail_for_all'])){
            //Available to all does not add any na_realm entries
            }else{
                foreach(array_keys($this->request->data) as $key){
                    if(preg_match('/^\d+/',$key)){
                        //----------------
                        $this->_add_nas_realm($this->{$this->modelClass}->id,$key);
                        //-------------
                    }
                }
            }   
            $this->request->data['id'] = $this->{$this->modelClass}->id;
            $this->set(array(
                'success' => true,
                'data'      => $this->request->data,
                '_serialize' => array('success','data')
            ));
        } else {
            $first_error = reset($this->{$this->modelClass}->validationErrors);
            $this->set(array(
                'errors'    => $this->{$this->modelClass}->validationErrors,
                'success'   => false,
                'message'   => array('message' => __('Could not create item').' <br>'.$first_error[0]),
                '_serialize' => array('errors','success','message')
            ));
        }
    }

    public function add_open_vpn(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $conn_type = 'openvpn';
        $this->request->data['connection_type'] = $conn_type;

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

        //If this attribute is not present it will fail empty check
        if(!isset($this->request->data['nasname'])){
            $this->request->data['nasname'] = ''; //Make it empty if not present
        }

        //First we create the OpenVPN entry....
        $d = array();
        $d['OpenvpnClient']['username'] = $this->request->data['username'];
        $d['OpenvpnClient']['password'] = $this->request->data['password'];
        $this->Na->OpenvpnClient->create();
        if(!$this->Na->OpenvpnClient->save($d)){
            $first_error = reset($this->Na->OpenvpnClient->validationErrors);
            $this->set(array(
                'errors'    => $this->Na->OpenvpnClient->validationErrors,
                'success'   => false,
                'message'   => array('message' => 'Could not create OpenVPN Client <br>'.$first_error[0]),
                '_serialize' => array('errors','success','message')
            ));
            return;
        }else{
            //Derive the nasname (ip address) from the new OpenvpnClient entry
            $qr = $this->Na->OpenvpnClient->findById($this->Na->OpenvpnClient->id);
            //IP Address =
            $nasname = Configure::read('openvpn.ip_half').$qr['OpenvpnClient']['subnet'].'.'.$qr['OpenvpnClient']['peer1'];
            $this->request->data['nasname'] = $nasname;
        }

        //Then we add the rest.....
        $this->{$this->modelClass}->create();
        //print_r($this->request->data);
        if ($this->{$this->modelClass}->save($this->request->data)) {

            //Check if we need to add na_realms table
            if(isset($this->request->data['avail_for_all'])){
            //Available to all does not add any na_realm entries
            }else{
                foreach(array_keys($this->request->data) as $key){
                    if(preg_match('/^\d+/',$key)){
                        //----------------
                        $this->_add_nas_realm($this->{$this->modelClass}->id,$key);
                        //-------------
                    }
                }
            }

            //Save the new ID to the OpenvpnClient....
            $this->Na->OpenvpnClient->saveField('na_id', $this->{$this->modelClass}->id);

            $this->request->data['id'] = $this->{$this->modelClass}->id;
            $this->set(array(
                'success' => true,
                'data'      => $this->request->data,
                '_serialize' => array('success','data')
            ));

        } else {
            //If it was an OpenvpnClient we need to remove the created openvpnclient entry since there was a failure
            $this->Na->OpenvpnClient->delete();
            $first_error = reset($this->{$this->modelClass}->validationErrors);
            $this->set(array(
                'errors'    => $this->{$this->modelClass}->validationErrors,
                'success'   => false,
                'message'   => array('message' => __('Could not create item').' <br>'.$first_error[0]),
                '_serialize' => array('errors','success','message')
            ));
        }
    }

    public function add_dynamic(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $conn_type = 'dynamic';
        $this->request->data['connection_type'] = $conn_type;

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

        //If this attribute is not present it will fail empty check
        if(!isset($this->request->data['nasname'])){
            $this->request->data['nasname'] = ''; //Make it empty if not present
        }

        //Get the class B subnet of the start_ip
        $start_ip   = Configure::read('dynamic.start_ip');
        $pieces     = explode('.',$start_ip);
        $octet_1    = $pieces[0];
        $octet_2    = $pieces[1];
        $class_b    = $octet_1.'.'.$octet_2;
        $q_r        = $this->Na->find('first',array('conditions' => array('Na.nasname LIKE' => "$class_b%"), 'order' => 'Na.nasname DESC'));

        if($q_r){
            $ip         = $q_r['Na']['nasname'];
            $next_ip    = $this->_get_next_ip($ip);           
            $not_available = true;
            while($not_available){
                if($this->_check_if_available($next_ip)){
                    $this->request->data['nasname']     = $next_ip;
                    $not_available = false;
                    break;
                }else{
                    $next_ip = $this->_get_next_ip($next_ip);
                }
            }              
        }else{ //The very first entry
            $this->request->data['nasname'] = $start_ip;
        }

        //Then we add the rest.....
        $this->{$this->modelClass}->create();
        //print_r($this->request->data);
        if ($this->{$this->modelClass}->save($this->request->data)) {

            //Check if we need to add na_realms table
            if(isset($this->request->data['avail_for_all'])){
            //Available to all does not add any na_realm entries
            }else{
                foreach(array_keys($this->request->data) as $key){
                    if(preg_match('/^\d+/',$key)){
                        //----------------
                        $this->_add_nas_realm($this->{$this->modelClass}->id,$key);
                        //-------------
                    }
                }
            }   
            $this->request->data['id'] = $this->{$this->modelClass}->id;
            $this->set(array(
                'success' => true,
                'data'      => $this->request->data,
                '_serialize' => array('success','data')
            ));
        } else {
            $first_error = reset($this->{$this->modelClass}->validationErrors);
            $this->set(array(
                'errors'    => $this->{$this->modelClass}->validationErrors,
                'success'   => false,
                'message'   => array('message' => __('Could not create item').' <br>'.$first_error[0]),
                '_serialize' => array('errors','success','message')
            ));
        }
    }

    public function add_pptp(){
          //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $conn_type = 'pptp';
        $this->request->data['connection_type'] = $conn_type;

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

        //If this attribute is not present it will fail empty check
        if(!isset($this->request->data['nasname'])){
            $this->request->data['nasname'] = ''; //Make it empty if not present
        }

        //First we create the OpenVPN entry....
        $d = array();
        $d['PptpClient']['username'] = $this->request->data['username'];
        $d['PptpClient']['password'] = $this->request->data['password'];
        $this->{$this->modelClass}->PptpClient->create();
        if(!$this->{$this->modelClass}->PptpClient->save($d)){
            $first_error = reset($this->{$this->modelClass}->PptpClient->validationErrors);
            $this->set(array(
                'errors'    => $this->{$this->modelClass}->PptpClient->validationErrors,
                'success'   => false,
                'message'   => array('message' => 'Could not create OpenVPN Client <br>'.$first_error[0]),
                '_serialize' => array('errors','success','message')
            ));
            return;
        }else{
            //Derive the nasname (ip address) from the new PptpClient entry
            $qr = $this->{$this->modelClass}->PptpClient->findById($this->Na->PptpClient->id);
            //IP Address =
            $nasname = $qr['PptpClient']['ip'];
            $this->request->data['nasname'] = $nasname;
        }

        //Then we add the rest.....
        $this->{$this->modelClass}->create();
        //print_r($this->request->data);
        if ($this->{$this->modelClass}->save($this->request->data)) {

            //Check if we need to add na_realms table
            if(isset($this->request->data['avail_for_all'])){
            //Available to all does not add any na_realm entries
            }else{
                foreach(array_keys($this->request->data) as $key){
                    if(preg_match('/^\d+/',$key)){
                        //----------------
                        $this->_add_nas_realm($this->{$this->modelClass}->id,$key);
                        //-------------
                    }
                }
            }

            //Save the new ID to the PptpClient....
            $this->{$this->modelClass}->PptpClient->saveField('na_id', $this->{$this->modelClass}->id);
          
            $this->request->data['id'] = $this->{$this->modelClass}->id;
            $this->set(array(
                'success' => true,
                'data'      => $this->request->data,
                '_serialize' => array('success','data')
            ));
        } else {
            //If it was an PptpClient we need to remove the created pptpclient entry since there was a failure
            $this->{$this->modelClass}->PptpClient->delete();
            $first_error = reset($this->{$this->modelClass}->validationErrors);
            $this->set(array(
                'errors'    => $this->{$this->modelClass}->validationErrors,
                'success'   => false,
                'message'   => array('message' => __('Could not create item').' <br>'.$first_error[0]),
                '_serialize' => array('errors','success','message')
            ));
        }
    }

    public function view_openvpn(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();
        if(isset($this->request->query['nas_id'])){

            $q_r = $this->{$this->modelClass}->OpenvpnClient->find('first',
                array('conditions' => array('OpenvpnClient.na_id' => $this->request->query['nas_id']))
            );

            if($q_r){
                $items['username'] = $q_r['OpenvpnClient']['username'];
                $items['password'] = $q_r['OpenvpnClient']['password'];
            }
        }

        $this->set(array(
            'data'   => $items, //For the form to load we use data instead of the standard items as for grids
            'success' => true,
            '_serialize' => array('success','data')
        ));
    }

    public function edit_openvpn(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        //TODO Check if the owner ...

        if(isset($this->request->data['nas_id'])){

            $q_r = $this->{$this->modelClass}->OpenvpnClient->find('first',
                array('conditions' => array('OpenvpnClient.na_id' => $this->request->data['nas_id']))
            );

            if($q_r){
                $id = $q_r['OpenvpnClient']['id'];
                $this->request->data['id']      = $id;
                $this->request->data['subnet']  = $q_r['OpenvpnClient']['subnet'];
                $this->request->data['peer1']   = $q_r['OpenvpnClient']['peer1'];
                $this->request->data['peer2']   = $q_r['OpenvpnClient']['peer2'];  
            }

            if(!$this->Na->OpenvpnClient->save($this->request->data)){
                $first_error = reset($this->Na->OpenvpnClient->validationErrors);
                $this->set(array(
                    'errors'    => $this->Na->OpenvpnClient->validationErrors,
                    'success'   => false,
                    'message'   => array('message' => __('Could not update OpenVPN detail').' <br>'.$first_error[0]),
                    '_serialize' => array('errors','success','message')
                ));
                return;
            }else{
                    $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }
        }
    }

    public function view_pptp(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();
        if(isset($this->request->query['nas_id'])){

            $q_r = $this->{$this->modelClass}->PptpClient->find('first',
                array('conditions' => array('PptpClient.na_id' => $this->request->query['nas_id']))
            );

            if($q_r){
                $items['username'] = $q_r['PptpClient']['username'];
                $items['password'] = $q_r['PptpClient']['password'];
            }
        }

        $this->set(array(
            'data'   => $items, //For the form to load we use data instead of the standard items as for grids
            'success' => true,
            '_serialize' => array('success','data')
        ));
    }

    public function edit_pptp(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        //TODO Check if the owner ...

        if(isset($this->request->data['nas_id'])){

            $q_r = $this->{$this->modelClass}->PptpClient->find('first',
                array('conditions' => array('PptpClient.na_id' => $this->request->data['nas_id']))
            );

            if($q_r){
                $id = $q_r['PptpClient']['id'];
                $this->request->data['id']      = $id;
                $this->request->data['ip']      = $q_r['PptpClient']['ip'];
            }

            if(!$this->Na->PptpClient->save($this->request->data)){
                $first_error = reset($this->Na->PptpClient->validationErrors);
                $this->set(array(
                    'errors'    => $this->Na->PptpClient->validationErrors,
                    'success'   => false,
                    'message'   => array('message' => __('Could not update PPTP detail').' <br>'.$first_error[0]),
                    '_serialize' => array('errors','success','message')
                ));
                return;
            }else{
                    $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }
        }
    }


    public function view_dynamic(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();
        if(isset($this->request->query['nas_id'])){

            $q_r = $this->{$this->modelClass}->findById($this->request->query['nas_id']);

            if($q_r){
                $items['dynamic_attribute'] = $q_r['Na']['dynamic_attribute'];
                $items['dynamic_value']     = $q_r['Na']['dynamic_value'];
            }
        }

        $this->set(array(
            'data'   => $items, //For the form to load we use data instead of the standard items as for grids
            'success' => true,
            '_serialize' => array('success','data')
        ));
    }

    public function edit_dynamic(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        if ($this->{$this->modelClass}->save($this->request->data)) {
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

    public function view_nas(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();
        if(isset($this->request->query['nas_id'])){

            $q_r = $this->{$this->modelClass}->findById($this->request->query['nas_id']);

            if($q_r){
                $items = $q_r['Na'];
            }
        }

        $this->set(array(
            'data'   => $items, //For the form to load we use data instead of the standard items as for grids
            'success' => true,
            '_serialize' => array('success','data')
        ));
    }

    public function edit_nas(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
     

        if(array_key_exists('on_public_maps',$this->request->data)){
            $this->request->data['on_public_maps'] = 1;
        }else{
            $this->request->data['on_public_maps']      = 0;
        }

        if(array_key_exists('session_auto_close',$this->request->data)){
            $this->request->data['session_auto_close'] = 1;
        }else{
            $this->request->data['session_auto_close']  = 0;
        }
        if(array_key_exists('record_auth',$this->request->data)){
            $this->request->data['record_auth'] = 1;
        }else{
            $this->request->data['record_auth']         = 0;
        }
        if(array_key_exists('ignore_acct',$this->request->data)){
            $this->request->data['ignore_acct'] = 1;
        }else{
            $this->request->data['ignore_acct']         = 0; 
        }


        if($this->request->data['monitor'] == 'off'){   //Clear the last contact when off
            $this->request->data['last_contact'] = null;
        }

        if ($this->{$this->modelClass}->save($this->request->data)) {

            //If monitor was == off; clear the NaStates
            $this->{$this->modelClass}->NaState->deleteAll(array('NaState.na_id' => $this->request->data['id']), false);

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

    public function view_photo(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $items = array();

        if(isset($this->request->query['id'])){
            $this->Na->contain();
            $q_r = $this->{$this->modelClass}->findById($this->request->query['id']);
            if($q_r){
                $items['photo_file_name'] = $q_r['Na']['photo_file_name'];
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

    public function view_map_pref(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $items = array();

        $this->UserSetting = ClassRegistry::init('UserSetting');

        $zoom = Configure::read('user_settings.map.zoom');
        //Check for personal overrides
        $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'map_zoom')));
        if($q_r){
            $zoom = intval($q_r['UserSetting']['value']);
        }

        $type = Configure::read('user_settings.map.type');
        //Check for personal overrides

        $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'map_type')));
        if($q_r){
            $type = $q_r['UserSetting']['value'];
        }

        $lat = Configure::read('user_settings.map.lat');
        //Check for personal overrides

        $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'map_lat')));
        if($q_r){
            $lat = $q_r['UserSetting']['value']+0;
        }

        $lng = Configure::read('user_settings.map.lng');
        //Check for personal overrides

        $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'map_lng')));
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
            $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'map_zoom')));
            if(!empty($q_r)){
                $this->UserSetting->id = $q_r['UserSetting']['id'];    
                $this->UserSetting->saveField('value', $this->request->data['zoom']);
            }else{
                $d['UserSetting']['user_id']= $user_id;
                $d['UserSetting']['name']   = 'map_zoom';
                $d['UserSetting']['value']  = $this->request->data['zoom'];
                $this->UserSetting->create();
                $this->UserSetting->save($d);
                $this->UserSetting->id = null;
            }
        }

        if(array_key_exists('type',$this->request->data)){        
            $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'map_type')));
            if(!empty($q_r)){
                $this->UserSetting->id = $q_r['UserSetting']['id'];    
                $this->UserSetting->saveField('value', $this->request->data['type']);
            }else{
                $d['UserSetting']['user_id']= $user_id;
                $d['UserSetting']['name']   = 'map_type';
                $d['UserSetting']['value']  = $this->request->data['type'];
                $this->UserSetting->create();
                $this->UserSetting->save($d);
                $this->UserSetting->id = null;
            }
        }

        if(array_key_exists('lat',$this->request->data)){        
            $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'map_lat')));
            if(!empty($q_r)){
                $this->UserSetting->id = $q_r['UserSetting']['id'];    
                $this->UserSetting->saveField('value', $this->request->data['lat']);
            }else{
                $d['UserSetting']['user_id']= $user_id;
                $d['UserSetting']['name']   = 'map_lat';
                $d['UserSetting']['value']  = $this->request->data['lat'];

                $this->UserSetting->create();
                $this->UserSetting->save($d);
                $this->UserSetting->id = null;
            }
        }

        if(array_key_exists('lng',$this->request->data)){        
            $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'map_lng')));
            if(!empty($q_r)){
                $this->UserSetting->id = $q_r['UserSetting']['id'];    
                $this->UserSetting->saveField('value', $this->request->data['lng']);
            }else{
                $d['UserSetting']['user_id']= $user_id;
                $d['UserSetting']['name']   = 'map_lng';
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
            $this->Na->id = $this->request->query['id'];
            $this->Na->saveField('lat', null);
            $this->Na->saveField('lon', null);
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
            $this->Na->id = $this->request->query['id'];
            $this->Na->saveField('lat', $this->request->query['lat']);
            $this->Na->saveField('lon', $this->request->query['lon']);
        }

        $this->set(array(
                'success' => true,
                '_serialize' => array('success')
        ));
    }

    public function manage_tags(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];


        $tag_id = $this->request->data['tag_id'];
        $rb     = $this->request->data['rb'];

        foreach(array_keys($this->request->data) as $key){
            if(preg_match('/^\d+/',$key)){
                //----------------
                if($rb == 'add'){
                    $this->_add_nas_tag($key,$tag_id);
                }
                if($rb == 'remove'){
                    $this->Na->NaTag->deleteAll(array('NaTag.na_id' => $key,'NaTag.tag_id' => $tag_id), false);
                }
                //-------------
            }
        }
     
        $this->set(array(
                'success' => true,
                '_serialize' => array('success')
        ));
    }

    public function edit_panel_cfg(){

        $items = array();
        $nn_disabled = true;
        $chilli_heartbeat_flag = false;

        //Determine which tabs will be displayed (based on the connection type)
        if(isset($this->request->query['nas_id'])){
            $q_r = $this->{$this->modelClass}->findById($this->request->query['nas_id']);
            if($q_r){

                if(($q_r['Na']['type'] == 'CoovaChilli-Heartbeat')||($q_r['Na']['type'] == 'Mikrotik-Heartbeat')){
                    $chilli_heartbeat_flag = true;
                }
                $conn_type = $q_r['Na']['connection_type'];
                if($conn_type == 'openvpn'){
                    array_push($items,array( 'title'  => __('OpenVPN'), 'itemId' => 'tabOpenVpn', 'xtype' => 'pnlNasOpenVpn'));
                }
                if($conn_type == 'pptp'){
                    array_push($items,array( 'title'  => __('PPTP'),    'itemId' => 'tabPptp', 'xtype' => 'pnlNasPptp'));
                }
                if($conn_type == 'dynamic'){
                    array_push($items,array( 'title'  => __('Dynamic AVP detail'), 'itemId' => 'tabDynamic', 'xtype' => 'pnlNasDynamic' ));
                }
                if($conn_type == 'direct'){
                    $nn_disabled = false;
                }
            }
        }

        //This will be with all of them
       /// array_push($items, array( 'title'  => __('NAS'), 'itemId' => 'tabNas', 'layout' => 'hbox', 
       ///     'items' => array('xtype' => 'frmNasBasic', 'height' => '100%', 'width' => 500)
       /// ));
         array_push($items, array( 'title'  => __('NAS'), 'itemId' => 'tabNas', 'xtype' => 'pnlNasNas', 'nn_disabled' => $nn_disabled));
        array_push($items,array( 'title'  => __('Realms'),'itemId' => 'tabRealms', 'layout' => 'fit', 'border' => false, 'xtype' => 'pnlRealmsForNasOwner'));
         array_push($items,array( 'title'  => __('Photo'),'itemId' => 'tabPhoto', 'xtype' => 'pnlNasPhoto'));
         array_push($items,array( 'title'  => __('Availability'), 'itemId' => 'tabAvailability', 'xtype' => 'gridNasAvailability'));

        if($chilli_heartbeat_flag == true){
            array_push($items,array( 'title'  => __('Heartbeat actions'),'itemId' => 'tabActions','xtype' => 'gridNasActions'));
        }

        $na_id = $this->request->query['nas_id'];

        $this->set(array(
                'items'     => $items,
                'success'   => true,
                '_serialize' => array('items','success')
        ));

    }

    public function delete($id = null) {
    //FIXME This is seriously wrong! it is going to delete wrong stuff!
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];  

	    if(isset($this->data['id'])){   //Single item delete
            $message = __("Single item")." ".$this->data['id'];
            $this->{$this->modelClass}->id = $this->data['id'];
            $this->{$this->modelClass}->delete();
      
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                $this->{$this->modelClass}->id = $d['id'];
                $this->{$this->modelClass}->delete();
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
	}

    //FOR The CoovaAP heartbeat system
    //This view needs to send plain text out
    public function get_coova_detail($mac){

        $this->autoRender = false; // no view to render
        $this->response->type('text');

        $pattern = '/^([0-9a-fA-F]{2}[-]){5}[0-9a-fA-F]{2}$/i';
        if(preg_match($pattern, $mac)< 1){
            $error      = "ERROR=MAC missing or wrong";
            $response   = "HEARTBEAT=NO\n$error\n";
            $this->response->body($response);
            return;
        }

        //MAC format fine; see if defined
        $this->{$this->modelClass}->contain();
        $q_r = $this->{$this->modelClass}->find('first', array('conditions' => 
            array('Na.community' => $mac,'Na.type' => 'CoovaChilli-Heartbeat'))
        );

        if($q_r){
            $nas_id = $q_r['Na']['nasidentifier'];
            $nas_ip = $q_r['Na']['nasname'];
            if(($nas_id == '')||($nas_ip == '')){
                $response = "HEARTBEAT=NO\nERROR=DATA MISSING\n"; 
            }else{
                $response = "HEARTBEAT=YES\nNAS-ID=$nas_id\nNAS-IP=$nas_ip\n";         
            }
            
        }else{
            $response = "HEARTBEAT=NO\nERROR=NO MATCH FOR MAC $mac\n"; 
        }
        $this->response->body($response);
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
            $na_id  = $this->request->query['for_id'];
            $q_r    = $this->Na->NaNote->find('all', 
                array(
                    'contain'       => array('Note'),
                    'conditions'    => array('NaNote.na_id' => $na_id)
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
        $this->Na->NaNote->Note->create(); 
        //print_r($this->request->data);
        if ($this->Na->NaNote->Note->save($this->request->data)) {
            $d                      = array();
            $d['NaNote']['na_id']   = $this->request->data['for_id'];
            $d['NaNote']['note_id'] = $this->Na->NaNote->Note->id;
            $this->Na->NaNote->create();
            if ($this->Na->NaNote->save($d)) {
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
            $item       = $this->Na->NaNote->Note->findById($this->data['id']);
            $owner_id   = $item['Note']['user_id'];
            if($owner_id != $user_id){
                if($this->_is_sibling_of($user_id,$owner_id)== true){
                    $this->Na->NaNote->Note->id = $this->data['id'];
                    $this->Na->NaNote->Note->delete($this->data['id'],true);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->Na->NaNote->Note->id = $this->data['id'];
                $this->Na->NaNote->Note->delete($this->data['id'],true);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                $item       = $this->Na->NaNote->Note->findById($d['id']);
                $owner_id   = $item['Note']['user_id'];
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($user_id,$owner_id) == true){
                        $this->Na->NaNote->Note->id = $d['id'];
                        $this->Na->NaNote->Note->delete($d['id'],true);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->Na->NaNote->Note->id = $d['id'];
                    $this->Na->NaNote->Note->delete($d['id'],true);
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


    //______ EXT JS UI functions ___________

    //------ List of available connection types ------
    //This is displayed as options when a user adds a new NAS device
    public function conn_types_available(){

        $items = array();

        $ct = Configure::read('conn_type');
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

    //------ List of configured dynamic attributes types ------
    //This is displayed as a select to choose from when the user adds a NAS of connection type dynamic
    public function dynamic_attributes(){
        $items = array();
        $ct = Configure::read('dynamic_attributes');
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

    //------ List of configured nas types  ------
    //This is displayed as a select to choose from when the user specifies the NAS detail 
    public function nas_types(){
        $items = array();
        $ct = Configure::read('nas_types');
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
                        array( 'xtype' =>  'splitbutton',  'glyph'     => Configure::read('icnReload'),'scale'   => 'large', 'itemId'    => 'reload',   'tooltip'    => __('Reload'),
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
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit'))
                )),
                array('xtype' => 'buttongroup','title' => __('Document'), 'items' => array(
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnNote'),'scale' => 'large', 'itemId' => 'note',     'tooltip'=> __('Add notes')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnCsv'),'scale' => 'large', 'itemId' => 'csv',      'tooltip'=> __('Export CSV')),
                )),
                array('xtype' => 'buttongroup','title' => __('Nas'), 'items' => array(
                     array('xtype' => 'button', 'glyph'    => Configure::read('icnGraph'),'scale' => 'large', 'itemId' => 'graph',  'tooltip'=> __('Graphs')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnTag'),'scale' => 'large', 'itemId' => 'tag',     'tooltip'=> __('Manage tags')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnMap'),'scale' => 'large', 'itemId' => 'map',      'tooltip'=> __('Map'))
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
                'xtype'     => 'button',
                'glyph'     => Configure::read('icnReload'),  
                'scale'     => 'large', 
                'itemId'    => 'reload',   
                'tooltip'   => __('Reload')));

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
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'edit')){
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

            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'export_csv')){ 
                array_push($document_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnCsv'),     
                    'scale'     => 'large', 
                    'itemId'    => 'csv',      
                    'tooltip'   => __('Export CSV')));
            }

            //Graph
            array_push($specific_group,array(
                'xtype'     => 'button', 
                'glyph'     => Configure::read('icnGraph'),
                'scale'     => 'large', 
                'itemId'    => 'graph',    
                'tooltip'   => __('Graphs'))
            );


            //Tags
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'manage_tags')){
                array_push($specific_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnMeta'),    
                    'scale'     => 'large', 
                    'itemId'    => 'tag',
                    'disabled'  => true,     
                    'tooltip'=> __('Manage tags')));
            }

            array_push($specific_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnMap'),     
                    'scale'     => 'large', 
                    'itemId'    => 'map',      
                    'tooltip'   => __('Maps')));

           // array_push($menu,array('xtype' => 'tbfill'));

            $menu = array(
                        array('xtype' => 'buttongroup','title' => __('Action'),        'items' => $action_group),
                        array('xtype' => 'buttongroup','title' => __('Document'),   'items' => $document_group),
                        array('xtype' => 'buttongroup','title' => __('Nas'),        'items' => $specific_group)
                    );
        }
        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }


     public function menu_for_maps(){

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
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnConfigure'), 'scale' => 'large', 'itemId' => 'preferences', 'tooltip'=> __('Preferences')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnAdd'), 'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnEdit'), 'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit'))
                )) 
            );
        }
        //FIXME Fine tune the menu based on AP rights
        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)

             $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnConfigure'), 'scale' => 'large', 'itemId' => 'preferences', 'tooltip'=> __('Preferences')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnAdd'), 'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnEdit'), 'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit'))
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

    private function _add_nas_realm($nas_id,$realm_id){
        $d                          = array();
        $d['NaRealm']['id']         = '';
        $d['NaRealm']['na_id']      = $nas_id;
        $d['NaRealm']['realm_id']   = $realm_id;

        $this->Na->NaRealm->create();
        $this->Na->NaRealm->save($d);
        $this->Na->NaRealm->id      = false;
    }

    private function _add_nas_tag($nas_id,$tag_id){
        //Delete any previous tags if there were any
        $this->Na->NaTag->deleteAll(array('NaTag.na_id' => $nas_id,'NaTag.tag_id' => $tag_id), false);
        $d                      = array();
        $d['NaTag']['id']       = '';
        $d['NaTag']['na_id']    = $nas_id;
        $d['NaTag']['tag_id']   = $tag_id;
        $this->Na->NaTag->create();
        $this->Na->NaTag->save($d);
        $this->Na->NaTag->id    = false;
    }

    private function _get_action_flags($owner_id,$user){
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            return array('update' => true, 'delete' => true ,'manage_tags' => true);
        }

        if($user['group_name'] == Configure::read('group.ap')){  //AP
            $user_id = $user['id'];

            //test for self
            if($owner_id == $user_id){
                return array('update' => true, 'delete' => true ,'manage_tags' => true);
            }
            //Test for Parents
            foreach($this->parents as $i){
                if($i['User']['id'] == $owner_id){
                    return array('update' => false, 'delete' => false ,'manage_tags' => false);
                }
            }

            //Test for Children
            foreach($this->children as $i){
                if($i['id'] == $owner_id){
                    return array('update' => true, 'delete' => true ,'manage_tags' => true);
                }
            }  
        }
    }

    function _build_common_query($user){

        //Empty to start with
        $c                  = array();
        $c['joins']         = array(); 
        $c['conditions']    = array();

        //What should we include....
        $c['contain']   = array(
                            'NaRealm'   => array('Realm.name','Realm.id','Realm.available_to_siblings','Realm.user_id'),
                            'NaTag'     => array('Tag.name','Tag.id','Tag.available_to_siblings','Tag.user_id'),
                            'NaNote'    => array('Note.note','Note.id','Note.available_to_siblings','Note.user_id'),
                            'User',
                            'NaState'
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'nasname';
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

                //Lists
                if($f->type == 'list'){

                    //The tags field has to be treated specially
                    if($f->field == 'tags'){

                        $list_array = array();
                        foreach($f->value as $filter_list){
                            $col = 'Tag.name';
                            array_push($list_array,array("$col" => "$filter_list"));
                        }
                        array_push($c['joins'],array(
                            'table'         => 'na_tags',
                            'alias'         => 'NaTag',
                            'type'          => 'INNER',
                            'conditions'    => array('NaTag.na_id = Na.id')
                        ));
                        array_push($c['joins'],array(
                            'table'         => 'tags',
                            'alias'         => 'Tag',
                            'type'          => 'INNER',
                            'conditions'    => array('Tag.id = NaTag.tag_id',array('OR' => $list_array))
                        ));

                    }elseif($f->field == 'realms'){
                        $list_array = array();
                        foreach($f->value as $filter_list){
                            $col = 'Realm.name';
                            array_push($list_array,array("$col" => "$filter_list"));
                        }
                        array_push($c['joins'],array(
                            'table'         => 'na_realms',
                            'alias'         => 'NaRealm',
                            'type'          => 'INNER',
                            'conditions'    => array('NaRealm.na_id = Na.id')
                        ));
                        array_push($c['joins'],array(
                            'table'         => 'realms',
                            'alias'         => 'Realm',
                            'type'          => 'INNER',
                            'conditions'    => array('Realm.id = NaRealm.realm_id',array('OR' => $list_array))
                        ));                     
                    }else{
                        $list_array = array();
                        foreach($f->value as $filter_list){
                            $col = $this->modelClass.'.'.$f->field;
                            array_push($list_array,array("$col" => "$filter_list"));
                        }
                        //Add it as an OR condition
                        array_push($c['conditions'],array('OR' => $list_array));
                    }
                }
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
        //If the user is an AP; we need to add an extra clause to only show the NAS devices which he is allowed to see.
        if($user['group_name'] == Configure::read('group.ap')){  //AP
            $tree_array = array();
            $user_id    = $user['id'];

            //**AP and upward in the tree**
            $this->parents = $this->User->getPath($user_id,'User.id');
            //So we loop this results asking for the parent nodes who have available_to_siblings = true
            foreach($this->parents as $i){
                $i_id = $i['User']['id'];
                if($i_id != $user_id){ //upstream
                    array_push($tree_array,array('Na.user_id' => $i_id,'Na.available_to_siblings' => true));
                }else{
                    array_push($tree_array,array('Na.user_id' => $i_id));
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

   private function _check_if_available($ip){

        $count = $this->Na->find('count',array('conditions' => array('Na.nasname' => $ip)));
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
