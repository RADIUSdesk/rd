<?php
App::uses('AppController', 'Controller');

class DesktopController extends AppController {

    public $name       = 'Desktop';
    public $components = array('Aa');   //We'll use the Aa component to determine certain rights
    protected $base    = "Access Providers/Controllers/Desktop/";


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
            $this->User = ClassRegistry::init('User');

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

    public function list_wallpapers(){
        $items = array();
        //List all the wallpapres in the wallpaper directory:
        $wp_document_root   = "/usr/share/nginx/html";
        $r_wp_dir           = "/rd/resources/images/wallpapers/";
        $wp_dir             = "/usr/share/nginx/html/rd/resources/images/wallpapers/";

        $id = 1;

        if ($handle = opendir($wp_dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $regexp = "/^[0-9a-zA-z\.]+\.(gif|jpg|png|jpeg)$/"; //Match only images
                    if(preg_match($regexp, $entry)){
                      //  echo "$entry\n";
                        array_push($items, array(
                            'id'    => $id,
                            'file'  => $entry,
                            'r_dir' => $r_wp_dir,
                            'img'   => "/cake2/rd_cake/webroot/files/image.php?width=200&height=200&image=".$r_wp_dir.$entry
                            //'img'   => "/cake2/rd_cake/webroot/files/image.php/image-name.jpg?width=200&height=200&image=".$r_wp_dir.$entry
                        ));
                        $id++;
                    }     
                }
            }
            closedir($handle);
        }
        $this->set(array(
            'items'          => $items,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

    public function save_wallpaper_selection(){
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        if(isset($this->request->query['wallpaper'])){
            $path_parts     = pathinfo($this->request->query['wallpaper']);
            $this->UserSetting = ClassRegistry::init('UserSetting');
            $q_r = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $user_id,'UserSetting.name' => 'wallpaper')));
            if($q_r){
                $this->UserSetting->id = $q_r['UserSetting']['id'];    
                $this->UserSetting->saveField('value', $path_parts['basename']);
            }else{
                $d['UserSetting']['user_id']= $user_id;
                $d['UserSetting']['name']   = 'wallpaper';
                $d['UserSetting']['value']  = $path_parts['basename'];
                $this->UserSetting->create();
                $this->UserSetting->save($d);
            }
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
        
        $this->User             = ClassRegistry::init('User');
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

    public function desktop_shortcuts(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $items = array();
        if($user['group_name'] == Configure::read('group.admin')){ 
            $items = $this->_build_admin_shortcuts();
        }

        if($user['group_name'] == Configure::read('group.ap')){ 
            $items = $this->_build_ap_shortcuts($user_id);
        }

        $this->set(array(
            'success' => true,
            'items'    => $items,
            '_serialize' => array('success','items')
        ));

    }


    private function _get_user_detail($username){

        $this->User = ClassRegistry::init('User');
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
            $menu= $this->_build_admin_menus();  //We do not care for rights here;
            $isRootUser = true;
        }
        if( $group == Configure::read('group.ap')){  //Or AP
            $cls = 'access_provider';
            $menu= $this->_build_ap_menus($id);  //We DO care for rights here!
        }

        $wp_url = Configure::read('paths.wallpaper_location').Configure::read('user_settings.wallpaper');
        //Check for personal overrides
        $this->UserSetting = ClassRegistry::init('UserSetting');
        $q = $this->UserSetting->find('first',array('conditions' => array('UserSetting.user_id' => $id,'UserSetting.name' => 'wallpaper')));
        if($q){
            $wp_base = $q['UserSetting']['value'];
            $wp_url = Configure::read('paths.wallpaper_location').$wp_base;
        }

        return array(
            'token'         =>  $q_r['User']['token'],
            'isRootUser'    =>  $isRootUser,
            'menu'          =>  $menu,
            'user'          =>  array('id' => $id, 'username' => $username,'group' => $group,'cls' => $cls),
            'urlWallpaper'  =>  $wp_url,
            'shortcuts'     =>  array() 
        );
    }

    private function _build_admin_menus(){
        $menus = array(
            array(  'text'  => __('Realms and Providers'),  'glyph' => Configure::read('icnRealm') ,'menu'  =>
                 array( 'items' =>
                    array(
                        array('text' => __('Access Providers') ,'glyph' => Configure::read('icnKey'),   'itemId' => 'cAccessProviders'),
                        array('text' => __('Realms') ,          'glyph' => Configure::read('icnRealm'), 'itemId' => 'cRealms'),
						array('text' => __('SSIDs') ,           'glyph' => Configure::read('icnWifi'), 'itemId' => 'cSsids'),
                    )
                )
            ),
            array(  'text'  => __('NAS Devices'), 'glyph' => Configure::read('icnNas'), 'menu'  =>
                 array( 'items' =>
                    array(
                        array('text' => __('Dynamic RADIUS Clients') ,  'glyph' => Configure::read('icnDynamicNas'), 'itemId' => 'cDynamicClients'),
                        array('text' => __('NAS Devices') ,             'glyph' => Configure::read('icnNas'),  'itemId' => 'cNas'),
                        array('text' => __('NAS Device tags') , 'glyph' => Configure::read('icnTag'), 'itemId' => 'cTags'),
                    )
                )
            ),
            array(  'text'  => __('Profiles'),  'glyph' => Configure::read('icnProfile'), 'menu'  =>
                 array( 'items' =>
                    array(
                        array('text' => __('Profile Components') ,  'glyph' => Configure::read('icnComponent'),  'itemId' => 'cProfileComponents'),
                        array('text' => __('Profiles') ,            'glyph' => Configure::read('icnProfile'), 'itemId' => 'cProfiles'),
                    )
                )
            ),
            array(  'text'  => __('Tools'),  'glyph' => Configure::read('icnLight'), 'menu'  =>
                 array( 'items' =>
                    array(
                        array(  'text'  => __('RADIUS client'),     'glyph' => Configure::read('icnRadius'),'itemId' => 'cRadiusClient'),
                        array(  'text'  => __('Password manager'),  'glyph' => Configure::read('icnLock'),'itemId' => 'cPassword'),
                        array(  'text'  => __('Logfile viewer'),    'glyph' => Configure::read('icnLog'), 'itemId' => 'cLogViewer'),
                        array(  'text'  => __('Debug output'),      'glyph' => Configure::read('icnBug'), 'itemId' => 'cDebug'), 
                        array(  'text'  => __('Translation manager'), 'glyph' => Configure::read('icnTranslate'),'itemId' => 'cI18n'),
                        array(  'text'  => __('Rights manager'),    'glyph' => Configure::read('icnKey'), 'itemId' => 'cAcos'),
						array( 'text'  => __('IP Pools'),           'glyph' => Configure::read('icnIP'), 'itemId' => 'cIpPools'),
						array( 'text'  => __('OpenVPN Servers'),    'glyph' => Configure::read('icnVPN'), 'itemId' => 'cOpenvpnServers'),
                       // array( 'text'  => __('Licensing'),          'glyph' => Configure::read('icnLock'),'itemId' => 'cLicensing'),    
                    )
                )
            ),
            //Finances
/*
            array(  'text'  => __('Finances'),  'glyph' => Configure::read('icnFinance') ,'menu'  =>
                 array( 'items' =>
                    array(
						array(
                            'text'      => __('Payment Plans'),
                            'glyph'     => Configure::read('icnTag'),
                            'itemId'    => 'cFinPaymentPlans'
                        ),
                        array(
                            'text'      => __('Paypal'),
                            'glyph'     => Configure::read('icnOnlineShop'),
                            'itemId'    => 'cFinPaypalTransactions'
                        ),
                        array(
                            'text'      => __('PayU'), 
                            'glyph'     => Configure::read('icnOnlineShop'), 
                            'itemId'    => 'cFinPayUTransactions'
                        ),
						array(
                            'text'      => __('Authorize.Net'), 
                            'glyph'     => Configure::read('icnOnlineShop'), 
                            'itemId'    => 'cFinAuthorizeNetTransactions'
                        ),
						array(
                            'text'      => __('MyGate'), 
                            'glyph'     => Configure::read('icnOnlineShop'), 
                            'itemId'    => 'cFinMyGateTransactions'
                        ),
						array(
                            'text'      => __('Premium SMS'), 
                            'glyph'     => Configure::read('icnOnlineShop'), 
                            'itemId'    => 'cFinPremiumSmsTransactions'
                        ),
                    )
                )
            ),
*/
            //Permanent users
            array(  'text'  => __('Permanent Users'),  'glyph' => Configure::read('icnUser') ,'menu'  =>
                 array( 'items' =>
                    array(
                        array(
                            'text'      => __('Permanent Users'),
                            'glyph'     => Configure::read('icnUser'),
                            'itemId'    => 'cPermanentUsers'
                        ),
                        array(
                            'text'      => __('BYOD Manager'), 
                            'glyph'     => Configure::read('icnDevice'), 
                            'itemId'    => 'cDevices'
                        ),
                        array(
                            'text'      => __('Top-ups'), 
                            'glyph'     => Configure::read('icnTopUp'), 
                            'itemId'    => 'cTopUps'
                        ),
                    )
                )
            ),

/*
			//Dynamic Firewalls
            array(  'text'  => __('Dynamic Firewalls'),  'glyph' => Configure::read('icnLock') ,'menu'  =>
                 array( 'items' =>
                    array(
                        array(
                            'text'      => __('Dynamic Firewall Components'),
                            'glyph'     => Configure::read('icnComponent'),
                            'itemId'    => 'cDynamicFirewallComponents'
                        ),
                        array(
                            'text'      => __('Dynamic Firewalls'), 
                            'glyph'     => Configure::read('icnLock'), 
                            'itemId'    => 'cDevices'
                        )
                    )
                )
            ),
*/

            array(  'text'  => __('Vouchers'),              'glyph' => Configure::read('icnVoucher'),   'itemId' => 'cVouchers'),
            array(  'text'  => __('Dynamic login pages'),   'glyph' => Configure::read('icnDynamic'),   'itemId' => 'cDynamicDetails'),
            array(  'text'  => __('Activity monitor'),      'glyph' => Configure::read('icnActivity'),  'itemId' => 'cActivityMonitor'),
            array(  'xtype' => 'menuseparator'),
            array(  'text'  => __('APdesk'),                'glyph' => Configure::read('icnCloud'),      'itemId' => 'cAccessPoints'),
            array(  'text'  => __('MESHdesk'),              'glyph' => Configure::read('icnMesh'),      'itemId' => 'cMeshes'),
            
          //  array(  'xtype' => 'menuseparator'),
          //  array(  'text'  => __('Notifications'),         'glyph' => Configure::read('icnNotify'),    'itemId' => 'cNotifications')
        );

        //Optional experimental stuff 
        if(Configure::read('experimental.active') == true){
            array_push($menus,array(  'text'  => __('Auto Setup'), 'glyph' => Configure::read('icnConfigure'), 'itemId' => 'cAutoSetups'));
        }

        return $menus;
    }
    
    private function _build_ap_menus($id){

        $menu   = array();

        //Add-on for Password Manager Only (Typically Hotel Front Desk)
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), "Access Providers/Other Rights/Password Manager Only")){
            return $menu;
        }

        //Base to start looking from.
        $base   = "Access Providers/Controllers/";

        //____ Realms and Providers ____

        //___Check the sub-menu rights___:
        $sm_r_p = array();
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."AccessProviders/index")){
            array_push($sm_r_p, array('text' => __('Access Providers') ,'glyph' => Configure::read('icnKey'),    'itemId' => 'cAccessProviders'));
        }
        //Then the one we checked for ... realms
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Realms/index")){
            array_push($sm_r_p, array('text' => __('Realms') , 'glyph' => Configure::read('icnRealm'), 'itemId' => 'cRealms'));
        }
        //___Check for SSID___:
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Ssids/index")){
            array_push($sm_r_p, array('text' => __('SSIDs') ,'glyph' => Configure::read('icnWifi'),    'itemId' => 'cSsids'));
        }

        //___ END Sub Menu___
        if ($sm_r_p != null) {
            array_push($menu, array(  'text'  => __('Realms and Providers'),  'glyph' => Configure::read('icnRealm'), 'menu'  => array('items' =>$sm_r_p)));
        }
                 
        

        //____ NAS devices _____

        $sm_nas_devices = array();
        
        //__ DynamicClients __
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."DynamicClients/index")){
            array_push($sm_nas_devices, array('text' => __('Dynamic RADIUS Clients') ,  'glyph' => Configure::read('icnDynamicNas'), 'itemId' => 'cDynamicClients'));
        }
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Nas/index")) {
            array_push($sm_nas_devices, array('text' => __('NAS Devices'), 'glyph' => Configure::read('icnNas'), 'itemId' => 'cNas'));
        }
        //___Check the sub-menu rights___:
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Tags/index")){
            array_push($sm_nas_devices, array(  'text'  => __('NAS Device tags'),   'glyph' => Configure::read('icnTag'), 'itemId' => 'cTags'));
        } 
        //___ END Sub Menu___

        if ($sm_nas_devices != null) {
            array_push($menu, array(  'text'  => __('NAS Devices'),  'glyph' => Configure::read('icnNas'), 'menu'  => array('items' =>$sm_nas_devices)));
        }
        

        //____ Profiles _____

        $sm_profiles = array();

        //___Check the sub-menu rights___:
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."ProfileComponents/index")){
            array_push($sm_profiles, array('text' => __('Profile Components') ,  'glyph' => Configure::read('icnComponent'), 'itemId' => 'cProfileComponents'));
        } 
        //___ END Sub Menu___
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Profiles/index")) {
            array_push($sm_profiles, array('text' => __('Profiles'), 'glyph' => Configure::read('icnProfile'), 'itemId' => 'cProfiles'));
        }
        if ($sm_profiles != null) {
            array_push($menu, array(  'text'  => __('Profiles'),  'glyph' => Configure::read('icnProfile'),  'menu'  => array('items' =>$sm_profiles)));     
        }

        //____ Tools ____

        $sm_tools = array();

        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."PermanentUsers/change_password")) {
            array_push($sm_tools,
                array(
                    'text'      => __('Password manager'),
                    'glyph'     => Configure::read('icnLock'),
                    'itemId'    => 'cPassword'
                )
            );
        }

        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."FreeRadius/test_radius")) {
            array_push($sm_tools,
                array(
                    'text' => __('RADIUS client'),
                    'glyph' => Configure::read('icnRadius'),
                    'itemId' => 'cRadiusClient'
                )
            );
        }
/*
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."LicensedDevices/index")){
             array_push($sm_tools, 
                            array(
                                'text'      => __('Licensing') ,  
                                'glyph'     => Configure::read('icnLock'), 
                                'itemId'    => 'cLicensing')
                );
        }
*/
        if ($sm_tools != null)    {
            array_push($menu,
                array(  'text'  => __('Tools'),  'glyph' => Configure::read('icnLight'),  'menu'  => array( 'items' =>$sm_tools))
            );
        }



        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."PermanentUsers/index")){
             $pu_sub_menu = array(
                        array(
                            'text'      => __('Permanent Users'),
                            'glyph'     => Configure::read('icnUser'),
                            'itemId'    => 'cPermanentUsers'
                        ),
                        array(
                            'text'      => __('BYOD Manager'), 
                            'glyph'     => Configure::read('icnDevice'), 
                            'itemId'    => 'cDevices'
                        ),
                        array(
                            'text'      => __('Top-ups'), 
                            'glyph'     => Configure::read('icnTopUp'), 
                            'itemId'    => 'cTopUps'
                        ),
                    );
            
            array_push($menu, 
                array(  'text'  => __('Permanent Users'), 
                        'glyph' => Configure::read('icnUser'),  
                        'menu'  => array('items' =>$pu_sub_menu))
            );
        }

        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."DynamicDetails/index")){
            array_push($menu,
                array(  'text'  => __('Dynamic login pages'),  'glyph' => Configure::read('icnDynamic'),'itemId' => 'cDynamicDetails')
            );
        }

        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Radaccts/index")){
            array_push($menu,
                array(  'text'  => __('Activity monitor'),  'glyph' => Configure::read('icnActivity'),'itemId' => 'cActivityMonitor')
            );
        }

        //Seperator
        array_push($menu,array(  'xtype' => 'menuseparator'));
        
        //Cloud Controller for APs
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."ApProfiles/index")){
		    array_push($menu,
			    array(  'text'  => __('APdesk'), 'glyph' => Configure::read('icnCloud'),      'itemId' => 'cAccessPoints')
			);
		}

        //Meshdesk
		if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Meshes/index")){
			array_push($menu,
				array(  'text'  => __('MESHdesk'),  'glyph' => Configure::read('icnMesh'), 'itemId' => 'cMeshes')
			);
		}
		    
        return $menu;
    }

    private function _build_admin_shortcuts(){
        $items = array();
        array_push($items, array( 'name'    => __('Permanent Users'), 'iconCls' => 'users-shortcut', 'controller' => 'cPermanentUsers'));
        array_push($items, array( 'name'    => __('Vouchers'), 'iconCls' => 'vouchers-shortcut', 'controller' => 'cVouchers'));
        array_push($items, array( 'name'    => __('Activity monitor'), 'iconCls' => 'activity-shortcut', 'controller' => 'cActivityMonitor'));
        array_push($items, array( 'name'    => __('Password manager'), 'iconCls' => 'password-shortcut', 'controller' => 'cPassword'));
        array_push($items, array( 'name'    => __('MESHdesk'), 'iconCls' => 'meshdesk-shortcut', 'controller' => 'cMeshes'));
        array_push($items, array( 'name'    => __('APdesk'), 'iconCls' => 'apdesk-shortcut', 'controller' => 'cAccessPoints'));
        return $items;
    }




    private function _build_ap_shortcuts($id){

        $items = array();

        //Add-on for Password Manager Only (Typically Hotel Front Desk)
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), "Access Providers/Other Rights/Password Manager Only")){
            //WIP
            array_push($items, array( 'name'    => __('Password manager'), 'iconCls' => 'password-shortcut', 'controller' => 'cPassword'));
            return $items;
        }

        $base   = "Access Providers/Controllers/";

       
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."PermanentUsers/index")){
            array_push($items, array( 'name'    => 'Permanent Users', 'iconCls' => 'users-shortcut', 'controller' => 'cPermanentUsers'));
        }

        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Vouchers/index")){
            array_push($items, array( 'name' => 'Vouchers', 'iconCls' => 'vouchers-shortcut', 'controller' => 'cVouchers'));
        }
       
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Radaccts/index")){
            array_push($items, array( 'name'    => 'Activity monitor', 'iconCls' => 'activity-shortcut', 'controller' => 'cActivityMonitor'));
        }
        
        
        
        //Meshdesk
		if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."Meshes/index")){
			array_push($items, array( 'name' => 'MESHdesk', 'iconCls' => 'meshdesk-shortcut', 'controller' => 'cMeshes'));
		}
        
        
        if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $base."ApProfiles/index")){
            array_push($items, array( 'name' => 'APdesk', 'iconCls' => 'apdesk-shortcut', 'controller' => 'cAccessPoints'));
        }
        
        
        return $items;

    }

}
