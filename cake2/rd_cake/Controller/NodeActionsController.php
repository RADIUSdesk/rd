<?php
App::uses('AppController', 'Controller');

class NodeActionsController extends AppController {

    public $name       = 'NodeActions';
    public $components = array('Aa','Formatter','GridFilter');
    public $uses       = array('NodeAction','User');
    protected $base    = "Access Providers/Controllers/NodeActions/";

//------------------------------------------------------------------------


    //____ BASIC CRUD Actions Manager ________
    public function index(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
 
        $c = $this->_build_common_query($user); 

        if(isset($this->request->query['node_id'])){
            array_push($c['conditions'],array("NodeAction.node_id" => $this->request->query['node_id']));
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
        //If there are more than one
        if($q_r){
            foreach($q_r as $item){          
                array_push($items,array(
                    'id'        =>  $item['NodeAction']['id'],
                    'action'    =>  $item['NodeAction']['action'],
                    'command'   =>  $item['NodeAction']['command'],
                    'status'    =>  $item['NodeAction']['status'],
                    'created'   =>  $item['NodeAction']['created'],
                    'modified'  =>  $item['NodeAction']['modified'],
                )); 
            }
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

		foreach(array_keys($this->request->data) as $key){
            if(preg_match('/^\d+/',$key)){
                $d['node_id']   = $this->request->data[$key];
				$d['command']	= $this->request->data['command'];
                $this->{$this->modelClass}->create();
                $this->{$this->modelClass}->save($d);   
            }
        }

		if($this->request->data['node_id'] != ''){
			$this->{$this->modelClass}->create();
			if ($this->{$this->modelClass}->save($this->request->data)) {
				$this->set(array(
				    'success' => true,
				    '_serialize' => array('success')
				));
				return;
			} else {
				$message = 'Error';
				$this->set(array(
				    'errors'    => $this->{$this->modelClass}->validationErrors,
				    'success'   => false,
				    'message'   => array('message' => __('Could not create item')),
				    '_serialize' => array('errors','success','message')
				));
				return;
			}
		}

		$this->set(array(
		    'success' => true,
		    '_serialize' => array('success')
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

	public function get_actions_for(){

		if(!(array_key_exists('mac',$this->request->data))){
				$this->set(array(
				'message'		=> 'Required field missing in POST',
		        'success' => false,
		        '_serialize' => array('success','message')
		    ));
			return;
		}

		$mac = $this->request->data['mac'];
		$this->NodeAction->contain('Node');
		$q_r = $this->NodeAction->find('all', 
				array('conditions' => array('Node.mac' => $mac,'NodeAction.status' => 'awaiting')
		)); //Only awaiting actions

		$items = array();
		foreach($q_r as $i){
			$id		= $i['NodeAction']['id'];
			$c 		= $i['NodeAction']['command'];
			array_push($items,array('id' => $id,'command' => $c));	
		}

		//Run through this list and mark them as 'fetched'
		foreach($items as $i){
		    $this->NodeAction->id = $i['id'];
		    if($this->NodeAction->id){
		        $this->NodeAction->saveField('status','fetched');
		    }
		}	

		$this->set(array(
			'items'		=> $items,
            'success' 	=> true,
            '_serialize' => array('success','items')
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
                    array('xtype' => 'button', 'iconCls' => 'b-reload', 'glyph' => Configure::read('icnReload'), 'scale' => 'large', 'itemId' => 'reload',   'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'iconCls' => 'b-add',    'glyph' => Configure::read('icnAdd'),  'scale' => 'large', 'itemId' => 'add',   'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'iconCls' => 'b-delete', 'glyph' => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
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
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'add')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-add', 
                    'glyph'     => Configure::read('icnAdd'), 
                    'scale'     => 'large', 
                    'itemId'    => 'add',
                    'disabled'  => false,   
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
                    'disabled'  => false,   
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

    function _build_common_query($user){

        //Empty to start with
        $c                  = array();
        $c['joins']         = array(); 
        $c['conditions']    = array();

        //What should we include....
        $c['contain']   = array(
                            'Node'
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'NodeAction.created';
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

        //====== AP FILTER =====
        //If the user is an AP; we need to add an extra clause to only show the Tags which he is allowed to see.
        if($user['group_name'] == Configure::read('group.ap')){  //AP
            $tree_array = array();
            $user_id    = $user['id'];

            //**AP and upward in the tree**
            $this->parents = $this->User->getPath($user_id,'User.id');
            //So we loop this results asking for the parent nodes who have available_to_siblings = true
            foreach($this->parents as $i){
                $i_id = $i['User']['id'];
                if($i_id != $user_id){ //upstream
                  ////  array_push($tree_array,array('Node.user_id' => $i_id,'Na.available_to_siblings' => true));
                }else{
                  ///  array_push($tree_array,array('Na.user_id' => $i_id));
                }
            }
            //** ALL the AP's children
            $this->children    = $this->User->find_access_provider_children($user['id']);
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $id = $i['id'];
                  ////  array_push($tree_array,array('Na.user_id' => $id));
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
