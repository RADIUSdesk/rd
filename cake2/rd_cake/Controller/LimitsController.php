<?php
App::uses('AppController', 'Controller');

class LimitsController extends AppController {

    public $name        = 'Limits';
    public $components  = array('Aa');
    public $uses        = array('Limit','User');
    protected $base     = "Access Providers/Controllers/Limits/";

//------------------------------------------------------------------------

    //____ BASIC CRUD Manager ________
    
    public function limit_check(){
    
        //Check if the limits is actually turned on
        Configure::load('Limits');
        $is_active = Configure::read('Limits.Global.Active');
        $this->set(array(
            'data' => array('enabled' => $is_active),
            'success' => true,
            '_serialize' => array('data','success')
        ));
    }
    
    
    
    
    public function index(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        
        $items      = array();
        //Check if the limits is actually turned on
        Configure::load('Limits');
        $is_active = Configure::read('Limits.Global.Active');
        if($is_active){
        
            $ap_id = false;
            if(array_key_exists('ap_id', $this->request->query)){
                $ap_id = $this->request->query['ap_id'];
            }
            
            if($ap_id){
                $items = $this->_find_limits_for($ap_id);
            }
        }
         
        
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

	public function edit(){

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if ($this->request->is('post')) {
            if(array_key_exists('ap_id', $this->request->query)){
                $ap_id      = $this->request->query['ap_id'];    
                $alias      = $this->request->data['alias'];
                $this->request->data['user_id'] = $ap_id;
             
                //See if there is already and entry for this one
                $q_r = $this->Limit->find('first',array('conditions' =>array('Limit.user_id' => $ap_id,'Limit.alias' => $alias)));
                if($q_r){
                    $this->request->data['id']      = $q_r['Limit']['id'];
                }else{
                    unset($this->request->data['id']);
                }
                if ($this->{$this->modelClass}->save($this->request->data)) {
                       $this->set(array(
                        'success' => true,
                        '_serialize' => array('success')
                    ));
                }
            }       
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
            );
        }

        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $action_group   = array();

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

            $menu = array(
                        array('xtype' => 'buttongroup','title' => __('Action'),        'items' => $action_group)
                   );
        }
        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }
    
    private function _find_limits_for($ap_id){
    
        $limits_return =array();
    
        //Get the default list; run through it and see if there are overrides
        Configure::load('Limits');
        $is_active      = Configure::read('Limits.Global.Active');
        $limits_data    = Configure::read('Limits');
        $id             = 1;
        
        foreach(array_keys($limits_data) as $key){
            if($key != 'Global'){
                $alias  = $key;
                $overrides = $this->_find_overrides_for($ap_id,$alias);
                if(count($overrides)>0){
                    $active = $overrides[0];
                    $count  = $overrides[1];
                }else{
                    $active = $limits_data["$key"]['Active'];
                    $count  = $limits_data["$key"]['Count'];
                }
                $desc   = $limits_data["$key"]['Description'];
                array_push($limits_return, array('id' =>$id, 'alias' => $alias, 'active' => $active, 'count' => $count, 'description' => $desc ));
                $id ++;
            }
        }
        return $limits_return;
    }
    
    private function _find_overrides_for($ap_id, $alias){
        $overrides = array();
        $q_r = $this->Limit->find('first',array('conditions' => array('Limit.user_id' => $ap_id,'Limit.alias' => $alias)));
        if($q_r){
            $overrides[0] = $q_r['Limit']['active'];
            $overrides[1] = $q_r['Limit']['count'];
        }
        return $overrides;
    }
}
