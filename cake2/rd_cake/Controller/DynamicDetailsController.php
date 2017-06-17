<?php
App::uses('AppController', 'Controller');

class DynamicDetailsController extends AppController {

    public $name       = 'DynamicDetails';
    public $components = array('Aa','GridFilter');
    public $uses       = array('DynamicDetail','User');
    protected $base    = "Access Providers/Controllers/DynamicDetails/";

    public function info_for(){

        $items = array();

		$sl_items	= array();

        if(isset($this->request->query['dynamic_id'])){ //preview link will call this page ?dynamic_id=<id>

            $this->{$this->modelClass}->contain('DynamicPage','DynamicPhoto','DynamicDetailSocialLogin');
            $q_r = $this->{$this->modelClass}->findById($this->request->query['dynamic_id']);
            if($q_r){
                //Modify the photo path:
                $c = 0;
                foreach($q_r['DynamicPhoto'] as $i){
                    $q_r['DynamicPhoto'][$c]['file_name'] = Configure::read('paths.dynamic_photos').$i['file_name'];
                    $c++;
                }
                $items['photos']                    = $q_r['DynamicPhoto'];
                $items['pages']                     = $q_r['DynamicPage'];

				$sl_items = $q_r['DynamicDetailSocialLogin'];
            }

        }else{ //Build a query since it was not called from the preview link
            $conditions = array("OR" =>array());
      
            foreach(array_keys($this->request->query) as $key){
                array_push($conditions["OR"],
                    array("DynamicPair.name" => $key, "DynamicPair.value" =>  $this->request->query[$key])
                ); //OR query all the keys
            }
            $this->{$this->modelClass}->DynamicPair->contain(
				array('DynamicDetail' => array('DynamicPhoto','DynamicPage','DynamicDetailSocialLogin'))
			);

            $q_r = $this->{$this->modelClass}->DynamicPair->find('first', 
                array('conditions' => $conditions, 'order' => 'DynamicPair.priority DESC')); //Return the one with the highest priority

            if($q_r){
                
                //Modify the photo path:
                $c = 0;
                foreach($q_r['DynamicDetail']['DynamicPhoto'] as $i){
                    $q_r['DynamicDetail']['DynamicPhoto'][$c]['file_name'] = Configure::read('paths.dynamic_photos').$i['file_name'];
                    $c++;
                }
                $items['photos']                    = $q_r['DynamicDetail']['DynamicPhoto'];
                $items['pages']                     = $q_r['DynamicDetail']['DynamicPage'];

				$sl_items = $q_r['DynamicDetail']['DynamicDetailSocialLogin'];
            }
        }
		//print_r($q_r);

        //Get the detail for the page
        if($q_r){
            $items['detail']['id']              = $q_r['DynamicDetail']['id'];
            $items['detail']['name']            = $q_r['DynamicDetail']['name'];
            $items['detail']['icon_file_name']  = Configure::read('paths.dynamic_detail_icon').$q_r['DynamicDetail']['icon_file_name'];
            $items['detail']['phone']           = $q_r['DynamicDetail']['phone'];
            $items['detail']['fax']             = $q_r['DynamicDetail']['fax'];
            $items['detail']['cell']            = $q_r['DynamicDetail']['cell'];
            $items['detail']['email']           = $q_r['DynamicDetail']['email'];
            $items['detail']['url']             = $q_r['DynamicDetail']['url'];
            $items['detail']['street_no']       = $q_r['DynamicDetail']['street_no'];
            $items['detail']['street']          = $q_r['DynamicDetail']['street'];
            $items['detail']['town_suburb']     = $q_r['DynamicDetail']['town_suburb'];
            $items['detail']['city']            = $q_r['DynamicDetail']['city'];
            $items['detail']['country']         = $q_r['DynamicDetail']['country'];
            $items['detail']['lon']             = $q_r['DynamicDetail']['lon'];
            $items['detail']['lat']             = $q_r['DynamicDetail']['lat'];

            $items['settings']['t_c_check']         = $q_r['DynamicDetail']['t_c_check'];
            $items['settings']['t_c_url']           = $q_r['DynamicDetail']['t_c_url'];
            $items['settings']['redirect_check']    = $q_r['DynamicDetail']['redirect_check'];
            $items['settings']['redirect_url']      = $q_r['DynamicDetail']['redirect_url'];
            $items['settings']['slideshow_check']   = $q_r['DynamicDetail']['slideshow_check'];
            $items['settings']['seconds_per_slide'] = $q_r['DynamicDetail']['seconds_per_slide'];
            $items['settings']['connect_check']     = $q_r['DynamicDetail']['connect_check'];
            $items['settings']['connect_username']  = $q_r['DynamicDetail']['connect_username'];
            $items['settings']['connect_suffix']    = $q_r['DynamicDetail']['connect_suffix'];
            $items['settings']['connect_delay']     = $q_r['DynamicDetail']['connect_delay'];
            $items['settings']['connect_only']      = $q_r['DynamicDetail']['connect_only'];
			$items['settings']['user_login_check']  = $q_r['DynamicDetail']['user_login_check'];
			$items['settings']['voucher_login_check']= $q_r['DynamicDetail']['voucher_login_check'];
			$items['settings']['auto_suffix_check'] = $q_r['DynamicDetail']['auto_suffix_check'];
			$items['settings']['auto_suffix']       = $q_r['DynamicDetail']['auto_suffix'];
			$items['settings']['usage_show_check']  = $q_r['DynamicDetail']['usage_show_check'];
			$items['settings']['usage_refresh_interval'] 	= $q_r['DynamicDetail']['usage_refresh_interval'];
			$items['settings']['register_users']    = $q_r['DynamicDetail']['register_users'];
			$items['settings']['lost_password']    	= $q_r['DynamicDetail']['lost_password'];

			if($q_r['DynamicDetail']['social_enable']){
				$items['settings']['social_login']['active'] = true;
				
				//Find the temp username and password
				$temp_user_id = $q_r['DynamicDetail']['social_temp_permanent_user_id'];
				$user_detail  = $this->_find_username_and_password($temp_user_id);
				$items['settings']['social_login']['temp_username'] = $user_detail['username'];
				$items['settings']['social_login']['temp_password'] = $user_detail['password'];
				//Find if there are any defined
				$items['settings']['social_login']['items'] = array();
				foreach($sl_items as $i){
					$n = $i['name'];
					array_push($items['settings']['social_login']['items'], array('name' => $n));
				}

			}else{
				$items['settings']['social_login']['active'] = false;
			}
        }

        $success = true;
        if(count($items) == 0){ //Not found
            $success = false;
        }

        $this->set(array(
            'data' => $items,
            'success' => $success,
            '_serialize' => array('data','success')
        ));

    }

    public function id_me(){
        $this->layout = 'rd';
        $u_a = $this->request->header('User-Agent');
        $this->set('u_a',$u_a);
        $this->set(array(
            'data' => array("User-Agent" => $u_a),
            'success' => true,
            '_serialize' => array('data','success')
        ));
    }

    public function chilli_browser_detect(){

		$conditions     = array("OR" =>array());
		$query_string   = $_SERVER['QUERY_STRING'];

		foreach(array_keys($this->request->query) as $key){
                array_push($conditions["OR"],
                    array("DynamicPair.name" => $key, "DynamicPair.value" =>  $this->request->query[$key])
                ); //OR query all the keys
       	}

       	$this->{$this->modelClass}->DynamicPair->contain('DynamicDetail');
      	$q_r = $this->{$this->modelClass}->DynamicPair->find('first', 
                array('conditions' => $conditions, 'order' => 'DynamicPair.priority DESC')); //Return the one with the highest priority

		//See which Theme are selected
		$theme = 'Default';
		$theme_selected = 'Default';
		
		$i18n = 'en_GB';
		if($q_r){
            $theme_selected =  $q_r['DynamicDetail']['theme'];
            if($q_r['DynamicDetail']['default_language'] != ''){
                $i18n = $q_r['DynamicDetail']['default_language'];
            }
		}
		$query_string = $query_string."&i18n=$i18n";
		
		
        if($theme_selected == 'Custom'){ //With custom themes we read the valuse out of the DB
		    $redir_to = $q_r['DynamicDetail']['coova_desktop_url'].'?'.$query_string;
		    if($this->request->is('mobile')){
                $redir_to = $q_r['DynamicDetail']['coova_mobile_url'].'?'.$query_string;
            }    
		}else{  //Else we fetch the 'global' theme's value from the file

		    Configure::load('DynamicLogin'); 
            $pages       = Configure::read('DynamicLogin.theme.'.$theme_selected); //Read the defaults
		    if(!$pages){
			    $pages       = Configure::read('DynamicLogin.theme.'.$theme); //Read the defaults
		    }

		    $redir_to = $pages['coova_desktop'].'?'.$query_string;
            if($this->request->is('mobile')){
                $redir_to = $pages['coova_mobile'].'?'.$query_string;
            }
        }

        $this->response->header('Location', $redir_to);
    }
    
    public function mikrotik_browser_detect(){

		$conditions     = array("OR" =>array());
		$query_string   = $_SERVER['QUERY_STRING'];
		
		if (!$this->request->is('post')) {
		    foreach(array_keys($this->request->query) as $key){
                    array_push($conditions["OR"],
                        array("DynamicPair.name" => $key, "DynamicPair.value" =>  $this->request->query[$key])
                    ); //OR query all the keys
           	}	
		}else{
		    $q_array = array();
		    foreach(array_keys($this->request->data) as $key){
		    
		        $q_array[$key] = $this->request->data[$key];
		        
		        array_push($conditions["OR"],
                    array("DynamicPair.name" => $key, "DynamicPair.value" =>  $this->request->data[$key])
                ); //OR query all the keys
		     }
		     
		     $query_string =  http_build_query($q_array);
		}
		
       	$this->{$this->modelClass}->DynamicPair->contain('DynamicDetail');
      	$q_r = $this->{$this->modelClass}->DynamicPair->find('first', 
                array('conditions' => $conditions, 'order' => 'DynamicPair.priority DESC')); //Return the one with the highest priority

		//See which Theme are selected
		$theme = 'Default';
		$theme_selected = 'Default';
		
		$i18n = 'en_GB';
		if($q_r){
            $theme_selected =  $q_r['DynamicDetail']['theme'];
            if($q_r['DynamicDetail']['default_language'] != ''){
                $i18n = $q_r['DynamicDetail']['default_language'];
            }
		}
		$query_string = $query_string."&i18n=$i18n";
		

	    if($theme_selected == 'Custom'){ //With custom themes we read the valuse out of the DB
		    $redir_to = $q_r['DynamicDetail']['mikrotik_desktop_url'].'?'.$query_string;
		    if($this->request->is('mobile')){
                $redir_to = $q_r['DynamicDetail']['mikrotik_mobile_url'].'?'.$query_string;
            }
		}else{  //Else we fetch the 'global' theme's value from the file
		    Configure::load('DynamicLogin'); 
            $pages       = Configure::read('DynamicLogin.theme.'.$theme_selected); //Read the defaults
		    if(!$pages){
			    $pages       = Configure::read('DynamicLogin.theme.'.$theme); //Read the defaults
		    }

		    $redir_to = $pages['mikrotik_desktop'].'?'.$query_string;
            if($this->request->is('mobile')){
                $redir_to = $pages['mikrotik_mobile'].'?'.$query_string;
            }
        }

        $this->response->header('Location', $redir_to);
    }


    public function ruckus_browser_detect(){

		$conditions     = array("OR" =>array());
		$query_string   = $_SERVER['QUERY_STRING'];

		foreach(array_keys($this->request->query) as $key){
                array_push($conditions["OR"],
                    array("DynamicPair.name" => $key, "DynamicPair.value" =>  $this->request->query[$key])
                ); //OR query all the keys
       	}

       	$this->{$this->modelClass}->DynamicPair->contain('DynamicDetail');
      	$q_r = $this->{$this->modelClass}->DynamicPair->find('first', 
                array('conditions' => $conditions, 'order' => 'DynamicPair.priority DESC')); //Return the one with the highest priority

		//See which Theme are selected
		$theme = 'Default';
		$theme_selected = 'Default';
		
		$i18n = 'en_GB';
		if($q_r){
            $theme_selected =  $q_r['DynamicDetail']['theme'];
            if($q_r['DynamicDetail']['default_language'] != ''){
                $i18n = $q_r['DynamicDetail']['default_language'];
            }
		}
		$query_string = $query_string."&i18n=$i18n";
		
        //------------------------
        //FIXME For now we'll use the Coova URL on custom as a hack
        //-----------------------
        
	    if($theme_selected == 'Custom'){ //With custom themes we read the valuse out of the DB
		    $redir_to = $q_r['DynamicDetail']['coova_desktop_url'].'?'.$query_string;
		    if($this->request->is('mobile')){
                $redir_to = $q_r['DynamicDetail']['coova_mobile_url'].'?'.$query_string;
            }
		}else{  //Else we fetch the 'global' theme's value from the file
		    Configure::load('DynamicLogin'); 
            $pages       = Configure::read('DynamicLogin.theme.'.$theme_selected); //Read the defaults
		    if(!$pages){
			    $pages       = Configure::read('DynamicLogin.theme.'.$theme); //Read the defaults
		    }

		    $redir_to = $pages['ruckus_desktop'].'?'.$query_string;
            if($this->request->is('mobile')){
                $redir_to = $pages['ruckus_mobile'].'?'.$query_string;
            }
        }

        $this->response->header('Location', $redir_to);
    }
    
	//----- Give better preview pages -----
	public function preview_chilli_desktop(){
	
	    if(isset($this->request->query['wizard_name'])){
	        $this->{$this->modelClass}->contain();
		    $q_r = $this->{$this->modelClass}->findByName($this->request->query['wizard_name']);
		    if($q_r){
		        $this->request->query['dynamic_id'] = $q_r['DynamicDetail']['id'];
		        $_SERVER['QUERY_STRING'] = $_SERVER['QUERY_STRING'].'&dynamic_id='.$this->request->query['dynamic_id'].'&uamip=10.1.0.1&uamport=3990';
		    }   
	    }else{
		    $this->{$this->modelClass}->contain();
		    $q_r = $this->{$this->modelClass}->findById($this->request->query['dynamic_id']);
        }
      	
		//See which Theme are selected
		$theme = 'Default';
		$i18n = 'en_GB';
		if($q_r){
            $theme_selected =  $q_r['DynamicDetail']['theme'];
            if($q_r['DynamicDetail']['default_language'] != ''){
                $i18n = $q_r['DynamicDetail']['default_language'];
            }
		}

		if($theme_selected == 'Custom'){ //With custom themes we read the valuse out of the DB
		
		    $redir_to = $q_r['DynamicDetail']['coova_desktop_url'].'?'.$_SERVER['QUERY_STRING']."&i18n=$i18n";
		    
        }else{   
		    Configure::load('DynamicLogin'); 
            $pages       = Configure::read('DynamicLogin.theme.'.$theme_selected); //Read the defaults
		    if(!$pages){
			    $pages       = Configure::read('DynamicLogin.theme.'.$theme); //Read the defaults
		    }
		    $redir_to = $pages['coova_desktop'].'?'.$_SERVER['QUERY_STRING']."&i18n=$i18n";
		}

        $this->response->header('Location', $redir_to);
	}

	public function preview_chilli_mobile(){

	    if(isset($this->request->query['wizard_name'])){
	        $this->{$this->modelClass}->contain();
		    $q_r = $this->{$this->modelClass}->findByName($this->request->query['wizard_name']);
		    if($q_r){
		        $this->request->query['dynamic_id'] = $q_r['DynamicDetail']['id'];
		        $_SERVER['QUERY_STRING'] = $_SERVER['QUERY_STRING'].'&dynamic_id='.$this->request->query['dynamic_id'].'&uamip=10.1.0.1&uamport=3990';
		    }   
	    }else{
		    $this->{$this->modelClass}->contain();
		    $q_r = $this->{$this->modelClass}->findById($this->request->query['dynamic_id']);
        }
      	
		//See which Theme are selected
		$theme = 'Default';
		$i18n = 'en_GB';
		if($q_r){
            $theme_selected =  $q_r['DynamicDetail']['theme'];
            if($q_r['DynamicDetail']['default_language'] != ''){
                $i18n = $q_r['DynamicDetail']['default_language'];
            }
		}

		if($theme_selected == 'Custom'){ //With custom themes we read the valuse out of the DB
		    $redir_to = $q_r['DynamicDetail']['coova_mobile_url'].'?'.$_SERVER['QUERY_STRING']."&i18n=$i18n";    
        }else{  
		    Configure::load('DynamicLogin'); 
            $pages       = Configure::read('DynamicLogin.theme.'.$theme_selected); //Read the defaults
		    if(!$pages){
			    $pages       = Configure::read('DynamicLogin.theme.'.$theme); //Read the defaults
		    }
		    $redir_to = $pages['coova_mobile'].'?'.$_SERVER['QUERY_STRING']."&i18n=$i18n";
		}
		
        $this->response->header('Location', $redir_to);
	}
	
	//------ List of configured dynamic attributes types ------
    //This is displayed as a select to choose from when the user adds a NAS of connection type dynamic
    public function i18n(){
        $items = array();
        Configure::load('DynamicLogin');
        $i18n = Configure::read('DynamicLogin.i18n');
        foreach($i18n as $i){
            if($i['active']){
                array_push($items, $i);
            }
        }

        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

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
        $q_r        = $this->DynamicDetail->find('all',$c);

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
                    //DynamicDetails
                    $column_name = $c->name;
                    if($column_name == 'notes'){
                        $notes   = '';
                        foreach($i['DynamicDetailNote'] as $n){
                            if(!$this->_test_for_private_parent($n['Note'],$user)){
                                $notes = $notes.'['.$n['Note']['note'].']';    
                            }
                        }
                        array_push($csv_line,$notes);
                    }elseif($column_name =='owner'){
                        $owner_id       = $i['DynamicDetail']['user_id'];
                        $owner_tree     = $this->_find_parents($owner_id);
                        array_push($csv_line,$owner_tree); 
                    }else{
                        array_push($csv_line,$i['DynamicDetail']["$column_name"]);  
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

    //____ BASIC CRUD DynamicDetail Manager ________
    public function index(){
    //Display a list of DynamicDetails with their owners
    //This will be dispalyed to the Administrator as well as Access Providers who has righs

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

        $total              = $this->{$this->modelClass}->find('count',$c);       
        $q_r                = $this->{$this->modelClass}->find('all',$c_page);

        $items              = array();

        foreach($q_r as $i){
            //Create notes flag
            $notes_flag  = false;
            foreach($i['DynamicDetailNote'] as $rn){
                if(!$this->_test_for_private_parent($rn['Note'],$user)){
                    $notes_flag = true;
                    break;
                }
            }

            $owner_id       = $i['DynamicDetail']['user_id'];
            $owner_tree     = $this->_find_parents($owner_id);
            $action_flags   = $this->_get_action_flags($owner_id,$user);

            array_push($items,array(
                'id'                    => $i['DynamicDetail']['id'], 
                'name'                  => $i['DynamicDetail']['name'],
                'user_id'               => $owner_id,
                'owner'                 => $owner_tree, 
                'available_to_siblings' => $i['DynamicDetail']['available_to_siblings'],
                'phone'                 => $i['DynamicDetail']['phone'],
                'fax'                   => $i['DynamicDetail']['fax'],
                'cell'                  => $i['DynamicDetail']['cell'],
                'email'                 => $i['DynamicDetail']['email'],
                'url'                   => $i['DynamicDetail']['url'],
                'street_no'             => $i['DynamicDetail']['street_no'],
                'street'                => $i['DynamicDetail']['street'],
                'town_suburb'           => $i['DynamicDetail']['town_suburb'],
                'city'                  => $i['DynamicDetail']['city'],
                'country'               => $i['DynamicDetail']['country'],
                'lat'                   => $i['DynamicDetail']['lat'],
                'lon'                   => $i['DynamicDetail']['lon'],
                't_c_check'             => $i['DynamicDetail']['t_c_check'],
                't_c_url'               => $i['DynamicDetail']['t_c_url'],
				'theme'					=> $i['DynamicDetail']['theme'],
				'register_users'		=> $i['DynamicDetail']['register_users'],
				'lost_password'			=> $i['DynamicDetail']['lost_password'],
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
            '_serialize' => array('items','success','totalCount')
        ));
    }

    public function index_for_filter(){
    //Display a list of DynamicDetails with their owners
    //This will be dispalyed to the Administrator as well as Access Providers who has righs

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        $user_id = null;

        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $user_id = $user['id'];
        }

        if($user['group_name'] == Configure::read('group.ap')){  //Or AP
            $user_id = $user['id'];
        }

        //_____ ADMIN _____
        $items = array();
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $q_r = $this->DynamicDetail->find('all');

            //Init before the loop
            foreach($q_r as $i){
                $name   = $i['DynamicDetail']['name'];
                array_push($items,array(
                        'id'                    => $name, 
                        'text'                  => $name
                    ));
            }
        }

        //_____ AP _____
        if($user['group_name'] == Configure::read('group.ap')){  

            //If it is an Access Provider that requested this list; we should show:
            //1.) all those DynamicDetails that he is allowed to use from parents with the available_to_sibling flag set (no edit or delete)
            //2.) all those he created himself (if any) (this he can manage, depending on his right)
            //3.) all his children -> check if they may have created any. (this he can manage, depending on his right)

            $this->DynamicDetail->contain();
            $q_r = $this->DynamicDetail->find('all');

            //Loop through this list. Only if $user_id is a sibling of $owner_id we will add it to the list
            $ap_child_count = $this->User->childCount($user_id);

            foreach($q_r as $i){        
                $name           = $i['DynamicDetail']['name'];
                $owner_id       = $i['DynamicDetail']['user_id'];
                $a_t_s          = $i['DynamicDetail']['available_to_siblings'];
                
                //Filter for parents and children
                //DynamicDetails of parent's can not be edited, where DynamicDetails of childern can be edited
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($owner_id,$user_id)){ //Is the user_id an upstream parent of the AP
                        //Only those available to siblings:
                        if($a_t_s == 1){
                            array_push($items,array(
                                'id'                    => $name, 
                                'text'                  => $name
                            ));
                        }
                    }
                    if($ap_child_count != 0){ //See if this DynamicDetail is perhaps not one of those created by a sibling of the Access Provider
                        if($this->_is_sibling_of($user_id,$owner_id)){ //Is the owner a downstream sibling of the AP - Full rights
                            array_push($items,array(
                                'id'                    => $name, 
                                'text'                  => $name
                            ));
                        }
                    }
                }

                //Created himself
                if($owner_id == $user_id){    
                    array_push($items,array(
                        'id'                    => $name, 
                        'text'                  => $name
                    ));
                }
            }
        }

        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    public function add() {

        if(!$this->_ap_right_check()){
            return;
        }

        $user       = $this->Aa->user_for_token($this);
        $user_id    = $user['id'];

        //Get the owner's id
         if($this->request->data['user_id'] == '0'){ //This is the holder of the token - override '0'
            $this->request->data['user_id'] = $user_id;
        }
        
        $check_items = array('available_to_siblings', 't_c_check', 'register_users', 'lost_password');
        foreach($check_items as $ci){
            if(isset($this->request->data[$ci])){
                $this->request->data[$ci] = 1;
            }else{
                $this->request->data[$ci] = 0;
            }
        }
        
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
                'message'   => array('message' => 'Could not create item'),
                '_serialize' => array('errors','success','message')
            ));
        }
	}


    public function edit() {

        if(!$this->_ap_right_check()){
            return;
        }

        //We will not modify user_id
        unset($this->request->data['user_id']);

        //Make available to siblings check
        if(isset($this->request->data['available_to_siblings'])){
            $this->request->data['available_to_siblings'] = 1;
        }else{
            $this->request->data['available_to_siblings'] = 0;
        }

		if ($this->DynamicDetail->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }else{
             $this->set(array(
                'success' => false,
                '_serialize' => array('success')
            ));

        }
	}

    public function edit_settings(){

        if(!$this->_ap_right_check()){
            return;
        }
            
        //We will not modify user_id
        unset($this->request->data['user_id']);
        $check_items = array(
            'reg_email',
            'reg_auto_add',
            'reg_mac_check',
            'register_users',
            't_c_check',
            'redirect_check',
            'slideshow_check',
            'user_login_check',
            'voucher_login_check',
            'auto_suffix_check',
            'usage_show_check',
            'lost_password'
	    );
	    
        foreach($check_items as $i){
            if(isset($this->request->data[$i])){
                $this->request->data[$i] = 1;
            }else{
                $this->request->data[$i] = 0;
            }
        }
        
        if ($this->DynamicDetail->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }else{
             $this->set(array(
                'success' => false,
                '_serialize' => array('success')
            ));
        }
    }

    public function edit_click_to_connect(){

        if(!$this->_ap_right_check()){
            return;
        }
        //We will not modify user_id
        unset($this->request->data['user_id']);

        //connect_check compulsory check
        if(isset($this->request->data['connect_check'])){
            $this->request->data['connect_check'] = 1;
        }else{
            $this->request->data['connect_check'] = 0;
        }

        //connect_only compulsory check
        if(isset($this->request->data['connect_only'])){
            $this->request->data['connect_only'] = 1;
        }else{
            $this->request->data['connect_only'] = 0;
        }

        if ($this->DynamicDetail->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }else{
             $this->set(array(
                'success' => false,
                '_serialize' => array('success')
            ));
        }
    }


    public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        if(!$this->_ap_right_check()){
            return;
        }

	    if(isset($this->data['id'])){   //Single item delete

            //First find all the photos for this item then delete them
            $this->{$this->modelClass}->contain('DynamicPhoto');
            $q_r = $this->{$this->modelClass}->findById($this->data['id']);
            foreach($q_r['DynamicPhoto'] as $i){
                $file_to_delete = IMAGES."/dynamic_photos/".$i['file_name'];
                $this->{$this->modelClass}->DynamicPhoto->id = $i['id'];
                if($this->{$this->modelClass}->DynamicPhoto->delete($this->{$this->modelClass}->DynamicPhoto->id,true)){
                        if(file_exists($file_to_delete)){
                            unlink($file_to_delete);
                        }
                }
            }

            $this->DynamicDetail->id = $this->data['id'];
            $this->DynamicDetail->delete($this->DynamicDetail->id,true);
      
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                //First find all the photos for this item then delete them
                $this->{$this->modelClass}->contain('DynamicPhoto');
                $q_r = $this->{$this->modelClass}->findById($d['id']);
                foreach($q_r['DynamicPhoto'] as $i){
                    $file_to_delete = IMAGES."/dynamic_photos/".$i['file_name'];
                    $this->{$this->modelClass}->DynamicPhoto->id = $i['id'];
                    if($this->{$this->modelClass}->DynamicPhoto->delete($this->{$this->modelClass}->DynamicPhoto->id,true)){
                            if(file_exists($file_to_delete)){
                                unlink($file_to_delete);
                            }
                    }
                }

                $this->DynamicDetail->id = $d['id'];
                $this->DynamicDetail->delete($this->DynamicDetail->id,true);
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
	}

    public function view(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();
        if(isset($this->request->query['dynamic_detail_id'])){
            $this->{$this->modelClass}->contain();
            $q_r = $this->{$this->modelClass}->findById($this->request->query['dynamic_detail_id']);
            if($q_r){
            
                $realm = '';
            
                if($q_r['DynamicDetail']['realm_id'] != null){
                    $r  = ClassRegistry::init('Realm');
                    $r->contain();
                    $q_realm    = $r->findById($q_r['DynamicDetail']['realm_id']);
                    if($q_r){
                        $realm = $q_realm['Realm']['name'];
                    }
                }
                $profile = '';
                
                if($q_r['DynamicDetail']['profile_id'] != null){
                    $p  = ClassRegistry::init('Profile');
                    $p->contain();
                    $q_profile    = $p->findById($q_r['DynamicDetail']['profile_id']);
                    if($q_profile){
                        $profile = $q_profile['Profile']['name'];
                    }
                }
            
                $owner_tree                         = $this->_find_parents($q_r['DynamicDetail']['user_id']);
                $items['id']                        = $q_r['DynamicDetail']['id'];
                $items['name']                      = $q_r['DynamicDetail']['name'];
                $items['available_to_siblings']     = $q_r['DynamicDetail']['available_to_siblings'];
                $items['phone']                     = $q_r['DynamicDetail']['phone'];
                $items['fax']                       = $q_r['DynamicDetail']['fax'];
                $items['cell']                      = $q_r['DynamicDetail']['cell'];
                $items['email']                     = $q_r['DynamicDetail']['email'];
                $items['url']                       = $q_r['DynamicDetail']['url'];
                $items['street_no']                 = $q_r['DynamicDetail']['street_no'];
                $items['street']                    = $q_r['DynamicDetail']['street'];
                $items['town_suburb']               = $q_r['DynamicDetail']['town_suburb'];
                $items['city']                      = $q_r['DynamicDetail']['city'];
                $items['country']                   = $q_r['DynamicDetail']['country'];
                $items['lat']                       = $q_r['DynamicDetail']['lat'];
                $items['lon']                       = $q_r['DynamicDetail']['lon'];
                $items['t_c_check']                 = $q_r['DynamicDetail']['t_c_check'];
                $items['t_c_url']                   = $q_r['DynamicDetail']['t_c_url'];
                $items['redirect_check']            = $q_r['DynamicDetail']['redirect_check'];
                $items['redirect_url']              = $q_r['DynamicDetail']['redirect_url'];
                $items['slideshow_check']           = $q_r['DynamicDetail']['slideshow_check'];
                $items['seconds_per_slide']         = $q_r['DynamicDetail']['seconds_per_slide'];
                $items['connect_check']             = $q_r['DynamicDetail']['connect_check'];
                $items['connect_username']          = $q_r['DynamicDetail']['connect_username'];
                $items['connect_suffix']            = $q_r['DynamicDetail']['connect_suffix'];
                $items['connect_delay']             = $q_r['DynamicDetail']['connect_delay'];
                $items['connect_only']              = $q_r['DynamicDetail']['connect_only'];
				$items['user_login_check']          = $q_r['DynamicDetail']['user_login_check'];
				$items['voucher_login_check']       = $q_r['DynamicDetail']['voucher_login_check'];
				$items['auto_suffix_check']         = $q_r['DynamicDetail']['auto_suffix_check'];
				$items['auto_suffix']               = $q_r['DynamicDetail']['auto_suffix'];
				$items['usage_show_check']          = $q_r['DynamicDetail']['usage_show_check'];
				$items['usage_refresh_interval']    = $q_r['DynamicDetail']['usage_refresh_interval'];
				$items['theme']    					= $q_r['DynamicDetail']['theme'];
				$items['register_users']    		= $q_r['DynamicDetail']['register_users'];
				$items['lost_password']    			= $q_r['DynamicDetail']['lost_password'];
                $items['owner']                     = $owner_tree;
                $items['icon_file_name']            = $q_r['DynamicDetail']['icon_file_name'];
                
                $items['coova_desktop_url']         = $q_r['DynamicDetail']['coova_desktop_url'];
                $items['coova_mobile_url']          = $q_r['DynamicDetail']['coova_mobile_url'];
                $items['mikrotik_desktop_url']      = $q_r['DynamicDetail']['mikrotik_desktop_url'];
                $items['mikrotik_mobile_url']       = $q_r['DynamicDetail']['mikrotik_mobile_url'];
                
                //User registration add on
                $items['realm_id']                  = $q_r['DynamicDetail']['realm_id'];
                $items['profile_id']                = $q_r['DynamicDetail']['profile_id'];
                ///This is taken care of on the realm itself
                /// $items['reg_auto_suffix_check']     = $q_r['DynamicDetail']['reg_auto_suffix_check'];
                /// $items['reg_auto_suffix']           = $q_r['DynamicDetail']['reg_auto_suffix'];
                $items['reg_mac_check']             = $q_r['DynamicDetail']['reg_mac_check'];
                $items['reg_auto_add']              = $q_r['DynamicDetail']['reg_auto_add'];
                $items['reg_email']                 = $q_r['DynamicDetail']['reg_email'];
                $items['realm']                     = $realm;
                $items['profile']                   = $profile;
                $items['default_language']          = $q_r['DynamicDetail']['default_language'];
                
            }
        }
        
        $this->set(array(
            'data'     => $items,
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
    }

    public function upload_logo($id = null){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        //This is a deviation from the standard JSON serialize view since extjs requires a html type reply when files
        //are posted to the server.
        $this->layout = 'ext_file_upload';

        $path_parts     = pathinfo($_FILES['photo']['name']);
        $unique         = time();
        $dest           = IMAGES."dynamic_details/".$unique.'.'.$path_parts['extension'];
        $dest_www       = "/cake2/rd_cake/webroot/img/dynamic_details/".$unique.'.'.$path_parts['extension'];

        //Now add....
        $data['photo_file_name']  = $unique.'.'.$path_parts['extension'];
       
        $this->{$this->modelClass}->id = $this->request->data['id'];
       // $this->{$this->modelClass}->saveField('photo_file_name', $unique.'.'.$path_parts['extension']);
        if($this->{$this->modelClass}->saveField('icon_file_name', $unique.'.'.$path_parts['extension'])){
            move_uploaded_file ($_FILES['photo']['tmp_name'] , $dest);
            $json_return['id']                  = $this->{$this->modelClass}->id;
            $json_return['success']             = true;
            $json_return['icon_file_name']      = $unique.'.'.$path_parts['extension'];
        }else{
            $json_return['errors']      = $this->{$this->modelClass}->validationErrors;
            $json_return['message']     = array("message"   => __('Problem uploading photo'));
            $json_return['success']     = false;
        }
        $this->set('json_return',$json_return);
    }

    public function index_photo(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();
        if(isset($this->request->query['dynamic_detail_id'])){
            $dd_id = $this->request->query['dynamic_detail_id'];
            $this->{$this->modelClass}->DynamicPhoto->contain();
            $q_r = $this->{$this->modelClass}->DynamicPhoto->find('all', array('conditions' => array('DynamicPhoto.dynamic_detail_id' =>$dd_id)));
            foreach($q_r as $i){
                $id     = $i['DynamicPhoto']['id'];
                $dd_id  = $i['DynamicPhoto']['dynamic_detail_id'];
                $t      = $i['DynamicPhoto']['title'];
                $d      = $i['DynamicPhoto']['description'];
                $u      = $i['DynamicPhoto']['url'];
                $f      = $i['DynamicPhoto']['file_name'];
                $location = Configure::read('paths.dynamic_photos').$f;
                array_push($items,
                    array(
                        'id'                => $id, 
                        'dynamic_detail_id' => $dd_id, 
                        'title'             => $t, 
                        'description'       => $d,
                        'url'               => $u, 
                        'file_name'         => $f,
                        'img'               => "/cake2/rd_cake/webroot/files/image.php?width=200&height=200&image=".$location
                    )
                );
            }
        }
        
        $this->set(array(
            'items'     => $items,
            'success'   => true,
            '_serialize'=> array('success', 'items')
        ));
    }

    public function upload_photo($id = null){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        //This is a deviation from the standard JSON serialize view since extjs requires a html type reply when files
        //are posted to the server.
        $this->layout = 'ext_file_upload';

        $path_parts     = pathinfo($_FILES['photo']['name']);
        $unique         = time();
        $dest           = IMAGES."dynamic_photos/".$unique.'.'.$path_parts['extension'];
        $dest_www       = "/cake2/rd_cake/webroot/img/dynamic_photos/".$unique.'.'.$path_parts['extension'];

        //Now add....
        $data                       = array();
        $data['id']                 = null;
        $data['file_name']          = $unique.'.'.$path_parts['extension'];
        $data['dynamic_detail_id']  = $this->request->data['dynamic_detail_id'];
        $data['title']              = $this->request->data['title'];
        $data['description']        = $this->request->data['description'];
        $data['url']                = $this->request->data['url'];

        $this->{$this->modelClass}->DynamicPhoto->create();

        if($this->{$this->modelClass}->DynamicPhoto->save($data)){
            move_uploaded_file ($_FILES['photo']['tmp_name'] , $dest);
            $json_return['id']                  = $this->{$this->modelClass}->DynamicPhoto->id;
            $json_return['success']             = true;

        }else{
            $json_return['errors']      = $this->{$this->modelClass}->validationErrors;
            $json_return['message']     = array("message"   => __('Problem uploading photo'));
            $json_return['success']     = false;
        }
        $this->set('json_return',$json_return);
    }

    public function delete_photo($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        if(!$this->_ap_right_check()){
            return;
        }

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id'];
            //Get the filename to delete
            $q_r = $this->{$this->modelClass}->DynamicPhoto->findById($this->data['id']);
            if($q_r){
                $file_to_delete = IMAGES."/dynamic_photos/".$q_r['DynamicPhoto']['file_name'];
                $this->{$this->modelClass}->DynamicPhoto->id = $this->data['id'];
                if($this->{$this->modelClass}->DynamicPhoto->delete($this->{$this->modelClass}->DynamicPhoto->id,true)){
                    unlink($file_to_delete);
                }
            }     
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                //Get the filename to delete
                $q_r = $this->{$this->modelClass}->DynamicPhoto->findById($d['id']);
                if($q_r){
                    $file_to_delete = IMAGES."/dynamic_photos/".$q_r['DynamicPhoto']['file_name'];
                    $this->{$this->modelClass}->DynamicPhoto->id = $d['id'];
                    if($this->{$this->modelClass}->DynamicPhoto->delete($this->{$this->modelClass}->DynamicPhoto->id,true)){
                        unlink($file_to_delete);
                    }
                }
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
	}

    public function edit_photo(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        //This is a deviation from the standard JSON serialize view since extjs requires a html type reply when files
        //are posted to the server.
        $this->layout = 'ext_file_upload';

        //Always do this
        $this->{$this->modelClass}->DynamicPhoto->id = $this->request->data['id'];
        $this->{$this->modelClass}->DynamicPhoto->saveField('title',        $this->request->data['title']);
        $this->{$this->modelClass}->DynamicPhoto->saveField('description',  $this->request->data['description']);
        $this->{$this->modelClass}->DynamicPhoto->saveField('url',          $this->request->data['url']);

        if($_FILES['photo']['size'] > 0){
            $q_r        = $this->{$this->modelClass}->DynamicPhoto->findById($this->request->data['id']);
            if($q_r){
                
                $file_name      = $q_r['DynamicPhoto']['file_name'];
                $file_to_delete = IMAGES."/dynamic_photos/".$file_name;
                unlink($file_to_delete);

                $path_parts     = pathinfo($_FILES['photo']['name']);
                $unique         = time();
                $dest           = IMAGES."dynamic_photos/".$unique.'.'.$path_parts['extension'];
                move_uploaded_file ($_FILES['photo']['tmp_name'] , $dest);
                $this->{$this->modelClass}->DynamicPhoto->saveField('file_name', $unique.'.'.$path_parts['extension']);
            }  
        }

        $json_return['success'] = true;
        $this->set('json_return',$json_return);
    }

    public function index_page(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();
        if(isset($this->request->query['dynamic_detail_id'])){
            $dd_id = $this->request->query['dynamic_detail_id'];
            $this->{$this->modelClass}->DynamicPage->contain();
            $q_r = $this->{$this->modelClass}->DynamicPage->find('all', array('conditions' => array('DynamicPage.dynamic_detail_id' =>$dd_id)));
            foreach($q_r as $i){
                $id     = $i['DynamicPage']['id'];
                $dd_id  = $i['DynamicPage']['dynamic_detail_id'];
                $n      = $i['DynamicPage']['name'];
                $c      = $i['DynamicPage']['content'];
                array_push($items,
                    array(
                        'id'                => $id, 
                        'dynamic_detail_id' => $dd_id, 
                        'name'              => $n, 
                        'content'           => $c
                    )
                );
            }
        }
        
        $this->set(array(
            'items'     => $items,
            'success'   => true,
            '_serialize'=> array('success', 'items')
        ));
    }

    public function add_page() {

        if(!$this->_ap_right_check()){
            return;
        }

        $user       = $this->Aa->user_for_token($this);
        $user_id    = $user['id'];

        if ($this->{$this->modelClass}->DynamicPage->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        } else {
            $message = 'Error';
            $this->set(array(
                'errors'    => $this->{$this->modelClass}->DynamicPage->validationErrors,
                'success'   => false,
                'message'   => array('message' => 'Could not create item'),
                '_serialize' => array('errors','success','message')
            ));
        }
	}

    public function edit_page() {

        if(!$this->_ap_right_check()){
            return;
        }

        $user       = $this->Aa->user_for_token($this);
        $user_id    = $user['id'];

        if ($this->{$this->modelClass}->DynamicPage->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        } else {
            $message = 'Error';
            $this->set(array(
                'errors'    => $this->{$this->modelClass}->DynamicPage->validationErrors,
                'success'   => false,
                'message'   => array('message' => 'Could not create item'),
                '_serialize' => array('errors','success','message')
            ));
        }
	}

    public function delete_page($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        if(!$this->_ap_right_check()){
            return;
        }

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id'];
            //Get the filename to delete
            $this->{$this->modelClass}->DynamicPage->id = $this->data['id'];
            $this->{$this->modelClass}->DynamicPage->delete($this->{$this->modelClass}->DynamicPage->id,true);
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                //Get the filename to delete
                $this->{$this->modelClass}->DynamicPage->id = $d['id'];
                $this->{$this->modelClass}->DynamicPage->delete($this->{$this->modelClass}->DynamicPage->id,true);
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
	}

    public function index_pair(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();
        if(isset($this->request->query['dynamic_detail_id'])){
            $dd_id = $this->request->query['dynamic_detail_id'];
            $this->{$this->modelClass}->DynamicPair->contain();
            $q_r = $this->{$this->modelClass}->DynamicPair->find('all', array('conditions' => array('DynamicPair.dynamic_detail_id' =>$dd_id)));
            foreach($q_r as $i){
                $id     = $i['DynamicPair']['id'];
                $dd_id  = $i['DynamicPair']['dynamic_detail_id'];
                $n      = $i['DynamicPair']['name'];
                $v      = $i['DynamicPair']['value'];
                $p      = $i['DynamicPair']['priority'];
                array_push($items,
                    array(
                        'id'                => $id, 
                        'dynamic_detail_id' => $dd_id, 
                        'name'              => $n, 
                        'value'             => $v,
                        'priority'          => $p
                    )
                );
            }
        }
        
        $this->set(array(
            'items'     => $items,
            'success'   => true,
            '_serialize'=> array('success', 'items')
        ));
    }

    public function add_pair() {

        if(!$this->_ap_right_check()){
            return;
        }

        $user       = $this->Aa->user_for_token($this);
        $user_id    = $user['id'];

        if ($this->{$this->modelClass}->DynamicPair->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        } else {
            $message = 'Error';
            $this->set(array(
                'errors'    => $this->{$this->modelClass}->DynamicPair->validationErrors,
                'success'   => false,
                'message'   => array('message' => 'Could not create item'),
                '_serialize' => array('errors','success','message')
            ));
        }
	}

    public function edit_pair() {

        if(!$this->_ap_right_check()){
            return;
        }

        $user       = $this->Aa->user_for_token($this);
        $user_id    = $user['id'];

        if ($this->{$this->modelClass}->DynamicPair->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        } else {
            $message = 'Error';
            $this->set(array(
                'errors'    => $this->{$this->modelClass}->DynamicPair->validationErrors,
                'success'   => false,
                'message'   => array('message' => 'Could not create item'),
                '_serialize' => array('errors','success','message')
            ));
        }
	}

    public function delete_pair($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        if(!$this->_ap_right_check()){
            return;
        }

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id'];
            //Get the filename to delete
            $this->{$this->modelClass}->DynamicPair->id = $this->data['id'];
            $this->{$this->modelClass}->DynamicPair->delete($this->{$this->modelClass}->DynamicPair->id,true);
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                //Get the filename to delete
                $this->{$this->modelClass}->DynamicPair->id = $d['id'];
                $this->{$this->modelClass}->DynamicPair->delete($this->{$this->modelClass}->DynamicPair->id,true);
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
	}


	public function view_social_login(){

		 //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();
        if(isset($this->request->query['dynamic_detail_id'])){
            $this->{$this->modelClass}->contain(array('DynamicDetailSocialLogin' => array('Realm.name','Profile.name'),'PermanentUser.username'));
            $q_r = $this->{$this->modelClass}->findById($this->request->query['dynamic_detail_id']);
			//print_r($q_r);

            if($q_r){
                
				$items['social_enable']    					= $q_r['DynamicDetail']['social_enable'];
				$items['id']    							= $q_r['DynamicDetail']['id'];
				$items['social_temp_permanent_user_id'] 	= intval($q_r['DynamicDetail']['social_temp_permanent_user_id']);
				$items['social_temp_permanent_user_name'] 	= $q_r['PermanentUser']['username'];

				//Some default values

				foreach($q_r['DynamicDetailSocialLogin'] as $i){

					if($i['name'] == 'Facebook'){
						$items['fb_enable'] 		= $i['enable'];
						$items['fb_record_info'] 	= $i['record_info'];
						$items['fb_id'] 			= $i['key'];
						$items['fb_secret'] 		= $i['secret'];
						$items['fb_active'] 		= $i['enable'];
						$items['fb_profile'] 	    = intval($i['profile_id']);
						$items['fb_profile_name'] 	= $i['Profile']['name'];
						$items['fb_realm'] 			= intval($i['realm_id']);
						$items['fb_realm_name'] 	= $i['Realm']['name'];
						$items['fb_voucher_or_user']= $i['type'];
					}

					if($i['name'] == 'Google'){
						$items['gp_enable'] 		= $i['enable'];
						$items['gp_record_info'] 	= $i['record_info'];
						$items['gp_id'] 			= $i['key'];
						$items['gp_secret'] 		= $i['secret'];
						$items['gp_active'] 		= $i['enable'];
						$items['gp_profile'] 	    = intval($i['profile_id']);
						$items['gp_profile_name'] 	= $i['Profile']['name'];
						$items['gp_realm'] 			= intval($i['realm_id']);
						$items['gp_realm_name'] 	= $i['Realm']['name'];
						$items['gp_voucher_or_user']= $i['type'];
					}

					if($i['name'] == 'Twitter'){
						$items['tw_enable'] 		= $i['enable'];
						$items['tw_record_info'] 	= $i['record_info'];
						$items['tw_id'] 			= $i['key'];
						$items['tw_secret'] 		= $i['secret'];
						$items['tw_active'] 		= $i['enable'];
						$items['tw_profile'] 	    = intval($i['profile_id']);
						$items['tw_profile_name'] 	= $i['Profile']['name'];
						$items['tw_realm'] 			= intval($i['realm_id']);
						$items['tw_realm_name'] 	= $i['Realm']['name'];
						$items['tw_voucher_or_user']= $i['type'];
					}
				}
               
            }
        }
        
        $this->set(array(
            'data'     => $items,
            'success'   => true,
            '_serialize'=> array('success', 'data')
        ));
	}

	public function edit_social_login(){
	
	    //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }


		//We need the social_temp_permanent_user_id else we fail it if it is enabbled...
		//social_enable check 
        if(isset($this->request->data['social_enable'])){
            $this->request->data['social_enable'] = 1;
        }else{
            $this->request->data['social_enable'] = 0;
        }

		//Check which is enabled:
		if(isset($this->request->data['fb_enable'])){
            $this->request->data['fb_enable'] = 1;
        }else{
            $this->request->data['fb_enable'] = 0;
        }

		//Check which is enabled:
		if(isset($this->request->data['gp_enable'])){
            $this->request->data['gp_enable'] = 1;
        }else{
            $this->request->data['gp_enable'] = 0;
        }

		//Check which is enabled:
		if(isset($this->request->data['tw_enable'])){
            $this->request->data['tw_enable'] = 1;
        }else{
            $this->request->data['tw_enable'] = 0;
        }

		//Set the *_record_info
		if(isset($this->request->data['fb_record_info'])){
            $this->request->data['fb_record_info'] = 1;
        }else{
            $this->request->data['fb_record_info'] = 0;
        }
		if(isset($this->request->data['gp_record_info'])){
            $this->request->data['gp_record_info'] = 1;
        }else{
            $this->request->data['gp_record_info'] = 0;
        }
		if(isset($this->request->data['tw_record_info'])){
            $this->request->data['tw_record_info'] = 1;
        }else{
            $this->request->data['tw_record_info'] = 0;
        }



		//We have to have a temp user else we fail it
		if(($this->request->data['social_enable'] == 1)&&($this->request->data['social_temp_permanent_user_id'] == '')){
			 $this->set(array(
                'errors'    => array('social_temp_permanent_user_id' => "Temp user cannot be empty"),
                'success'   => false,
                'message'   => array('message' => 'Could not save data'),
                '_serialize' => array('errors','success','message')
            ));
			return;
		}

		//=== FACEBOOK (fb)====
		//Do some more check and reject them if they are not valid
		if(
			($this->request->data['social_enable'] == 1)&&
			($this->request->data['fb_enable'] == 1)
		){

			$fb_check_for  = array('fb_voucher_or_user','fb_secret','fb_id','fb_realm','fb_profile');
			foreach($fb_check_for as $i){
				if($this->request->data["$i"] == ''){
					$this->set(array(
				        'errors'    => array("$i" => $i." is required"),
				        'success'   => false,
				        'message'   => array('message' => 'Could not save data'),
				        '_serialize' => array('errors','success','message')
				    ));
					return;
				}
			}
		}

		//=== GOOGLE PLUS (gp)====
		//Do some more check and reject them if they are not valid
		if(
			($this->request->data['social_enable'] == 1)&&
			($this->request->data['gp_enable'] == 1)
		){	
			$gp_check_for  = array('gp_voucher_or_user','gp_secret','gp_id','gp_realm','gp_profile');
			foreach($gp_check_for as $i){
				if($this->request->data["$i"] == ''){
					$this->set(array(
				        'errors'    => array("$i" => $i." is required"),
				        'success'   => false,
				        'message'   => array('message' => 'Could not save data'),
				        '_serialize' => array('errors','success','message')
				    ));
					return;
				}
			}
		}


		//=== TWITTER (tw)====
		//Do some more check and reject them if they are not valid
		if(
			($this->request->data['social_enable'] == 1)&&
			($this->request->data['tw_enable'] == 1)
		){
			$tw_check_for  = array('tw_voucher_or_user','tw_secret','tw_id','tw_realm','tw_profile');
			foreach($tw_check_for as $i){
				if($this->request->data["$i"] == ''){
					$this->set(array(
				        'errors'    => array("$i" => $i." is required"),
				        'success'   => false,
				        'message'   => array('message' => 'Could not save data'),
				        '_serialize' => array('errors','success','message')
				    ));
					return;
				}
			}
		}

		//If it got here without a return we can surely then add the social logins the user defined
		//First we delete the existing ones:
		$id = $this->request->data['id'];
		$this->{$this->modelClass}->{'DynamicDetailSocialLogin'}->deleteAll(array('DynamicDetailSocialLogin.dynamic_detail_id' => $id), true);

		if ($this->{$this->modelClass}->save($this->request->data)) {

			if($this->request->data['social_enable'] == 0){
			
				//if not enabled we don't care ....
				$this->set(array(
		            'success' => true,
		            '_serialize' => array('success')
		        ));
				return;
			}

			if($this->request->data['fb_enable'] == 1){
				//Facebook
				$fb_data = array();
				$fb_data['name'] 				= 'Facebook';
				$fb_data['dynamic_detail_id'] 	= $id;
				$fb_data['profile_id'] 			= $this->request->data["fb_profile"];
				$fb_data['type'] 				= $this->request->data["fb_voucher_or_user"];
				$fb_data['key'] 				= $this->request->data["fb_id"];
				$fb_data['secret'] 				= $this->request->data["fb_secret"];
				$fb_data['realm_id'] 			= $this->request->data["fb_realm"];
				$fb_data['secret'] 				= $this->request->data["fb_secret"];
				$fb_data['record_info']			= $this->request->data["fb_record_info"];
				$fb_data['enable']				= $this->request->data["fb_enable"];

				$this->{$this->modelClass}->{'DynamicDetailSocialLogin'}->create(); 
				$this->{$this->modelClass}->{'DynamicDetailSocialLogin'}->save($fb_data);
				$this->{$this->modelClass}->{'DynamicDetailSocialLogin'}->id = null;
			}

			if($this->request->data['gp_enable'] == 1){

				//Google Plus
				$gp_data = array();
				$gp_data['name'] 				= 'Google';
				$gp_data['dynamic_detail_id'] 	= $id;
				$gp_data['profile_id'] 			= $this->request->data["gp_profile"];
				$gp_data['type'] 				= $this->request->data["gp_voucher_or_user"];
				$gp_data['key'] 				= $this->request->data["gp_id"];
				$gp_data['secret'] 				= $this->request->data["gp_secret"];
				$gp_data['realm_id'] 			= $this->request->data["gp_realm"];
				$gp_data['secret'] 				= $this->request->data["gp_secret"];
				$gp_data['record_info']			= $this->request->data["gp_record_info"];
				$gp_data['enable']				= $this->request->data["gp_enable"];

				$this->{$this->modelClass}->{'DynamicDetailSocialLogin'}->create(); 
				$this->{$this->modelClass}->{'DynamicDetailSocialLogin'}->save($gp_data);
				$this->{$this->modelClass}->{'DynamicDetailSocialLogin'}->id = null;
			}

			if($this->request->data['tw_enable'] == 1){

				//Twitter
				$tw_data = array();
				$tw_data['name'] 				= 'Twitter';
				$tw_data['dynamic_detail_id'] 	= $id;
				$tw_data['profile_id'] 			= $this->request->data["tw_profile"];
				$tw_data['type'] 				= $this->request->data["tw_voucher_or_user"];
				$tw_data['key'] 				= $this->request->data["tw_id"];
				$tw_data['secret'] 				= $this->request->data["tw_secret"];
				$tw_data['realm_id'] 			= $this->request->data["tw_realm"];
				$tw_data['secret'] 				= $this->request->data["tw_secret"];
				$tw_data['record_info']			= $this->request->data["tw_record_info"];
				$tw_data['enable']				= $this->request->data["tw_enable"];

				$this->{$this->modelClass}->{'DynamicDetailSocialLogin'}->create(); 
				$this->{$this->modelClass}->{'DynamicDetailSocialLogin'}->save($tw_data);
				$this->{$this->modelClass}->{'DynamicDetailSocialLogin'}->id = null;
			}

            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));

        } else {

            $message = 'Error';
            $this->set(array(
                'errors'    => $this->{$this->modelClass}->validationErrors,
                'success'   => false,
                'message'   => array('message' => 'Could not save data'),
                '_serialize' => array('errors','success','message')
            ));
        }
	}


    public function note_index(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        $items = array();
        if(isset($this->request->query['for_id'])){
            $dynamic_detail_id = $this->request->query['for_id'];
            $q_r    = $this->DynamicDetail->DynamicDetailNote->find('all', 
                array(
                    'contain'       => array('Note'),
                    'conditions'    => array('DynamicDetailNote.dynamic_detail_id' => $dynamic_detail_id)
                )
            );
            foreach($q_r as $i){
                if(!$this->_test_for_private_parent($i['Note'],$user)){
                    $owner_id   = $i['Note']['user_id'];
                    $owner      = $this->_find_parents($owner_id);
                    $afs        = $this->_get_action_flags($owner_id,$user);
                    array_push($items,
                        array(
                            'id'        => $i['Note']['id'], 
                            'note'      => $i['Note']['note'], 
                            'available_to_siblings' => $i['Note']['available_to_siblings'],
                            'owner'     => $owner,
                            'delete'    => $afs['delete']
                        )
                    );
                }
            }
        } 
        $this->set(array(
            'items'     => $items,
            'success'   => true,
            '_serialize'=> array('success', 'items')
        ));
    }

    public function note_add(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];

        //Get the creator's id
        if($this->request->data['user_id'] == '0'){ //This is the holder of the token - override '0'
            $this->request->data['user_id'] = $user_id;
        }

        //Make available to siblings check
        if(isset($this->request->data['available_to_siblings'])){
            $this->request->data['available_to_siblings'] = 1;
        }else{
            $this->request->data['available_to_siblings'] = 0;
        }

        $success    = false;
        $message    = array('message' => __('Could not create note'));
        $this->DynamicDetail->DynamicDetailNote->Note->create(); 
        //print_r($this->request->data);
        if ($this->DynamicDetail->DynamicDetailNote->Note->save($this->request->data)) {
            $d                      = array();
            $d['DynamicDetailNote']['dynamic_detail_id']   = $this->request->data['for_id'];
            $d['DynamicDetailNote']['note_id'] = $this->DynamicDetail->DynamicDetailNote->Note->id;
            $this->DynamicDetail->DynamicDetailNote->create();
            if ($this->DynamicDetail->DynamicDetailNote->save($d)) {
                $success = true;
            }
        }

        if($success){
            $this->set(array(
                'success' => $success,
                '_serialize' => array('success')
            ));
        }else{
             $this->set(array(
                'success' => $success,
                'message' => $message,
                '_serialize' => array('success','message')
            ));
        }
    }

    public function note_del(){

        if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $user_id    = $user['id'];
        $fail_flag  = false;

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id'];

            //NOTE: we first check of the user_id is the logged in user OR a sibling of them:   
            $item       = $this->DynamicDetail->DynamicDetailNote->Note->findById($this->data['id']);
            $owner_id   = $item['Note']['user_id'];
            if($owner_id != $user_id){
                if($this->_is_sibling_of($user_id,$owner_id)== true){
                    $this->DynamicDetail->DynamicDetailNote->Note->id = $this->data['id'];
                    $this->DynamicDetail->DynamicDetailNote->Note->delete($this->data['id'],true);
                }else{
                    $fail_flag = true;
                }
            }else{
                $this->DynamicDetail->DynamicDetailNote->Note->id = $this->data['id'];
                $this->DynamicDetail->DynamicDetailNote->Note->delete($this->data['id'],true);
            }
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){

                $item       = $this->DynamicDetail->DynamicDetailNote->Note->findById($d['id']);
                $owner_id   = $item['Note']['user_id'];
                if($owner_id != $user_id){
                    if($this->_is_sibling_of($user_id,$owner_id) == true){
                        $this->DynamicDetail->DynamicDetailNote->Note->id = $d['id'];
                        $this->DynamicDetail->DynamicDetailNote->Note->delete($d['id'],true);
                    }else{
                        $fail_flag = true;
                    }
                }else{
                    $this->DynamicDetail->DynamicDetailNote->Note->id = $d['id'];
                    $this->DynamicDetail->DynamicDetailNote->Note->delete($d['id'],true);
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

	public function available_themes(){

        
        $items = array();
        Configure::load('DynamicLogin'); 
        $data       = Configure::read('DynamicLogin.theme');
        foreach(array_keys($data) as $i){
            array_push($items, array('name' => $i,'id' => $i));   
        }
        
        if(
            (isset($this->request->query['exclude_custom']))&&
            ($this->request->query['exclude_custom'] == 'true')
        ){
           array_shift($items); //Remove the first item which will be "Custom" in the config file
        }
            
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
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
                    array('xtype' => 'button', 'iconCls' => 'b-reload',  'glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload',   'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'iconCls' => 'b-add',     'glyph' => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'iconCls' => 'b-delete',  'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'iconCls' => 'b-edit',    'glyph' => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit'))
                )),
                array('xtype' => 'buttongroup','title' => __('Document'), 'items' => array(
                    array('xtype' => 'button', 'iconCls' => 'b-note',     'glyph' => Configure::read('icnNote'),'scale' => 'large', 'itemId' => 'note',    'tooltip'=> __('Add notes')),
                    array('xtype' => 'button', 'iconCls' => 'b-csv',     'glyph' => Configure::read('icnCsv'),'scale' => 'large', 'itemId' => 'csv',      'tooltip'=> __('Export CSV')),
                )),
                array('xtype' => 'buttongroup','title' => __('Preview'), 'items' => array(
                    array('xtype' => 'button', 'iconCls' => 'b-mobile',   'glyph' => Configure::read('icnMobile'),  'scale' => 'large', 'itemId' => 'mobile',    'tooltip'=> __('Mobile')),
                    array('xtype' => 'button', 'iconCls' => 'b-desktop',  'glyph' => Configure::read('icnDesktop'),  'scale' => 'large', 'itemId' => 'desktop',   'tooltip'=> __('Desktop')),
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
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base."add")){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-add',
                    'glyph'     => Configure::read('icnAdd'),     
                    'scale'     => 'large', 
                    'itemId'    => 'add',      
                    'tooltip'   => __('Add')));
            }
            //Delete
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'delete')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-delete',
                    'glyph'     => Configure::read('icnDelete'),  
                    'scale'     => 'large', 
                    'itemId'    => 'delete',
                    'disabled'  => true,   
                    'tooltip'   => __('Delete')));
            }

            //Edit
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'edit')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-edit',
                    'glyph'     => Configure::read('icnEdit'),    
                    'scale'     => 'large', 
                    'itemId'    => 'edit',
                    'disabled'  => true,     
                    'tooltip'   => __('Edit')));
            }

            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'note_index')){ 
                array_push($document_group,array(
                        'xtype'     => 'button', 
                        'iconCls'   => 'b-note', 
                        'glyph'     => Configure::read('icnNote'),    
                        'scale'     => 'large', 
                        'itemId'    => 'note',      
                        'tooltip'   => __('Add Notes')));
            }

            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'export_csv')){ 
                array_push($document_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-csv',
                    'glyph'     => Configure::read('icnCsv'),     
                    'scale'     => 'large', 
                    'itemId'    => 'csv',      
                    'tooltip'   => __('Export CSV')));
            }

            $menu = array(
                        array('xtype' => 'buttongroup','title' => __('Action'),     'items' => $action_group),
                        array('xtype' => 'buttongroup','title' => __('Document'),   'items' => $document_group),
                        array('xtype' => 'buttongroup','title' => __('Preview'),    'items' => array(
                            array(
                                'xtype'     => 'button', 
                                'iconCls'   => 'b-mobile',   
                                'glyph'     => Configure::read('icnMobile'),  
                                'scale'     => 'large', 
                                'itemId'    => 'mobile',    
                                'tooltip'   => __('Mobile')
                            ),
                            array(
                                'xtype'     => 'button', 
                                'iconCls'   => 'b-desktop',  
                                'glyph'     => Configure::read('icnDesktop'),  
                                'scale'     => 'large', 
                                'itemId'    => 'desktop',   
                                'tooltip'   => __('Desktop')
                            )))
                        );
        }
        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

    public function menu_for_photos(){

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
                    array('xtype' => 'button', 'iconCls' => 'b-reload',  'glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload',   'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'iconCls' => 'b-add',     'glyph' => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'iconCls' => 'b-delete',  'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'iconCls' => 'b-edit',    'glyph' => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit'))
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
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base."add")){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-add', 
                    'glyph'     => Configure::read('icnAdd'),    
                    'scale'     => 'large', 
                    'itemId'    => 'add',      
                    'tooltip'   => __('Add')));
            }
            //Delete
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'delete')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-delete', 
                    'glyph'     => Configure::read('icnDelete'), 
                    'scale'     => 'large', 
                    'itemId'    => 'delete',
                    'disabled'  => false,   
                    'tooltip'   => __('Delete')));
            }

            //Edit
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'edit')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-edit',
                    'glyph'     => Configure::read('icnEdit'),    
                    'scale'     => 'large', 
                    'itemId'    => 'edit',
                    'disabled'  => false,     
                    'tooltip'   => __('Edit')));
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

    public function menu_for_dynamic_pages(){

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
                    array('xtype' => 'button', 'iconCls' => 'b-reload',  'glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload',   'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'iconCls' => 'b-add',     'glyph' => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'iconCls' => 'b-delete',  'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'iconCls' => 'b-edit',    'glyph' => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit'))
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
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base."add")){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-add', 
                    'glyph'     => Configure::read('icnAdd'),    
                    'scale'     => 'large', 
                    'itemId'    => 'add',      
                    'tooltip'   => __('Add')));
            }
            //Delete
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'delete')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-delete',
                    'glyph'     => Configure::read('icnDelete'),  
                    'scale'     => 'large', 
                    'itemId'    => 'delete',
                    'disabled'  => false,   
                    'tooltip'   => __('Delete')));
            }

            //Edit
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'edit')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-edit',
                    'glyph'     => Configure::read('icnEdit'),    
                    'scale'     => 'large', 
                    'itemId'    => 'edit',
                    'disabled'  => false,     
                    'tooltip'   => __('Edit')));
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

    public function menu_for_dynamic_pairs(){

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
                    array('xtype' => 'button', 'iconCls' => 'b-reload',  'glyph' => Configure::read('icnReload'),'scale' => 'large', 'itemId' => 'reload',   'tooltip'=> __('Reload')),
                    array('xtype' => 'button', 'iconCls' => 'b-add',     'glyph' => Configure::read('icnAdd'),'scale' => 'large', 'itemId' => 'add',      'tooltip'=> __('Add')),
                    array('xtype' => 'button', 'iconCls' => 'b-delete',  'glyph' => Configure::read('icnDelete'),'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'iconCls' => 'b-edit',    'glyph' => Configure::read('icnEdit'),'scale' => 'large', 'itemId' => 'edit',     'tooltip'=> __('Edit'))
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
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base."add")){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-add',
                    'glyph'     => Configure::read('icnAdd'),     
                    'scale'     => 'large', 
                    'itemId'    => 'add',      
                    'tooltip'   => __('Add')));
            }
            //Delete
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'delete')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-delete',
                    'glyph'     => Configure::read('icnDelete'),  
                    'scale'     => 'large', 
                    'itemId'    => 'delete',
                    'disabled'  => false,   
                    'tooltip'   => __('Delete')));
            }

            //Edit
            if($this->Acl->check(array('model' => 'User', 'foreign_key' => $id), $this->base.'edit')){
                array_push($action_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-edit',
                    'glyph'     => Configure::read('icnEdit'),    
                    'scale'     => 'large', 
                    'itemId'    => 'edit',
                    'disabled'  => false,     
                    'tooltip'   => __('Edit')));
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

    function _build_common_query($user){

        //Empty to start with
        $c                  = array();
        $c['joins']         = array(); 
        $c['conditions']    = array();

        //What should we include....
        $c['contain']   = array(
                            'DynamicDetailNote'    => array('Note.note','Note.id','Note.available_to_siblings','Note.user_id'),
                            'User'
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'DynamicDetail.name';
        $dir    = 'DESC';

        if(isset($this->request->query['sort'])){
            if($this->request->query['sort'] == 'owner'){
                $sort = 'User.username';
            }else{
                $sort = $this->modelClass.'.'.$this->request->query['sort'];
            }
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
                    if($f->field == 'owner'){
                        array_push($c['conditions'],array("User.username LIKE" => '%'.$f->value.'%'));   
                    }else{
                        $col = $this->modelClass.'.'.$f->field;
                        array_push($c['conditions'],array("$col LIKE" => '%'.$f->value.'%'));
                    }
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
        //If the user is an AP; we need to add an extra clause to only show the DynamicDetails which he is allowed to see.
        if($user['group_name'] == Configure::read('group.ap')){  //AP
            $tree_array = array();
            $this->User = ClassRegistry::init('User');
            $user_id    = $user['id'];

            //**AP and upward in the tree**
            $this->parents = $this->User->getPath($user_id,'User.id');
            //So we loop this results asking for the parent nodes who have available_to_siblings = true
            foreach($this->parents as $i){
                $i_id = $i['User']['id'];
                if($i_id != $user_id){ //upstream
                    array_push($tree_array,array($this->modelClass.'.user_id' => $i_id,$this->modelClass.'.available_to_siblings' => true));
                }else{
                    array_push($tree_array,array('DynamicDetail.user_id' => $i_id));
                }
            }
            //** ALL the AP's children
            $this->children    = $this->User->find_access_provider_children($user['id']);
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $id = $i['id'];
                    array_push($tree_array,array($this->modelClass.'.user_id' => $id));
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

    private function _use_jquery_mobile(){
        $match_found    = false;
        $jq_m           = Configure::read('DynamicLogin.use_jquery');
        if(!empty($jq_m)){
            $u_a = $this->request->header('User-Agent');
            foreach($jq_m as $i){
                $value = $i['value'];
                if($i['type'] == 'contain'){
                    if(preg_match("/$value/", $u_a)){
                        $match_found = true;
                        break; //We've found our match
                    }
                }
                if($i['type'] == 'match'){
                    if("$value" == "$u_a"){
                        $match_found = true;
                        break; //We've found our match
                    }
                }
            } 
        }
        return $match_found;
    }

	private function _find_username_and_password($id){
		$this->PermanentUser = ClassRegistry::init('PermanentUser');
		$this->PermanentUser->contain();
		$q_r = $this->PermanentUser->findById($id);
		$user_data = array('username' => 'notfound','password' => 'notfound');
		if($q_r){
			$un = $q_r['PermanentUser']['username'];
			$user_data['username'] = $un;
			$this->Radcheck = ClassRegistry::init('Radcheck');

			$q_pw = $this->Radcheck->find('first', 
				array('conditions' => array('Radcheck.username' => $un,'Radcheck.attribute' => 'Cleartext-Password'))
			);

			if($q_pw){
				$user_data['password'] = $q_pw['Radcheck']['value'];
			}
		}

		return $user_data;
	}

}
