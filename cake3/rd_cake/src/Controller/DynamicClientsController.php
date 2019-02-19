<?php

namespace App\Controller;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Exception;
//use GeoIp2\Database\Reader;

class DynamicClientsController extends AppController{
  
    protected $base         = "Access Providers/Controllers/DynamicClients/";   
    protected $owner_tree   = array();
    protected $main_model   = 'DynamicClients';
  
    public function initialize(){  
        parent::initialize();
        $this->loadModel('DynamicClients'); 
        $this->loadModel('Users');
                 
        $this->loadComponent('Aa');
        $this->loadComponent('GridButtons');
        $this->loadComponent('GridFilter');
        $this->loadComponent('CommonQuery', [ //Very important to specify the Model
            'model' => 'DynamicClients'
        ]);
        
        $this->loadComponent('JsonErrors'); 
        $this->loadComponent('TimeCalculations'); 
        
        $this->loadComponent('Notes', [
            'model'     => 'DynamicClientNotes',
            'condition' => 'dynamic_client_id'
        ]);        
    }

    //____ BASIC CRUD Manager ________
    public function index(){

 //       $reader = new Reader('/usr/share/nginx/AmpCore/cake3/rd_cake/setup/GeoIp/data/GeoLite2-City.mmdb');

        $cquery = $this->request->getQuery();

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $query = $this->{$this->main_model}->find();

        $this->_build_common_query($query, $user);

        //===== PAGING (MUST BE LAST) ======
        $limit  = 50;   //Defaults
        $page   = 1;
        $offset = 0;

        if(isset($cquery['limit'])){
            $limit  = $cquery['limit'];
            $page   = $cquery['page'];
            $offset = $cquery['start'];
        }

        $query->page($page);
        $query->limit($limit);
        $query->offset($offset);

        $total = $query->count();
        $q_r = $query->all();

        $items      = [];

        foreach($q_r as $i){

            $country_code   = '';
            $country_name   = '';
            $city           = '';
            $postal_code    = '';
            $state_name     = '';
            $state_code     = '';
/*
            if($i->last_contact_ip != ''){
                try {
                    $location         = $reader->city($i->last_contact_ip);
                } catch (\Exception $e) {
                    //Do Nothing
                }

                if(!empty($location)){
                    $city           = $location->city->name;
                    $postal_code    = $location->postal->code;
                    $country_name   = $location->country->name;
                    $country_code   = $location->country->isoCode;
                    $state_name     = $location->mostSpecificSubdivision->name;
                    $state_code     = $location->mostSpecificSubdivision->isoCode;
                }
            }
*/
            $realms     = [];
            //Realms
            foreach($i->dynamic_client_realms as $dcr){
                if(! $this->Aa->test_for_private_parent($dcr->realm, $user)){
                    if(! isset($dcr->realm->id)){
                        $r_id = "undefined";
                        $r_n = "undefined";
                        $r_s =  false;
                    }else{
                        $r_id= $dcr->realm->id;
                        $r_n = $dcr->realm->name;
                        $r_s = $dcr->realm->available_to_siblings;
                    }
                    array_push($realms,
                        [
                            'id'                    => $r_id,
                            'name'                  => $r_n,
                            'available_to_siblings' => $r_s
                        ]);
                }
            }

            $owner_id       = $i->user_id;

            $owner_tree     = $this->{'Users'}->find_parents($owner_id);
            $action_flags   = $this->_get_action_flags($owner_id,$user);


            $i->country_code = $country_code;
            $i->country_name = $country_name;
            $i->city         = $city;
            $i->postal_code  = $postal_code;
            if($i->last_contact != null){
                $i->last_contact_human    = $this->TimeCalculations->time_elapsed_string($i->last_contact);
            }

            //Create notes flag
            $notes_flag  = false;
            foreach($i->dynamic_client_notes as $dcn){
                if(! $this->Aa->test_for_private_parent($dcn->note,$user)){
                    $notes_flag = true;
                    break;
                }
            }


            $i->notes  = $notes_flag;

            $i->owner  = $owner_tree;
            $i->realms = $realms;
            $i->update = $action_flags['update'];
            $i->delete = $action_flags['delete'];
            
            //Check if there is data cap on unit
            if($i->data_limit_active){
                $d_limit_bytes = $this->_getBytesValue($i->data_limit_amount,$i->data_limit_unit);
                $i->data_cap = $d_limit_bytes;
                if($i->data_used >0){
                    $i->perc_data_used =  round($i->data_used /$d_limit_bytes,2) ;
                    if($i->perc_data_used > 1){
                        $i->perc_data_used = 1;
                    }
                }else{
                    $i->perc_data_used = 0;
                }
            }
            

            array_push($items,$i);
        }

        //___ FINAL PART ___
        $this->set([
            'items' => $items,
            'success' => true,
            'totalCount' => $total,
            '_serialize' => ['items','success','totalCount']
        ]);
    }

    public function clientsAvailForMap() {

        $cquery = $this->request->getQuery();

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $query = $this->{$this->main_model}->find();

        $this->_build_common_query($query, $user);

        //===== PAGING (MUST BE LAST) ======
        $limit  = 50;   //Defaults
        $page   = 1;
        $offset = 0;

        if(isset($cquery['limit'])){
            $limit  = $cquery['limit'];
            $page   = $cquery['page'];
            $offset = $cquery['start'];
        }

        $query->page($page);
        $query->limit($limit);
        $query->offset($offset);

        $total = $query->count();
        $q_r = $query->all();

        $items  = [];

        foreach($q_r as $i){
            $id     = $i->id;
            $name   = $i->name;
            $item = ['id' => $id,'name' => $name];
            array_push($items,$item);
        }

        //___ FINAL PART ___
        $this->set([
            'items' => $items,
            'success' => true,
            'totalCount' => $total,
            '_serialize' => ['items','success','totalCount']
        ]);




    }

    public function add() {
        $this->loadModel('UnknownDynamicClients');

        $cdata = $this->request->getData();

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        //Get the creator's id
        if($cdata['user_id'] == '0'){ //This is the holder of the token - override '0'
            $cdata['user_id'] = $user_id;
        }

        $check_items = ['active', 'available_to_siblings', 'on_public_maps', 'session_auto_close','data_limit_active'];
        foreach($check_items as $ci){
            if(isset($cdata[$ci])){
                $cdata[$ci] = 1;
            }else{
                $cdata[$ci] = 0;
            }
        }

        $unknown_flag = false;
        //Check if it was an attach!
        if(array_key_exists('unknown_dynamic_client_id',$cdata)){
            //Now we need to do a lookup
            $u = $this->UnknownDynamicClients->findById($cdata['unknown_dynamic_client_id'])->first();
            if($u){
                $unknown_flag   = true;
                $nas_id         = $u->nasidentifier;
                $called         = $u->calledstationid;

                $cdata['nasidentifier']   = $nas_id;
                $cdata['calledstationid'] = $called;
            }
        }


        $modelEntity = $this->{$this->main_model}->newEntity($cdata);

        if ($this->{$this->main_model}->save($modelEntity)) {
            //Check if we need to add na_realms table
            if(isset($cdata['avail_for_all'])){
                //Available to all does not add any dynamic_client_realm entries
            }else{
                foreach(array_keys($cdata) as $key){
                    if(preg_match('/^\d+/',$key)){
                        //----------------
                        $this->_add_dynamic_client_realm($modelEntity->id, $key);
                        //-------------
                    }
                }
            }
            $cdata['id'] = $modelEntity->id;

            //If it was an unknown attach - remove the unknown
            if($unknown_flag){
                //$modelEntity->id = $cdata['unknown_dynamic_client_id'];
                $deleteEntity = $this->UnknownDynamicClients->get($cdata['unknown_dynamic_client_id']);
                $this->UnknownDynamicClients->delete($deleteEntity);
            }


            $this->set([
                'success' => true,
                'data'      => $cdata,
                '_serialize' => ['success','data']
            ]);
        } else {
            $message = 'Error';
            $this->set([
                'errors'    => $this->JsonErrors->entityErros($modelEntity, $message),
                'success'   => false,
                'message'   => ['message' => __('Could not create item')],
                '_serialize' => ['errors','success','message']
            ]);
        }
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

        $cdata = $this->request->getData();

        $user_id    = $user['id'];
        $fail_flag  = false;

        if(isset($cdata['id'])){   //Single item delete
            $message = "Single item ".$cdata['id'];

            $deleteEntity = $this->{$this->main_model}->get($cdata['id']);

            $this->{$this->main_model}->delete($deleteEntity);
        }else{                          //Assume multiple item delete
            foreach($this->request->getData() as $d){
                $deleteEntity = $this->{$this->main_model}->get($d['id']);

                $this->{$this->main_model}->delete($deleteEntity);
            }
        }

        if($fail_flag == true){
            $this->set([
                'success'   => false,
                'message'   => ['message' => __('Could not delete some items')],
                '_serialize' => ['success','message']
            ]);
        }else{
            $this->set([
                'success' => true,
                '_serialize' => ['success']
            ]);
        }
    }

    public function edit(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $cdata = $this->request->getData();

        if ($this->request->is('post')) {

            //Unfortunately there are many check items which means they will not be in the POST if unchecked
            //so we have to check for them
            $check_items = [
                'active', 'available_to_siblings', 'on_public_maps', 'session_auto_close','data_limit_active'
            ];

            foreach($check_items as $i){
                if(isset($cdata[$i])){
                    $cdata[$i] = 1;
                }else{
                    $cdata[$i] = 0;
                }
            }

            $modelEntity = $this->{$this->main_model}->get($cdata['id']);
            // Update Entity with Request Data
            $modelEntity = $this->{$this->main_model}->patchEntity($modelEntity, $cdata);

            if ($this->{$this->main_model}->save($modelEntity)) {
                $this->set([
                    'success' => true,
                    '_serialize' => ['success']
                ]);
            }
        }
    }


    public function view(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $items = [];

        if(null !== $this->request->getQuery('dynamic_client_id')){

            $q_r = $this->{$this->main_model}->find()->where(['id' => $this->request->getQuery('dynamic_client_id')])->first();
            // print_r($q_r);
            if($q_r){
                $items = $q_r;
            }
        }

        $this->set([
            'data'   => $items, //For the form to load we use data instead of the standard items as for grids
            'success' => true,
            '_serialize' => ['success','data']
        ]);

    }


    public function viewPhoto(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $items = [];

        if(null !== $this->request->getQuery('id')){
            $q_r = $this->{$this->main_model}->find()->where(['id' => $this->request->getQuery('id')])->first();

            if($q_r){
                $items['photo_file_name'] = $q_r->photo_file_name;
            }
        }

        $this->set([
            'data'   => $items, //For the form to load we use data instead of the standard items as for grids
            'success' => true,
            '_serialize' => ['success','data']
        ]);
    }

    public function uploadPhoto($id = null){

       //This is a deviation from the standard JSON serialize view since extjs requires a html type reply when files
        //are posted to the server.
        $this->viewBuilder()->setLayout('ext_file_upload');

        $path_parts     = pathinfo($_FILES['photo']['name']);
        $unique         = time();
        $dest           = WWW_ROOT."img/nas/".$unique.'.'.$path_parts['extension'];
        $dest_www       = "/cake3/rd_cake/webroot/img/nas/".$unique.'.'.$path_parts['extension'];

        //Now add....
        $data['id']  = $this->request->getData('id');
        $data['photo_file_name']  = $unique.'.'.$path_parts['extension'];

        $uploadEntity = $this->{$this->main_model}->newEntity($data);
        if($this->{$this->main_model}->save($uploadEntity)){
            move_uploaded_file ($_FILES['photo']['tmp_name'] , $dest);
            $json_return['id']                  = $uploadEntity->id;
            $json_return['success']             = true;
            $json_return['photo_file_name']     = $unique.'.'.$path_parts['extension'];
        }else{
            $message = 'Error';
            $json_return['errors']      = $this->JsonErrors->entityErros($uploadEntity, $message);
            $json_return['message']     = array("message"   => __('Problem uploading photo'));
            $json_return['success']     = false;
        }
        $this->set('json_return',$json_return);
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

    //----- Menus ------------------------
    public function menuForGrid(){

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        //Empty by default
        $menu = [];

        $shared_secret = "(Please specify one)";
        if(Configure::read('DynamicClients.shared_secret')){
            $shared_secret = Configure::read('DynamicClients.shared_secret');
        }

        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin

            $menu = [
                ['xtype' => 'buttongroup','title' => __('Action'), 'items' => [
                    [ 'xtype' =>  'splitbutton',  'glyph'     => Configure::read('icnReload'),'scale'   => 'large', 'itemId'    => 'reload',   'tooltip'    => __('Reload'),
                        'menu'  => [
                            'items' => [
                                '<b class="menu-title">'.__('Reload every').':</b>',
                                ['text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ],
                                ['text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false],
                                ['text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ],
                                ['text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true ]

                            ]
                        ]
                    ],
                    ['xtype' => 'button', 'glyph'     => Configure::read('icnAdd'), 'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')],
                    ['xtype' => 'button', 'glyph'     => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')],
                    ['xtype' => 'button', 'glyph'     => Configure::read('icnEdit'), 'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit')]
                ]],
                ['xtype' => 'buttongroup','title' => __('Other'), 'items' => [
                    ['xtype' => 'button','glyph'=> Configure::read('icnNote'),'scale' => 'large', 'itemId' => 'note', 'tooltip'=> __('Add notes')],
                    ['xtype' => 'button','glyph'=> Configure::read('icnCsv'),'scale' => 'large', 'itemId' => 'csv', 'tooltip'=> __('Export CSV')],
                    ['xtype' => 'button','glyph'=> Configure::read('icnGraph'),'scale' => 'large', 'itemId' => 'graph','tooltip'=> __('Graphs')],
                    ['xtype' => 'button','glyph'=> Configure::read('icnMap'),'scale' => 'large', 'itemId' => 'map',   'tooltip'=> __('Map')]
                ]],
                ['xtype' => 'buttongroup', 'width'=> 300,'title' => '<span class="txtBlue"><i class="fa  fa-lightbulb-o"></i> Site Wide Shared Secret</span>', 'items' => [
                    ['xtype' => 'tbtext', 'html' => "<h1>$shared_secret</h1>"]
                ]],
            ];
        }

        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)
            $id             = $user['id'];
            $action_group   = [];

            array_push($action_group, ['xtype' =>  'splitbutton',  'glyph'     => Configure::read('icnReload'),'scale'   => 'large', 'itemId'    => 'reload',   'tooltip'    => __('Reload'),
                'menu'  => [
                    'items' => [
                        '<b class="menu-title">'.__('Reload every').':</b>',
                        ['text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ],
                        ['text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false],
                        ['text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ],
                        ['text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true ]
                    ]
                ]
            ]);

            //Add
            if($this->Acl->check(['model' => 'Users', 'foreign_key' => $id], $this->base."add")){
                array_push($action_group, [
                    'xtype'     => 'button',
                    'iconCls'   => 'b-add',
                    'glyph'     => Configure::read('icnAdd'),
                    'scale'     => 'large',
                    'itemId'    => 'add',
                    'tooltip'   => __('Add')]);
            }
            //Delete
            if($this->Acl->check(['model' => 'Users', 'foreign_key' => $id], $this->base.'delete')){
                array_push($action_group, [
                    'xtype'     => 'button',
                    'iconCls'   => 'b-delete',
                    'glyph'     => Configure::read('icnDelete'),
                    'scale'     => 'large',
                    'itemId'    => 'delete',
                    'disabled'  => true,
                    'tooltip'   => __('Delete')]);
            }

            //Edit
            if($this->Acl->check(['model' => 'Users', 'foreign_key' => $id], $this->base.'edit')){
                array_push($action_group,[
                    'xtype'     => 'button',
                    'iconCls'   => 'b-edit',
                    'glyph'     => Configure::read('icnEdit'),
                    'scale'     => 'large',
                    'itemId'    => 'edit',
                    'disabled'  => true,
                    'tooltip'   => __('Edit')]);
            }

            $menu = [
                ['xtype' => 'buttongroup','title' => __('Action'),        'items' => $action_group],
                ['xtype' => 'buttongroup','title' => __('Other'), 'items' => [
                    ['xtype' => 'button','glyph'=> Configure::read('icnNote'),'scale' => 'large', 'itemId' => 'note', 'tooltip'=> __('Add notes')],
                    ['xtype' => 'button','glyph'=> Configure::read('icnCsv'),'scale' => 'large', 'itemId' => 'csv', 'tooltip'=> __('Export CSV')],
                    ['xtype' => 'button','glyph'=> Configure::read('icnGraph'),'scale' => 'large', 'itemId' => 'graph','tooltip'=> __('Graphs')],
                    ['xtype' => 'button','glyph'=> Configure::read('icnMap'),'scale' => 'large', 'itemId' => 'map',   'tooltip'=> __('Map')],
                ]],
                [
                    'xtype'     => 'buttongroup',
                    'width'     => 300,
                    'title'     => '<span class="txtBlue"><i class="fa  fa-lightbulb-o"></i> Site Wide Shared Secret</span>',
                    'items'     => [
                        ['xtype' => 'tbtext', 'html' => "<h1>$shared_secret</h1>"]
                    ]],
            ];
        }
        $this->set([
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => ['items','success']
        ]);
    }

    function _build_common_query($query, $user){

        $where = [];

        $query->contain([
            'Users',
            'DynamicClientRealms.Realms',
            'DynamicClientNotes.Notes',
        ]);

        //===== SORT =====
        //Default values for sort and dir
        $sort   = $this->main_model.'.last_contact';
        $dir    = 'DESC';

        if(null !== $this->request->getQuery('sort')){
            if($this->request->getQuery('sort') == 'username'){
                $sort = 'Users.username';
            }else{
                $sort = $this->main_model.'.'.$this->request->getQuery('sort');
            }
            $dir  = $this->request->getQuery('dir');
        }

        $query->order([$sort => $dir]);
        //==== END SORT ===


        //====== REQUEST FILTER =====
        if(null !== $this->request->getQuery('filter')){
            $filter = json_decode($this->request->getQuery('filter'));
            foreach($filter as $f){

                $f = $this->GridFilter->xformFilter($f);

                //Strings
                if($f->type == 'string'){
                    if($f->field == 'owner'){
                        array_push($where, ["Users.username LIKE" => '%'.$f->value.'%']);
                    }else{
                        $col = $this->main_model.'.'.$f->field;
                        array_push($where, ["$col LIKE" => '%'.$f->value.'%']);
                    }
                }
                //Bools
                if($f->type == 'boolean'){
                    $col = $this->main_model.'.'.$f->field;
                    array_push($where, ["$col" => $f->value]);
                }
            }
        }
        //====== END REQUEST FILTER =====

        //====== AP FILTER =====
        //If the user is an AP; we need to add an extra clause to only show the LicensedDevices which he is allowed to see.
        if($user['group_name'] == Configure::read('group.ap')){  //AP
            $tree_array = [];
            $user_id    = $user['id'];

            //**AP and upward in the tree**
            $this->parents = $this->Users->find('path', ['for' => $user_id, 'fields' => 'Users.id']);
            //So we loop this results asking for the parent nodes who have available_to_siblings = true
            foreach($this->parents as $i){
                $i_id = $i->id;
                if($i_id != $user_id){ //upstream
                    array_push($tree_array,[$this->main_model.'.user_id' => $i_id,$this->main_model.'.available_to_siblings' => true]);
                }else{
                    array_push($tree_array,[$this->main_model.'.user_id' => $i_id]);
                }
            }
            //** ALL the AP's children
            $this->children    = $this->Users->find_access_provider_children($user['id']);
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $id = $i['id'];
                    array_push($tree_array,[$this->main_model.'.user_id' => $id]);
                }
            }
            //Add it as an OR clause
            array_push($where, ['OR' => $tree_array]);
        }
        //====== END AP FILTER =====
        return $query->where($where);
    }

    private function _get_action_flags($owner_id,$user){
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            return ['update' => true, 'delete' => true];
        }

        if($user['group_name'] == Configure::read('group.ap')){  //AP
            $user_id = $user['id'];

            //test for self
            if($owner_id == $user_id){
                return ['update' => true, 'delete' => true ];
            }
            //Test for Parents
            foreach($this->parents as $i){
                if($i->id == $owner_id){
                    return ['update' => false, 'delete' => false ];
                }
            }

            //Test for Children
            foreach($this->children as $i){
                if($i['id'] == $owner_id){
                    return ['update' => true, 'delete' => true];
                }
            }
        }
    }

    private function _add_dynamic_client_realm($dynamic_client_id,$realm_id){

        $d                                              = [];
        $d['DynamicClientRealms']['id']                  = '';
        $d['DynamicClientRealms']['dynamic_client_id']   = $dynamic_client_id;
        $d['DynamicClientRealms']['realm_id']            = $realm_id;

        $dynClientRealmEntity = $this->DynamicClients->DynamicClientRealms->newEntity($d);

        $this->DynamicClients->DynamicClientRealms->save($dynClientRealmEntity);
    }
    
    private function _getBytesValue($total_data,$unit){
    
        if(strpos($unit, 'kb') !== false){
           $total_data = $total_data * 1024; 
        }
        if(strpos($unit, 'mb') !== false){
           $total_data = $total_data * 1024 * 1024; 
        }
        if(strpos($unit, 'gb') !== false){
           $total_data = $total_data * 1024 * 1024 * 1024; 
        }
        if(strpos($unit, 'tb') !== false){
           $total_data = $total_data * 1024 * 1024 * 1024 * 1024; 
        }
           
        return $total_data;
    }
    
}
