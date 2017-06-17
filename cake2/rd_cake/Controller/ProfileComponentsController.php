<?php
class ProfileComponentsController extends AppController {

   // var $scaffold;
    public $name        = 'ProfileComponents';
    public $components  = array('Aa','Freeradius','GridFilter');
    public $uses        = array('ProfileComponent','User');
    protected $base     = "Access Providers/Controllers/ProfileComponents/";
    protected $itemNote = 'ProfileComponentNote';

    public function vendors(){
    //Gives a list of Vendors from the dictionaries including a few special ones that is not defined but used to group the attributes
        $v = $this->Freeradius->getVendors();
        $items = array();
        foreach($v as $i){
            array_push($items, array('id' => $i, 'name' => $i));
        }
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    public function attributes(){
    //Gives the attributes based on the name of the 'vendor' query attribute
        $items = array();
        if(isset($this->request->query['vendor'])){
            $a  = $this->Freeradius->getAttributes($this->request->query['vendor']);
            foreach($a as $i){
                array_push($items, array('id' => $i, 'name' => $i));
            }
        }
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    //____ BASCI CRUD for Component Attributes ____
    public function index_comp(){
    //Returns a list of vendor attributes based on the tmpl_id query attribute.
        $items = array();
        if(isset($this->request->query['comp_id'])){
            $comp_id = $this->request->query['comp_id'];
            $q_r = $this->ProfileComponent->findById($comp_id);         
            //First the radchecks
            $id_prefix = 'chk_';
            foreach($q_r['Radgroupcheck'] as $i){
                array_push($items, array(
                    'id'            => $id_prefix.$i['id'],
                    'type'          => 'check',
                    'groupname'     => $i['groupname'],
                    'attribute'     => $i['attribute'],
                    'op'            => $i['op'],
                    'value'         => $i['value'],
                    'comment'       => $i['comment']
                ));
            }

            //Then the radreply's
            $id_prefix = 'rpl_';
            foreach($q_r['Radgroupreply'] as $i){
                array_push($items, array(
                    'id'            => $id_prefix.$i['id'],
                    'type'          => 'reply',
                    'groupname'     => $i['groupname'],
                    'attribute'     => $i['attribute'],
                    'op'            => $i['op'],
                    'value'         => $i['value'],
                    'comment'       => $i['comment']
                ));
            }
            
        }
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    public function add_comp(){

        if(isset($this->request->query['comp_id'])){
            $comp_id = $this->request->query['comp_id'];
            $q_r = $this->ProfileComponent->findById($comp_id);
            $this->request->data['groupname'] = $q_r['ProfileComponent']['name'];

             $this->request->data['id'] = ''; //Wipe it since ExtJs 6 add some random stuff here

            //CHECK
            if($this->request->data['type'] == 'check'){
                $this->{$this->modelClass}->Radgroupcheck->create();
                if ($this->{$this->modelClass}->Radgroupcheck->save($this->request->data)) {
                    $id = 'chk_'.$this->{$this->modelClass}->Radgroupcheck->id;
                    $this->request->data['id'] = $id;
                    $this->set(array(
                        'items'     => $this->request->data,
                        'success'   => true,
                        '_serialize' => array('success','items')
                    ));
                } else {
                    $message = 'Error';
                    $this->set(array(
                        'errors'    => $this->{$this->modelClass}->Radgroupcheck->validationErrors,
                        'success'   => false,
                        'message'   => array('message' => __('Could not create item')),
                        '_serialize' => array('errors','success','message')
                    ));
                }
            }

            //REPLY
            if($this->request->data['type'] == 'reply'){
                $this->{$this->modelClass}->Radgroupreply->create();
                if ($this->{$this->modelClass}->Radgroupreply->save($this->request->data)) {
                    $id = 'rpl_'.$this->{$this->modelClass}->Radgroupreply->id;
                    $this->request->data['id'] = $id;
                    $this->set(array(
                        'items'     => $this->request->data,
                        'success'   => true,
                        '_serialize' => array('success','items')
                    ));
                } else {
                    $message = 'Error';
                    $this->set(array(
                        'errors'    => $this->{$this->modelClass}->Radgroupreply->validationErrors,
                        'success'   => false,
                        'message'   => array('message' => __('Could not create item')),
                        '_serialize' => array('errors','success','message')
                    ));
                }
            }
        }
    }

    public function edit_comp(){

         if(isset($this->request->query['comp_id'])){

            //Check if the type check was not changed
            if((preg_match("/^chk_/",$this->request->data['id']))&&($this->request->data['type']=='check')){ //check Type remained the same
                //Get the id for this one
                $type_id            = explode( '_', $this->data['id']);
                $this->request->data['id']   = $type_id[1];
                if ($this->{$this->modelClass}->Radgroupcheck->save($this->request->data)) {
                    $id = 'chk_'.$this->{$this->modelClass}->Radgroupcheck->id;
                    $this->request->data['id'] = $id;
                    $this->set(array(
                        'items'     => $this->request->data,
                        'success'   => true,
                        '_serialize' => array('success','items')
                    ));
                } else {
                    $message = 'Error';
                    $this->set(array(
                        'errors'    => $this->{$this->modelClass}->Radgroupcheck->validationErrors,
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
                if ($this->{$this->modelClass}->Radgroupreply->save($this->request->data)) {
                    $id = 'rpl_'.$this->{$this->modelClass}->Radgroupreply->id;
                    $this->request->data['id'] = $id;
                    $this->set(array(
                        'items'     => $this->request->data,
                        'success'   => true,
                        '_serialize' => array('success','items')
                    ));
                } else {
                    $message = 'Error';
                    $this->set(array(
                        'errors'    => $this->{$this->modelClass}->Radgroupreply->validationErrors,
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
                $this->{$this->modelClass}->Radgroupcheck->id = $type_id[1];
                $this->{$this->modelClass}->Radgroupcheck->delete();

                //Create
                $this->{$this->modelClass}->Radgroupreply->create();
                if ($this->{$this->modelClass}->Radgroupreply->save($this->request->data)) {
                    $id = 'rpl_'.$this->{$this->modelClass}->Radgroupreply->id;
                    $this->request->data['id'] = $id;
                    $this->set(array(
                        'items'     => $this->request->data,
                        'success'   => true,
                        '_serialize' => array('success','items')
                    ));
                } else {
                    $message = 'Error';
                    $this->set(array(
                        'errors'    => $this->{$this->modelClass}->Radgroupreply->validationErrors,
                        'success'   => false,
                        'message'   => array('message' => __('Could not update item')),
                        '_serialize' => array('errors','success','message')
                    ));
                }
            }

            if((preg_match("/^rpl_/",$this->request->data['id']))&&($this->request->data['type']=='check')){

                //Delete the check; add a reply
                $type_id            = explode( '_', $this->request->data['id']);
                $this->{$this->modelClass}->Radgroupreply->id = $type_id[1];
                $this->{$this->modelClass}->Radgroupreply->delete();

                //Create
                $this->{$this->modelClass}->Radgroupcheck->create();
                if ($this->{$this->modelClass}->Radgroupcheck->save($this->request->data)) {
                    $id = 'chk_'.$this->{$this->modelClass}->Radgroupcheck->id;
                    $this->request->data['id'] = $id;
                    $this->set(array(
                        'items'     => $this->request->data,
                        'success'   => true,
                        '_serialize' => array('success','items')
                    ));
                } else {
                    $message = 'Error';
                    $this->set(array(
                        'errors'    => $this->{$this->modelClass}->Radgroupcheck->validationErrors,
                        'success'   => false,
                        'message'   => array('message' => __('Could not update item')),
                        '_serialize' => array('errors','success','message')
                    ));
                }
            }
   
        }
    }

    public function delete_comp(){

        $fail_flag = true;
        if(isset($this->data['id'])){   //Single item delete
            $type_id            = explode( '_', $this->request->data['id']);
            if(preg_match("/^chk_/",$this->request->data['id'])){
                $this->{$this->modelClass}->Radgroupcheck->id = $type_id[1];
                $this->{$this->modelClass}->Radgroupcheck->delete();
            }

            if(preg_match("/^rpl_/",$this->request->data['id'])){   
                $this->{$this->modelClass}->Radgroupreply->id = $type_id[1];
                $this->{$this->modelClass}->Radgroupreply->delete();
            }
            
            $fail_flag = false;
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                $type_id            = explode( '_', $d['id']);
                if(preg_match("/^chk_/",$d['id'])){
                    $this->{$this->modelClass}->Radgroupcheck->id = $type_id[1];
                    $this->{$this->modelClass}->Radgroupcheck->delete();
                }
                if(preg_match("/^rpl_/",$d['id'])){   
                    $this->{$this->modelClass}->Radgroupreply->id = $type_id[1];
                    $this->{$this->modelClass}->Radgroupreply->delete();
                }          
                $fail_flag = false;  
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


      //____ BASIC CRUD Manager ________
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

        $c_page             = $c;
        $c_page['page']     = $page;
        $c_page['limit']    = $limit;
        $c_page['offset']   = $offset;

        $total  = $this->{$this->modelClass}->find('count',$c);       
        $q_r    = $this->{$this->modelClass}->find('all',$c_page);

        $items      = array();

        foreach($q_r as $i){
            //$attribute_count    = $this->_getAttributeCount($i['TemplateAttribute']);
            //Create notes flag
            $notes_flag  = false;
            foreach($i['ProfileComponentNote'] as $nn){
                if(!$this->_test_for_private_parent($nn['Note'],$user)){
                    $notes_flag = true;
                    break;
                }
            }

            $owner_id       = $i['ProfileComponent']['user_id'];
            $owner_tree     = $this->_find_parents($owner_id);
            $action_flags   = $this->_get_action_flags($owner_id,$user);

            array_push($items,array(
                'id'                    => $i['ProfileComponent']['id'], 
                'name'                  => $i['ProfileComponent']['name'],
                'owner'                 => $owner_tree, 
                'available_to_siblings' => $i['ProfileComponent']['available_to_siblings'],
                'reply_attribute_count' => count($i['Radgroupreply']), 
                'check_attribute_count' => count($i['Radgroupcheck']),
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


    public function edit() {

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        //We will not modify user_id
        unset($this->request->data['user_id']);

        if(isset($this->request->data['available_to_siblings'])){
            $this->request->data['available_to_siblings'] = 1;
        }else{
            $this->request->data['available_to_siblings'] = 0;
        }

		if ($this->{$this->modelClass}->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }else{
             $this->set(array(
                'errors'    => $this->{$this->modelClass}->validationErrors,
                'success'   => false,
                'message'   => array('message' => __('Could not update item')),
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
            $item       = $this->{$this->modelClass}->findById($this->data['id']);
            $owner_id   = $item['ProfileComponent']['user_id'];
            $name       = $item['ProfileComponent']['name'];
            if($owner_id != $user_id){
                if($this->_is_sibling_of($user_id,$owner_id)== true){
                    $this->{$this->modelClass}->id = $this->data['id'];
                    $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                    $this->_delete_clean_up_component($name);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->{$this->modelClass}->id = $this->data['id'];
                $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                $this->_delete_clean_up_component($name);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                $item       = $this->{$this->modelClass}->findById($d['id']);
                $owner_id   = $item['ProfileComponent']['user_id'];
                $name       = $item['ProfileComponent']['name'];
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($user_id,$owner_id) == true){
                        $this->{$this->modelClass}->id = $d['id'];
                        $this->{$this->modelClass}->delete($this->{$this->modelClass}->id,true);
                        $this->_delete_clean_up_component($name);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->{$this->modelClass}->id = $d['id'];
                    $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
                    $this->_delete_clean_up_component($name);
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
                    'conditions'    => array('ProfileComponentNote.profile_component_id' => $pc_id)
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
            $d['ProfileComponentNote']['profile_component_id']   = $this->request->data['for_id'];
            $d['ProfileComponentNote']['note_id'] = $this->{$this->modelClass}->ProfileComponentNote->Note->id;
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
                $this->{$this->modelClass}->ProfileComponentNote->Note->id = $this->data['id'];
                $this->{$this->modelClass}->ProfileComponentNote->Note->delete($this->data['id'],true);
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
                   // array('xtype' => 'button', 'iconCls' => 'b-csv',     'scale' => 'large', 'itemId' => 'csv',      'tooltip'=> __('Export CSV')),
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

            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'note_index')){ 
                array_push($document_group,array(
                        'xtype'     => 'button', 
                        'iconCls'   => 'b-note', 
                        'glyph'     => Configure::read('icnNote'),     
                        'scale'     => 'large', 
                        'itemId'    => 'note',      
                        'tooltip'   => __('Add Notes')));
            }
/*
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'export_csv')){ 
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
                            'ProfileComponentNote'    => array('Note.note','Note.id','Note.available_to_siblings','Note.user_id'),
                            'User',
                            'Radgroupcheck',
                            'Radgroupreply'
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'ProfileComponent.name';
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

                if(isset($f->type)){
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
        }
        //====== END REQUEST FILTER =====

        //====== AP FILTER =====
        //If the user is an AP; we need to add an extra clause to only show the Components which he is allowed to see.
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
                    array_push($tree_array,array('ProfileComponent.user_id' => $i_id));
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

            //FIXME This will not fly when the AP creates many Permanent Users!!!!
            //Test for Children
            foreach($this->children as $i){
                if($i['id'] == $owner_id){
                    return array('update' => true, 'delete' => true);
                }
            }  
        }
    }

    private function _getAttributeCount($tempate_attributes){

        $reply_count = 0;
        $check_count = 0;

        foreach($tempate_attributes as $entry){
            if($entry['type'] == 'Reply'){
                $reply_count ++;
            }
             if($entry['type'] == 'Check'){
                $check_count ++;
            }
        }
        return array('check_count' => $check_count, 'reply_count' => $reply_count);
    }

    private function _delete_clean_up_component($component_name){

        $this->{$this->modelClass}->Radgroupcheck->deleteAll(   //Delete a previous one
            array('Radgroupcheck.groupname' => $component_name), false
        );

        $this->{$this->modelClass}->Radgroupreply->deleteAll(   //Delete a previous one
            array('Radgroupreply.groupname' => $component_name), false
        );
        $this->Radusergroup = ClassRegistry::init('Radusergroup');
        $this->Radusergroup->deleteAll(   //Delete a previous one
            array('Radusergroup.groupname' => $component_name), false
        );
    }


}
?>
