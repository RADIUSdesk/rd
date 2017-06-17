<?php
App::uses('AppController', 'Controller');

class ProfilesController extends AppController {

    public $name        = 'Profiles';
    public $components  = array('Aa','GridFilter');
    public $uses        = array('Profile','User');
    protected $base     = "Access Providers/Controllers/Profiles/";
    protected $itemNote = 'ProfileNote';

//------------------------------------------------------------------------


    //____ Access Provider _________
    public function index_ap(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $user_id    = null;
        $admin_flag = false;

        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $user_id    = $user['id'];
            $admin_flag = true;
        }

        if($user['group_name'] == Configure::read('group.ap')){  //Or AP
            $user_id = $user['id'];
        }
        $items      = array();

        if($admin_flag){
            $this->Profile->contain(array('Radusergroup'  => array('Radgroupcheck')));
            $r = $this->Profile->find('all');
            foreach($r as $j){
                $id     = $j['Profile']['id'];
                $name   = $j['Profile']['name'];
                $data_cap_in_profile = false; 
                $time_cap_in_profile = false; 
                foreach($j['Radusergroup'] as $cmp){
                    foreach($cmp['Radgroupcheck'] as $chk){
                      //  print_r($chk);
                        if($chk['attribute'] == 'Rd-Reset-Type-Data'){
                            $data_cap_in_profile = true;
                        }
                        if($chk['attribute'] == 'Rd-Reset-Type-Time'){
                            $time_cap_in_profile = true;
                        }
                    } 
                    unset($cmp['Radgroupcheck']);
                }
                array_push($items,
                    array(
                        'id'                    => $id, 
                        'name'                  => $name,
                        'data_cap_in_profile'   => $data_cap_in_profile,
                        'time_cap_in_profile'   => $time_cap_in_profile
                    )
                );
            }

        }else{
            //Access Providers needs more work...
            if(isset($this->request->query['ap_id'])){
                $ap_id      = $this->request->query['ap_id'];
                if($ap_id == 0){
                    $ap_id = $user_id;
                }
                $q_r        = $this->User->getPath($ap_id); //Get all the parents up to the root           
                foreach($q_r as $i){               
                    $user_id    = $i['User']['id'];
                    $this->Profile->contain(array('Radusergroup'  => array('Radgroupcheck')));
                    $r        = $this->Profile->find('all',array('conditions' => array('Profile.user_id' => $user_id, 'Profile.available_to_siblings' => true)));
                    foreach($r  as $j){
                        $id     = $j['Profile']['id'];
                        $name   = $j['Profile']['name'];

                        $data_cap_in_profile = false; 
                        $time_cap_in_profile = false; 
                        foreach($j['Radusergroup'] as $cmp){
							if(array_key_exists('Radgroupcheck',$cmp)){
		                        foreach($cmp['Radgroupcheck'] as $chk){
		                            if($chk['attribute'] == 'Rd-Reset-Type-Data'){
		                                $data_cap_in_profile = true;
		                            }
		                            if($chk['attribute'] == 'Rd-Reset-Type-Time'){
		                                $time_cap_in_profile = true;
		                            }
		                        } 
		                        unset($cmp['Radgroupcheck']);
							}
                        }
                        
                        array_push($items,
                            array(
                                'id'                    => $id, 
                                'name'                  => $name,
                                'data_cap_in_profile'   => $data_cap_in_profile,
                                'time_cap_in_profile'   => $time_cap_in_profile
                            )
                        );
                    }
                }
				//----------------
				//FIXME: There might be more of the hierarchical things that needs this add-on 
				//We also need to list all the Profiles for this Access Provider NOT available to siblings
				//------------------
				$r        = $this->Profile->find('all',array('conditions' => array('Profile.user_id' => $ap_id, 'Profile.available_to_siblings' => false)));
                foreach($r  as $j){
                    $id     = $j['Profile']['id'];
                    $name   = $j['Profile']['name'];

                    $data_cap_in_profile = false; 
                    $time_cap_in_profile = false; 
                    foreach($j['Radusergroup'] as $cmp){
						if(array_key_exists('Radgroupcheck',$cmp)){
		                    foreach($cmp['Radgroupcheck'] as $chk){
		                        if($chk['attribute'] == 'Rd-Reset-Type-Data'){
		                            $data_cap_in_profile = true;
		                        }
		                        if($chk['attribute'] == 'Rd-Reset-Type-Time'){
		                            $time_cap_in_profile = true;
		                        }
		                    } 
		                    unset($cmp['Radgroupcheck']);
						}
                    }
                    
                    array_push($items,
                        array(
                            'id'                    => $id, 
                            'name'                  => $name,
                            'data_cap_in_profile'   => $data_cap_in_profile,
                            'time_cap_in_profile'   => $time_cap_in_profile
                        )
                    );
				}
            }

        }
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

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
            foreach($i['ProfileNote'] as $nn){
                if(!$this->_test_for_private_parent($nn['Note'],$user)){
                    $notes_flag = true;
                    break;
                }
            }

            $owner_id       = $i['Profile']['user_id'];
            $owner_tree     = $this->_find_parents($owner_id);
            $action_flags   = $this->_get_action_flags($owner_id,$user);

            //Add the components (already from the highest priority
            $components = array();
            $data_cap_in_profile = false; // A flag that will be set if the profile contains a component with Rd-Reset-Type-Data group check attribute.
            $time_cap_in_profile = false; // A flag that will be set if the profile contains a component with Rd-Reset-Type-Time group check attribute.
            foreach($i['Radusergroup'] as $cmp){
                foreach($cmp['Radgroupcheck'] as $chk){
                    if($chk['attribute'] == 'Rd-Reset-Type-Data'){
                        $data_cap_in_profile = true;
                    }
                    if($chk['attribute'] == 'Rd-Reset-Type-Time'){
                        $time_cap_in_profile = true;
                    }
                } 
                unset($cmp['Radgroupcheck']);
                array_push($components,$cmp);
            }

            array_push($items,array(
                'id'                    => $i['Profile']['id'], 
                'name'                  => $i['Profile']['name'],
                'owner'                 => $owner_tree, 
                'available_to_siblings' => $i['Profile']['available_to_siblings'],
                'profile_components'    => $components,
                'data_cap_in_profile'   => $data_cap_in_profile,
                'time_cap_in_profile'   => $time_cap_in_profile,
                'notes'                 => $notes_flag,
                'update'                => $action_flags['update'],
                'delete'                => $action_flags['delete']
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

    //____ BASIC CRUD Manager ________
    public function index_for_filter(){
    //Display a list of items with their owners
    //This will be dispalyed to the Administrator as well as Access Providers who has righs

       //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];


        //_____ ADMIN _____
        $items = array();
        if($user['group_name'] == Configure::read('group.admin')){  //Admin

            $this->Profile->contain();
            $q_r = $this->Profile->find('all');

            foreach($q_r as $i){   
                array_push($items,array(
                    'id'            => $i['Profile']['name'], 
                    'text'          => $i['Profile']['name']
                ));
            }
        }

        //_____ AP _____
        if($user['group_name'] == Configure::read('group.ap')){  

            //If it is an Access Provider that requested this list; we should show:
            //1.) all those NAS devices that he is allowed to use from parents with the available_to_sibling flag set (no edit or delete)
            //2.) all those he created himself (if any) (this he can manage, depending on his right)
            //3.) all his children -> check if they may have created any. (this he can manage, depending on his right)
       
            $q_r = $this->Profile->find('all');

            //Loop through this list. Only if $user_id is a sibling of $creator_id we will add it to the list
            $ap_child_count = $this->User->childCount($user_id);

            foreach($q_r as $i){
                $add_flag   = false;
                $owner_id   = $i['Profile']['user_id'];
                $a_t_s      = $i['Profile']['available_to_siblings'];
                $add_flag   = false;
                
                //Filter for parents and children
                //NAS devices of parent's can not be edited, where realms of childern can be edited
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($owner_id,$user_id)){ //Is the user_id an upstream parent of the AP
                        //Only those available to siblings:
                        if($a_t_s == 1){
                            $add_flag = true;
                        }
                    }
                }

                if($ap_child_count != 0){ //See if this NAS device is perhaps not one of those created by a sibling of the Access Provider
                    if($this->_is_sibling_of($user_id,$owner_id)){ //Is the creator a downstream sibling of the AP - Full rights
                        $add_flag = true;
                    }
                }

                //Created himself
                if($owner_id == $user_id){
                    $add_flag = true;
                }

                if($add_flag == true ){
                    $owner_tree = $this->_find_parents($owner_id);                      
                    //Add to return items
                    array_push($items,array(
                        'id'            => $i['Profile']['name'], 
                        'text'          => $i['Profile']['name']
                    ));
                }
            }
        }

        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
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


    public function manage_components() {
    
         //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id        = $user['id'];

        $rb             = $this->request->data['rb']; 

        if(($rb == 'add')||($rb == 'remove')){
            $component_id   = $this->request->data['component_id'];
            $this->ProfileComponent = ClassRegistry::init('ProfileComponent');
            $q_r    = $this->ProfileComponent->findById($component_id);
            $component_name = $q_r['ProfileComponent']['name'];
        }

        foreach(array_keys($this->request->data) as $key){
            if(preg_match('/^\d+/',$key)){

                if($rb == 'sub'){
                    $this->Profile->id = $key;
                    $this->Profile->saveField('available_to_siblings', 1);
                }

                if($rb == 'no_sub'){
                    $this->Profile->id = $key;
                    $this->Profile->saveField('available_to_siblings', 0);
                }

                if($rb == 'remove'){
                    $q_r            = $this->Profile->findById($key);
                    $profile_name   = $q_r['Profile']['name'];
                    $this->{$this->modelClass}->Radusergroup->deleteAll(
                        array('Radusergroup.username' => $profile_name,'Radusergroup.groupname' => $component_name), false
                    );
                }
               
                if($rb == 'add'){
                    $q_r            = $this->Profile->findById($key);
                    $profile_name   = $q_r['Profile']['name'];
                    $this->{$this->modelClass}->Radusergroup->deleteAll(   //Delete a previous one
                        array('Radusergroup.username' => $profile_name,'Radusergroup.groupname' => $component_name), false
                    );
                    $d = array();
                    $d['username']  = $profile_name;
                    $d['groupname'] = $component_name;
                    $d['priority']  = $this->request->data['priority'];
                    $this->{$this->modelClass}->Radusergroup->create();
                    $this->{$this->modelClass}->Radusergroup->save($d);
                }     
                //-------------
            }
        }

        $this->set(array(
            'success'       => true,
            '_serialize'    => array('success')
        ));
       
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
            $owner_id       = $item['Profile']['user_id'];
            $profile_name   = $item['Profile']['name'];
            if($owner_id != $user_id){
                if($this->_is_sibling_of($user_id,$owner_id)== true){
                    $this->{$this->modelClass}->id = $this->data['id'];
                    $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                    $this->{$this->modelClass}->Radusergroup->deleteAll(   //Delete a previous one
                        array('Radusergroup.username' => $profile_name), false
                    );
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->{$this->modelClass}->id = $this->data['id'];
                $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                $this->{$this->modelClass}->Radusergroup->deleteAll(   //Delete a previous one
                    array('Radusergroup.username' => $profile_name), false
                );
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                $item           = $this->{$this->modelClass}->findById($d['id']);
                $owner_id       = $item['Profile']['user_id'];
                $profile_name   = $item['Profile']['name'];
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($user_id,$owner_id) == true){
                        $this->{$this->modelClass}->id = $d['id'];
                        $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                        $this->{$this->modelClass}->Radusergroup->deleteAll(   //Delete a previous one
                            array('Radusergroup.username' => $profile_name), false
                        );
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->{$this->modelClass}->id = $d['id'];
                    $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                    $this->{$this->modelClass}->Radusergroup->deleteAll(   //Delete a previous one
                        array('Radusergroup.username' => $profile_name), false
                    );
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
                    'conditions'    => array('ProfileNote.profile_id' => $pc_id)
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
            $d['ProfileNote']['profile_id']   = $this->request->data['for_id'];
            $d['ProfileNote']['note_id'] = $this->{$this->modelClass}->ProfileNote->Note->id;
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
                $this->{$this->modelClass}->ProfileNote->Note->id = $this->data['id'];
                $this->{$this->modelClass}->ProfileNote->Note->delete($this->data['id'],true);
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
                    array('xtype' => 'button', 'iconCls' => 'b-reload',  'glyph'     => Configure::read('icnReload'), 'scale' => 'large', 'itemId' => 'reload',   'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'iconCls' => 'b-add',     'glyph'     => Configure::read('icnAdd'), 'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'iconCls' => 'b-delete',  'glyph'     => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'iconCls' => 'b-edit',    'glyph'     => Configure::read('icnEdit'), 'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit'))
                )),
                array('xtype' => 'buttongroup','title' => __('Document'), 'width' => 100, 'items' => array(
                    array('xtype' => 'button', 'iconCls' => 'b-note',     'glyph'     => Configure::read('icnNote'), 'scale' => 'large', 'itemId' => 'note',    'tooltip'=> __('Add notes')),
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
                'xtype'     => 'button',
                'iconCls'   => 'b-reload',
                'glyph'     => Configure::read('icnReload'),   
                'scale'     => 'large', 
                'itemId'    => 'reload',
                'tooltip'   => __('Reload')));

            //Add
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base."add")){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-add',
                    'glyph'     => Configure::read('icnAdd'),      
                    'scale'     => 'large', 
                    'itemId'    => 'add',    
                    'tooltip'   => __('Add')));
            }
            //Delete
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'delete')){
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
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'manage_components')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-edit',
                    'glyph'     => Configure::read('icnEdit'),     
                    'scale'     => 'large', 
                    'itemId'    => 'edit',
                    'disabled'  => true,   
                    'tooltip'   => __('Edit')));
            }

            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'note_index')){ 
                array_push($document_group,array(
                        'xtype'     => 'button', 
                        'iconCls'   => 'b-note',
                        'glyph'     => Configure::read('icnNote'),      
                        'scale'     => 'large', 
                        'itemId'    => 'note',  
                        'tooltip'   => __('Add Notes')));
            }
/*
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'export_csv')){ 
                array_push($document_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-csv',     
                    'scale'     => 'large', 
                    'itemId'    => 'csv',      
                    'tooltip'   => __('Export CSV')));
            }
*/
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
                            'ProfileNote'    => array('Note.note','Note.id','Note.available_to_siblings','Note.user_id'),
                            'User',
                            'Radusergroup'  => array('Radgroupcheck')
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'Profile.name';
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
                    array_push($tree_array,array('Profile.user_id' => $i_id));
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

}
