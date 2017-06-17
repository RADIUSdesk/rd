<?php
App::uses('AppController', 'Controller');


//----- Some notes: ----------------------------
// When we add a NAS device, we have can either have the option that a user from ANY realm can (e.g. Eduroam users)
// connect through the NAS, or specify CERTAIN realms that is exclusively allowed to connect through the device
// so during the creation process we will show a list of realms to the person creating the NAS that they can select one or more realms
// Another influence on the list of realms which will be returned is the value of the 'available_to_siblings' flag of an NAS
// If the flag is set -> All downstream realms will be listed
// If not set all PUBLIC upstream realms will be listed...

class RealmsController extends AppController {


    public $name       = 'Realms';
    public $components = array('Aa','GridFilter');
    public $uses       = array('Realm','User');
    protected $base    = "Access Providers/Controllers/Realms/";

    //----- ACL Checks -----
    /*
        index_ap_create
        index_ap_update
        index_ap
        list_realms_for_nas_owner
        dummy_edit
        update_na_realm
        
        list_realms_for_dynamic_client_owner
        update_dynamic_client_realm

    //---- END ACL ------- */
    


//------------------------------------------------------------------------

    //____ Access Providers application ______

    public function index_ap_create(){
    //This method will display the Access Provider we are looking at's list of realms which it has create rights for
    //This will be used to display in the Wizard of available Realms in the Create screens of Vouchers; Permanent Users; and Devices

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
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
        
        $ap_id = false;
        if(isset($this->request->query['ap_id'])){
            $ap_id      = $this->request->query['ap_id'];
        }

        if(($admin_flag)&&($ap_id == false)){
            $r = $this->Realm->find('all');
            foreach($r as $j){
                $id     = $j['Realm']['id'];
                $name   = $j['Realm']['name'];
                array_push($items,array('id' => $id, 'name' => $name));
            }

        }else{
            //Access Providers needs more work...
            if($ap_id == false){
                $ap_id      = $user_id;
            }
            if($ap_id == 0){
                $ap_id = $user_id;
            }
            $q_r        = $this->User->getPath($ap_id); //Get all the parents up to the root   
                   
            foreach($q_r as $i){    
                $user_id    = $i['User']['id'];
                $this->Realm->contain();
                $r        = $this->Realm->find('all',array('conditions' => array('Realm.user_id' => $user_id, 'Realm.available_to_siblings' => true)));
                foreach($r  as $j){
                    $id     = $j['Realm']['id'];
                    $name   = $j['Realm']['name'];
                    $create = $this->Acl->check(
                                array('model' => 'User', 'foreign_key' => $ap_id), 
                                array('model' => 'Realm','foreign_key' => $id), 'create');
                    if($create == true){
                        array_push($items,array('id' => $id, 'name' => $name));
                    }
                }
            }

            //All the realms owned by anyone this access provider created (and also itself) 
            //will automatically be under full controll of this access provider
            
            $this->children    = $this->User->find_access_provider_children($ap_id);
            $tree_array     = array();
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $id = $i['id'];
                    array_push($tree_array,array('Realm.user_id' => $id));
                }       
            }  
            $this->Realm->contain();
            $r_sub  = $this->Realm->find('all',array('conditions' => array('OR' => $tree_array))); 
            foreach($r_sub  as $j){
                $id     = $j['Realm']['id'];
                $name   = $j['Realm']['name'];
                array_push($items,array('id' => $id, 'name' => $name));
            }
           
        }

        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    public function index_ap_update(){
    //This method will display the Access Provider we are looking at's list of realms which it has edit rights for
    //This will be used to display in the Wizard of available Realms in the Edit screens of Vouchers; Permanent Users; and Devices

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        $user_id    = null;
        $admin_flag = false;

        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $user_id = $user['id'];
        }

        if($user['group_name'] == Configure::read('group.ap')){  //Or AP
            $user_id = $user['id'];
            $admin_flag = true;
        }
        $items      = array();
        
        $ap_id = false;
        if(isset($this->request->query['ap_id'])){
            $ap_id      = $this->request->query['ap_id'];
        }

        if(($admin_flag)&&($ap_id == false)){

            $r = $this->Realm->find('all');
            foreach($r as $j){
                $id     = $j['Realm']['id'];
                $name   = $j['Realm']['name'];
                array_push($items,array('id' => $id, 'name' => $name));
            }

        }else{
            //Access Providers needs more work...
             if($ap_id == false){
                $ap_id      = $user_id;
            }
            
            if($ap_id == 0){
                $ap_id = $user_id;
            }
            $q_r        = $this->User->getPath($ap_id); //Get all the parents up to the root           
            foreach($q_r as $i){    
                $user_id    = $i['User']['id'];
                $this->Realm->contain();
                $r        = $this->Realm->find('all',array('conditions' => array('Realm.user_id' => $user_id, 'Realm.available_to_siblings' => true)));
                foreach($r  as $j){
                  //  print_r($j);
                    $id     = $j['Realm']['id'];
                    $name   = $j['Realm']['name'];
                    $create = $this->Acl->check(
                                array('model' => 'User', 'foreign_key' => $ap_id), 
                                array('model' => 'Realm','foreign_key' => $id), 'update');
                    if($create == true){
                        array_push($items,array('id' => $id, 'name' => $name));
                    }
                }
            }
   
            
            //All the realms owned by anyone this access provider created (and also itself) 
            //will automatically be under full controll of this access provider
            
            $this->children    = $this->User->find_access_provider_children($ap_id);
            $tree_array     = array();
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $id = $i['id'];
                    array_push($tree_array,array('Realm.user_id' => $id));
                }       
            }  
            $this->Realm->contain();
            $r_sub  = $this->Realm->find('all',array('conditions' => array('OR' => $tree_array))); 
            foreach($r_sub  as $j){
                $id     = $j['Realm']['id'];
                $name   = $j['Realm']['name'];
                array_push($items,array('id' => $id, 'name' => $name));
            }
            
        }

        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    public function index_ap(){
    //This method will display the Access Provider we are looking at's list of available realms.
    //This will be:
    // ALL the upstream AP's who has realms that is flagged as 'available_to_siblings' (And That's it :-))
    //The Access Provider creating a sub-provider can create a private realm that is not listed in any of the sub-provider's lists (but that is under the realms application)
    //We will also go through each of these ones to determine if the AP has CRUD access to the realm and reflect it in the feedback...

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        $user_id = null;
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $user_id = $user['id'];
        }

        if($user['group_name'] == Configure::read('group.ap')){  //Or AP
            $user_id = $user['id'];
        }
        $items      = array();

        if(isset($this->request->query['ap_id'])){
            $ap_id      = $this->request->query['ap_id'];

            //Get all the parents up to the root
            $q_r        = $this->User->getPath($ap_id); 
            foreach($q_r as $i){
                
                $user_id    = $i['User']['id'];
                $this->Realm->contain();
                $r        = $this->Realm->find('all',array('conditions' => array('Realm.user_id' => $user_id, 'Realm.available_to_siblings' => true)));
                foreach($r  as $j){
                    $id     = $j['Realm']['id'];
                    $name   = $j['Realm']['name'];
                    $create = $this->Acl->check(
                                array('model' => 'User', 'foreign_key' => $ap_id), 
                                array('model' => 'Realm','foreign_key' => $id), 'create');
                    $read   = $this->Acl->check(
                                array('model' => 'User', 'foreign_key' => $ap_id), 
                                array('model' => 'Realm','foreign_key' => $id), 'read');
                    $update = $this->Acl->check(
                                array('model' => 'User', 'foreign_key' => $ap_id), 
                                array('model' => 'Realm','foreign_key' => $id), 'update');
                    $delete = $this->Acl->check(
                                array('model' => 'User', 'foreign_key' => $ap_id), 
                                array('model' => 'Realm','foreign_key' => $id), 'delete');
                    array_push($items,array('id' => $id, 'name' => $name, 'create' => $create, 'read' => $read, 'update' => $update, 'delete' => $delete));
                }
            }

            
            //All the realms owned by anyone this access provider created (and also itself) 
            //will automatically be under full controll of this access provider
            $this->children    = $this->User->find_access_provider_children($ap_id);
            $tree_array     = array();
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $id = $i['id'];
                    array_push($tree_array,array('Realm.user_id' => $id));
                }       
            }  
            $this->Realm->contain();
            $r_sub  = $this->Realm->find('all',array('conditions' => array('OR' => $tree_array))); 
            foreach($r_sub  as $j){
                $id     = $j['Realm']['id'];
                $name   = $j['Realm']['name'];
                array_push($items,array('id' => $id, 'name' => $name, 'create' => true, 'read' => true, 'update' => true, 'delete' => true));
            }
              
        }  

        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    public function list_realms_for_nas_owner(){

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        $user_id = null;
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $user_id = $user['id'];
        }

        if($user['group_name'] == Configure::read('group.ap')){  //Or AP
            $user_id = $user['id'];
        }

        if(isset($this->request->query['owner_id'])){

            //Check if it was 0 -> which means it is the current user
            if($this->request->query['owner_id'] == 0){
                $owner_id = $user_id;
            }else{
                $owner_id = $this->request->query['owner_id'];
            }
        }

        if(isset($this->request->query['available_to_siblings'])){
            $a_to_s      = $this->request->query['available_to_siblings'];  
        }

        //By default nas_id not included
        $nas_id = false;
        if(isset($this->request->query['nas_id'])){
            $nas_id      = $this->request->query['nas_id'];  
        }

        //========== CLEAR FIRST CHECK =======
        //By default clear_flag is not included
        $clear_flag = false;
        if(isset($this->request->query['clear_flag'])){
            if($this->request->query['clear_flag'] == 'true'){
                $clear_flag = true;
            }
        }

        if($clear_flag){    //If we first need to remove previous associations!   
            $this->Realm->NaRealm->deleteAll(array('NaRealm.na_id' => $nas_id),false);
        }
        //========== END CLEAR FIRST CHECK =======

        $items = array();

        //if $a_to_s is false we need to find the chain upwards to root and seek the public realms
        if($a_to_s == 'false'){
            $this->User->contain();
            $q_r        = $this->User->getPath($owner_id, array('id'));
            foreach($q_r as $i){
                $user_id = $i['User']['id'];
                $this->Realm->contain('NaRealm.na_id');
                $q = $this->Realm->find('all',
                    array(  'conditions'    => array('Realm.available_to_siblings' => true,'Realm.user_id' => $user_id),
                            'fields'        => array('Realm.id','Realm.name')
                    ));
                foreach($q as $j){
                    $selected = false;
                    foreach($j['NaRealm'] as $nr){
                        if($nr['na_id'] == $nas_id){
                            $selected = true;
                        }
                    }
                    array_push($items,array('id' => $j['Realm']['id'], 'name' => $j['Realm']['name'],'selected' => $selected));                
                }

                //When it got down to the owner; also get the private realms
                if($user_id == $owner_id){
                    $this->Realm->contain('NaRealm.na_id');
                    $q = $this->Realm->find('all',
                    array(  'conditions'    => array('Realm.available_to_siblings' => false,'Realm.user_id' => $user_id),
                            'fields'        => array('Realm.id','Realm.name')
                    ));
                    foreach($q as $j){
                        $selected = false;
                        foreach($j['NaRealm'] as $nr){
                            if($nr['na_id'] == $nas_id){
                                $selected = true;
                            }
                        }
                        array_push($items,array('id' => $j['Realm']['id'], 'name' => $j['Realm']['name'],'selected' => $selected));                
                    }
                }
            }
        }

        //If $a_to_s is true, we neet to find the chain downwards to list ALL the realms of belonging to children of the owner
        if($a_to_s == 'true'){

            //First find all the realms beloning to the owner:
            $this->Realm->contain('NaRealm.na_id');
            $q = $this->Realm->find('all',
                array(  'conditions'    => array('Realm.user_id' => $owner_id),
                        'fields'        => array('Realm.id','Realm.name')
                ));
            foreach($q as $j){
                $selected = false;
                //Check if the nas is not already assigned to this realm
                if($nas_id){
                    foreach($j['NaRealm'] as $nr){
                        if($nr['na_id'] == $nas_id){
                            $selected = true;
                        }
                    }
                }
                array_push($items,array('id' => $j['Realm']['id'], 'name' => $j['Realm']['name'],'selected' => $selected));                
            }
            
            //Now get all the realms of the siblings of the owner
            $this->children    = $this->User->find_access_provider_children($owner_id);
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $user_id = $i['id'];
                    $this->Realm->contain('NaRealm.na_id');
                    $q = $this->Realm->find('all',
                        array(  'conditions'    => array('Realm.user_id' => $user_id),
                                'fields'        => array('Realm.id','Realm.name')
                        ));
                    foreach($q as $j){
                        $selected = false;
                        //Check if the nas is not already assigned to this realm
                        if($nas_id){
                            foreach($j['NaRealm'] as $nr){
                                if($nr['na_id'] == $nas_id){
                                    $selected = true;
                                }
                            }
                        }   
                        array_push($items,array('id' => $j['Realm']['id'], 'name' => $j['Realm']['name'],'selected' => $selected));                
                    }
                }       
            }    
        }
       
        $this->set(array(
            'items'     => $items,
            'success'   => true,
            '_serialize' => array('items','success')
        ));
    }

    public function dummy_edit(){
          $this->set(array(
            'items'     => array(),
            'success'   => true,
            '_serialize' => array('items','success')
        ));
    }

    public function update_na_realm(){

        if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if(isset($this->request->query['nas_id'])){
            $nas_id     = $this->request->query['nas_id'];
	        if(isset($this->data['id'])){   //Single item select
                $realm_id   = $this->data['id'];
                if($this->data['selected']){
                    $this->Realm->NaRealm->create();
                    $d['NaRealm']['na_id']      = $nas_id;
                    $d['NaRealm']['realm_id']   = $realm_id;
                    $this->Realm->NaRealm->save($d);
                }else{
                    $this->Realm->NaRealm->deleteAll(array('NaRealm.na_id' => $nas_id,'NaRealm.realm_id' => $realm_id), false);        
                }
            }else{                          //Assume multiple item select
                foreach($this->data as $d){
                    if(isset($d['id'])){   //Single item select
                        $realm_id   = $d['id'];
                        if($d['selected']){
                            $this->Realm->NaRealm->create();
                            $d['NaRealm']['na_id']      = $nas_id;
                            $d['NaRealm']['realm_id']   = $realm_id;
                            $this->Realm->NaRealm->save($d);
                        }else{
                            $this->Realm->NaRealm->deleteAll(array('NaRealm.na_id' => $nas_id,'NaRealm.realm_id' => $realm_id), false);        
                        }
                    }
                }
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }
    
    public function list_realms_for_dynamic_client_owner(){

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        $user_id = null;
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $user_id = $user['id'];
        }

        if($user['group_name'] == Configure::read('group.ap')){  //Or AP
            $user_id = $user['id'];
        }

        if(isset($this->request->query['owner_id'])){

            //Check if it was 0 -> which means it is the current user
            if($this->request->query['owner_id'] == 0){
                $owner_id = $user_id;
            }else{
                $owner_id = $this->request->query['owner_id'];
            }
        }

        if(isset($this->request->query['available_to_siblings'])){
            $a_to_s      = $this->request->query['available_to_siblings'];  
        }

        //By default nas_id not included
        $dynamic_client_id = false;
        if(isset($this->request->query['dynamic_client_id'])){
            $dynamic_client_id      = $this->request->query['dynamic_client_id'];  
        }

        //========== CLEAR FIRST CHECK =======
        //By default clear_flag is not included
        $clear_flag = false;
        if(isset($this->request->query['clear_flag'])){
            if($this->request->query['clear_flag'] == 'true'){
                $clear_flag = true;
            }
        }

        if($clear_flag){    //If we first need to remove previous associations!   
            $this->Realm->DynamicClientRealm->deleteAll(array('DynamicClientRealm.dynamic_client_id' => $dynamic_client_id),false);
        }
        //========== END CLEAR FIRST CHECK =======

        $items = array();

        //if $a_to_s is false we need to find the chain upwards to root and seek the public realms
        if($a_to_s == 'false'){
            $this->User->contain();
            $q_r        = $this->User->getPath($owner_id, array('id'));
            foreach($q_r as $i){
                $user_id = $i['User']['id'];
                $this->Realm->contain('DynamicClientRealm.dynamic_client_id');
                $q = $this->Realm->find('all',
                    array(  'conditions'    => array('Realm.available_to_siblings' => true,'Realm.user_id' => $user_id),
                            'fields'        => array('Realm.id','Realm.name')
                    ));
                foreach($q as $j){
                    $selected = false;
                    foreach($j['DynamicClientRealm'] as $nr){
                        if($nr['na_id'] == $dynamic_client_id){
                            $selected = true;
                        }
                    }
                    array_push($items,array('id' => $j['Realm']['id'], 'name' => $j['Realm']['name'],'selected' => $selected));                
                }

                //When it got down to the owner; also get the private realms
                if($user_id == $owner_id){
                    $this->Realm->contain('DynamicClientRealm.dynamic_client_id');
                    $q = $this->Realm->find('all',
                    array(  'conditions'    => array('Realm.available_to_siblings' => false,'Realm.user_id' => $user_id),
                            'fields'        => array('Realm.id','Realm.name')
                    ));
                    foreach($q as $j){
                        $selected = false;
                        foreach($j['DynamicClientRealm'] as $nr){
                            if($nr['dynamic_client_id'] == $dynamic_client_id){
                                $selected = true;
                            }
                        }
                        array_push($items,array('id' => $j['Realm']['id'], 'name' => $j['Realm']['name'],'selected' => $selected));                
                    }
                }
            }
        }

        //If $a_to_s is true, we neet to find the chain downwards to list ALL the realms of belonging to children of the owner
        if($a_to_s == 'true'){

            //First find all the realms beloning to the owner:
            $this->Realm->contain('DynamicClientRealm.dynamic_client_id');
            $q = $this->Realm->find('all',
                array(  'conditions'    => array('Realm.user_id' => $owner_id),
                        'fields'        => array('Realm.id','Realm.name')
                ));
            foreach($q as $j){
                $selected = false;
                //Check if the nas is not already assigned to this realm
                if($dynamic_client_id){
                    foreach($j['DynamicClientRealm'] as $nr){
                        if($nr['dynamic_client_id'] == $dynamic_client_id){
                            $selected = true;
                        }
                    }
                }
                array_push($items,array('id' => $j['Realm']['id'], 'name' => $j['Realm']['name'],'selected' => $selected));                
            }
            
            //Now get all the realms of the siblings of the owner
            $this->children    = $this->User->find_access_provider_children($owner_id);
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $user_id = $i['id'];
                    $this->Realm->contain('DynamicClientRealm.dynamic_client_id');
                    $q = $this->Realm->find('all',
                        array(  'conditions'    => array('Realm.user_id' => $user_id),
                                'fields'        => array('Realm.id','Realm.name')
                        ));
                    foreach($q as $j){
                        $selected = false;
                        //Check if the nas is not already assigned to this realm
                        if($dynamic_client_id){
                            foreach($j['DynamicClientRealm'] as $nr){
                                if($nr['dynamic_client_id'] == $dynamic_client_id){
                                    $selected = true;
                                }
                            }
                        }   
                        array_push($items,array('id' => $j['Realm']['id'], 'name' => $j['Realm']['name'],'selected' => $selected));                
                    }
                }       
            }    
        }
       
        $this->set(array(
            'items'     => $items,
            'success'   => true,
            '_serialize' => array('items','success')
        ));
    }
    
    
    public function update_dynamic_client_realm(){

        if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if(isset($this->request->query['dynamic_client_id'])){
            $dynamic_client_id     = $this->request->query['dynamic_client_id'];
	        if(isset($this->data['id'])){   //Single item select
                $realm_id   = $this->data['id'];
                if($this->data['selected']){
                    $this->Realm->DynamicClientRealm->create();
                    $d['DynamicClientRealm']['dynamic_client_id']   = $dynamic_client_id;
                    $d['DynamicClientRealm']['realm_id']            = $realm_id;
                    $this->Realm->DynamicClientRealm->save($d);
                }else{
                    $this->Realm->DynamicClientRealm->deleteAll(
                        array('DynamicClientRealm.dynamic_client_id' => $dynamic_client_id,'DynamicClientRealm.realm_id' => $realm_id),
                    false);        
                }
            }else{                          //Assume multiple item select
                foreach($this->data as $d){
                    if(isset($d['id'])){   //Single item select
                        $dynamic_client_id   = $d['id'];
                        if($d['selected']){
                            $this->Realm->DynamicClientRealm->create();
                            $d['DynamicClientRealm']['dynamic_client_id']   = $dynamic_client_id;
                            $d['DynamicClientRealm']['realm_id']            = $realm_id;
                            $this->Realm->DynamicClientRealm->save($d);
                        }else{
                            $this->Realm->DynamicClientRealm->deleteAll(
                                array('DynamicClientRealm.dynamic_client_id' => $dynamic_client_id,'DynamicClientRealm.realm_id' => $realm_id), 
                            false);        
                        }
                    }
                }
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }
    
    public function edit_ap(){

        //The ap_id who's realm rights HAS to be a sibling of the user who initiated the request
        //___AA Check Starts ___
        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }
        $user_id = null;
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $user_id = $user['id'];
        }elseif($user['group_name'] == Configure::read('group.ap')){  //Or AP
            $user_id = $user['id'];
        }else{
           $this->Aa->fail_no_rights($this);
           return;
        }
        //__ AA Check Ends ___


        if(isset($this->request->query['ap_id'])){

            //Make sure the $ap_id is a child of $user_id - perhaps we should sub-class the Behaviaour...
            //TODO Complete this check

            $ap_id  = $this->request->query['ap_id'];
            $id     = $this->request->data['id'];
            if($this->request->data['create'] == true){
                $this->Acl->allow(
                array('model' => 'User', 'foreign_key' => $ap_id), 
                array('model' => 'Realm','foreign_key' => $id), 'create');
            }else{
                $this->Acl->deny(
                array('model' => 'User', 'foreign_key' => $ap_id), 
                array('model' => 'Realm','foreign_key' => $id), 'create');
            } 

            if($this->request->data['read'] == true){
                $this->Acl->allow(
                array('model' => 'User', 'foreign_key' => $ap_id), 
                array('model' => 'Realm','foreign_key' => $id), 'read');
            }else{
                $this->Acl->deny(
                array('model' => 'User', 'foreign_key' => $ap_id), 
                array('model' => 'Realm','foreign_key' => $id), 'read');
            }

            if($this->request->data['update'] == true){
                $this->Acl->allow(
                array('model' => 'User', 'foreign_key' => $ap_id), 
                array('model' => 'Realm','foreign_key' => $id), 'update');
            }else{
                $this->Acl->deny(
                array('model' => 'User', 'foreign_key' => $ap_id), 
                array('model' => 'Realm','foreign_key' => $id), 'update');
            } 
            
            if($this->request->data['delete'] == true){
                $this->Acl->allow(
                array('model' => 'User', 'foreign_key' => $ap_id), 
                array('model' => 'Realm','foreign_key' => $id), 'delete');
            }else{
                $this->Acl->deny(
                array('model' => 'User', 'foreign_key' => $ap_id), 
                array('model' => 'Realm','foreign_key' => $id), 'delete');
            }  
        }

        $this->set(array(
            'items' => array(),
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }
    //____ END :: Access Providers application ______


//----------------------------------------------------------------------------


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
        $q_r        = $this->Realm->find('all',$c);

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
                    if($column_name == 'notes'){
                        $notes   = '';
                        foreach($i['RealmNote'] as $n){
                            if(!$this->_test_for_private_parent($n['Note'],$user)){
                                $notes = $notes.'['.$n['Note']['note'].']';    
                            }
                        }
                        array_push($csv_line,$notes);
                    }elseif($column_name =='owner'){
                        $owner_id       = $i['Realm']['user_id'];
                        $owner_tree     = $this->_find_parents($owner_id);
                        array_push($csv_line,$owner_tree); 
                    }else{
                        array_push($csv_line,$i['Realm']["$column_name"]);  
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

    //____ BASIC CRUD Realm Manager ________
    public function index(){
    //Display a list of realms with their owners
    //This will be dispalyed to the Administrator as well as Access Providers who has rights

         //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

		//Fields
		$fields  	= array(
			'id',		'name',			'phone',		'fax',			'cell',		'email',
			'url',		'street_no',	'street',		'town_suburb',	'city',		'country',
			'lat',		'lon',			'twitter',		'facebook',		'youtube',	'google_plus',
			'linkedin',	't_c_title',	't_c_content',	'available_to_siblings'	
		);
      
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

        $total              = $this->{$this->modelClass}->find('count',$c);       
        $q_r                = $this->{$this->modelClass}->find('all',$c_page);

        $items              = array();

        foreach($q_r as $i){
            //Create notes flag
            $notes_flag  = false;
            foreach($i['RealmNote'] as $rn){
                if(!$this->_test_for_private_parent($rn['Note'],$user)){
                    $notes_flag = true;
                    break;
                }
            }

            $owner_id       = $i['Realm']['user_id'];
            $owner_tree     = $this->_find_parents($owner_id);
            $action_flags   = $this->_get_action_flags($owner_id,$user);

			$row = array();
            foreach($fields as $field){
                if(array_key_exists($field,$i['Realm'])){
                    $row["$field"]= $i['Realm']["$field"];
                }
            }

			$row['owner']		= $owner_tree;
			$row['notes']		= $notes_flag;
			$row['update']		= $action_flags['update'];
			$row['delete']		= $action_flags['delete'];

			array_push($items,$row);
        }

        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            'totalCount' => $total,
            '_serialize' => array('items','success','totalCount')
        ));
    }

    public function index_for_filter(){
    //Display a list of realms with their owners
    //This will be dispalyed to the Administrator as well as Access Providers who has righs

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        $user_id = null;

        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $user_id = $user['id'];
        }

        if($user['group_name'] == Configure::read('group.ap')){  //Or AP
            $user_id = $user['id'];
        }

        //_____ ADMIN _____
        $items = array();
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $q_r = $this->Realm->find('all');

            //Init before the loop
            foreach($q_r as $i){
                $name   = $i['Realm']['name'];
                array_push($items,array(
                        'id'                    => $name, 
                        'text'                  => $name
                    ));
            }
        }

        //_____ AP _____
        if($user['group_name'] == Configure::read('group.ap')){  

            //If it is an Access Provider that requested this list; we should show:
            //1.) all those realms that he is allowed to use from parents with the available_to_sibling flag set (no edit or delete)
            //2.) all those he created himself (if any) (this he can manage, depending on his right)
            //3.) all his children -> check if they may have created any. (this he can manage, depending on his right)

            $this->Realm->contain();
            $q_r = $this->Realm->find('all');

            //Loop through this list. Only if $user_id is a sibling of $owner_id we will add it to the list
            $ap_child_count = $this->User->childCount($user_id);

            foreach($q_r as $i){        
                $name           = $i['Realm']['name'];
                $owner_id     = $i['Realm']['user_id'];
                $a_t_s          = $i['Realm']['available_to_siblings'];
                //Filter for parents and children
                //Realms of parent's can not be edited, where realms of childern can be edited
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($owner_id,$user_id)){ //Is the user_id an upstream parent of the AP
                        //Only those available to siblings:
                        if($a_t_s == 1){
                            array_push($items,array(
                                'id'                    => $name, 
                                'text'                  => $name
                            ));
                        }
                    }
                    if($ap_child_count != 0){ //See if this realm is perhaps not one of those created by a sibling of the Access Provider
                        if($this->_is_sibling_of($user_id,$owner_id)){ //Is the owner a downstream sibling of the AP - Full rights
                            array_push($items,array(
                                'id'                    => $name, 
                                'text'                  => $name
                            ));
                        }
                    }
                }

                //Created himself
                if($owner_id == $user_id){    
                    array_push($items,array(
                        'id'                    => $name, 
                        'text'                  => $name
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

        if(!$this->_ap_right_check()){
            return;
        }

        $user       = $this->Aa->user_for_token($this);
        $user_id    = $user['id'];

        //Get the owner's id
         if($this->request->data['user_id'] == '0'){ //This is the holder of the token - override '0'
            $this->request->data['user_id'] = $user_id;
        }

        //Make available to siblings check
        if(isset($this->request->data['available_to_siblings'])){
            $this->request->data['available_to_siblings'] = 1;
        }else{
            $this->request->data['available_to_siblings'] = 0;
        }
        
        if(isset($this->request->data['suffix_permanent_users'])){
            $this->request->data['suffix_permanent_users'] = 1;
        }else{
            $this->request->data['suffix_permanent_users'] = 0;
        }
        
        if(isset($this->request->data['suffix_vouchers'])){
            $this->request->data['suffix_vouchers'] = 1;
        }else{
            $this->request->data['suffix_vouchers'] = 0;
        }

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
                'message'   => array('message' => 'Could not create item'),
                '_serialize' => array('errors','success','message')
            ));
        }
	}


    public function edit() {

        if(!$this->_ap_right_check()){
            return;
        }

        //We will not modify user_id
        unset($this->request->data['user_id']);

        //Make available to siblings check
        if(isset($this->request->data['available_to_siblings'])){
            $this->request->data['available_to_siblings'] = 1;
        }else{
            $this->request->data['available_to_siblings'] = 0;
        }
        
        if(isset($this->request->data['suffix_permanent_users'])){
            $this->request->data['suffix_permanent_users'] = 1;
        }else{
            $this->request->data['suffix_permanent_users'] = 0;
        }
        
        if(isset($this->request->data['suffix_vouchers'])){
            $this->request->data['suffix_vouchers'] = 1;
        }else{
            $this->request->data['suffix_vouchers'] = 0;
        }

		if ($this->Realm->save($this->request->data)) {
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


    public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        if(!$this->_ap_right_check()){
            return;
        }

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id'];
            $this->Realm->id = $this->data['id'];
            $this->Realm->delete($this->Realm->id,true);
      
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                $this->Realm->id = $d['id'];
                $this->Realm->delete($this->Realm->id,true);
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
	}


     public function view(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

		//Fields
		$fields  	= array(
			'id',		'name',			'phone',		'fax',			'cell',		'email',
			'url',		'street_no',	'street',		'town_suburb',	'city',		'country',
			'lat',		'lon',			'twitter',		'facebook',		'youtube',	'google_plus',
			'linkedin',	't_c_title',	't_c_content',	'available_to_siblings',	'icon_file_name',
			'suffix',   'suffix_permanent_users',       'suffix_vouchers'
		);

        $items = array();
        if(isset($this->request->query['realm_id'])){
            $this->{$this->modelClass}->contain();
            $q_r = $this->{$this->modelClass}->findById($this->request->query['realm_id']);
            if($q_r){

		        foreach($fields as $field){
		            if(array_key_exists($field,$q_r['Realm'])){
		                $items["$field"]= $q_r['Realm']["$field"];
		            }
		        }
				$owner_tree                         = $this->_find_parents($q_r['Realm']['user_id']);
				$items['owner']                     = $owner_tree;
            }
        }
        
        $this->set(array(
            'data'     => $items,
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
    }

    public function upload_logo($id = null){

        //This is a deviation from the standard JSON serialize view since extjs requires a html type reply when files
        //are posted to the server.
        $this->layout = 'ext_file_upload';

        $path_parts     = pathinfo($_FILES['photo']['name']);
        $unique         = time();
        $dest           = IMAGES."realms/".$unique.'.'.$path_parts['extension'];
        $dest_www       = "/cake2/rd_cake/webroot/img/realms/".$unique.'.'.$path_parts['extension'];

        //Now add....
        $data['photo_file_name']  = $unique.'.'.$path_parts['extension'];
       
        $this->{$this->modelClass}->id = $this->request->data['id'];
       // $this->{$this->modelClass}->saveField('photo_file_name', $unique.'.'.$path_parts['extension']);
        if($this->{$this->modelClass}->saveField('icon_file_name', $unique.'.'.$path_parts['extension'])){
            move_uploaded_file ($_FILES['photo']['tmp_name'] , $dest);
            $json_return['id']                  = $this->{$this->modelClass}->id;
            $json_return['success']             = true;
            $json_return['icon_file_name']      = $unique.'.'.$path_parts['extension'];
        }else{
            $json_return['errors']      = $this->{$this->modelClass}->validationErrors;
            $json_return['message']     = array("message"   => __('Problem uploading photo'));
            $json_return['success']     = false;
        }
        $this->set('json_return',$json_return);
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
            $realm_id = $this->request->query['for_id'];
            $q_r    = $this->Realm->RealmNote->find('all', 
                array(
                    'contain'       => array('Note'),
                    'conditions'    => array('RealmNote.realm_id' => $realm_id)
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
        $this->Realm->RealmNote->Note->create(); 
        //print_r($this->request->data);
        if ($this->Realm->RealmNote->Note->save($this->request->data)) {
            $d                      = array();
            $d['RealmNote']['realm_id']   = $this->request->data['for_id'];
            $d['RealmNote']['note_id'] = $this->Realm->RealmNote->Note->id;
            $this->Realm->RealmNote->create();
            if ($this->Realm->RealmNote->save($d)) {
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
            $item       = $this->Realm->RealmNote->Note->findById($this->data['id']);
            $owner_id   = $item['Note']['user_id'];
            if($owner_id != $user_id){
                if($this->_is_sibling_of($user_id,$owner_id)== true){
                    $this->Realm->RealmNote->Note->id = $this->data['id'];
                    $this->Realm->RealmNote->Note->delete($this->data['id'],true);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->Realm->RealmNote->Note->id = $this->data['id'];
                $this->Realm->RealmNote->Note->delete($this->data['id'],true);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                $item       = $this->Realm->RealmNote->Note->findById($d['id']);
                $owner_id   = $item['Note']['user_id'];
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($user_id,$owner_id) == true){
                        $this->Realm->RealmNote->Note->id = $d['id'];
                        $this->Realm->RealmNote->Note->delete($d['id'],true);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->Realm->RealmNote->Note->id = $d['id'];
                    $this->Realm->RealmNote->Note->delete($d['id'],true);
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
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload',   'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit'))
                )),
                array('xtype' => 'buttongroup','title' => __('Document'), 'items' => array(
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnNote'),'scale' => 'large', 'itemId' => 'note',    'tooltip'=> __('Add notes')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnCsv'),'scale' => 'large', 'itemId' => 'csv',      'tooltip'=> __('Export CSV')),
                )),
                array('xtype' => 'buttongroup','title' => __('More'), 'items' => array(
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnGraph'),'scale' => 'large', 'itemId' => 'graph',    'tooltip'=> __('Graphs')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnCamera'),'scale' => 'large', 'itemId' => 'logo',     'tooltip'=> __('Edit logo')),
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
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'edit')){
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

            $menu = array(
                        array('xtype' => 'buttongroup','title' => __('Action'),        'items' => $action_group),
                        array('xtype' => 'buttongroup','title' => __('Document'),   'items' => $document_group),
                        array('xtype' => 'buttongroup','title' => __('More'), 'items' => array(
                            array(
                                'xtype'     => 'button', 
                                'glyph'     => Configure::read('icnGraph'),
                                'scale'     => 'large', 
                                'itemId'    => 'graph',    
                                'tooltip'   => __('Graphs')
                            ),
                            array(
                                'xtype'     => 'button', 
                                'glyph'     => Configure::read('icnCamera'),
                                'scale'     => 'large', 
                                'itemId'    => 'logo',     
                                'tooltip'   => __('Edit logo')
                            ),
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
                            'RealmNote'    => array('Note.note','Note.id','Note.available_to_siblings','Note.user_id'),
                            'User'
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'Realm.name';
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
        //If the user is an AP; we need to add an extra clause to only show the Realms which he is allowed to see.
        if($user['group_name'] == Configure::read('group.ap')){  //AP
            $tree_array = array();
            $this->User = ClassRegistry::init('User');
            $user_id    = $user['id'];

            //**AP and upward in the tree**
            $this->parents = $this->User->getPath($user_id,'User.id');
            //So we loop this results asking for the parent nodes who have available_to_siblings = true
            foreach($this->parents as $i){
                $i_id = $i['User']['id'];
                if($i_id != $user_id){ //upstream
                    array_push($tree_array,array($this->modelClass.'.user_id' => $i_id,$this->modelClass.'.available_to_siblings' => true));
                }else{
                    array_push($tree_array,array('Realm.user_id' => $i_id));
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
