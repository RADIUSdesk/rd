<?php

namespace App\Controller;
use App\Controller\AppController;

use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

use Cake\Utility\Inflector;


class AccessProvidersController extends AppController{
  
    protected $base  = "Access Providers/Controllers/AccessProviders/";
    
    protected $owner_tree = array();
    
    protected $main_model = 'Users';
  
    public function initialize(){  
        parent::initialize();  
          
        $this->loadModel('Users'); 
        $this->loadModel('Groups');
          
        $this->loadComponent('Aa');
        $this->loadComponent('GridButtons');
        $this->loadComponent('CommonQuery', [ //Very important to specify the Model
            'model' => $this->main_model
        ]);
        
        $this->loadComponent('Notes', [
            'model'     => 'UserNotes',
            'condition' => 'user_id'
        ]);
        
        $this->loadComponent('WhiteLabel'); 
        $this->loadComponent('TimeCalculations');     
    }
    
    
    public function exportCsv(){

        $this->autoRender   = false;

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $query = $this->{$this->main_model}->find(); 
        $this->CommonQuery->build_ap_query($query,$user); //AP QUERY is sort of different in a way
        
        $q_r    = $query->all();

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
                        foreach($i->user_notes as $un){
                            if(!$this->Aa->test_for_private_parent($un->note,$user)){
                                $notes = $notes.'['.$un->note->note.']';    
                            }
                        }
                        array_push($csv_line,$notes);
                    }elseif($column_name =='owner'){
                        $owner_id       = $i->parent_id;
                        $owner_tree     = $this->Users->find_parents($owner_id);
                        array_push($csv_line,$owner_tree); 
                    }else{
                        array_push($csv_line,$i->{$column_name});  
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
    
     //____ BASIC CRUD Manager ________
    public function index(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        
        //Check if the AP has any children, if not retrun immediately with an empty list
        if($user['group_name'] == Configure::read('group.ap')){  //AP
            $entity = $this->Users->get($user_id); 
            if($this->Users->childCount($entity) == 0){
                $this->set(array(
                    'items'         => array(),
                    'success'       => true,
                    '_serialize'    => array('items','success')
                ));
                return;
            }
        }
 
        $query = $this->{$this->main_model}->find();
        
        $this->CommonQuery->build_ap_query($query,$user); //AP QUERY is sort of different in a way
        
        //===== PAGING (MUST BE LAST) ======
        $limit  = 50; 
        $page   = 1;
        $offset = 0;
        if(isset($this->request->query['limit'])){
            $limit  = $this->request->query['limit'];
            $page   = $this->request->query['page'];
            $offset = $this->request->query['start'];
        }
        
        $query->page($page);
        $query->limit($limit);
        $query->offset($offset);

        $total  = $query->count();       
        $q_r    = $query->all();
        $items  = array();
        
        foreach($q_r as $i){
        
            $owner_id   = $i->parent_id;
            if($owner_id == ''){ //The root user does not have a parent and is thus empty. We assume then it is owned by itself
                $owner_id = $i->id;
            }
            
            if(!array_key_exists($owner_id,$this->owner_tree)){
                $owner_tree     = $this->Users->find_parents($owner_id);
            }else{
                $owner_tree = $this->owner_tree[$owner_id];
            }
            
            
            
            $action_flags   = $this->Aa->get_action_flags($owner_id,$user); 
            $notes_flag     = false;
            foreach($i->user_notes as $un){
                if(!$this->Aa->test_for_private_parent($un->note,$user)){
                    $notes_flag = true;
                    break;
                }
            } 
            
            $row        = array();
            $fields     = $this->{$this->main_model}->schema()->columns();
            foreach($fields as $field){
                $row["$field"]= $i->{"$field"};
                
                if($field == 'created'){
                    $row['created_in_words'] = $this->TimeCalculations->time_elapsed_string($i->{"$field"});
                }
                if($field == 'modified'){
                    $row['modified_in_words'] = $this->TimeCalculations->time_elapsed_string($i->{"$field"});
                }   
            } 
            
            //Unset password and token fields
            unset($row["password"]);
            unset($row["token"]);
            
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
    
    public function indexTree(){
        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }
             
        $user_id    = $user['id'];
        $items      = array();
    
        if(isset($this->request->query['node'])){
            if($this->request->query['node'] == 0){ //This is the root node.
                $id = $user_id;
            }else{
                $id = $this->request->query['node'];
            }
        }
               
        //We only will list the first level of nodes
        $ap_name    = Configure::read('group.ap');
        $query      = $this->{$this->main_model}->find();
        $q_r        = $query->where([$this->main_model.'.parent_id' => $id])->all();
        
        foreach($q_r as $i){
            $id         = $i->id;
            $parent_id  = $i->parent_id;
            $username   = $i->username;
            $leaf       = false;
            $icon       = 'users';
            
            $entity = $this->Users->get($id); 
            if($this->Users->childCount($entity) == 0){
                $leaf = true;
                $icon = 'user';
            }

            array_push($items,
                array('id' => $id, 'username' => $username,'leaf' => $leaf,'iconCls' => $icon)
            ); 
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
  
        $check_items = array(
			'active',
			'monitor'
		);
        foreach($check_items as $i){
            if(isset($this->request->data[$i])){
                $this->request->data[$i] = 1;
            }else{
                $this->request->data[$i] = 0;
            }
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
        $q_r        = $this->Groups->find()->where(['Groups.name' => $ap_name])->first();
        $group_id   = $q_r->id;
        $this->request->data['group_id'] = $group_id;

        //Zero the token to generate a new one for this user:
        $this->request->data['token'] = '';

        //The rest of the attributes should be same as the form..
        $entity = $this->{$this->main_model}->newEntity($this->request->data()); 
        if($this->{$this->main_model}->save($entity)){
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }else{
            $message = 'Error';
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
    
    public function view(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $items = array();
        if(isset($this->request->query['ap_id'])){
            $q_r = $this->{$this->main_model}->find()->where([$this->main_model.'.id' => $this->request->query['ap_id']])->first();
            if($q_r){
                $owner_tree         = $this->Users->find_parents($q_r->parent_id);
                $items['id']        = $q_r->id;
                $items['username']  = $q_r->username;
                $items['name']      = $q_r->name;
                $items['surname']   = $q_r->surname;
                $items['phone']     = $q_r->phone;
                $items['address']   = $q_r->address;
                $items['email']     = $q_r->email;
                $items['active']    = $q_r->active;
                $items['monitor']   = $q_r->monitor;
                $items['owner']     = $owner_tree;
                $language           = $q_r->country_id.'_'.$q_r->language_id;
                $items['language']  = $language;
                
                //Get the White Label Detail
                $wl = $this->WhiteLabel->detail($this->request->query['ap_id']);
                
                $items = array_merge($items, $wl);   
            }
        }
        
        $this->set(array(
            'data'     => $items,
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
    }
  
    public function edit(){
    
         //This is a deviation from the standard JSON serialize view since extjs requires a html type reply when files
        //are posted to the server.    
        $this->viewBuilder()->layout('ext_file_upload');
    
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
        
        $ap_id = $this->request->data['id'];

        $check_items = array(
			'active',
			'monitor',
			'wl_active',
			'wl_img_active',
		);
        foreach($check_items as $i){
            if(isset($this->request->data[$i])){
                $this->request->data[$i] = 1;
            }else{
                $this->request->data[$i] = 0;
            }
        }    

        //Get the language and country
        $country_language   = explode( '_', $this->request->data['language'] );

        $country            = $country_language[0];
        $language           = $country_language[1];

        $this->request->data['language_id'] = $language;
        $this->request->data['country_id']  = $country;
        
        $entity = $this->{$this->main_model}->get($this->request->data['id']);
        $this->{$this->main_model}->patchEntity($entity, $this->request->data());
        
        //The White Label stuff ONLY if it is enabled Site Wide
        
        if(Configure::read('whitelabel.active') == true){
            $this->loadModel('UserSettings');
            $looking_for    = ['wl_active','wl_header','wl_h_bg','wl_h_fg','wl_footer','wl_img_active','wl_img_file'];
        
            //Only if it is enabled do we do it
            if($this->request->data['wl_active'] == 1){
            
                $new_logo = false;
            
                //Check if there came some image with it
                if(isset($_FILES['wl_img_file_upload'])){
                    if($_FILES['wl_img_file_upload']['size'] > 0){    
                        $path_parts     = pathinfo($_FILES['wl_img_file_upload']['name']);
                        $unique         = time();
                        $filename       = $unique.'.'.$path_parts['extension'];
                        $dest           = WWW_ROOT."img/access_providers/".$filename;
                        move_uploaded_file ($_FILES['wl_img_file_upload']['tmp_name'] , $dest);
                        $new_logo       = true;
                    }
                    
                    if($new_logo == true){
                        $this->UserSettings->deleteAll(['user_id' => $ap_id,'name' => 'wl_img_file']);
                        $entity = $this->UserSettings->newEntity();
                        $entity->user_id    = $ap_id;
                        $entity->name       = 'wl_img_file';
                        $entity->value      = $filename;
                        $this->UserSettings->save($entity);
                    }
                }   
            
                foreach($looking_for as $i){    
                    //Delete old one
                    if(($i == 'wl_img_file')&&($new_logo == true)){
                        continue; //We skip it if we added a new file
                    }
                      
                    $this->UserSettings->deleteAll(['user_id' => $ap_id,'name' => $i]);
                    //Add a New ONE
                    $entity             = $this->UserSettings->newEntity();
                    $entity->user_id    = $ap_id;
                    $entity->name       = $i;
                    $entity->value      = $this->request->data["$i"];
                    $this->UserSettings->save($entity);
                }
            }else{
                //Only disable it
                $this->UserSettings->deleteAll(['user_id' => $ap_id,'name' => 'wl_active']);
                $entity = $this->UserSettings->newEntity();
                $entity->user_id    = $ap_id;
                $entity->name       = 'wl_active';
                $entity->value      = 0;
                $this->UserSettings->save($entity);
            }  
        }

        if ($this->{$this->main_model}->save($entity)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        } else {
            $message = 'Error';
            
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
     
    public function menuForGrid(){
        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }
        
        $menu = $this->GridButtons->returnButtons($user,true,'access_providers');
        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
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

	    if(isset($this->request->data['id'])){   //Single item delete
            $message = "Single item ".$this->request->data['id'];

            //NOTE: we first check of the user_id is the logged in user OR a sibling of them:         
            $entity     = $this->{$this->main_model}->get($this->request->data['id']);   
            $owner_id   = $entity->parent_id;
            
            if($owner_id != $user_id){
                if($this->{$this->main_model}->is_sibling_of($user_id,$owner_id)== true){
                    $this->{$this->main_model}->delete($entity);
                    $this->Users->recover();
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->{$this->main_model}->delete($entity);
                $this->Users->recover();
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->request->data as $d){
                $entity     = $this->{$this->main_model}->get($d['id']);  
                $owner_id   = $entity->parent_id;
                if($owner_id != $user_id){
                    if($this->{$this->main_model}->is_sibling_of($user_id,$owner_id) == true){
                        $this->{$this->main_model}->delete($entity);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->{$this->main_model}->delete($entity);
                    $this->Users->recover();
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
	
	public function changePassword(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $success = false;
        if(isset($this->request->data['user_id'])){
            $entity = $this->{$this->main_model}->get($this->request->data['user_id']); 
            $data = [
                'password'  => $this->request->data['password'],
                'token'     => ''
            ];
            $this->{$this->main_model}->patchEntity($entity, $data);
            $this->{$this->main_model}->save($entity);
            $success               = true;  
        }

        $this->set(array(
            'success' => $success,
            '_serialize' => array('success',)
        ));
    }
    
    public function enableDisable(){     
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $rb         = $this->request->data['rb'];
        if($rb == 'enable'){
            $active = 1;
        }else{
            $active = 0;
        }
        foreach(array_keys($this->request->data) as $key){
            if(preg_match('/^\d+/',$key)){  
                $entity = $this->{$this->main_model}->get($key);
                $entity->active = $active;
                $this->{$this->main_model}->save($entity);  
            }
        }
        $this->set(array(
            'success' => true,
            '_serialize' => array('success',)
        ));
    }
	
	public function childCheck(){
        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }
        $user_id  = $user['id'];
        $tree     = false;     
        $entity = $this->Users->get($user_id); 
        if($this->Users->childCount($entity) > 0){
            $tree = true;
        }
        $items['tree'] = $tree;
        $this->set(array(
            'items'         => $items,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }
    
    public function noteIndex(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $items = $this->Notes->index($user); 
    }
    
    public function noteAdd(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }   
        $this->Notes->add($user);
    }
    
    public function noteDel(){  
        if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $this->Notes->del($user);
    }
	
}
