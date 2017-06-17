<?php
App::uses('AppController', 'Controller');

class DevicesController extends AppController {

    public $name       = 'Devices';
    public $components = array('Aa','GridFilter');
    public $uses       = array('Device','User','PermanentUser');
    protected $base    = "Access Providers/Controllers/Devices/"; //Required for AP Rights
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

            $columns    = array();
            $csv_line   = array();
            if(isset($this->request->query['columns'])){
                $columns = json_decode($this->request->query['columns']);
                foreach($columns as $c){
                    $column_name = $c->name;
                    if($column_name == 'notes'){
                        $notes   = '';
                        foreach($i['DeviceNote'] as $n){
                            if(!$this->_test_for_private_parent($n['Note'],$user)){
                                $notes = $notes.'['.$n['Note']['note'].']';    
                            }
                        }
                        array_push($csv_line,$notes);
                    }elseif($column_name =='user'){
                        $owner       = $i['User']['username'];
                        array_push($csv_line,$owner); 
                    }elseif($column_name =='realm'){
                        $realm = 'n/a';
                        foreach($i['Radcheck'] as $rc){       
                            if($rc['attribute'] == 'Rd-Realm'){
                                $realm = $rc['value'];
                            }
                        }
                        array_push($csv_line,$realm); 
                    }elseif($column_name =='profile'){
                        $profile = 'n/a';
                        foreach($i['Radcheck'] as $rc){       
                            if($rc['attribute'] == 'User-Profile'){
                                $profile = $rc['value'];
                            }
                        }
                        array_push($csv_line,$profile); 
                    }else{
                        array_push($csv_line,$i['Device']["$column_name"]);  
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

        $total  = $this->{$this->modelClass}->find('count'  , $c);  
        $q_r    = $this->{$this->modelClass}->find('all'    , $c_page);

        $items      = array();
        $profiles   = array();

        foreach($q_r as $i){

            //Create notes flag
            $notes_flag  = false;
            foreach($i['DeviceNote'] as $un){
                if(!$this->_test_for_private_parent($un['Note'],$user)){
                    $notes_flag = true;
                    break;
                }
            }

            //Find the realm and profile names
            $realm  = 'not defined';
            $profile= 'not defined';
            $p_id=0;

            foreach($i['Radcheck'] as $rc){
                if($rc['attribute'] == 'User-Profile'){
                    $profile = $rc['value'];
                    if(!array_key_exists($profile,$profiles)){
                        $p = ClassRegistry::init('Profile');
                        $p->contain();
                        $q_r = $p->findByName($profile);
                        $profiles[$profile] = $q_r['Profile']['id'];
                        $p_id = $q_r['Profile']['id'];
                    }
                }
                if($rc['attribute'] == 'Rd-Realm'){
                    $realm = $rc['value'];
                }
            }

            $action_flags = array();
            $action_flags['update'] = false;
            $action_flags['delete'] = false;

            if($realm != 'not defined'){
                $owner_id       = $i['PermanentUser']['user_id'];
                $q_r            = ClassRegistry::init('Realm')->findByName($realm);
                $action_flags   = $this->_get_action_flags_for_devices($user,$owner_id,$q_r['Realm']);
            }

            if($action_flags['read']){
                array_push($items,
                    array(
                        'id'            		=> $i['Device']['id'], 
                        'user'          		=> $i['PermanentUser']['username'],
                        'permanent_user_id'     => $i['Device']['permanent_user_id'],
                        'name'          		=> $i['Device']['name'],
                        'description'   		=> $i['Device']['description'], 
                        'realm'         		=> $realm,
                        'profile'       		=> $profile,
                        //'profile_id'    => $profiles[$profile],
                        'profile_id'    		=> $p_id,
                        'perc_time_used'		=> $i['Device']['perc_time_used'],
                        'perc_data_used'		=> $i['Device']['perc_data_used'],
                        'active'        		=> $i['Device']['active'],
                        'last_accept_time'      => $i['Device']['last_accept_time'],
                        'last_accept_nas'       => $i['Device']['last_accept_nas'],
                        'last_reject_time'      => $i['Device']['last_reject_time'],
                        'last_reject_nas'       => $i['Device']['last_reject_nas'],
                        'last_reject_message'   => $i['Device']['last_reject_message'],
                        'notes'         		=> $notes_flag,
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
   
        //Two fields should be tested for first:
        if(array_key_exists('active',$this->request->data)){
            $this->request->data['active'] = 1;
        }

        //Ensure the MAC is UC
        $this->request->data['name'] = strtolower($this->request->data['name']);

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
            $this->{$this->modelClass}->contain('User');
            $item       = $this->{$this->modelClass}->findById($this->data['id']);
            $ap_id      = $item['PermanentUser']['user_id'];
            $username   = $item['Device']['name'];
            if($ap_id != $user_id){
                if($this->_is_sibling_of($user_id,$ap_id)== true){
                    $this->{$this->modelClass}->id = $this->data['id'];
                    $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                    $this->_delete_clean_up_device($username);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->{$this->modelClass}->id = $this->data['id'];
                $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                $this->_delete_clean_up_device($username);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                $item       = $this->{$this->modelClass}->findById($d['id']);
                $ap_id      = $item['PermanentUser']['user_id'];
                $username   = $item['Device']['name'];
                if($ap_id != $user_id){
                    if($this->_is_sibling_of($user_id,$ap_id) == true){
                        $this->{$this->modelClass}->id = $d['id'];
                        $this->{$this->modelClass}->delete($this->{$this->modelClass}->id,true);
                        $this->_delete_clean_up_device($username);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->{$this->modelClass}->id = $d['id'];
                    $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                    $this->_delete_clean_up_device($username);
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

        //We need the device_id;
        //We supply the profile_id; realm_id; cap; always_active; from_date; to_date

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $user_id    = $user['id'];

        $items = array();

        //TODO Check if the owner of this device is in the chain of the APs
        if(isset($this->request->query['device_id'])){

            $profile        = false;
            $realm          = false;
            $always_active  = true;
            $to_date        = false;
            $from_date      = false;
            $cap_data       = false;
            $cap_time       = false;
            $owner          = false;
            $description    = false;

            $this->{$this->modelClass}->contain('Radcheck');
            $q_r = $this->{$this->modelClass}->findById($this->request->query['device_id']);

            $items['description'] = $q_r['Device']['description'];

            foreach($q_r['Radcheck'] as $rc){

                if($rc['attribute'] == 'Rd-Realm'){
                  $realm =  $rc['value'];
                }

                if($rc['attribute'] == 'User-Profile'){
                  $profile =  $rc['value'];
                }

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

                if($rc['attribute'] == 'Rd-Device-Owner'){
                  $owner =  $rc['value'];
                }
            }
        
            //Now we do the rest....
            if($profile){
                $q_r = $this->User = ClassRegistry::init('Profile')->findByName($profile);
                $items['profile_id'] = intval($q_r['Profile']['id']);
            }


            if($owner){
                $q_r = $this->User = ClassRegistry::init('PermanentUser')->findByUsername($owner);
                $items['permanent_user_id'] = intval($q_r['PermanentUser']['id']);
				$items['user_id'] = intval($q_r['PermanentUser']['id']);
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
        }
               // $items = array('realm_id' => 26, 'profile_id' => 2, 'always_active' => false,'cap' => 'soft');

        $this->set(array(
            'data'   => $items, //For the form to load we use data instead of the standard items as for grids
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

        $this->{$this->modelClass}->save($this->request->data);

        //TODO Check if the owner of this user is in the chain of the APs
        if(isset($this->request->data['id'])){
            $q_r        = $this->{$this->modelClass}->findById($this->request->data['id']);
            $username   = $q_r['Device']['name'];

            if(isset($this->request->data['profile_id'])){
                $q_r = ClassRegistry::init('Profile')->findById($this->data['profile_id']);
                $profile_name = $q_r['Profile']['name'];
                $this->_replace_radcheck_item($username,'User-Profile',$profile_name);
            }

            if(isset($this->request->data['permanent_user_id'])){
                $u = ClassRegistry::init('PermanentUser');
                $u->contain();
                $q_r = $u->findById($this->data['permanent_user_id']);
                $owner_name = $q_r['PermanentUser']['username'];
                $this->_replace_radcheck_item($username,'Rd-Device-Owner',$owner_name);
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
            
            if(isset($this->request->data['cap_data'])){
                $this->_replace_radcheck_item($username,'Rd-Cap-Type-Data',$this->request->data['cap_data']);
            }else{              //Clean up if there were previous ones
                ClassRegistry::init('Radcheck')->deleteAll(
                    array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Cap-Type-Data'), false
                );
            }

            if(isset($this->request->data['cap_time'])){
                $this->_replace_radcheck_item($username,'Rd-Cap-Type-Time',$this->request->data['cap_time']);
            }else{              //Clean up if there were previous ones
                ClassRegistry::init('Radcheck')->deleteAll(
                    array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Cap-Type-Time'), false
                );
            }

        }

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
        if(isset($this->request->query['device_id'])){

            $acct           = true;
            $auth           = true;

            $this->{$this->modelClass}->contain('Radcheck');
            $q_r = $this->{$this->modelClass}->findById($this->request->query['device_id']);

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
            $username   = $q_r['Device']['name'];
           
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



    public function view(){

    }

    public function remove_mac(){

        $success = false;
        $message = '';

        if(Configure::read('UserCanRemoveDevice') == true){
            if(isset($this->request->query['mac'])){

                $mac        = $this->request->query['mac'];
                $item       = $this->{$this->modelClass}->findByName($mac);
                if($item){
                    $id   = $item['Device']['id'];
                    $this->{$this->modelClass}->id = $id;
                    $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                    $this->_delete_clean_up_device($mac);
                    $success = true;
                    $message = "Device $mac deleted fine";
                }else{
                    $message = "Device $mac not found";
                }
            }else{
                $message = "Device to remove not specified";
            }
            $this->set(array(
                'success'   => $success,
                'message'   => $message,
                '_serialize' => array('success', 'message')
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
            $device_id = $this->request->query['for_id'];
            $q_r    = $this->Device->DeviceNote->find('all', 
                array(
                    'contain'       => array('Note'),
                    'conditions'    => array('DeviceNote.device_id' => $device_id)
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
        $this->Device->DeviceNote->Note->create(); 
        //print_r($this->request->data);
        if ($this->Device->DeviceNote->Note->save($this->request->data)) {
            $d                      = array();
            $d['DeviceNote']['device_id']   = $this->request->data['for_id'];
            $d['DeviceNote']['note_id']     = $this->Device->DeviceNote->Note->id;
            $this->Device->DeviceNote->create();
            if ($this->Device->DeviceNote->save($d)) {
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
            $item       = $this->Device->DeviceNote->Note->findById($this->data['id']);
            $owner_id   = $item['Note']['user_id'];
            if($owner_id != $user_id){
                if($this->_is_sibling_of($user_id,$owner_id)== true){
                    $this->Device->DeviceNote->Note->id = $this->data['id'];
                    $this->Device->DeviceNote->Note->delete($this->data['id'],true);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->Device->DeviceNote->Note->id = $this->data['id'];
                $this->Device->DeviceNote->Note->delete($this->data['id'],true);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                $item       = $this->Device->DeviceNote->Note->findById($d['id']);
                $owner_id   = $item['Note']['user_id'];
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($user_id,$owner_id) == true){
                        $this->Device->DeviceNote->Note->id = $d['id'];
                        $this->Device->DeviceNote->Note->delete($d['id'],true);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->Device->DeviceNote->Note->id = $d['id'];
                    $this->Device->DeviceNote->Note->delete($d['id'],true);
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
            $d['Device']['active'] = 1;
        }else{
            $d['Device']['active'] = 0;
        }

        foreach(array_keys($this->request->data) as $key){
            if(preg_match('/^\d+/',$key)){
                $d['Device']['id']              = $key;
                $this->{$this->modelClass}->id  = $key;
                $this->{$this->modelClass}->save($d);   
            }
        }
        $this->set(array(
            'success' => true,
            '_serialize' => array('success',)
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
                        array( 'xtype' =>  'splitbutton', 'glyph' => Configure::read('icnReload'),  'scale'   => 'large', 'itemId'    => 'reload',   'tooltip'    => __('Reload'),
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
                    array('xtype' => 'button', 'glyph' => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit'))
                )),
                array('xtype' => 'buttongroup','title' => __('Document'), 'items' => array(
                    array('xtype' => 'button', 'glyph' => Configure::read('icnNote'),'scale' => 'large', 'itemId' => 'note',     'tooltip'=> __('Add notes')),
                    array('xtype' => 'button', 'glyph' => Configure::read('icnCsv'),'scale' => 'large', 'itemId' => 'csv',      'tooltip'=> __('Export CSV')),
                )),
                array('xtype' => 'buttongroup','title' => __('Extra actions'), 'items' => array(
                    array('xtype' => 'button', 'scale' => 'large', 'glyph' => Configure::read('icnLight'),'itemId' => 'enable_disable','tooltip'=> __('Enable / Disable')),
                    array('xtype' => 'button', 'scale' => 'large', 'glyph' => Configure::read('icnRadius'),'itemId' => 'test_radius',  'tooltip'=> __('Test RADIUS')),
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
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'edit_basic_info')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-edit',
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

            //Enable-disable
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'enable_disable')){ 
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
                        array( 'xtype'=>  'button', 'glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload',   'tooltip'   => __('Reload')),
                        array('xtype' => 'button',  'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'   => __('Delete')), 
                )) 
            );
        }

        if($user['group_name'] == Configure::read('group.ap')){  //AP
            $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                        array( 'xtype'=>  'button', 'glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload',   'tooltip'   => __('Reload')),
                        array('xtype' => 'button',  'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'   => __('Delete')), 
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
                        array( 'xtype'=>  'button', 'glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload',   'tooltip'   => __('Reload')),
                        array('xtype' => 'button',  'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'   => __('Delete')), 
                )) 
            );
        }

        if($user['group_name'] == Configure::read('group.ap')){  //Ap
            $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                        array( 'xtype'=>  'button', 'glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload',   'tooltip'   => __('Reload')),
                        array('xtype' => 'button',  'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'   => __('Delete')), 
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
                            'PermanentUser',     
                            'Radcheck',
                            'DeviceNote'  => array('Note.note','Note.id','Note.available_to_siblings','Note.user_id'),
                               
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'Device.name';
        $dir    = 'DESC';

        if(isset($this->request->query['sort'])){
            if($this->request->query['sort'] == 'user'){
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

        //======= For a specified owner filter *Usually on the edit permanent user ======
        if(isset($this->request->query['permanent_user_id'])){
            $u_id = $this->request->query['permanent_user_id'];
            array_push($c['conditions'],array($this->modelClass.".permanent_user_id" => $u_id));
        }
        
        //If it is a combobox filter
        if(isset($this->request->query['query'])){
            $query = $this->request->query['query'];
            array_push($c['conditions'],array("Device.name LIKE" => '%'.$query.'%'));
        }


        //====== REQUEST FILTER =====
        if(isset($this->request->query['filter'])){
            $filter = json_decode($this->request->query['filter']);
            foreach($filter as $f){

                $f = $this->GridFilter->xformFilter($f);

                //Strings
                if($f->type == 'string'){

                    if($f->field == 'realm'){
                        //Add a search clause
                        //Join the Radcheck table - only together with clause:
                        array_push($c['joins'],array(
                            'table'         => 'radcheck',
                            'alias'         => 'Radcheck_realm',
                            'type'          => 'LEFT',
                            'conditions'    => array('Radcheck_realm.username = Device.name')
                        )); 
                        array_push($c['conditions'],array(
                            'Radcheck_realm.attribute'  => 'Rd-Realm',
                            "Radcheck_realm.value LIKE" => '%'.$f->value.'%'
                        ));
                    }elseif($f->field == 'profile'){                       
                        //Add a search clause
                        //Join the Radcheck table - only together with clause:
                        array_push($c['joins'],array(
                            'table'         => 'radcheck',
                            'alias'         => 'Radcheck_profile',
                            'type'          => 'LEFT',
                            'conditions'    => array('Radcheck_profile.username = Device.name')
                        ));
                        array_push($c['conditions'],array(
                            'Radcheck_profile.attribute'  => 'User-Profile',
                            "Radcheck_profile.value LIKE" => '%'.$f->value.'%'
                        ));
                    }elseif($f->field == 'user'){
                        array_push($c['conditions'],array("PermanentUser.username LIKE" => '%'.$f->value.'%'));   
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

                 //Date
                if($f->type == 'date'){        
                    //date we want it in "2013-03-12"
                    $col = $this->modelClass.'.'.$f->field;
                    if($f->comparison == 'eq'){
                        array_push($c['conditions'],array("DATE($col)" => $f->value));
                    }

                    if($f->comparison == 'lt'){
                        array_push($c['conditions'],array("DATE($col) <" => $f->value));
                    }
                    if($f->comparison == 'gt'){
                        array_push($c['conditions'],array("DATE($col) >" => $f->value));
                    }
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
                        'model'         => 'Users', 
                        'foreign_key'   => $user_id), 
                        "Access Providers/Other Rights/View users or vouchers not created self")
                    ){
                        array_push($tree_array,array('PermanentUser.user_id' => $i_id));
                    }
                }else{
                    array_push($tree_array,array('PermanentUser.user_id' => $i_id));
                }   
            }
  
            //** ALL the AP's children
            $this->children    = $this->User->find_access_provider_children($user_id);
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $id = $i['id'];
                    array_push($tree_array,array('PermanentUser.user_id' => $id));
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

    private function _get_action_flags_for_devices($user,$owner_id,$realm){
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
                    'model'         => 'Users', 
                    'foreign_key'   => $user['id']), 
                    "Access Providers/Other Rights/View users or vouchers not created self")
                ){
                    $read = $this->Acl->check(
                                array('model' => 'Users', 'foreign_key' => $user['id']), 
                                array('model' => 'Realms','foreign_key' => $realm_id), 'read');
                }else{
                    $read = false; //Since the user is not the owner and they can not view other's vouchers we leave it out
                }  
            
                $update = $this->Acl->check(
                                array('model' => 'Users', 'foreign_key' => $user['id']), 
                                array('model' => 'Realms','foreign_key' => $realm_id), 'update');
                $delete = $this->Acl->check(
                                array('model' => 'Users', 'foreign_key' => $user['id']), 
                                array('model' => 'Realms','foreign_key' => $realm_id), 'delete');
                //Prime it                 
                $this->AclCache[$realm_id] =  array('update' => $update, 'delete' => $delete,'read' => $read);      
                return array('update' => $update, 'delete' => $delete,'read' => $read);
            }
        }
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
