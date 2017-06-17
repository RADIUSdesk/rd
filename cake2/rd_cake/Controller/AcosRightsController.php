<?php
App::uses('AppController', 'Controller');

class AcosRightsController extends AppController {

    public $name       = 'AcosRights';
    public $components = array('Acl','Aa');
    public $aco_ap     = 'Access Providers';
    protected $base    = "Access Providers/Controllers/AcosRights/";   //This is required for Aa component  
    
    //-------- BASIC CRUD -------------------------------
    public function index(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        } 

        $id = null;
        if(isset($this->request->query['node'])){
            if($this->request->query['node'] == 0){
                $id = null;
            }else{
                $id = $this->request->query['node'];
            }
        }
      
        //We only will list the first level of nodes
        $q_r = $this->Acl->Aco->find('all',array('conditions' => array('Aco.parent_id' => $id), 'recursive' => 0));

        $items = array();
        foreach($q_r as $i){

            $id         = $i['Aco']['id'];
            $parent_id  = $i['Aco']['parent_id'];
            $alias      = $i['Aco']['alias'];
            $comment    = $i['Aco']['comment'];
            $leaf       = false;
            $icon_cls   = '';
            if($this->Acl->Aco->childCount($id) == 0){
                $leaf       = true;
                $icon_cls   = 'list';
            }
            array_push($items,array('id' => $id, 'alias' => $alias,'leaf' => $leaf,'comment' => $comment, 'iconCls' => $icon_cls)); 
        }
            
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));

    }


    public function index_ap(){
        //Return the default rights of the Access Provider group which is under the 'Access Providers' branch of the tree

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $id = null;
        if(isset($this->request->query['node'])){
            if($this->request->query['node'] == 0){
                $ap = $this->aco_ap;
                //Get the Node that is specified as 'Access Providers' directly under the root node
                $qr = $this->Acl->Aco->find('first',array('conditions' => array('Aco.parent_id' => null,'Aco.alias' =>$ap)));
                $id = $qr['Aco']['id'];
            }else{
                $id = $this->request->query['node'];
            }
        }
      
        //We only will list the first level of nodes
        $q_r = $this->Acl->Aco->find('all',array('conditions' => array('Aco.parent_id' => $id), 'recursive' => 0));

        //Get the id of the AP Group
        $this->Group    = ClassRegistry::init('Group');
        $g_q            = $this->Group->findByName(Configure::read('group.ap'));
        $group_id       = $g_q['Group']['id'];

        if(isset($this->request->query['ap_id'])){
            $fk_id = $this->request->query['ap_id'];
            $model = 'User';
        }else{
            $fk_id = $group_id;
            $model = 'Group';
        }
        $items = array();
        foreach($q_r as $i){

            $id         = $i['Aco']['id'];
            $parent_id  = $i['Aco']['parent_id'];
            $alias      = $i['Aco']['alias'];
            $comment    = $i['Aco']['comment'];
            $leaf       = false;
            $allowed    = false;
            $group_right= '';   //default for branches
            $icon_cls   = '';
        

            if($this->Acl->Aco->childCount($id) == 0){
                $leaf = true;
                $icon_cls = 'list';
                //Check if allowed //We only toggle on leave level
                if($this->Acl->check(array('model' => $model, 'foreign_key' => $fk_id),$this->_return_aco_path($id))){
                    $allowed = true;
                }
                //Add-on to display the default group right if we are showing the rights for an AP person
                if($model == 'User'){
                    $group_right = 'no';
                    if($this->Acl->check(array('model' => 'Group', 'foreign_key' => $group_id),$this->_return_aco_path($id))){
                        $group_right = 'yes';
                    }
                }
            }
            if($model == 'User'){
                array_push($items,
                    array('id' => $id, 'alias' => $alias,'leaf' => $leaf,'comment' => $comment,'allowed' => $allowed,'group_right' => $group_right,'iconCls' => $icon_cls));
            }else{
                array_push($items,
                    array('id' => $id, 'alias' => $alias,'leaf' => $leaf,'comment' => $comment,'allowed' => $allowed, 'iconCls' => $icon_cls));
            }
        }
            
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }


    public function add(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        } 
    
        if ($this->request->is('post')) {
            $this->Acl->Aco->create();
            if($this->request->data['parent_id'] == 0){
                $this->request->data['parent_id'] = null;            
            }

            if($this->Acl->Aco->save($this->request->data)){
                $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }
        }
    }

    public function edit(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        }

        if ($this->request->is('post')) {
            if($this->request->data['parent_id'] == 0){
                $this->request->data['parent_id'] = null;            
            }

            if($this->request->data['id'] == 0){
                $this->set(array(
                    'success' => false,
                    'message' => array('message' => __('Not allowed to change root node')),
                    '_serialize' => array('success','message')
                ));
            }elseif($this->Acl->Aco->save($this->request->data)){
                $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }
        }
    }

    public function edit_ap(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $id             = $this->data['id'];
        if(isset($this->request->query['ap_id'])){   //This is specific to an AP
            $fk_id = $this->request->query['ap_id'];
            $model = 'User';
        }else{ //Assume this is general to the AP Group
            //Get the id of the AP Group
            $this->Group    = ClassRegistry::init('Group');
            $g_q            = $this->Group->findByName(Configure::read('group.ap'));
            $fk_id          = $g_q['Group']['id'];
            $model          = 'Group';
        }

        if($this->data['allowed'] == false){
             $this->Acl->deny(array('model' => $model, 'foreign_key' => $fk_id),$this->_return_aco_path($id));
        }

        if($this->data['allowed'] == true){
            $this->Acl->allow(array('model' => $model, 'foreign_key' => $fk_id),$this->_return_aco_path($id));
        }
        
        $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
        ));
    }

    public function view(){

    }

    //Extjs specific workaround for treestore that has a defined model
    public function dummy_delete(){
        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    public function delete(){
        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        } 

        if(isset($this->data['id'])){   //Single item delete

            $message = "Single item ".$this->data['id'];
            $this->Acl->Aco->id = $this->data['id'];
            $this->Acl->Aco->delete();
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                $this->Acl->Aco->id = $d['id'];
                $this->Acl->Aco->delete();
            }
        }
        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    //--------- END BASIC CRUD ---------------------------


    private function _return_aco_path($id){
        $parents = $this->Acl->Aco->getPath($id);
        $path_string = '';
        foreach($parents as $line_num => $i){
            if($line_num == 0){
                $path_string = $i['Aco']['alias'];
            }else{
                $path_string = $path_string."/".$i['Aco']['alias'];
            }
        }
        return $path_string;
    }

}
