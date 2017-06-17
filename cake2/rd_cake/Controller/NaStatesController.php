<?php
App::uses('AppController', 'Controller');

class NaStatesController extends AppController {

    public $name       = 'NaStates';
    public $components = array('Aa','Formatter','GridFilter');
    public $uses       = array('NaState','User');
    protected $base    = "Access Providers/Controllers/NaStates/";

//------------------------------------------------------------------------


    //____ BASIC CRUD NaStates Manager ________
    public function index(){

        //Display a list of nas tags with their owners
        //This will be dispalyed to the Administrator as well as Access Providers who has righs

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
 
        $c = $this->_build_common_query($user); 

        if(isset($this->request->query['nas_id'])){
            array_push($c['conditions'],array("NaState.na_id" => $this->request->query['nas_id']));
        }

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

        //If there is only one entry - we take it from that entry till now
        if(count($q_r) == 1){
            $state_time = $this->Formatter->diff_in_time($q_r[0]['NaState']['created']);
            array_push($items,array(
                'id'                    => $q_r[0]['NaState']['id'], 
                'state'                 => $q_r[0]['NaState']['state'],
                'time'                  => $state_time,
                'start'                 => $q_r[0]['NaState']['created']
            ));
        }

        //If there are more than one
        if(count($q_r) > 1){

            $counter = 0;
            foreach($q_r as $item){
                if($counter != 0){
                    $previous_time  = $q_r[($counter-1)]['NaState']['created'];
                    $previous_state = $q_r[($counter-1)]['NaState']['state'];
                    $id             = $q_r[($counter-1)]['NaState']['id'];
                    $state_time     = $this->Formatter->diff_in_time($q_r[$counter]['NaState']['created'],$previous_time);
                    array_push($items,array('id' =>  $id,'state'=>$previous_state,'time'=> $state_time,'start' =>$previous_time,'end' => $q_r[$counter]['NaState']['created'])); 
                }
                $counter++;
            }

            //Add the last one
            $state_now      = $q_r[($counter-1)]['NaState']['state'];
            $state_since    = $q_r[($counter-1)]['NaState']['created'];
            $id             = $q_r[($counter-1)]['NaState']['id'];
            $state_time     = $this->Formatter->diff_in_time($state_since);
            array_push($items,array('id' =>  $id,'state'=>$state_now,'time'=> $state_time,'start' => $q_r[($counter-1)]['NaState']['created'])); 
        }

        $items = array_reverse($items); //Put the last state at the top!
       
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            'totalCount' => $total,
            '_serialize' => array('items','success','totalCount')
        ));
    }

   
    //FIXME check rights
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
            $this->{$this->modelClass}->id = $this->data['id'];
            $this->{$this->modelClass}->delete();
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                $this->{$this->modelClass}->id = $d['id'];
                $this->{$this->modelClass}->delete();
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
                    array('xtype' => 'button', 'iconCls' => 'b-delete',  'glyph'     => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
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
                            'Na'
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'NaState.created';
        $dir    = 'ASC';

        if(isset($this->request->query['sort'])){  
            $sort = $this->modelClass.'.'.$this->request->query['sort'];
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
                    $col = $this->modelClass.'.'.$f->field;
                    array_push($c['conditions'],array("$col LIKE" => '%'.$f->value.'%'));
                }
                //Bools
                if($f->type == 'boolean'){
                     $col = $this->modelClass.'.'.$f->field;
                     array_push($c['conditions'],array("$col" => $f->value));
                }
            }
        }
        //====== END REQUEST FILTER =====
//FIXME Is this needed?
/*
        //====== AP FILTER =====
        //If the user is an AP; we need to add an extra clause to only show the NaStates which he is allowed to see.
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
            $ap_children    = $this->User->find_access_provider_children($user['id']);
            if($ap_children){   //Only if the AP has any children...
                foreach($ap_children as $i){
                    $id = $i['id'];
                    array_push($tree_array,array($this->modelClass.'.user_id' => $id));
                }       
            }   
            //Add it as an OR clause
            print_r($tree_array);
            array_push($c['conditions'],array('OR' => $tree_array));  
        } 
*/      
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
