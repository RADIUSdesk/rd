<?php
App::uses('AppController', 'Controller');

class TopUpsController extends AppController {

    public $name        = 'TopUps';
    public $components  = array('Aa','GridFilter');
    public $uses        = array('TopUp','User');
    protected $base     = "Access Providers/Controllers/TopUps/";

    protected $fields  = array(
        'user_id',      'permanent_user_id',    'data',  'time',
        'days_to_use',  'comment',
        'created',       'modified',            'id'
    );


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

        foreach($q_r as $i){

            $owner_id       = $i['TopUp']['user_id'];
            $owner_tree     = $this->_find_parents($owner_id);
            $action_flags   = $this->_get_action_flags($owner_id,$user);

            $row = array();
            foreach($this->fields as $field){
                if(array_key_exists($field,$i['TopUp'])){
                    $row["$field"]= $i['TopUp']["$field"];
                }
            }
            $row['user']                = $i['User']['username'];
            $row['permanent_user']      = $i['PermanentUser']['username'];
            $row['update']              = $action_flags['update'];
            $row['delete']              = $action_flags['delete'];

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

        //====Check what type it is====
        //---Data---
        if($this->request->data['type'] == 'data'){
            $multiplier = 1;
            if(isset($this->request->data['data_unit'])){
                if($this->request->data['data_unit'] == 'mb'){
                    $multiplier = 1048576; //(1024*1024)
                }
                if($this->request->data['data_unit'] == 'gb'){
                    $multiplier = 1073741824; //(1024*1024*1024)
                }            
            }
            $this->request->data['data'] = $this->request->data['value'] * $multiplier;
        }

        //---Time---
        if($this->request->data['type'] == 'time'){
            $multiplier = 1;
            if(isset($this->request->data['time_unit'])){
                if($this->request->data['time_unit'] == 'minutes'){
                    $multiplier = 60; //(60 seconds = minute)
                }
                if($this->request->data['time_unit'] == 'hours'){
                    $multiplier = 3600; //(60 seconds * 60 minutes)
                } 
                if($this->request->data['time_unit'] == 'days'){
                    $multiplier = 86400; //(60 seconds * 60 minutes * 24 Hours)
                }             
            }
            $this->request->data['time'] = $this->request->data['value'] * $multiplier;
        }

        //---Days To Use---
        if($this->request->data['type'] == 'days_to_use'){
            $this->request->data['days_to_use'] = $this->request->data['value'];
        }
        //====END Check what type it is====


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
            $owner_id       = $item['TopUp']['user_id'];
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
                $owner_id       = $item['TopUp']['user_id'];
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


    public function view(){

        $data = array(
            'id'                => 12,
            'permanent_user_id' => 187,
            'data'              => 1048576,
            'time'              => null,
            'days_to_use'       => null,
            'comment'           => ''
        );

        $this->set(array(
            'data'      => $data,
            'success'   => true,
            '_serialize'=> array('success', 'data')
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
                    array(
                        'xtype'     => 'button', 
                        'glyph'     => Configure::read('icnAdd'), 
                        'scale'     => 'large', 
                        'itemId'    => 'add',      
                        'tooltip'   => __('Add')
                    ),
                    array(
                        'xtype'     => 'button', 
                        'glyph'     => Configure::read('icnDelete'), 
                        'scale'     => 'large', 
                        'itemId'    => 'delete',   
                        'tooltip'   => __('Delete')
                    ),
                    array(
                        'xtype'     => 'button', 
                        'glyph'     => Configure::read('icnEdit'), 
                        'scale'     => 'large', 
                        'itemId'    => 'edit',     
                        'tooltip'   => __('Edit')
                    )
                )),
                array('xtype' => 'buttongroup','title' => __('Document'), 'width' => 100, 'items' => array(  
                    array(
                        'xtype'     => 'button',
                        'glyph'     => Configure::read('icnCsv'), 
                        'scale'     => 'large', 
                        'itemId'    => 'csv',      
                        'tooltip'   => __('Export CSV')
                    ),
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
            ));

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
                            'User',
                            'PermanentUser'
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'TopUp.created';
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
        //If the user is an AP; we need to add an extra clause to only show the TopUps which he is allowed to see.
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
                    array_push($tree_array,array('TopUp.user_id' => $i_id));
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
