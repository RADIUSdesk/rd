<?php
App::uses('AppController', 'Controller');

class RadpostauthsController extends AppController {

    public $name       = 'Radpostauths';
    public $components = array('Aa','GridFilter');
    protected $base    = "Access Providers/Controllers/Radpostauths/";

    //--- FROM THE OLD ---
    /* json_index json_add json_del json_view json_edit 
        // json_prepaid_list json_tabs json_send_message csv json_change_profile 
        // json_private_attributes json_add_private json_del_private json_edit_private
        // json_test_auth json_disable json_usage json_kick json_notify_detail json_notify_save
        // json_view_activity json_del_activity json_password 
        // json_actions json_actions_for_user_private json_actions_for_user_profile json_actions_for_user_activity
    */

    //-- NOTES on users:
    //-- Each user belongs to (A) group (group_id) = Permanent Users. (B) a realm (realm_id) (C) a creator (user_id)
    //-- (D) Profile ID (profile_id) (E) a Language ID (language_id) (F) an Auth Method id (auth_method_id)

    //-- Each user will have a token which should be used in the URL to do Ajax calls

    //-- NOTES on rights:
    //-- Most controller actions will require a token in the query string to determine who originated the request
    //-- The rights of that person will then be checked and also against who it is attempting to be done.


    //-------- BASIC CRUD -------------------------------

    public function export_csv(){

        $this->autoRender   = false;

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        //Build query
        $user_id    = $user['id'];
        $c          = $this->_build_common_query($user);
        $q_r        = $this->{$this->modelClass}->find('all',$c);

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

        //Results
        foreach($q_r as $i){

            $columns    = array();
            $csv_line   = array();
            if(isset($this->request->query['columns'])){
                $columns = json_decode($this->request->query['columns']);
                foreach($columns as $c){
                    $column_name = $c->name;     
                    array_push($csv_line,$i['Radpostauth']["$column_name"]); 
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


  
    public function index(){
        //-- Required query attributes: token;
        //-- Optional query attribute: sel_language (for i18n error messages)
        //-- also LIMIT: limit, page, start (optional - use sane defaults)
        //-- FILTER <- This will need fine tunning!!!!
        //-- AND SORT ORDER <- This will need fine tunning!!!!

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $c = $this->_build_common_query($user); 

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

        $total  = $this->{$this->modelClass}->find('count'  , $c); 
        $q_r    = $this->{$this->modelClass}->find('all'    , $c_page);
        
        $items  = array();
        foreach($q_r as $i){ 
            array_push($items,
                array(
                    'id'                => $i['Radpostauth']['id'], 
                    'username'          => $i['Radpostauth']['username'],
                    'pass'              => $i['Radpostauth']['pass'],
                    'realm'             => $i['Radpostauth']['realm'],
                    'reply'             => $i['Radpostauth']['reply'],
                    'nasname'           => $i['Radpostauth']['nasname'],
                    'authdate'          => $i['Radpostauth']['authdate']
                )
            );
        }                
        $this->set(array(
            'items'         => $items,
            'success'       => true,
            'totalCount'    => $total,
            '_serialize'    => array('items','success','totalCount')
        ));
    }

    public function add(){

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        $this->request['active']       = 0;
        $this->request['monitor']      = 0;     


        //Two fields should be tested for first:
        if(array_key_exists('active',$this->request->data)){
            $this->request->data['active'] = 1;
        }

        if(array_key_exists('monior',$this->request->data)){
            $this->request->data['monitor'] = 1;
        }

        if($this->request->data['parent_id'] == '0'){ //This is the holder of the token
            $this->request->data['parent_id'] = $user['id'];
        }

        if(!array_key_exists('language',$this->request->data)){
            $this->request->data['language'] = Configure::read('language.default');
        }

        //Get the language and country
        $country_language   = explode( '_', $this->request->data['language'] );

        $country            = $country_language[0];
        $language           = $country_language[1];

        $this->request->data['language_id'] = $language;
        $this->request->data['country_id']  = $country;

        //Get the group ID for AP's
        $group_name = Configure::read('group.user');
        $q_r        = ClassRegistry::init('Group')->find('first',array('conditions' =>array('Group.name' => $group_name)));
        $group_id   = $q_r['Group']['id'];
        $this->request->data['group_id'] = $group_id;

        //Zero the token to generate a new one for this user:
        $this->request->data['token'] = '';

        //The rest of the attributes should be same as the form..
        $this->{$this->modelClass}->create();
        if ($this->{$this->modelClass}->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        } else {
            $message = 'Error';
            $this->set(array(
                'errors'    => $this->{$this->modelClass}->validationErrors,
                'success'   => false,
                'message'   => array('message' => __('Could not create item')),
                '_serialize' => array('errors','success','message')
            ));
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

        //FIXME We need to find a creative wat to determine if the Access Provider can delete this accounting data!!!
	    if(isset($this->data['id'])){   //Single item delete             
            $this->{$this->modelClass}->id = $this->data['id'];
            $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){   
                $this->{$this->modelClass}->id = $d['id'];
                $this->{$this->modelClass}->delete($this->{$this->modelClass}->id,true);
            }         
        }

        $fail_flag = false;
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

    //--------- END BASIC CRUD ---------------------------

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
                        array( 'xtype' =>  'splitbutton',  'iconCls' => 'b-reload',   'glyph'     => Configure::read('icnReload'),'scale'   => 'large', 'itemId'    => 'reload',   'tooltip'    => __('Reload'),
                            'menu'  => array( 
                                'items' => array( 
                                    '<b class="menu-title">Reload every:</b>',
                                  //  array( 'text'   => _('Cancel auto reload'),   'itemId' => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true ),
                                    array( 'text'  => _('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                                    array( 'text'  => _('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                                    array( 'text'  => _('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                                    array( 'text'  => _('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )
                                   
                                )
                            )
                    ) 
                )),
                array('xtype' => 'buttongroup', 'width'=> 75 ,'title' => __('Document'), 'items' => array(
                    array('xtype' => 'button', 'iconCls' => 'b-csv', 'glyph'     => Configure::read('icnCsv'),    'scale' => 'large', 'itemId' => 'csv',      'tooltip'=> __('Export CSV')),
                )),
               
            );
        }

        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)

            $id             = $user['id'];
            $action_group   = array();
            $document_group = array();
            $specific_group = array();
            //Reload
            array_push($action_group,array( 
                'xtype'     =>  'splitbutton',  
                'iconCls'   => 'b-reload',
                'glyph'     => Configure::read('icnReload'),   
                'scale'     => 'large', 
                'itemId'    => 'reload',   
                'tooltip'   => __('Reload'),
                'menu'      => array(             
                    'items'     => array( 
                                    '<b class="menu-title">Reload every:</b>',            
                    array( 'text'  => _('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                    array( 'text'  => _('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                    array( 'text'  => _('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                    array( 'text'  => _('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )                                  
                ))));

            

            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'export_csv')){ 
                array_push($document_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-csv',
                    'glyph'     => Configure::read('icnCsv'),     
                    'scale'     => 'large', 
                    'itemId'    => 'csv',      
                    'tooltip'   => __('Export CSV')));
            }

            $menu = array(
                        array('xtype' => 'buttongroup','title' => __('Action'),                 'items' => $action_group),
                        array('xtype' => 'buttongroup','title' => __('Document'),'width'=> 75,  'items' => $document_group)
                    );
        }
        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

    //______ END EXT JS UI functions ________


     function _build_common_query($user){

        //Empty to start with
        $c                  = array();
        $c['joins']         = array(); 
        $c['conditions']    = array();

        //What should we include....
        $c['contain']   = array(
                        //    'Radcheck'     
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = $this->modelClass.'.authdate';
        $dir    = 'DESC';

        if(isset($this->request->query['sort'])){
            $sort = $this->modelClass.'.'.$this->request->query['sort'];
            $dir  = $this->request->query['dir'];
/*
            if($this->request->query['sort'] == 'owner'){
                $sort = 'User.username';
            }elseif(($this->request->query['sort'] == 'profile')||($this->request->query['sort'] == 'realm')){
                $sort = 'Radcheck.value';
            }else{
                $sort = $this->modelClass.'.'.$this->request->query['sort'];
            }
            $dir  = $this->request->query['dir'];
*/
        } 

        $c['order'] = array("$sort $dir");
        //==== END SORT ===

        //======= For a specified username filter *Usually on the edit of user / device / voucher ======
        if(isset($this->request->query['username'])){
            $un = $this->request->query['username'];
            array_push($c['conditions'],array($this->modelClass.".username" => $un));
        }


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
                   
                }
                //Date
                if($f->type == 'date'){
                    //date we want it in "2013-03-12"
                    $col = $this->modelClass.'.'.$f->field;
                    if($f->comparison == 'eq'){
                        array_push($c['conditions'],array("DATE($col)" => $f->value));
                    }

                    if($f->comparison == 'lt'){
                        array_push($c['conditions'],array("DATE($col) <" => $f->value));
                    }
                    if($f->comparison == 'gt'){
                        array_push($c['conditions'],array("DATE($col) >" => $f->value));
                    }
                }
            }
        }
        //====== END REQUEST FILTER =====

        //====== AP FILTER =====
        if($user['group_name'] == Configure::read('group.ap')){  //AP 
            $this->Realm = ClassRegistry::init('Realm');
            $this->User  = ClassRegistry::init('User');
            $q_r        = $this->User->getPath($user['id']); //Get all the parents up to the root
            $ap_clause  = array();
            $ap_id      = $user['id'];

            foreach($q_r as $i){         
                $user_id    = $i['User']['id'];
                $this->Realm->contain();
                $r        = $this->Realm->find('all',array('conditions' => array('Realm.user_id' => $user_id, 'Realm.available_to_siblings' => true)));
                foreach($r  as $j){
                    $id     = $j['Realm']['id'];
                    $name   = $j['Realm']['name'];   
                    $read   = $this->Acl->check(
                                array('model' => 'Users', 'foreign_key' => $user['id']), 
                                array('model' => 'Realms','foreign_key' => $id), 'read');
                    if($read == true){
                        array_push($ap_clause,array($this->modelClass.'.realm' => $name));
                    }                   
                }
            }

            //Get all the realms owned by the $ap_id 
            $r        = $this->Realm->find('all',array('conditions' => array('Realm.user_id' => $ap_id)));
            foreach($r  as $j){
                $id     = $j['Realm']['id'];
                $name   = $j['Realm']['name']; 
                array_push($ap_clause,array($this->modelClass.'.realm' => $name));
            }

            //Add it as an OR clause
            array_push($c['conditions'],array('OR' => $ap_clause)); 
        }
        //====== END AP FILTER =====
        return $c;
    }

    private function _find_parents($id){

        $this->User->contain();//No dependencies
        $q_r        = $this->User->getPath($id);
        $path_string= '';
        if($q_r){

            foreach($q_r as $line_num => $i){
                $username       = $i['User']['username'];
                if($line_num == 0){
                    $path_string    = $username;
                }else{
                    $path_string    = $path_string.' -> '.$username;
                }
            }
            if($line_num > 0){
                return $username." (".$path_string.")";
            }else{
                return $username;
            }
        }else{
            return __("orphaned");
        }
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
