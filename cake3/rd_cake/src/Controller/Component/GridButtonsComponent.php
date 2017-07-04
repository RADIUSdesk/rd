<?php
//----------------------------------------------------------
//---- Author: Dirk van der Walt
//---- License: GPL v3
//---- Description: A component used to check and produce Ajax-ly called grid tooblaar items
//---- Date: 01-01-2016
//------------------------------------------------------------

namespace App\Controller\Component;
use Cake\Controller\Component;

use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class GridButtonsComponent extends Component {

    public $components = ['Acl'];
    protected $scale   = 'large';  //Later we will improve the code to change this to small for smaller screens

    // Execute any other additional setup for your component.
    public function initialize(array $config)
    {
        $this->btnReload = ['xtype'=>  'button', 'glyph' => Configure::read('icnReload'), 'scale' => $this->scale, 'itemId' => 'reload','tooltip'   => __('Reload')];
        $this->btnReloadTimer = [
            'xtype'     => "splitbutton",
            'glyph'     => Configure::read('icnReload'),
            'scale'     => $this->scale,
            'itemId'    => 'reload',
            'tooltip'   => __('Reload'),
            'menu'      => [
                'items' => [
                    '<b class="menu-title">Reload every:</b>',
                    array( 'text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                    array( 'text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                    array( 'text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                    array( 'text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true)
                ]
            ]
        ];
        $this->btnAdd =  [
            'xtype'     => 'button',
            'glyph'     => Configure::read('icnAdd'),
            'scale'     => $this->scale,
            'itemId'    => 'add',
            'tooltip'   => __('Add')
        ];

        $this->btnDelete =  [
            'xtype'     => 'button',
            'glyph'     => Configure::read('icnDelete'),
            'scale'     => $this->scale,
            'itemId'    => 'delete',
            'tooltip'   => __('Delete')
        ];

        $this->btnNote = [
            'xtype'     => 'button',     
            'glyph'     => Configure::read('icnNote'), 
            'scale'     => $this->scale, 
            'itemId'    => 'note',    
            'tooltip'   => __('Add notes')
        ];

        $this->btnCSV = [
            'xtype'     => 'button',     
            'glyph'     => Configure::read('icnCsv'), 
            'scale'     => $this->scale, 
            'itemId'    => 'csv',      
            'tooltip'   => __('Export CSV')
        ];

        $this->btnPassword = [
            'xtype'     => 'button', 
            'glyph'     => Configure::read('icnLock'), 
            'scale'     => $this->scale, 
            'itemId'    => 'password', 
            'tooltip'   => __('Change Password')
        ];

        $this->btnEnable = [
            'xtype'     => 'button',  
            'glyph'     => Configure::read('icnLight'),
            'scale'     => $this->scale, 
            'itemId'    => 'enable_disable',
            'tooltip'   => __('Enable / Disable')
        ];

        $this->btnRadius = [
            'xtype'     => 'button', 
            'glyph'     => Configure::read('icnRadius'), 
            'scale'     => $this->scale, 
            'itemId'    => 'test_radius',  
            'tooltip'   => __('Test RADIUS')
        ];

        $this->btnGraph = [
            'xtype'     => 'button', 
            'glyph'     => Configure::read('icnGraph'),   
            'scale'     => $this->scale, 
            'itemId'    => 'graph',  
            'tooltip'   => __('Graphs')
        ];

        $this->btnMail = [
            'xtype'     => 'button', 
            'glyph'     => Configure::read('icnEmail'),
            'scale'     => 'large', 
            'itemId'    => 'email', 
            'tooltip'   => __('e-Mail voucher')
        ];

        $this->btnPdf  = [
            'xtype'     => 'button', 
            'glyph'     => Configure::read('icnPdf'),    
            'scale'     => 'large', 
            'itemId'    => 'pdf',      
            'tooltip'   => __('Export to PDF')
        ];

    }

    public function returnButtons($user,$title = true,$type='basic'){
        //First we will ensure there is a token in the request
        $this->controller = $this->_registry->getController();
        
        if($title){
            $this->t = __('Action');
        }else{
            $this->t = null;
        }
        
        $menu = [];
        $this->user = $user;
        
        if($type == 'basic'){
            $b = $this->_fetchBasic();
            $menu = array($b);
        }
        
        if($type == 'add_and_delete'){
            $b = $this->_fetchAddAndDelete();
            $menu = array($b);
        }
        
        if($type == 'basic_no_disabled'){
            $b = $this->_fetchBasic('no_disabled');
            $menu = array($b);
        }
        
        if($type == 'access_providers'){
            $b  = $this->_fetchBasic();
            $d  = $this->_fetchDocument();
            $a  = $this->_fetchApExtras();
            $menu = array($b,$d,$a);
        }
        
        if($type == 'realms'){
            $b  = $this->_fetchBasic();
            $d  = $this->_fetchDocument();
            $a  = $this->_fetchRealmExtras();
            $menu = array($b,$d,$a);
        }
        
        if($type == 'basic_and_doc'){
            $b  = $this->_fetchBasic();
            $d  = $this->_fetchDocument();
            $menu = array($b,$d);
        }
        
        if($type == 'dynamic_details'){
            $b  = $this->_fetchBasic();
            $d  = $this->_fetchDocument();
            $a  = $this->_fetchDynamicDetailExtras();
            $menu = array($b,$d,$a);
        }
        
        if($type == 'profiles'){
            $b  = $this->_fetchBasic();
            $n  = $this->_fetchNote();
            $menu = array($b,$n);
        }
        
        if($type == 'permanent_users'){
            $b  = $this->_fetchBasic('disabled',true);
            $d  = $this->_fetchDocument();
            $a  = $this->_fetchPermanentUserExtras();
            $menu = array($b,$d,$a);
        }

        if($type == 'fr_acct_and_auth'){
            $b  = $this->_fetchFrAcctAuthBasic();
            $menu = [$b];
        }

        if($type == 'devices'){
            $b  = $this->_fetchBasic('disabled',true);
            $d  = $this->_fetchDocument();
            $a  = $this->_fetchDeviceExtras();
            $menu = array($b,$d,$a);
        }

        if($type == 'vouchers'){
            $b  = $this->_fetchBasicVoucher('disabled');
            $d  = $this->_fetchDocumentVoucher();
          //  $a  = $this->_fetchDeviceExtras();
            $a  = $this->_fetchVoucherExtras();
            $menu = array($b,$d,$a);
        }
        
        if($type == 'top_ups'){
            $b  = $this->_fetchBasic('disabled',false);
            $d  = $this->_fetchDocumentTopUp();
            $menu = array($b,$d);
        }
        
        return $menu;
    }

    private function _fetchFrAcctAuthBasic(){

        $user = $this->user;
        $menu = [];
        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                        $this->btnReload,
                       $this->btnDelete, 
                )) 
            );
        }

        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                        $this->btnReload,
                        $this->btnDelete, 
                )) 
            );
        }

        return $menu;
    }
    
    private function _fetchAddAndDelete(){
    
        $menu = ['xtype' => 'buttongroup', 'items' => [
                    [
                        'xtype'     => 'button',  
                        'glyph'     => Configure::read('icnReload'), 
                        'scale'     => $this->scale, 
                        'itemId'    => 'reload',   
                        'tooltip'=> __('Reload')
                    ],
                    $this->btnAdd,
                    $this->btnDelete,    
                ]
        ];
        return $menu;
    }
    
    private function _fetchBasic($action='disabled',$with_reload_timer=false){
    
        $user = $this->user;
        
        if($action == 'no_disabled'){
            $disabled = false; 
        }else{
            $disabled = true;
        }
        
        $menu = array();
        
        
        $reload = [
            'xtype'     => 'button',  
            'glyph'     => Configure::read('icnReload'), 
            'scale'     => $this->scale, 
            'itemId'    => 'reload',   
            'tooltip'=> __('Reload')
        ];
        
        if($with_reload_timer == true){
            $reload = [
                'xtype'     => "splitbutton",
                'glyph'     => Configure::read('icnReload'),
                'scale'     => $this->scale,
                'itemId'    => 'reload',
                'tooltip'   => __('Reload'),
                'menu'      => [
                    'items' => [
                        '<b class="menu-title">Reload every:</b>',
                        array( 'text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                        array( 'text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                        array( 'text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                        array( 'text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true)
                    ]
                ] 
            ];
        }
        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $menu = array('xtype' => 'buttongroup','title' => $this->t, 'items' => array(
                    $reload,
                    array(
                        'xtype'     => 'button',
                        'glyph'     => Configure::read('icnAdd'),
                        'scale'     => $this->scale,
                        'itemId'    => 'add',
                        'tooltip'   => __('Add')
                    ),
                    $this->btnDelete,
                    array(
                        'xtype'     => 'button',
                        'glyph'     => Configure::read('icnEdit'),
                        'scale'     => $this->scale,
                        'itemId'    => 'edit',
                        'tooltip'   => __('Edit')
                    )
                )
            );
        }
        
        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $action_group   = array();

            array_push($action_group,$reload);

            //Add
            if($this->controller->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base."add")){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnAdd'),      
                    'scale'     => $this->scale, 
                    'itemId'    => 'add',
                    'tooltip'   => __('Add')));
            }
            //Delete
            if($this->controller->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'delete')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnDelete'),   
                    'scale'     => $this->scale, 
                    'itemId'    => 'delete',
                    'disabled'  => $disabled,   
                    'tooltip'   => __('Delete')));
            }
            //Edit
            if($this->controller->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'edit')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnEdit'),     
                    'scale'     => $this->scale, 
                    'itemId'    => 'edit',
                    'disabled'  => $disabled,     
                    'tooltip'   => __('Edit')));
            }
            $menu = array('xtype' => 'buttongroup','title' => $this->t,  'items' => $action_group);
        }   
        return $menu;
    }

    private function _fetchBasicVoucher(){
    
        $user       = $this->user;
        $disabled   = false;   
        $menu       = array();
         
     

        $add = [
            'xtype' 	=> 'splitbutton',   
            'glyph' 	=> Configure::read('icnAdd'),    
            'scale' 	=> $this->scale, 
            'itemId' 	=> 'add',      
            'tooltip'	=> __('Add'),
            'disabled'  => $disabled,
            'menu'      => [
                    'items' => [
                        array( 'text'  => __('Single field'),      		'itemId'    => 'addSingle', 'group' => 'add', 'checked' => true ),
                        array( 'text'  => __('Username and Password'),   'itemId'    => 'addDouble', 'group' => 'add' ,'checked' => false), 
                        array( 'text'  => __('Import CSV List'),         'itemId'    => 'addCsvList','group' => 'add' ,'checked' => false),  
                    ]
            ]
        ];

        $delete = [
            'xtype' 	=> 'splitbutton',   
            'glyph' 	=> Configure::read('icnDelete'),    
            'scale' 	=> $this->scale, 
            'itemId' 	=> 'delete',      
            'tooltip'	=> __('Delete'),
            'disabled'  => $disabled,
            'menu'      => [
                    'items' => [
                        array( 'text'  => __('Simple Delete'), 'itemId'    => 'deleteSimple', 'group' => 'delete', 'checked' => true ),
                        array( 'text'  => __('Bulk Delete'),   'itemId'    => 'deleteBulk', 'group' => 'delete' ,'checked' => false),  
                    ]
            ]
        ];

        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $menu = array('xtype' => 'buttongroup','title' => $this->t, 'items' => array(
                    $this->btnReloadTimer,
                    $add,
                    $delete,
                    array(
                        'xtype'     => 'button',
                        'glyph'     => Configure::read('icnEdit'),
                        'scale'     => $this->scale,
                        'itemId'    => 'edit',
                        'tooltip'   => __('Edit')
                    )
                )
            );
        }
        
        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $action_group   = array();
            $disabled       = true;

            array_push($action_group,$this->btnReloadTimer);

            //Add
            if($this->controller->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base."add")){
                array_push($action_group,$add);
            }
            //Delete
            if($this->controller->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'delete')){
                array_push($action_group,$delete);
            }

            //Edit
            if($this->controller->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'editBasicInfo')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnEdit'),     
                    'scale'     => $this->scale, 
                    'itemId'    => 'edit',
                    'disabled'  => $disabled,     
                    'tooltip'   => __('Edit')));
            }

            $menu = array('xtype' => 'buttongroup','title' => $this->t,        'items' => $action_group);
        }
        
        return $menu;
    }
    
    private function _fetchDocument(){

        $user = $this->user;
        $menu = array();
        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $menu = array(
                'xtype' => 'buttongroup',
                'title' => __('Document'), 
                'items' => array(
                    $this->btnNote,
                    $this->btnCSV
                )
            );
        }
        
        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $document_group = array();

            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'noteIndex')){ 
                array_push($document_group,$this->btnNote);
            }

            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'exportCsv')){ 
                array_push($document_group,$this->btnCSV);
            }

            $menu = array('xtype' => 'buttongroup', 'title' => __('Document'),        'items' => $document_group );
        }
            
        return $menu;
    }

    private function _fetchDocumentVoucher(){

        $user = $this->user;
        $menu = array();
        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $menu = array(
                'xtype' => 'buttongroup',
                'title' => __('Document'), 
                'items' => array(
                    $this->btnPdf,
                    $this->btnCSV,
                    $this->btnMail
                )
            );
        }
        
        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
           $menu = array(
                'xtype' => 'buttongroup',
                'title' => __('Document'), 
                'items' => array(
                    $this->btnPdf,
                    $this->btnCSV,
                    $this->btnMail
                )
            );
        }
            
        return $menu;
    }
    
    private function _fetchDocumentTopUp(){
         $user = $this->user;
        $menu = array();
        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $menu = array(
                'xtype' => 'buttongroup',
                'title' => __('Document'), 
                'width' => 100,
                'items' => array(
                    $this->btnCSV
                )
            );
        }
        
        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $document_group = array();
         
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'exportCsv')){ 
                array_push($document_group,$this->btnCSV);
            }

            $menu = array('xtype' => 'buttongroup', 'title' => __('Document'),  'width' => 100,  'items' => $document_group );
        }
            
        return $menu;
    
    
    }
    
    private function _fetchNote(){

        $user = $this->user;
        $menu = array();
        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $menu = array(
                'xtype' => 'buttongroup',
                'title' => __('Document'), 
                'width' => 100,
                'items' => array(
                    $this->btnNote
                )
            );
        }
        
        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $document_group = array();

            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'noteIndex')){ 
                array_push($document_group,$this->btnNote);
            }

            $menu = array('xtype' => 'buttongroup', 'title' => __('Document'), 'width' => 100,  'items' => $document_group );
        }         
        return $menu;
    }
    
    private function _fetchApExtras(){

        $user = $this->user;
        $menu = array();
        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin   
             $menu = array(
                'xtype' => 'buttongroup',
                'title' => __('Extra actions'), 
                'items' => array(
                    $this->btnPassword,
                    $this->btnEnable
                )
            );    
        }
        
        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $specific_group = array();

            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'changePassword')){      
                array_push($specific_group,$this->btnPassword);
           }
            
           if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'enableDisable')){      
                array_push($specific_group, $this->btnEnable);
            }
           
            $menu = array('xtype' => 'buttongroup', 'title' =>  __('Extra actions'), 'items' => $specific_group );
        }
            
        return $menu;
    }
    
    private function _fetchRealmExtras(){
        $menu = array(
            'xtype' => 'buttongroup',
            'title' => __('More'), 
            'items' => array(
                $this->btnGraph,
                array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnCamera'),
                    'scale'     => $this->scale, 
                    'itemId'    => 'logo',     
                    'tooltip'   => __('Edit logo')
                )
            )
        );             
        return $menu;
    }
    
    private function _fetchDynamicDetailExtras(){
    
        $menu = array(
            'xtype' => 'buttongroup',
            'title' => __('Preview'), 
            'items' => array(
                array(
                    'xtype'     => 'button',  
                    'glyph'     => Configure::read('icnMobile'),  
                    'scale'     => $this->scale, 
                    'itemId'    => 'mobile',    
                    'tooltip'   => __('Mobile')
                ),
                array(
                    'xtype'     => 'button',  
                    'glyph'     => Configure::read('icnDesktop'),  
                    'scale'     => $this->scale, 
                    'itemId'    => 'desktop',   
                    'tooltip'   => __('Desktop')
                )
            )
        );             
        return $menu;
    }
    
    private function _fetchPermanentUserExtras(){
    
        $user = $this->user;
        $menu = array();
        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin   
             $menu = array(
                'xtype' => 'buttongroup',
                'title' => __('Extra actions'), 
                'items' => array(
                   $this->btnPassword,
                   $this->btnEnable,
                   $this->btnRadius,
                   $this->btnGraph
                )
            );    
        }
        
        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $specific_group = array();

            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'changePassword')){      
                array_push($specific_group,$this->btnPassword);
           }
            
           if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'enableDisable')){      
                array_push($specific_group, $this->btnEnable);
            }
            //FIXME when FreeRadius has been ported ... update this one also
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), 'Access Providers/Controllers/FreeRadius/test_radius')){      
                array_push($specific_group, $this->btnRadius);
            }
            
            array_push($specific_group,$this->btnGraph);
           
            $menu = array('xtype' => 'buttongroup', 'title' =>  __('Extra actions'), 'items' => $specific_group );
        }
                
        return $menu;
    }

     private function _fetchDeviceExtras(){
    
        $user = $this->user;
        $menu = array();
        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin   
             $menu = array(
                'xtype' => 'buttongroup',
                'title' => __('Extra actions'), 
                'items' => array(
                    $this->btnEnable,
                    $this->btnRadius,
                    $this->btnGraph
                )
            );    
        }
        
        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $specific_group = array();
      
           if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'enableDisable')){      
                array_push($specific_group, $this->btnEnable);
            }
            
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), 'Access Providers/Controllers/FreeRadius/test_radius')){      
                array_push($specific_group, $this->btnRadius);
            }
            
            array_push($specific_group, $this->btnGraph);
           
            $menu = array('xtype' => 'buttongroup', 'title' =>  __('Extra actions'), 'items' => $specific_group );
        }              
        return $menu;
    }

    private function _fetchVoucherExtras(){
    
        $user = $this->user;
        $menu = array();
        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin   
             $menu = array(
                'xtype' => 'buttongroup',
                'title' => __('Extra actions'), 
                'items' => array(
                   $this->btnPassword,
                   $this->btnRadius,
                   $this->btnGraph
                )
            );    
        }
        
        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $specific_group = array();

            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->controller->base.'changePassword')){      
                array_push($specific_group,$this->btnPassword);
            }
             
            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), 'Access Providers/Controllers/FreeRadius/test_radius')){      
                array_push($specific_group, $this->btnRadius);
            }
            
            array_push($specific_group,$this->btnGraph);
           
            $menu = array('xtype' => 'buttongroup', 'title' =>  __('Extra actions'), 'items' => $specific_group );
        }
                
        return $menu;
    }



}
