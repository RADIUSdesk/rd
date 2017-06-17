<?php
/**
 * Created by PhpStorm.
 * User: stevenkusters
 * Date: 18/01/2017
 * Time: 15:00
 */

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

use Cake\Event\Event;
use Cake\Utility\Inflector;


class ProfilesController extends AppController
{
    protected $base = "Access Providers/Controllers/Profiles/";
    protected $owner_tree = array();
    protected $main_model = 'Profiles';

    public function initialize()
    {
        parent::initialize();

        $this->loadModel('Profiles');
        $this->loadModel('Users');
        $this->loadModel('Groups');
        
        $this->loadModel('ProfileComponents');
        $this->loadModel('Radusergroups');
        
        $this->loadComponent('CommonQuery', [ //Very important to specify the Model
            'model' => 'Profiles'
        ]);
        $this->loadComponent('Aa');
        $this->loadComponent('GridButtons');
        $this->loadComponent('Notes', [
            'model' => 'ProfileNotes',
            'condition' => 'profile_id'
        ]);
    }

    public function indexAp(){
    
        $user = $this->_ap_right_check();
        if (!$user) {
            return;
        }
        if (isset($this->request->query['ap_id'])) {
            $ap_id = $this->request->query['ap_id'];  
            if($ap_id !== '0'){       
                //Now we have to make the ap_id the 'user'
                $q_ap = $this->Users->find()->where(['Users.id' => $ap_id])->contain(['Groups'])->first();
                $ap_user                = [];
                $ap_user['id']          = $q_ap->id;
                $ap_user['group_name']  = $q_ap->group->name;
                $ap_user['group_id']    = $q_ap->group->id;
                //Override the user
                $user = $ap_user;
            }  
        }
        
        $query      = $this->{$this->main_model}->find();
        $this->CommonQuery->build_common_query($query, $user, ['Radusergroups'=> ['Radgroupchecks']]);
        $q_r        = $query->all();
        
        $items      = array();
        
        foreach($q_r as $i){    
            $data_cap_in_profile    = false; 
            $time_cap_in_profile    = false;   
            $id                     = $i->id;
            $name                   = $i->name;
            $data_cap_in_profile    = false; 
            $time_cap_in_profile    = false; 

            foreach ($i->radusergroups as $cmp){
                foreach ($cmp->radgroupchecks as $radgroupcheck) {
                    if($radgroupcheck->attribute == 'Rd-Reset-Type-Data'){
                        $data_cap_in_profile = true;
                    }
                    if($radgroupcheck->attribute == 'Rd-Reset-Type-Time'){
                        $time_cap_in_profile = true;
                    }              
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
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items', 'success')
        ));

    }

    public function index(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if (!$user) {
            return;
        }
        $user_id    = $user['id'];
        $query      = $this->{$this->main_model}->find();

        $this->CommonQuery->build_common_query($query, $user, ['Users', 'ProfileNotes' => ['Notes'],'Radusergroups'=> ['Radgroupchecks']]); //AP QUERY is sort of different in a way

        //===== PAGING (MUST BE LAST) ======
        $limit = 50;   //Defaults
        $page = 1;
        $offset = 0;
        if (isset($this->request->query['limit'])) {
            $limit = $this->request->query['limit'];
            $page = $this->request->query['page'];
            $offset = $this->request->query['start'];
        }

        $query->page($page);
        $query->limit($limit);
        $query->offset($offset);

        $total = $query->count();
        $q_r = $query->all();

        $items = array();

        foreach ($q_r as $i) {

            $owner_id = $i->user_id;
            if (!array_key_exists($owner_id, $this->owner_tree)) {
                $owner_tree = $this->Users->find_parents($owner_id);
            } else {
                $owner_tree = $this->owner_tree[$owner_id];
            }

            //Add the components (already from the highest priority
            $components = array();
            $data_cap_in_profile = false; // A flag that will be set if the profile contains a component with Rd-Reset-Type-Data group check attribute.
            $time_cap_in_profile = false; // A flag that will be set if the profile contains a component with Rd-Reset-Type-Time group check attribute.

            foreach ($i->radusergroups as $cmp){
                foreach ($cmp->radgroupchecks as $radgroupcheck) {
                    if($radgroupcheck->attribute == 'Rd-Reset-Type-Data'){
                        $data_cap_in_profile = true;
                    }
                    if($radgroupcheck->attribute == 'Rd-Reset-Type-Time'){
                        $time_cap_in_profile = true;
                    }              
                }
                $a = $cmp->toArray();
                unset($a['radgroupchecks']);
                array_push($components,$a);     
            }
            
            $action_flags = $this->Aa->get_action_flags($owner_id, $user);
            $notes_flag = false;
            foreach ($i->profile_notes as $un) {
                if (!$this->Aa->test_for_private_parent($un->note, $user)) {
                    $notes_flag = true;
                    break;
                }
            }
            
            array_push($items, array(
                'id'                    => $i->id,
                'name'                  => $i->name,
                'owner'                 => $owner_tree,
                'available_to_siblings' => $i->available_to_siblings,
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
            '_serialize' => array('items', 'success', 'totalCount')
        ));
    }

    public function add(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        // get creators id
        if(isset($this->request->data['user_id'])){
            if($this->request->data['user_id'] == '0'){ //This is the holder of the token - override '0'
                $this->request->data['user_id'] = $user_id;
            }
        } 
        $check_items = array(
			'available_to_siblings'
		);
		
        foreach($check_items as $i){
            if(isset($this->request->data[$i])){
                $this->request->data[$i] = 1;
            }else{
                $this->request->data[$i] = 0;
            }
        }
        

        $entity = $this->{$this->main_model}->newEntity($this->request->data());
        if ($this->{$this->main_model}->save($entity)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        } else {
        
            $errors = $entity->errors();
            $a = [];
            foreach(array_keys($errors) as $field){
                $detail_string = '';
                $error_detail =  $errors[$field];
                foreach(array_keys($error_detail) as $error){
                    $detail_string = $detail_string." ".$error_detail[$error];   
                }
                $a[$field] = $detail_string;
            }
            
            $this->set(array(
                'errors'    => $a,
                'success'   => false,
                'message'   => array('message' => __('Could not create item')),
                '_serialize' => array('errors','success','message')
            ));
            
        }
    }

    public function manageComponents(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $rb         = $this->request->data['rb']; 

        if(($rb == 'add')||($rb == 'remove')){
            $component_id   = $this->request->data['component_id'];
            $entity         = $this->ProfileComponents->get($this->request->data['component_id']);  
            $component_name = $entity->name;
        }
        
        foreach(array_keys($this->request->data) as $key){
            if(preg_match('/^\d+/',$key)){

                if($rb == 'sub'){
                    $entity = $this->{$this->main_model}->get($key);
                    $entity->set('available_to_siblings', 1);
                    $this->{$this->main_model}->save($entity);
                }

                if($rb == 'no_sub'){
                    $entity = $this->{$this->main_model}->get($key);
                    $entity->set('available_to_siblings', 0);
                    $this->{$this->main_model}->save($entity);
                }

                if($rb == 'remove'){
                    $entity         = $this->{$this->main_model}->get($key);
                    $profile_name   = $entity->name;
                    $this->Radusergroups->deleteAll(['Radusergroups.username' => $profile_name,'Radusergroups.groupname' => $component_name]);
                }
               
                if($rb == 'add'){
                    $entity         = $this->{$this->main_model}->get($key);
                    $profile_name   = $entity->name;
                    
                    $this->Radusergroups->deleteAll(['Radusergroups.username' => $profile_name,'Radusergroups.groupname' => $component_name]);
                    
                    $priority = $this->request->data['priority'];
                    $ne = $this->Radusergroups->newEntity(
                        [
                            'username'  => $profile_name,
                            'groupname' => $component_name,
                            'priority'  => $priority
                        ]
                    );
                    $this->Radusergroups->save($ne);
                }
                
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

        $user_id   = $user['id'];
        $fail_flag = false;

	    if(isset($this->request->data['id'])){   //Single item delete
            $message = "Single item ".$this->request->data['id'];

            //NOTE: we first check of the user_id is the logged in user OR a sibling of them:         
            $entity         = $this->{$this->main_model}->get($this->request->data['id']);
            $profile_name   = $entity->name;   
            $owner_id       = $entity->user_id;
            
            if($owner_id != $user_id){
                if($this->Users->is_sibling_of($user_id,$owner_id)== true){
                    $this->{$this->main_model}->delete($entity);
                    $this->Radusergroups->deleteAll(['Radusergroups.username' => $profile_name]);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->{$this->main_model}->delete($entity);
                $this->Radusergroups->deleteAll(['Radusergroups.username' => $profile_name]);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->request->data as $d){
                $entity         = $this->{$this->main_model}->get($d['id']);
                $profile_name   = $entity->name;   
                $owner_id       = $entity->user_id;
                
                if($owner_id != $user_id){
                    if($this->Users->is_sibling_of($user_id,$owner_id) == true){
                        $this->{$this->main_model}->delete($entity);
                        $this->Radusergroups->deleteAll(['Radusergroups.username' => $profile_name]);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->{$this->main_model}->delete($entity);
                    $this->Radusergroups->deleteAll(['Radusergroups.username' => $profile_name]);
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


    public function noteIndex()
    {
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if (!$user) {
            return;
        }
        $items = $this->Notes->index($user);
    }

    public function noteAdd()
    {
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if (!$user) {
            return;
        }
        $this->Notes->add($user);
    }

    public function noteDel()
    {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $user = $this->_ap_right_check();
        if (!$user) {
            return;
        }
        $this->Notes->del($user);
    }

    public function menuForGrid()
    {
        $user = $this->Aa->user_for_token($this);
        if (!$user) {   //If not a valid user
            return;
        }

        $menu = $this->GridButtons->returnButtons($user, true, 'profiles'); 
        $this->set(array(
            'items' => $menu,
            'success' => true,
            '_serialize' => array('items', 'success')
        ));
    }
}
