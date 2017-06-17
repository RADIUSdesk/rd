<?php
App::uses('AppController', 'Controller');

class DashboardController extends AppController {

    public $name       = 'Dashboard';
    public $components = array('Aa');   //We'll use the Aa component to determine certain rights
    protected $base    = "Access Providers/Controllers/Dashboard/";
    public $uses       = array('User', 'UserSetting','Realm');


    public function authenticate(){

        $this->Auth = $this->Components->load('Auth');
        $this->request->data['User']['username']     = $this->request->data['username'];
        $this->request->data['User']['password']     = $this->request->data['password'];

        if($this->Auth->identify($this->request,$this->response)){
            
            //We can get the detail for the user
            $data = $this->_get_user_detail($this->request->data['User']['username']);
            $this->set(array(
                'data'          => $data,
                'success'       => true,
                '_serialize' => array('data','success')
            ));

        }else{
            //We can get the detail for the user

            $this->set(array(
                'errors'        => array('username' => __('Confirm this name'),'password'=> __('Type the password again')),
                'success'       => false,
                'message'       => array('message'  => __('Authentication failed')),
                '_serialize' => array('errors','success','message')
            ));
        }
    }

    public function check_token(){

        if((isset($this->request->query['token']))&&($this->request->query['token'] != '')){

            $token      = $this->request->query['token'];

            $q_r        = $this->User->find('first',array(
                'conditions'    => array('User.token' => $token)
            ));

            if($q_r == ''){

                $this->set(array(
                    'errors'        => array('token'=>'invalid'),
                    'success'       => false,
                    '_serialize'    => array('errors','success')
                ));
  
            }else{
                $data = $this->_get_user_detail($q_r['User']['username']);
                $this->set(array(
                    'data'          => $data,
                    'success'       => true,
                    '_serialize'    => array('data','success')
                ));
            }
        }else{

            $this->set(array(
                'errors'        => array('token'=>'missing'),
                'success'       => false,
                '_serialize'    => array('errors','success')
            ));
        }
    }
    
    public function utilities_items(){       
        $data = array(
            array(
                'xtype'   => 'button',
                'text'    => 'RADIUS Client',
                'glyph'   => Configure::read('icnRadius'),
                'scale'   => 'large',
                'itemId'  => 'btnRadiusClient'
            ),
            array(
                'xtype'   => 'button',
                'text'    => 'Password Manager',
                'glyph'   => Configure::read('icnKey'),
                'scale'   => 'large',
                'itemId'  => 'btnPassword'
            ),
            array(
                'xtype'   => 'button',
                'text'    => 'Activity Monitor',
                'glyph'   => Configure::read('icnActivity'),
                'scale'   => 'large',
                'itemId'  => 'btnActivityMonitor'
            ),
            array(
                'xtype'   => 'button',
                'text'    => 'Data Usage',
                'glyph'   => Configure::read('icnData'),
                'scale'   => 'large',
                'itemId'  => 'btnDataUsage'
            ),
            array(
                'xtype'   => 'button',
                'text'    => 'Setup Wizard',
                'glyph'   => Configure::read('icnWizard'),
                'scale'   => 'large',
                'itemId'  => 'btnSetupWizard'
            )
        );
        
        $this->set(array(
            'data'   => $data,
            'success' => true,
            '_serialize' => array('success','data')
        ));
    
    }
    
    public function settings_view(){
        $user = $this->Aa->user_for_token($this);
        if(!$user){
            return;
        }
        
        $user_id    = $user['id'];
        
       // print_r($user);
        
        $data = array();
        $this->UserSetting->contain();
        
        $data['show_data_usage']        = 'show_data_usage';
        $data['show_recent_failures']   = 'show_recent_failures';
        
        $q_rf = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'show_recent_failures')));
        if($q_rf){
            $val_rf = 0;
            if($q_rf['UserSetting']['value'] == 1){
                $val_rf = 'show_recent_failures';
            }
            $data['show_recent_failures'] = $val_rf;
        }
        
        $q_rdu = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'show_data_usage')));
        if($q_rdu){
        
            $val_du = 0;
            if($q_rdu['UserSetting']['value'] == 1){
                $val_du = 'show_data_usage';
            }
            $data['show_data_usage'] = $val_du;
        }
        
        //Now for the more difficult bit finding the default realm if there are not one.
        $q_rr = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'realm_id')));
        if($q_rr){
            $this->Realm->contain();
            $q_r                = $this->Realm->findById($q_rr['UserSetting']['value']);
            $realm_name         = $q_r['Realm']['name'];
            $data['realm_name'] = $realm_name;
            $data['realm_id']   = $q_rr['UserSetting']['value'];
        }else{
            //We need to find the first valid realm
            if($user['group_name'] == 'Administrators'){
                $this->Realm->contain();
                $q_r            = $this->Realm->find('first',array());
                if($q_r){
                    $realm_name         = $q_r['Realm']['name'];
                    $data['realm_name'] = $realm_name;
                    $data['realm_id']   = $q_r['Realm']['id'];
                }
            }
            
            if($user['group_name'] == 'Access Providers'){
                $realm_detail = $this->_ap_default_realm($user_id);
                if(array_key_exists('realm_id',$realm_detail)){
                    $data['realm_name'] = $realm_detail['realm_name'];
                    $data['realm_id']   = $realm_detail['realm_id'];
                }
            }    
        }
        
        
        $this->set(array(
            'data'   => $data,
            'success' => true,
            '_serialize' => array('success','data')
        ));
    
    }
    
    private function _ap_default_realm($ap_id){
    
        $realm = array();
        $q_r   = $this->User->getPath($ap_id); //Get all the parents up to the root 
        
        $found_flag = false;  
               
        foreach($q_r as $i){    
            $user_id    = $i['User']['id'];
            $this->Realm->contain();
            $r        = $this->Realm->find('all',array('conditions' => array('Realm.user_id' => $user_id, 'Realm.available_to_siblings' => true)));
            foreach($r  as $j){
                $id     = $j['Realm']['id'];
                $name   = $j['Realm']['name'];
                $read = $this->Acl->check(
                            array('model' => 'User', 'foreign_key' => $ap_id), 
                            array('model' => 'Realm','foreign_key' => $id), 'read');
                if($read == true){
                    $realm['realm_id']      = $id;
                    $realm['realm_name']    = $name;
                    $found_flag = true;
                    break; // We only need one 
                }
            }
        }

        //All the realms owned by anyone this access provider created (and also itself) 
        //will automatically be under full controll of this access provider  
        if($found_flag == false){
            
            $this->children    = $this->User->find_access_provider_children($ap_id);
            $tree_array     = array();
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $id = $i['id'];
                    array_push($tree_array,array('Realm.user_id' => $id));
                }       
            }  
            $this->Realm->contain();
            $r_sub  = $this->Realm->find('all',array('conditions' => array('OR' => $tree_array))); 
            foreach($r_sub  as $j){
                $realm['realm_id']     = $j['Realm']['id'];
                $realm['realm_name']   = $j['Realm']['name'];
                break; //We only need one
            }
        }
 
        return $realm;
    }
    
     public function settings_submit(){
        $user = $this->Aa->user_for_token($this);
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        
         //Make available to siblings check
       
        
        if(isset($this->request->data['realm_id'])){
        
            if(isset($this->request->data['show_data_usage'])){
                $this->request->data['show_data_usage'] = 1;
            }else{
                $this->request->data['show_data_usage'] = 0;
            }
            
            if(isset($this->request->data['show_recent_failures'])){
                $this->request->data['show_recent_failures'] = 1;
            }else{
                $this->request->data['show_recent_failures'] = 0;
            }
        
            //Delete old entries (if there are any)
            $this->UserSetting->deleteAll(array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'realm_id'), false);
            $this->UserSetting->deleteAll(array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'show_recent_failures'), false);
            $this->UserSetting->deleteAll(array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'show_data_usage'), false);
        
            $d['UserSetting']['user_id']= $user_id;
            $d['UserSetting']['name']   = 'realm_id';
            $d['UserSetting']['value']  = $this->request->data['realm_id'];
            $this->UserSetting->create();
            $this->UserSetting->save($d);
            $this->UserSetting->id = null;
            
            $d['UserSetting']['user_id']= $user_id;
            $d['UserSetting']['name']   = 'show_recent_failures';
            $d['UserSetting']['value']  = $this->request->data['show_recent_failures'];
            $this->UserSetting->create();
            $this->UserSetting->save($d);
            $this->UserSetting->id = null;
            
            $d['UserSetting']['user_id']= $user_id;
            $d['UserSetting']['name']   = 'show_data_usage';
            $d['UserSetting']['value']  = $this->request->data['show_data_usage'];
            $this->UserSetting->create();
            $this->UserSetting->save($d);
            $this->UserSetting->id = null;


        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    public function change_password(){
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $d                      = array();
        $d['User']['id']        = $user_id;
        $d['User']['password']  = $this->request->data['password'];
        $d['User']['token']     = '';
        
        $this->User->contain();
        $this->User->id         = $user_id;
        $this->User->save($d);
        $q_r                    = $this->User->findById($user_id);
        $data['token']          = $q_r['User']['token'];

        $this->set(array(
            'success' => true,
            'data'    => $data,
            '_serialize' => array('success','data')
        ));
    }

    private function _get_user_detail($username){

        $this->User->contain('Group');
        $q_r        = $this->User->find('first',array('conditions'    => array('User.username' => $username)));
        $token      = $q_r['User']['token'];
        $id         = $q_r['User']['id'];
        $group      = $q_r['Group']['name'];
        $username   = $q_r['User']['username'];

        $cls        = 'user';
        $menu       = array();

        $isRootUser = false;

        if( $group == Configure::read('group.admin')){  //Admin
            $cls = 'admin';
            $tabs= $this->_build_admin_tabs($id);  //We do not care for rights here;
            $isRootUser = true;
        }
        if( $group == Configure::read('group.ap')){  //Or AP
            $cls = 'access_provider';
            $tabs= $this->_build_ap_tabs($id);  //We DO care for rights here!
        }

        $wp_url = Configure::read('paths.wallpaper_location').Configure::read('user_settings.wallpaper');
        //Check for personal overrides
        $q = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $id,'UserSetting.name' => 'wallpaper')));
        if($q){
            $wp_base = $q['UserSetting']['value'];
            $wp_url = Configure::read('paths.wallpaper_location').$wp_base;
        }

        return array(
            'token'         =>  $q_r['User']['token'],
            'isRootUser'    =>  $isRootUser,
            'tabs'          =>  $tabs,
            'data_usage'    => array('realm_id' => $this->realm_id, 'realm_name' => $this->realm_name),
            'user'          =>  array('id' => $id, 'username' => $username,'group' => $group,'cls' => $cls)
        );
    }

    private function _build_admin_tabs($user_id){
    
        $tabs = array(
            array(
                'title'   => __('Admin'),
                'glyph'   => Configure::read('icnAdmin'),
                'xtype'   => 'tabpanel',
                'layout'  => 'fit',
                'items'   => array(
                    array(
                        'title'   => __('Admins'),
                        'glyph'   => Configure::read('icnAdmin'),
                        'id'      => 'cAccessProviders',
                        'layout'  => 'fit'
                    ),
                    array(
                        'title'   => __('Realms (Groups)'),
                        'glyph'   => Configure::read('icnRealm'),
                        'id'      => 'cRealms',
                        'layout'  => 'fit'
                    )
                )
            
            ),
            array(
                'title'   => __('Users'),
                'xtype'   => 'tabpanel',
                'glyph'   => Configure::read('icnUser'),
                'layout'  => 'fit',
                'items'   => array(
                    array(
                        'title'     => __('Permanent Users'),
                        'glyph'     => Configure::read('icnUser'),
                        'id'        => 'cPermanentUsers',
                        'layout'    => 'fit'
                    ),
                    array(
                        'title'     => __('Vouchers'),
                        'glyph'     => Configure::read('icnVoucher'),
                        'id'        => 'cVouchers',
                        'layout'    => 'fit'
                    ),
                    array(
                        'title'     => __('BYOD'),
                        'glyph'     => Configure::read('icnDevice'),
                        'id'        => 'cDevices',
                        'layout'    => 'fit'
                    ),
                    array(
                        'title'     => __('Top-Ups'),
                        'glyph'     => Configure::read('icnTopUp'),
                        'id'        => 'cTopUps',
                        'layout'    => 'fit'
                    ),
                    
                )
               
            ), 
            array(
                'title'   => __('Profiles'),
                'glyph'   => Configure::read('icnProfile'),
                'xtype'   => 'tabpanel',
                'layout'  => 'fit',
                'items'   => array(
                    array(
                        'title'   => __('Profile Components'),
                        'glyph'   => Configure::read('icnComponent'),
                        'id'      => 'cProfileComponents',
                        'layout'  => 'fit'
                    ),
                    array(
                        'title'   => __('Profiles'),
                        'glyph'   => Configure::read('icnProfile'),
                        'id'      => 'cProfiles',
                        'layout'  => 'fit'
                    )   
                )
            ), 
            array(
                'title'   => __('RADIUS'),
                'glyph'   => Configure::read('icnRadius'),
                'xtype'   => 'tabpanel',
                'layout'  => 'fit',
                'items'   => array(
                    array(
                        'title'   => __('Dynamic RADIUS Clients'),
                        'glyph'   => Configure::read('icnDynamicNas'),
                        'id'      => 'cDynamicClients',
                        'layout'  => 'fit'
                    ),
                    array(
                        'title'   => __('NAS Devices'),
                        'glyph'   => Configure::read('icnNas'),
                        'id'      => 'cNas',
                        'layout'  => 'fit'
                    ),
                    array(
                        'title'   => __('NAS Device Tags'),
                        'glyph'   => Configure::read('icnTag'),
                        'id'      => 'cTags',
                        'layout'  => 'fit'
                    ),
                    array(
                        'title'   => __('SSIDs'),
                        'glyph'   => Configure::read('icnSsid'),
                        'id'      => 'cSsids',
                        'layout'  => 'fit'
                    )  
                )
            ), 
            array(
                'title'   => __('MESHdesk'),
                'glyph'   => Configure::read('icnMesh'),
                'id'      => 'cMeshes',
                'layout'  => 'fit'
            ),
            array(
                'title'   => __('APdesk'),
                'glyph'   => Configure::read('icnCloud'),
                'id'      => 'cAccessPoints',
                'layout'  => 'fit' 
            ),
            array(
                'title'   => __('Other'),
                'glyph'   => Configure::read('icnGears'),
                'xtype'   => 'tabpanel',
                'layout'  => 'fit',
                'items'   => array(
                     array(
                        'title'   => __('Dynamic Login Pages'),
                        'glyph'   => Configure::read('icnDynamic'),
                        'id'      => 'cDynamicDetails',
                        'layout'  => 'fit'
                    ),
                    array(
                        'title'   => __('OpenVPN Servers'),
                        'glyph'   => Configure::read('icnVPN'),
                        'id'      => 'cOpenvpnServers',
                        'layout'  => 'fit'
                    ),
                    array(
                        'title'   => __('IP Pools'),
                        'glyph'   => Configure::read('icnIP'),
                        'id'      => 'cIpPools',
                        'layout'  => 'fit'
                    ),
                    array(
                        'title'   => __('Rights Manager'),
                        'glyph'   => Configure::read('icnKey'),
                        'id'      => 'cAcos',
                        'layout'  => 'fit'
                    ),
                    array(
                        'title'   => __('Logfile Viewer'),
                        'glyph'   => Configure::read('icnLog'),
                        'id'      => 'cLogViewer',
                        'layout'  => 'fit'
                    ),
                    array(
                        'title'   => __('Debug Output'),
                        'glyph'   => Configure::read('icnBug'),
                        'id'      => 'cDebug',
                        'layout'  => 'fit'
                    )    
                )
              )
        ); 
              
        //____ Overview Tab ___
        //This one is a bit different :-)
        $overview_items = array();
        
        //Find out if there is a dafault setting for the realm.
        $show_data_usage        = true;
        $show_recent_failures   = true;
        $realm_blank            = false;
        
        //Find if there is a realm specified in the settings
        $q_rr = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'realm_id')));
        if($q_rr){
            //Get the name of the realm
            $this->Realm->contain();
            $q_r                = $this->Realm->findById($q_rr['UserSetting']['value']);
            if($q_r){
                $realm_name         = $q_r['Realm']['name'];
                $data['realm_name'] = $realm_name;
                $data['realm_id']   = $q_rr['UserSetting']['value'];
                
                $this->realm_name   = $realm_name;
                $this->realm_id     = $q_rr['UserSetting']['value'];
               
                //Get the settings of whether to show the two tabs
                $q_rdu = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'show_data_usage')));
                
                if($q_rdu['UserSetting']['value'] == 0){
                    $show_data_usage = false;
                }
                
                $q_rf = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'show_recent_failures')));
                
                if($q_rf['UserSetting']['value'] == 0){
                    $show_recent_failures = false;
                }
            }else{            
                $realm_blank = true;
            }       
        //No realm specified in settings; get a default one (if there might be one )    
        }else{ 
            $this->Realm->contain();
            $q_r            = $this->Realm->find('first',array());
            if($q_r){
                $realm_name         = $q_r['Realm']['name'];
                $data['realm_name'] = $realm_name;
                $data['realm_id']   = $q_r['Realm']['id'];
                
                $this->realm_name   = $realm_name;
                $this->realm_id     = $q_r['Realm']['id'];
            }else{
                $realm_blank = true;
            }
        }
        
        //We found a realm and should display it
        if(($realm_blank == false)&&($show_data_usage == true)){
            array_push($overview_items, array(
                    'title'   => __('Data Usage'),
                    'glyph'   => Configure::read('icnData'),
                    'id'      => 'cDataUsage',
                    'layout'  => 'fit'
                )
            );
        }else{
        
            //We could not find a realm and should display a welcome message
            if($realm_blank == true){
                array_push($overview_items, array(
                        'title'   => __('Welcome Message'),
                        'glyph'   => Configure::read('icnNote'),
                        'id'      => 'cWelcome',
                        'layout'  => 'fit'
                    )
                );
            }
        }
        
        //We found a realm and should display it
        if(($realm_blank == false)&&($show_recent_failures == true)){
         /*   array_push($overview_items, array(
                    'title'   => __('Recent Failures'),
                        'glyph'   => Configure::read('icnBan'),
                        'id'      => 'cRejects',
                        'layout'  => 'fit'
                )
            );*/
        } 
       
        
        array_push($overview_items, array(
                'title'   => __('Utilities'),
                'glyph'   => Configure::read('icnGears'),
                'id'      => 'cUtilities',
                'layout'  => 'fit'
            )
        );
               
        array_unshift($tabs,array(
            'title'     => __('Overview'),
            'xtype'     => 'tabpanel',
            'glyph'     => Configure::read('icnView'),
            'itemId'    => 'tpOverview',
            'layout'    => 'fit',
            'items'     => $overview_items
        ));   
                
        return $tabs;
    }
    
    private function _build_ap_tabs($id){
        $tabs   = array();
        $user_id = $id;
        
        //Base to start looking from.
        $base   = "Access Providers/Controllers/"; 
           
        
        //____ Overview Tab ___
        //This one is a bit different :-)
        $overview_items = array();
        
        //Find out if there is a dafault setting for the realm.
        $show_data_usage        = true;
        $show_recent_failures   = true;
        $realm_blank            = false;
        
        //Find if there is a realm specified in the settings
        $q_rr = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'realm_id')));
        if($q_rr){
            //Get the name of the realm
            $this->Realm->contain();
            $q_r                = $this->Realm->findById($q_rr['UserSetting']['value']);
            $realm_name         = $q_r['Realm']['name'];
            $data['realm_name'] = $realm_name;
            $data['realm_id']   = $q_rr['UserSetting']['value'];
            
            $this->realm_name   = $realm_name;
            $this->realm_id     = $q_rr['UserSetting']['value'];
           
            //Get the settings of whether to show the two tabs
            $q_rdu = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'show_data_usage')));
            
            if($q_rdu['UserSetting']['value'] == 0){
                $show_data_usage = false;
            }
            
            $q_rf = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'show_recent_failures')));
            
            if($q_rf['UserSetting']['value'] == 0){
                $show_recent_failures = false;
            }  
         
        //No realm specified in settings; get a default one (if there might be one )    
        }else{    
            $realm_detail = $this->_ap_default_realm($user_id);
            if(array_key_exists('realm_id',$realm_detail)){
                $data['realm_name'] = $realm_detail['realm_name'];
                $data['realm_id']   = $realm_detail['realm_id'];
                
                $this->realm_name   = $realm_detail['realm_name'];
                $this->realm_id     = $realm_detail['realm_id'];
            }else{ // Could not find a default realm
                $realm_blank = true;
            }  
        }
        
        //We found a realm and should display it
        if(($realm_blank == false)&&($show_data_usage == true)){
            array_push($overview_items, array(
                    'title'   => __('Data Usage'),
                    'glyph'   => Configure::read('icnData'),
                    'id'      => 'cDataUsage',
                    'layout'  => 'fit'
                )
            );
        }else{
        
            //We could not find a realm and should display a welcome message
            if($realm_blank == true){
                array_push($overview_items, array(
                        'title'   => __('Welcome Message'),
                        'glyph'   => Configure::read('icnNote'),
                        'id'      => 'cWelcome',
                        'layout'  => 'fit'
                    )
                );
            }
        }
        
        //We found a realm and should display it
        if(($realm_blank == false)&&($show_recent_failures == true)){
           /* array_push($overview_items, array(
                    'title'   => __('Recent Failures'),
                        'glyph'   => Configure::read('icnBan'),
                        'id'      => 'cRejects',
                        'layout'  => 'fit'
                )
            );*/
        } 
       
        
        array_push($overview_items, array(
                'title'   => __('Utilities'),
                'glyph'   => Configure::read('icnGears'),
                'id'      => 'cUtilities',
                'layout'  => 'fit'
            )
        );
            
        array_push($tabs, array(
                'title'     => __('Overview'),
                'xtype'     => 'tabpanel',
                'glyph'     => Configure::read('icnView'),
                'itemId'    => 'tpOverview',
                'layout'    => 'fit',
                'items'   => $overview_items
            )
        );
        
        
        //____ Admin Tab ____
        $admin_items = array();
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."AccessProviders/index")){
            array_push($admin_items, array(
                    'title'   => __('Admins'),
                    'glyph'   => Configure::read('icnAdmin'),
                    'id'      => 'cAccessProviders',
                    'layout'  => 'fit'
                )
            );
        }
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Realms/index")){
            array_push($admin_items, array(
                    'title'   => __('Realms (Groups)'),
                    'glyph'   => Configure::read('icnRealm'),
                    'id'      => 'cRealms',
                    'layout'  => 'fit'
                )
            );
        }

        if(count($admin_items) > 0){
            array_push($tabs, array(
                    'title'   => __('Admin'),
                    'glyph'   => Configure::read('icnAdmin'),
                    'xtype'   => 'tabpanel',
                    'layout'  => 'fit',
                    'items'   => $admin_items
                )
            );
        }
        
        //____ Users Tab ____   
        $users_items = array();
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."PermanentUsers/index")){
            array_push($users_items, array(
                    'title'     => __('Permanent Users'),
                    'glyph'     => Configure::read('icnUser'),
                    'id'        => 'cPermanentUsers',
                    'layout'    => 'fit'                   
                )
            );
        
        }
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Vouchers/index")){
            array_push($users_items, array(
                    'title'     => __('Vouchers'),
                    'glyph'     => Configure::read('icnVoucher'),
                    'id'        => 'cVouchers',
                    'layout'    => 'fit'          
                )
            );
        }
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Devices/index")){
            array_push($users_items, array(
                    'title'     => __('BYOD'),
                    'glyph'     => Configure::read('icnDevice'),
                    'id'        => 'cDevices',
                    'layout'    => 'fit'       
                )
            );
        }
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."TopUps/index")){
            array_push($users_items, array(
                    'title'     => __('Top-Ups'),
                    'glyph'     => Configure::read('icnTopUp'),
                    'id'        => 'cTopUps',
                    'layout'    => 'fit'
                )
            ); 
        }
        
        if(count($admin_items) > 0){
            array_push($tabs, array(
                    'title'   => __('Users'),
                    'xtype'   => 'tabpanel',
                    'glyph'   => Configure::read('icnUser'),
                    'layout'  => 'fit',
                    'items'   => $users_items
                )
            );
        }
        
        //____ Profiles Tab ____   
        $profile_items = array();
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."ProfileComponents/index")){
            array_push($profile_items, array(
                    'title'   => __('Profile Components'),
                    'glyph'   => Configure::read('icnComponent'),
                    'id'      => 'cProfileComponents',
                    'layout'  => 'fit'          
                )
            );
        }
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Profiles/index")){
            array_push($profile_items, array(
                    'title'   => __('Profiles'),
                    'glyph'   => Configure::read('icnProfile'),
                    'id'      => 'cProfiles',
                    'layout'  => 'fit'            
                )
            );
        }
        
        if(count($profile_items) > 0){
            array_push($tabs, array(
                'title'   => __('Profiles'),
                'glyph'   => Configure::read('icnProfile'),
                'xtype'   => 'tabpanel',
                'layout'  => 'fit',
                'items'   => $profile_items
                )
            );
        }
        
        //____ RADIUS Tab ____  
        $radius_items = array();
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."DynamicClients/index")){
            array_push($radius_items, array(
                    'title'   => __('Dynamic RADIUS Clients'),
                    'glyph'   => Configure::read('icnDynamicNas'),
                    'id'      => 'cDynamicClients',
                    'layout'  => 'fit'             
                )
            );
        }
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Nas/index")){
            array_push($radius_items, array(
                    'title'   => __('NAS Devices'),
                    'glyph'   => Configure::read('icnNas'),
                    'id'      => 'cNas',
                    'layout'  => 'fit'           
                )
            );
        }
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Tags/index")){
            array_push($radius_items, array(
                    'title'   => __('NAS Device Tags'),
                    'glyph'   => Configure::read('icnTag'),
                    'id'      => 'cTags',
                    'layout'  => 'fit'
                              
                )
            );
        }
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Ssids/index")){
            array_push($radius_items, array(
                    'title'   => __('SSIDs'),
                    'glyph'   => Configure::read('icnSsid'),
                    'id'      => 'cSsids',
                    'layout'  => 'fit'             
                )
            );
        }
        
        if(count($radius_items) > 0){
            array_push($tabs, array(
                'title'   => __('RADIUS'),
                'glyph'   => Configure::read('icnRadius'),
                'xtype'   => 'tabpanel',
                'layout'  => 'fit',
                'items'   => $radius_items
                )
            );
        }
        
        //___ MESHdesk tab ___
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Meshes/index")){
             array_push($tabs, array(
                    'title'   => __('MESHdesk'),
                    'glyph'   => Configure::read('icnMesh'),
                    'id'      => 'cMeshes',
                    'layout'  => 'fit'
                )
            );
        }
        
        //___ APdesk tab ___
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."ApProfiles/index")){
             array_push($tabs, array(
                    'title'   => __('APdesk'),
                    'glyph'   => Configure::read('icnCloud'),
                    'id'      => 'cAccessPoints',
                    'layout'  => 'fit' 
                )
            );
        }
        
        // ____ Other Tab ____
        
        $other_items = array();
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."DynamicDetails/index")){
            array_push($other_items, array(
                    'title'   => __('Dynamic Login Pages'),
                    'glyph'   => Configure::read('icnDynamic'),
                    'id'      => 'cDynamicDetails',
                    'layout'  => 'fit' 
                )
            );
        }
        
        if(count($other_items) > 0){
            array_push($tabs, array(
                'title'   => __('Other'),
                'glyph'   => Configure::read('icnGears'),
                'xtype'   => 'tabpanel',
                'layout'  => 'fit',
                'items'   => $other_items
                )
            );
        }
                
        return $tabs;
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

}
