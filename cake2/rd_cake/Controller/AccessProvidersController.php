<?php
App::uses('AppController', 'Controller');

class AccessProvidersController extends AppController {

    public $name       = 'AccessProviders';
    public $components = array('Aa','GridFilter');
    public $uses       = array('User'); //This has primaraly to do with users :-)

    public $ap_acl     = 'Access Providers/Controllers/'; 
    protected $base    = "Access Providers/Controllers/AccessProviders/";   //This is required for Aa component  

    protected $ap_children  = array(); 


    //-- NOTES on users:
    //-- Each user belongs to (A) group (group_id) = Permanent Users. (B) a realm (realm_id) (C) a creator (user_id)
    //-- (D) Profile ID (profile_id) (E) a Language ID (language_id) (F) an Auth Method id (auth_method_id)

    //-- Each user will have a token which should be used in the URL to do Ajax calls

    //-- NOTES on rights:
    //-- Most controller actions will require a token in the query string to determine who originated the request
    //-- The rights of that person will then be checked and also against who it is attempting to be done.

    //-- Notes on AccessProviders
    //--APs can be created by Administrators or also by other Access Providers (provided they have the corrrect rights)
    //--An AP can view all those AP's he created


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
                        foreach($i['UserNote'] as $n){
                            if(!$this->_test_for_private_parent($n['Note'],$user)){
                                $notes = $notes.'['.$n['Note']['note'].']';    
                            }
                        }
                        array_push($csv_line,$notes);
                    }elseif($column_name =='owner'){
                        $owner_id       = $i['User']['parent_id'];
                        $owner_tree     = $this->_find_parents($owner_id);
                        array_push($csv_line,$owner_tree); 
                    }else{
                        array_push($csv_line,$i['User']["$column_name"]);  
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

        $items  = array();
        foreach($q_r as $i){ 

            $owner_id       = $i['Owner']['id'];
            $owner_tree     = $this->_find_parents($owner_id);

            //Create notes flag
            $notes_flag  = false;
            foreach($i['UserNote'] as $un){
                if(!$this->_test_for_private_parent($un['Note'],$user)){
                    $notes_flag = true;
                    break;
                }
            }

            array_push($items,
                array(
                    'id'        => $i['User']['id'], 
                    'owner'     => $owner_tree,
                    'username'  => $i['User']['username'],
                    'name'      => $i['User']['name'],
                    'surname'   => $i['User']['surname'], 
                    'phone'     => $i['User']['phone'], 
                    'email'     => $i['User']['email'],
                    'address'   => $i['User']['address'],
                    'active'    => $i['User']['active'], 
                    'monitor'   => $i['User']['monitor'],
                    'notes'     => $notes_flag
                )
            );
        }                
        $this->set(array(
            'items'         => $items,
            'success'       => true,
            'totalCount'    => $total,
            '_serialize'    => array('items','success','totalCount')
        ));
    }

    public function index_tree(){
        //-- Required query attributes: token;
        //-- Optional query attribute: sel_language (for i18n error messages)
        //-- also LIMIT: limit, page, start (optional - use sane defaults)
        //-- FILTER <- This will need fine tunning!!!!
        //-- AND SORT ORDER <- This will need fine tunning!!!!

       // $this->User->recover();

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

        $items = array();
    
        //If id is not set return an empty list:
        if($user_id != null){
            if(isset($this->request->query['node'])){
                if($this->request->query['node'] == 0){ //This is the root node.
                    $id = $user_id;
                }else{
                    $id = $this->request->query['node'];
                }
            }         
            //We only will list the first level of nodes
            $ap_name = Configure::read('group.ap');
            $q_r    = $this->User->find('all',array('conditions' => array('User.parent_id' => $id,'Group.name' => $ap_name  )));
            foreach($q_r as $i){
                $id         = $i['User']['id'];
                $parent_id  = $i['User']['parent_id'];
                $username   = $i['User']['username'];
               
                $leaf       = false;
                $icon       = 'users';

                //We do not use childcount since the admin or ap can also create users
                $child_count= $this->User->find('count',array('conditions' => array('User.parent_id' => $id,'Group.name' => $ap_name  )));
                if($child_count == 0){
                    $leaf = true;
                    $icon = 'user';
                }
                array_push($items,
                    array('id' => $id, 'username' => $username,'leaf' => $leaf,'iconCls' => $icon)
                ); 
            }
        }     
            
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));

    }


    public function add(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $this->request['active']       = 0;
        $this->request['monitor']      = 0;     


        //Two fields should be tested for first:
        if(array_key_exists('active',$this->request->data)){
            $this->request->data['active'] = 1;
        }

        if(array_key_exists('monitor',$this->request->data)){
            $this->request->data['monitor'] = 1;
        }

        if($this->request->data['parent_id'] == '0'){ //This is the holder of the token
            $this->request->data['parent_id'] = $user['id'];
        }

        //Get the language and country
        $country_language   = explode( '_', $this->request->data['language'] );

        $country            = $country_language[0];
        $language           = $country_language[1];

        $this->request->data['language_id'] = $language;
        $this->request->data['country_id']  = $country;

        //Get the group ID for AP's
        $ap_name    = Configure::read('group.ap');
        $q_r        = ClassRegistry::init('Group')->find('first',array('conditions' =>array('Group.name' => $ap_name)));
        $group_id   = $q_r['Group']['id'];
        $this->request->data['group_id'] = $group_id;

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

    //Extjs specific workaround for treestore that has a defined model
    public function dummy_delete(){
        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    public function delete(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id'];
            $this->User->id = $this->data['id'];
            $this->User->delete();
            $this->User->recover();     //This is a (potential) ugly hack since the left and right values is not calculated correct due to the Acl behaviour
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                $this->User->id = $d['id'];
                $this->User->delete();
                $this->User->recover(); //This is a (potential) ugly hack since the left and right values is not calculated correct due to the Acl behaviour
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

        $items = array();
        if(isset($this->request->query['ap_id'])){
            $this->{$this->modelClass}->contain();
            $q_r = $this->{$this->modelClass}->findById($this->request->query['ap_id']);
            if($q_r){
                $owner_tree         = $this->_find_parents($q_r['User']['parent_id']);
                $items['id']        = $q_r['User']['id'];
                $items['username']  = $q_r['User']['username'];
                $items['name']      = $q_r['User']['name'];
                $items['surname']   = $q_r['User']['surname'];
                $items['phone']     = $q_r['User']['phone'];
                $items['address']   = $q_r['User']['address'];
                $items['email']     = $q_r['User']['email'];
                $items['active']    = $q_r['User']['active'];
                $items['monitor']   = $q_r['User']['monitor'];
                $items['owner']     = $owner_tree;
                $language           = $q_r['User']['country_id'].'_'.$q_r['User']['language_id'];
                $items['language']  = $language;
            }
        }
        
        $this->set(array(
            'data'     => $items,
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));


    }

    public function edit(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        //We need to unset a few of the values submitted:
        unset($this->request->data['token']);
        unset($this->request->data['password']);
        unset($this->request->data['parent_id']);

        //Monitor checkbox
        if(isset($this->request->data['monitor'])){
            $this->request->data['monitor'] = 1;
        }else{
            $this->request->data['monitor'] = 0;
        }

         //Active checkbox
        if(isset($this->request->data['active'])){
            $this->request->data['active'] = 1;
        }else{
            $this->request->data['active'] = 0;
        }

        //Get the language and country
        $country_language   = explode( '_', $this->request->data['language'] );

        $country            = $country_language[0];
        $language           = $country_language[1];

        $this->request->data['language_id'] = $language;
        $this->request->data['country_id']  = $country;

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


     public function change_password(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $success = false;

        if(isset($this->request->data['user_id'])){
            $d           = array();
            $d['User']['id']        = $this->request->data['user_id'];
            $d['User']['password']  = $this->request->data['password'];
            $d['User']['token']     = '';
            $this->{$this->modelClass}->id  = $this->request->data['user_id'];
            $this->{$this->modelClass}->save($d);
            $success               = true;  
        }

        $this->set(array(
            'success' => $success,
            '_serialize' => array('success',)
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
        if($rb == 'enable'){
            $d['User']['active'] = 1;
        }else{
            $d['User']['active'] = 0;
        }

        foreach(array_keys($this->request->data) as $key){
            if(preg_match('/^\d+/',$key)){
                $d['User']['id']                = $key;
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
            $q_r    = $this->User->UserNote->find('all', 
                array(
                    'contain'       => array('Note'),
                    'conditions'    => array('UserNote.user_id' => $u_id)
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
        $this->User->UserNote->Note->create(); 
        //print_r($this->request->data);
        if ($this->User->UserNote->Note->save($this->request->data)) {
            $d                          = array();
            $d['UserNote']['user_id']   = $this->request->data['for_id'];
            $d['UserNote']['note_id']   = $this->User->UserNote->Note->id;
            $this->User->UserNote->create();
            if ($this->User->UserNote->save($d)) {
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
        $this->User = ClassRegistry::init('User');
        $fail_flag  = false;

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id'];

            //NOTE: we first check of the user_id is the logged in user OR a sibling of them:   
            $item       = $this->User->UserNote->Note->findById($this->data['id']);
            $owner_id   = $item['Note']['user_id'];
            if($owner_id != $user_id){
                if($this->_is_sibling_of($user_id,$owner_id)== true){
                    $this->User->UserNote->Note->id = $this->data['id'];
                    $this->User->UserNote->Note->delete($this->data['id'],true);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->User->UserNote->Note->id = $this->data['id'];
                $this->User->UserNote->Note->delete($this->data['id'],true);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                $item       = $this->User->UserNote->Note->findById($d['id']);
                $owner_id   = $item['Note']['user_id'];
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($user_id,$owner_id) == true){
                        $this->User->UserNote->Note->id = $d['id'];
                        $this->User->UserNote->Note->delete($d['id'],true);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->User->UserNote->Note->id = $d['id'];
                    $this->User->UserNote->Note->delete($d['id'],true);
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
                    array('xtype' => 'button', 'iconCls' => 'b-reload', 'glyph' => Configure::read('icnReload'), 'scale' => 'large', 'itemId' => 'reload',   'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'iconCls' => 'b-add',    'glyph' => Configure::read('icnAdd'),    'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'iconCls' => 'b-delete', 'glyph' => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    
                    array('xtype' => 'button', 'iconCls' => 'b-edit',   'glyph' => Configure::read('icnEdit'),   'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')),
                    
                )),
                array('xtype' => 'buttongroup','title' => __('Document'), 'items' => array(
                    array('xtype' => 'button', 'iconCls' => 'b-note',    'glyph' => Configure::read('icnNote'), 'scale' => 'large', 'itemId' => 'note',    'tooltip'=> __('Add notes')),
                    array('xtype' => 'button', 'iconCls' => 'b-csv',     'glyph' => Configure::read('icnCsv'), 'scale' => 'large', 'itemId' => 'csv',      'tooltip'=> __('Export CSV')),
                )),
                array('xtype' => 'buttongroup','title' => __('Extra actions'), 'items' => array(
                    array('xtype' => 'button', 'iconCls' => 'b-password', 'glyph' => Configure::read('icnLock'), 'scale' => 'large', 'itemId' => 'password', 'tooltip'=> __('Change Password')),
                    array('xtype' => 'button', 'iconCls' => 'b-disable',  'glyph' => Configure::read('icnLight'),'scale' => 'large', 'itemId' => 'enable_disable','tooltip'=> __('Enable / Disable'))
               
                ))
            );
        }

        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $action_group   = array();
            $document_group = array();
            $specific_group = array();

            $base   = "Access Providers/Controllers/AccessProviders/";
            array_push($action_group,array(  
                'xtype'     => 'button',
                'iconCls'   => 'b-reload',
                'glyph'     => Configure::read('icnReload'),  
                'scale'     => 'large', 
                'itemId'    => 'reload',   
                'tooltip'   => __('Reload')));

            //Add
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."add")){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-add',  
                    'glyph'     => Configure::read('icnAdd'),
                    'scale'     => 'large', 
                    'itemId'    => 'add',      
                    'tooltip'   => __('Add')));
            }
            //Delete
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base.'delete')){
                 array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-delete',
                    'glyph'     => Configure::read('icnDelete'), 
                    'scale'     => 'large', 
                    'itemId'    => 'delete',
                    'tooltip'   => __('Delete')));
            }

            //Edit
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base.'edit')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-edit',
                    'glyph'     => Configure::read('icnEdit'),    
                    'scale'     => 'large', 
                    'itemId'    => 'edit',    
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

            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'export_csv')){ 
                array_push($document_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-csv',
                    'glyph'     => Configure::read('icnCsv'),     
                    'scale'     => 'large', 
                    'itemId'    => 'csv',      
                    'tooltip'   => __('Export CSV')));
            }

           if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base.'change_password')){      
                array_push($specific_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-password',
                    'glyph'     => Configure::read('icnLock'),
                    'scale'     => 'large', 
                    'itemId'    => 'password', 
                    'tooltip'   => __('Change Password')));
            }
            
           if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base.'enable_disable')){      
                array_push($specific_group, array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-disable',
                    'glyph'     => Configure::read('icnLight'),
                    'scale'     => 'large', 
                    'itemId'    => 'enable_disable',
                    'tooltip'   => __('Enable / Disable')));
            }

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

    public function record_activity_checkbox(){

        //Status: DONE
        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }
        $items = array('checked' => 'true', 'disabled' => true);
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $items = array('checked' => true, 'disabled' => false);
        }

        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)

            //This is an Access Provider; we need to check the rights:
            $right_to_check = "Access Providers/Other Rights/Can disable activity recording";

            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $user['id']),$right_to_check)){  //The user can adjust recording

                if($user['monitor'] == 0){
                    $items = array('checked' => 'false', 'disabled' => false);   //The parent does not record, the child either
                }else{
                    $items = array('checked' => 'true', 'disabled' => false);   //The parent records, the child also
                }
         
            }else{
                //Inherit from creator
                
                if($user['monitor'] == 0){
                    $items = array('checked' => 'false', 'disabled' => true);   //The parent does not record, the child either
                }else{
                    $items = array('checked' => 'true', 'disabled' => true);   //The parent records, the child also
                }
            }
        }
        $this->set(array(
            'items'         => $items,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }


    public function child_check(){

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        $items = array();
        $this->User = ClassRegistry::init('User');
        $this->User->contain('Group');

        $tree = false;
        if(
            ($user['group_name'] == Configure::read('group.admin'))||
            ($user['group_name'] == Configure::read('group.ap'))
        ){  //Admin or AP
            $child_count= $this->User->find('count',
                array('conditions' => array('User.parent_id' => $user['id'],'Group.name' => Configure::read('group.ap')))
            );
            if($child_count > 0){
                $tree = true;
            }
        }

        $items['tree'] = $tree;

        $this->set(array(
            'items'         => $items,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }


    function _build_common_query($user){

        //Empty to start with
        $c                  = array();
        $c['joins']         = array(); 
        $c['conditions']    = array();

        //What should we include....
        $c['contain']   = array(
                            'UserNote'  => array('Note.note','Note.id','Note.available_to_siblings','Note.user_id'),
                            'Owner'     => array('Owner.username'),
                            'Group'        
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'User.username';
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
                        array_push($c['conditions'],array("Owner.username LIKE" => '%'.$f->value.'%'));   
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
    
        //== ONLY Access Providers ==
        $ap_name = Configure::read('group.ap');
        array_push($c['conditions'],array('Group.name' => $ap_name ));

        //====== END REQUEST FILTER =====

        //====== AP FILTER =====
        //If the user is an AP; we need to add an extra clause to only show all the AP's downward from its position in the tree
        if($user['group_name'] == Configure::read('group.ap')){  //AP 
            $ap_children    = $this->User->find_access_provider_children($user['id']);
            if($ap_children){   //Only if the AP has any children...
                $ap_clause      = array();
                foreach($ap_children as $i){
                    $id = $i['id'];
                    array_push($ap_clause,array($this->modelClass.'.parent_id' => $id));
                }      
                //Add it as an OR clause
                array_push($c['conditions'],array('OR' => $ap_clause));  
            }
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

}
