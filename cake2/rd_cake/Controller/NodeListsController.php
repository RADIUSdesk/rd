<?php
App::uses('AppController', 'Controller');

class NodeListsController extends AppController {

    public $name        = 'NodeLists';
    public $components  = array('Aa','TimeCalculations','GridFilter');
    public $uses        = array('Node','Mesh','User','UnknownNode','NodeSetting','OpenvpnServerClient');
    protected $base     = "Access Providers/Controllers/NodeLists/";

//------------------------------------------------------------------------



	public function unknown_nodes(){
		$items 	= array();
		$q_r  	= $this->UnknownNode->find('all');
		//print_r($q_r);
		
		App::uses('GeoIpLocation', 'GeoIp.Model');
        $GeoIpLocation = new GeoIpLocation();

		foreach($q_r as $i){
		
		    $location = $GeoIpLocation->find($i['UnknownNode']['from_ip']);
            //$location = $GeoIpLocation->find('10.0.0.1');
            
            //Some defaults:
            $country_code = '';
            $country_name = '';
            $city         = '';
            $postal_code  = '';
            
            if(array_key_exists('GeoIpLocation',$location)){
                if($location['GeoIpLocation']['country_code'] != ''){
                    $country_code = utf8_encode($location['GeoIpLocation']['country_code']);
                }
                if($location['GeoIpLocation']['country_name'] != ''){
                    $country_name = utf8_encode($location['GeoIpLocation']['country_name']);
                }
                if($location['GeoIpLocation']['city'] != ''){
                    $city = utf8_encode($location['GeoIpLocation']['city']);
                }
                if($location['GeoIpLocation']['postal_code'] != ''){
                    $postal_code = utf8_encode($location['GeoIpLocation']['postal_code']);
                }
            }
            $i['UnknownNode']['country_code']   = $country_code;
		    $i['UnknownNode']['country_name']   = $country_name;
		    $i['UnknownNode']['city']           = $city;
		    $i['UnknownNode']['postal_code']    = $postal_code;
		
		    $i['UnknownNode']['last_contact_human']     = $this->TimeCalculations->time_elapsed_string($i['UnknownNode']['last_contact']);
			array_push($items,$i['UnknownNode']);
		}
		
		$this->set(array(
            'items'         => $items,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
	}

	public function unknown_node_delete(){

       	if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id']; 
            $this->UnknownNode->id = $this->data['id'];
            $this->UnknownNode->delete($this->UnknownNode->id, true);
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                    $this->UnknownNode->id = $d['id'];
                    $this->UnknownNode->delete($this->UnknownNode->id, true);
            }
        } 
 
        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    //____ BASIC CRUD Manager ________
    public function index(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        
        $mesh_lookup = array();
        
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

		

        $total  = $this->Node->find('count',$c);      
        $q_r    = $this->Node->find('all',$c_page);

        $items      = array();

        foreach($q_r as $i){

            $owner_id       = $i['Mesh']['user_id'];
            $owner_tree     = $this->_find_parents($owner_id);
            $action_flags   = $this->_get_action_flags($owner_id,$user);
            
            $mesh_id        = $i['Mesh']['id'];
            
            $dead_after     = $this->_get_dead_after($mesh_id);  
            $l_contact      = $i['Node']['last_contact'];
            //Find the dead time (only once)
            if($l_contact == null){
                $state = 'never';
            }else{
                $last_timestamp = strtotime($l_contact);
                if($last_timestamp+$dead_after <= time()){
                    $state = 'down';
                }else{
                    $state = 'up';
                }
            }
            
            //We add this for a visual display of the gateway nodes or non-gateway nodes
            $gateway = 'yes';
			if(count($i['NodeNeighbor'])>0){
			    $gateway = $i['NodeNeighbor'][0]['gateway'];
			}
			$i['Node']['gateway'] = $gateway;
					
			if($gateway == 'yes'){
			    //See if there are any Openvpn connections
			    $this->OpenvpnServerClient->contain('OpenvpnServer');
			    $q_vpn = $this->OpenvpnServerClient->find('all',array('conditions' => array('OpenvpnServerClient.mesh_id' => $mesh_id)));
			    if($q_vpn){
			        if(!isset($mesh_lookup[$mesh_id])){ //This will ensure we only to it once per mesh :-)
			            $i['Node']['openvpn_list'] = array();
			            foreach($q_vpn as $vpn){
			                $vpn_name           = $vpn['OpenvpnServer']['name']; 
			                $vpn_description    = $vpn['OpenvpnServer']['description'];
			                $last_contact_to_server  = $vpn['OpenvpnServerClient']['last_contact_to_server'];
			                if($last_contact_to_server != null){
			                    $lc_human           = $this->TimeCalculations->time_elapsed_string($last_contact_to_server);
			                }else{
			                    $lc_human = 'never';
			                }
			                $vpn_state              = $vpn['OpenvpnServerClient']['state'];
			                array_push($i['Node']['openvpn_list'], array(
			                    'name'          => $vpn_name,
			                    'description'   => $vpn_description,
			                    'lc_human'      => $lc_human,
			                    'state'         => $vpn_state
			                ));
			            }
			            //print_r($q_vpn);
			            $mesh_lookup[$mesh_id] = true;
			        }
			    }
			}
            
            
            $i['Node']['last_contact_human']     = $this->TimeCalculations->time_elapsed_string($i['Node']['last_contact']);
            $i['Node']['state']     = $state;
			$i['Node']['update']    = $action_flags['update'];
            $i['Node']['delete'] 	= $action_flags['delete'];
			$i['Node']['owner'] 	= $owner_tree;
			$i['Node']['mesh'] 		= $i['Mesh']['name'];

            array_push($items,$i['Node']);

        }
       
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            'totalCount' => $total,
            '_serialize' => array('items','success','totalCount')
        ));
    }

    //----- Menus ------------------------
	public function menu_for_unknown_grid(){
		$menu = array();
		$menu = array(
                array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                     array( 
                        'xtype'     =>  'splitbutton',  
                        'iconCls'   => 'b-reload',
                        'glyph'     => Configure::read('icnReload'),   
                        'scale'     => 'large', 
                        'itemId'    => 'reload',   
                        'tooltip'   => __('Reload'),
                            'menu'  => array( 
                                'items' => array( 
                                    '<b class="menu-title">'.__('Reload every').':</b>',
                                    array( 'text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                                    array( 'text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                                    array( 'text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                                    array( 'text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )
                                   
                                )
                            )
                    ),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnAttach'), 'scale' => 'large', 'itemId' => 'attach',      'tooltip'=> __('Attach')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnRedirect'), 'scale' => 'large', 'itemId' => 'redirect',   'tooltip'=> __('Redirect')),
                    
                )),
            );

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
                            'Mesh' => array('User'),
                            'NodeNeighbor'
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'Node.name';
        $dir    = 'DESC';

        if(isset($this->request->query['sort'])){
            if($this->request->query['sort'] == 'mesh'){
                $sort = 'Mesh.name';
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
                    if($f->field == 'mesh'){
                        array_push($c['conditions'],array("Mesh.name LIKE" => '%'.$f->value.'%'));   
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
        //If the user is an AP; we need to add an extra clause to only show the Ssids which he is allowed to see.
        if($user['group_name'] == Configure::read('group.ap')){  //AP
            $tree_array = array();
            $user_id    = $user['id'];

            //**AP and upward in the tree**
            $this->parents = $this->User->getPath($user_id,'User.id');
            //So we loop this results asking for the parent nodes who have available_to_siblings = true
            foreach($this->parents as $i){
                $i_id = $i['User']['id'];
                if($i_id != $user_id){ //upstream
                    array_push($tree_array,array('Mesh.user_id' => $i_id, 'Mesh.available_to_siblings' => true));
                }else{
                    array_push($tree_array,array('Mesh.user_id' => $i_id));
                }
            }
            //** ALL the AP's children
            $this->children    = $this->User->find_access_provider_children($user['id']);
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $id = $i['id'];
                    array_push($tree_array,array('Mesh.user_id' => $id));
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
    
    private function _get_dead_after($mesh_id){
		Configure::load('MESHdesk');
		$data 		= Configure::read('common_node_settings'); //Read the defaults
		$dead_after	= $data['heartbeat_dead_after'];
		$n_s = $this->NodeSetting->find('first',array(
            'conditions'    => array(
                'NodeSetting.mesh_id' => $mesh_id
            )
        )); 
        if($n_s){
            $dead_after = $n_s['NodeSetting']['heartbeat_dead_after'];
        }
		return $dead_after;
	}
    
}
