<?php


class LimitBehavior extends ModelBehavior {

    public function setup(Model $Model, $settings = array()) {
        Configure::load('Limits'); 
        if (!isset($this->settings[$Model->alias])) {
            $this->settings[$Model->alias] = array(
                'option1_key' => 'option1_default_value'
            );
        }
        $this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], (array)$settings);
    }

    public function beforeSave(Model $model,$options = array()) {
    
         //Only when we're adding
        $alias = $model->alias;
        $is_active = Configure::read('Limits.Global.Active');
        if($is_active){
            //Ap Node and Device are not owned directly by an Access Provider. 
            //They are owned by ApProfile / Mesh / PermanentUser respectively
            if($alias == 'Ap'){
                return($this->_find_limit_for_aps($model));
            }
            
            if($alias == 'Node'){
                return($this->_find_limit_for_nodes($model));
            }
            
            if($alias == 'Device'){
                return($this->_find_limit_for_devices($model));
            }
              
            return($this->_find_limit_for_generic($model));
        }  
        return true;
    }
    
    private function _find_limit_for($user_id,$alias){ 
        $l   = ClassRegistry::init('Limit');  
        $q_r = $l->find('first',array('conditions' => array('Limit.user_id' => $user_id,'Limit.alias' =>$alias)));
        
        if($q_r){
            $active = $q_r['Limit']['active'];
            $count  = $q_r['Limit']['count'];
            if($active){
                return $count;
            }
            return false; //No Limit        
        }else{
            //Check if active
            if(Configure::read('Limits.'.$alias.'.Active')){ 
                $count = Configure::read('Limits.'.$alias.'.Count');
                return $count;
            }
            return false; //No Limit
        }
    }
    
    private function _find_add_flag($model){
        $alias      = $model->alias;
        $add_flag   = false;
        if(isset($model->data[$alias])){
            if(isset($model->data[$alias]['id'])){
                if($model->data[$alias]['id']==''){
                    $add_flag = true;    
                }   
            }else{ //ID missing so it means also ADD
                $add_flag = true;
            }
        }else{
            if(array_key_exists('id', $model->data)){
                if($model->data == ''){
                    $add_flag = true;
                    
                }else{
                    $add_flag = true;
                }
            }else{
                $add_flag = true;
            }
        }
        return $add_flag;
    }
    
    private function _find_user_id($model){
        $alias      = $model->alias;
        $user_id   = false;
        if(isset($model->data[$alias])){
            if(isset($model->data[$alias]['user_id'])){
                $user_id  = $model->data[$alias]['user_id'];   
            }  
        }else{
            if(array_key_exists('user_id', $model->data)){
                $user_id  = $model->data[$alias]['user_id'];  
            }
        }
        return $user_id;
    }
    
    private function _find_limit_for_generic($model){
        $alias    = $model->alias;
        $add_flag = $this->_find_add_flag($model);
        $user_id  = $this->_find_user_id($model); 
         
        if(($add_flag)&&($user_id)){   
            $user   = ClassRegistry::init('User');
            $user->contain('Group');
            $q_r = $user->findById($user_id);
            if($q_r){
                $group = $q_r['Group']['name'];
                if($group == Configure::read('group.ap')){ //Limits are only applied to Access Providers   
                    $l = $this->_find_limit_for($user_id,$alias);
                    if($l){
                        $current_count = $model->find('count',array('conditions' => array("$alias.user_id" => $user_id)));
                        if($current_count >= $l){
                            $model->validationErrors = array('name' => "Limit for $alias set to $l");
                            return false;
                        }
                    }
                }
            }
        }
        return true; //No Limits found
    }
    
    private function _find_limit_for_aps($model){
    
        $ap_profile     = ClassRegistry::init('ApProfile');
        $ap_profile->contain(array('User'=> 'Group')); 
        $ap_profile_id  = false;
        
        $add_flag = $this->_find_add_flag($model);
        if(!$add_flag){
            return;
        }
           
        //The model Data should contain ap_profile_id
        if(isset($model->data['Ap'])){
            if(isset($model->data['Ap']['ap_profile_id'])){
                if($model->data['Ap']['ap_profile_id']!=''){
                    $ap_profile_id =  $model->data['Ap']['ap_profile_id'];
                }   
            }
        }else{
            if(array_key_exists('ap_profile_id', $model->data)){
                if($model->data['ap_profile_id'] != ''){
                    $ap_profile_id =  $model->data['ap_profile_id']; 
                }
            }
        }
        
        if($ap_profile_id){
            $q_r = $ap_profile->findById($ap_profile_id);
            if($q_r['User']['Group']['name'] == Configure::read('group.ap')){  
                $spcl_limit = $this->_find_limit_for($q_r['User']['id'],'TotalDevices');
                if($spcl_limit){
                    $td_limit = $this->_test_total_devices_limit($q_r['User']['id'],$spcl_limit);
                    if($td_limit){
                        $model->validationErrors = array('name' => "Limit for Devices and Nodes combined set to $spcl_limit");
                        return false;
                    }
                }else{
                    $l = $this->_find_limit_for($q_r['User']['id'],'Ap');
                    if($l){
                        $current_count = $model->find('count',array('conditions' => array("Ap.ap_profile_id" => $ap_profile_id)));
                        if($current_count >= $l){
                            $model->validationErrors = array('name' => "Limit for Devices set to $l");
                            return false;
                        }
                    }
                }
            }
        }   
        return true; //No Limits found
    }
    
    private function _find_limit_for_nodes($model){ 
        $mesh     = ClassRegistry::init('Mesh');
        $mesh_id  = false;  
        $add_flag = $this->_find_add_flag($model);
        if(!$add_flag){
            return;
        }
           
        //The model Data should contain mesh_id
        if(isset($model->data['Node'])){
            if(isset($model->data['Node']['mesh_id'])){
                if($model->data['Node']['mesh_id']!=''){
                    $mesh_id =  $model->data['Node']['mesh_id'];
                }   
            }
        }else{
            if(array_key_exists('mesh_id', $model->data)){
                if($model->data['mesh_id'] != ''){
                    $mesh_id =  $model->data['mesh_id']; 
                }
            }
        }
        
        if($mesh_id){
        
            $mesh->contain(array('User'=> 'Group')); 
            $q_r = $mesh->findById($mesh_id);
            if($q_r['User']['Group']['name'] == Configure::read('group.ap')){
                   
                //Here we add a special limit which have overrriding powers
                $spcl_limit = $this->_find_limit_for($q_r['User']['id'],'TotalDevices');
                if($spcl_limit){
                    $td_limit = $this->_test_total_devices_limit($q_r['User']['id'],$spcl_limit);
                    if($td_limit){
                        $model->validationErrors = array('name' => "Limit for Devices and Nodes combined set to $spcl_limit");
                        return false;
                    }
                }else{
                    $l = $this->_find_limit_for($q_r['User']['id'],'Node');
                    if($l){
                        $current_count = $model->find('count',array('conditions' => array("Node.mesh_id" => $mesh_id)));
                        if($current_count >= $l){
                            $model->validationErrors = array('name' => "Limit for Nodes set to $l");
                            return false;
                        }
                    }
                }
            }
        }   
        return true; //No Limits found
    }
    
    private function _test_total_devices_limit($ap_id,$set_limit){
    
        //We need to find all the Meshes belonging to the Access Provider
        $mesh = ClassRegistry::init('Mesh');
        $node = ClassRegistry::init('Node');
        $node_total = 0;
        
        $mesh->contain();
        $m_list = $mesh->find('all',array('conditions' =>array('Mesh.user_id' => $ap_id)));
        foreach($m_list as $m){
            $mesh_id = $m['Mesh']['id'];
            $node->contain();
            $nc = $node->find('count',array('conditions' =>array('Node.mesh_id' =>$mesh_id)));
            $node_total = $node_total + $nc;
        }
        
        //We need to find all the Access Point Profiles belonging to the Access Provices
        $ap_profile = ClassRegistry::init('ApProfile');
        $ap         = ClassRegistry::init('Ap');
        $ap_total   = 0;
        
        $ap_profile->contain();
        $app_list = $ap_profile->find('all',array('conditions' =>array('ApProfile.user_id' => $ap_id)));
        foreach($app_list as $a){
            $ap_profile_id = $a['ApProfile']['id'];
            $ap->contain();
            $apc = $ap->find('count',array('conditions' =>array('Ap.ap_profile_id' =>$ap_profile_id)));
            $ap_total = $ap_total + $apc;
        }
        
        //Total and compare
        if(($ap_total+$node_total) >= $set_limit){
            return true; //More than allowed limit
        }
        return false;
    }
    
    private function _find_limit_for_devices($model){ 
        $pu     = ClassRegistry::init('PermanentUser');
        $pu->contain(array('User'=> 'Group')); 
        $pu_id  = false;   
        $add_flag = $this->_find_add_flag($model);
        if(!$add_flag){
            return;
        }
           
        //The model Data should contain permanent_user_id
        if(isset($model->data['Device'])){
            if(isset($model->data['Device']['permanent_user_id'])){
                if($model->data['Device']['permanent_user_id']!=''){
                    $pu_id =  $model->data['Device']['permanent_user_id'];
                }   
            }
        }else{
            if(array_key_exists('permanent_user_id', $model->data)){
                if($model->data['permanent_user_id'] != ''){
                    $pu_id =  $model->data['permanent_user_id']; 
                }
            }
        }
        
        if($pu_id){
            $q_r = $pu->findById($pu_id);
            if($q_r['User']['Group']['name'] == Configure::read('group.ap')){
                $l = $this->_find_limit_for($q_r['User']['id'],'Device');
                if($l){
                    $current_count = $model->find('count',array('conditions' => array("Device.permanent_user_id" => $pu_id)));
                    if($current_count >= $l){
                        $model->validationErrors = array('name' => "Limit for BYOD devices set to $l");
                        return false;
                    }
                }
            }
        }   
        return true; //No Limits found
    }
}
