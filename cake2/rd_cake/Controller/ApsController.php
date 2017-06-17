<?php
App::uses('AppController', 'Controller');

class ApsController extends AppController {

    public $name        = 'Aps';
    public $uses        = array('Ap', 'UnknownAp','User','Na','ApProfileSetting','ApStation','ApProfileEntry','OpenvpnServer');
    public $components  = array('MacVendors','Aa','Formatter','TimeCalculations','GridFilter');
    
    protected $base     = "Access Providers/Controllers/Aps/";
    
    protected $ApId     = '';
	protected $Hardware = 'dragino'; //Some default value
	protected $Power	= '10'; //Some default
    protected $RadioSettings = array();
    
    protected $special_mac = "30-B5-C2-B3-80-B1"; //hack
    
    public function get_config_for_ap(){

        if(isset($this->request->query['mac'])){

            $mac            = $this->request->query['mac'];
           // $mac    = 'AC-86-74-10-03-10'; //manual override
           //Make sure the MAC is in captials
           $mac             = strtoupper($mac);

            $this->Ap->contain();
            $q_r            = $this->Ap->findByMac($mac);

            if($q_r){
             //   print_r($q_r);
                $ap_profile_id  = $q_r['Ap']['ap_profile_id'];
                $this->ApId     = $q_r['Ap']['id'];
                $this->Mac      = $mac;
				$this->Hardware	= $q_r['Ap']['hardware'];
				
                $this->Ap->ApProfile->contain(
                    'ApProfileExit.ApProfileExitApProfileEntry',
                    'ApProfileEntry',
                    'ApProfileSetting',
                    'ApProfileSpecific',
                    'ApProfileExit.ApProfileExitCaptivePortal',
                    'Ap'
                );
                $ap_profile     = $this->Ap->ApProfile->findById($ap_profile_id);

                $ap_profile['ApDetail'] = $q_r['Ap'];
               //// print_r($ap_profile);
                

				//Update the last_contact field
				$data = array();
				$data['id'] 			        = $this->ApId;
				$data['last_contact']	        = date("Y-m-d H:i:s", time());
				$data['last_contact_from_ip']   = $this->request->clientIp();
				$this->Ap->save($data);
               
                $json = $this->_build_json($ap_profile);
                $this->set(array(
                    'config_settings'   => $json['config_settings'],
                    'timestamp'         => $json['timestamp'],
                    'success' => true,
                    '_serialize' => array('config_settings','success','timestamp')
                ));
                
            }else{
                //Write this to an "unknown nodes" table....
				$ip 					        = $this->request->clientIp();
				$data 					        = array();
				$data['mac'] 			        = $mac;
				$data['last_contact_from_ip']   = $ip;
				$data['last_contact']	        = date("Y-m-d H:i:s", time());

				$q_r 	= $this->UnknownAp->find('first',array('conditions' => array('UnknownAp.mac' => $mac)));
				
				$include_new_server     = false;

				if($q_r){
					$id         = $q_r['UnknownAp']['id'];
                    $new_server = $q_r['UnknownAp']['new_server'];
                    if($new_server != ''){
                        $data['new_server_status'] = 'fetched';
                        $include_new_server = true;
                    }
					$data['id'] = $id;
					$this->UnknownAp->save($data);
				}else{
					$data['vendor']  = $this->MacVendors->vendorFor($mac);
					$this->UnknownAp->create();
					$this->UnknownAp->save($data);
				}

                if($include_new_server){
                    $this->set(array(
                        'new_server' => $new_server,
                        'success' => false,
                        '_serialize' => array('new_server','success')
                    ));
                }else{
                     $this->set(array(
                        'error' => "MAC Address: ".$mac." not attaced to any AP Profile on the system",
                        'success' => false,
                        '_serialize' => array('error','success')
                    ));
                }
            }
        }else{  			
             $this->set(array(
                'error' => "MAC Address of node not specified",
                'success' => false,
                '_serialize' => array('error','success')
            ));
        }
    }
    
     //This we can just accept... i think
    public function redirect_unknown(){
        $this->request->data['new_server_status'] = 'awaiting';
        if ($this->UnknownAp->save($this->request->data)) {
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
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
        
        $OpenvpnServerLookup= array();
        	

        $total  = $this->Ap->find('count',$c);      
        $q_r    = $this->Ap->find('all',$c_page);

        $items      = array();
        
        //Create a hardware lookup for proper names of hardware
	    $hardware = $this->_make_hardware_lookup();  
        
        App::uses('GeoIpLocation', 'GeoIp.Model');
        $GeoIpLocation = new GeoIpLocation();

        foreach($q_r as $i){

            $owner_id       = $i['ApProfile']['user_id'];
            $owner_tree     = $this->_find_parents($owner_id);
            $action_flags   = $this->_get_action_flags($owner_id,$user); 
            
            
            //----
            //Some defaults:
            $country_code = '';
            $country_name = '';
            $city         = '';
            $postal_code  = '';
            
            if($i['Ap']['last_contact_from_ip'] != null){
                $location = $GeoIpLocation->find($i['Ap']['last_contact_from_ip']);
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
            }
            //----  
            
            			
			$hw_id 		    = $i['Ap']['hardware'];
			$hw_human	    = $hardware["$hw_id"]; 	//Human name for Hardware
			$ap_profile_id  = $i['ApProfile']['id'];
			
			$l_contact      = $i['Ap']['last_contact'];
		
			//Get the 'dead_after' value
		    $dead_after = $this->_get_dead_after($ap_profile_id);
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
            
            $ap_id      = $i['Ap']["id"];
            $modified 	= $this->_get_timespan(); //Default will be an hour
            
            $this->ApProfileEntry->contain();
            $q_e = $this->ApProfileEntry->find('all', array('conditions'=>array('ApProfileEntry.ap_profile_id' => $ap_profile_id)));
            $entries_list = array();
             
            
            $this->ApStation->contain('ApProfileEntry.name');
            $q_s = $this->ApStation->find('all',array(
                'conditions'    => array(
                    'ApStation.ap_id'       => $ap_id,
                    'ApStation.modified >='   => $modified
                ),
                'fields'        => array(
                    'DISTINCT(mac)'
                )
            ));
            
            $array_ssids = array();
            foreach($q_e as $e){
                $name = $e['ApProfileEntry']['name'];
                array_push($array_ssids, array('name' => $name,'users' => 0));
            }
            
            $ssid_devices = array();    
            foreach($q_s as $s){
                $mac = $s['ApStation']['mac'];
                $name  = $s['ApProfileEntry']['name'];
                if(array_key_exists($name,$ssid_devices)){
                    $ssid_devices["$name"] =  $ssid_devices["$name"] + 1;  
                }else{
                    $ssid_devices["$name"] = 1;
                }
            }
            
            $c = 0;
            foreach($array_ssids as $ssid){
                $n = $ssid['name'];
                if(array_key_exists($n,$ssid_devices)){
                    $users = $ssid_devices["$n"];
                    $array_ssids[$c] = array('name' => $n,'users' => $users);
                }              
                $c++;
            }
            
            //Get the newest visitor
            $this->ApStation->contain();
            $q_mac = $this->ApStation->find('first',array(
                    'conditions'    => array(
                        'ApStation.ap_id'         => $ap_id,
                    ),
                    'order' => array('ApStation.created' => 'desc')
                ));
            
            $newest_vendor  = "N/A";
            $newest_time    = "N/A";
            $newest_station = "N/A";
            if($q_mac){

                $newest_vendor  = $q_mac['ApStation']['vendor'];
                $newest_time    = $this->TimeCalculations->time_elapsed_string($q_mac['ApStation']['modified']);
                $newest_station = $q_mac['ApStation']['mac'];
            }
            
            //Get data usage
            $this->ApStation->contain();
            $q_t = $this->ApStation->find('first', array(
                        'conditions'    => array(
                            'ApStation.ap_id'         => $ap_id,
                            'ApStation.modified >='   => $modified
                        ),
                        'fields'    => array(
                            'SUM(ApStation.tx_bytes) as tx_bytes',
                            'SUM(ApStation.rx_bytes)as rx_bytes'
                        )
                    ));
                    
            $data_past_hour = '0kb';
            if($q_t){
                 $t_bytes    = $q_t[0]['tx_bytes'];
                 $r_bytes    = $q_t[0]['rx_bytes'];    
                  $data_past_hour = $this->Formatter->formatted_bytes(($t_bytes+$r_bytes));
            }
            
            //Merge the last command (if present)
			if(count($i['ApAction'])>0){
				$last_action = $i['ApAction'][0];
				//Add it to the list....
				$i['Ap']['last_cmd'] 			= $last_action['command'];
				$i['Ap']['last_cmd_status'] 	= $last_action['status'];
			}
			
			//List any OpenVPN connections
			
			if(count($i['OpenvpnServerClient'])>0){
			    $i['Ap']['openvpn_list'] = array();
		        foreach($i['OpenvpnServerClient'] as $vpn){ 
		            //Do a lookup to save Query time
		            $s_id = $vpn['openvpn_server_id'];
		            if(!isset($OpenvpnServerLookup[$s_id])){
		                $this->OpenvpnServer->contain();
		                $q_s                = $this->OpenvpnServer->findById($vpn['openvpn_server_id']);
		                $vpn_name           = $q_s['OpenvpnServer']['name']; 
                        $vpn_description    = $q_s['OpenvpnServer']['description'];
		                $l_array = array('name' => $vpn_name, 'description' => $vpn_description);
		                $OpenvpnServerLookup[$s_id] = $l_array;
		            }else{
		                $vpn_name           = $OpenvpnServerLookup[$s_id]['name']; 
                        $vpn_description    = $OpenvpnServerLookup[$s_id]['description'];
		            }
		               
                    $last_contact_to_server  = $vpn['last_contact_to_server'];
                    if($last_contact_to_server != null){
                        $lc_human           = $this->TimeCalculations->time_elapsed_string($last_contact_to_server);
                    }else{
                        $lc_human = 'never';
                    }
                    $vpn_state              = $vpn['state'];
                    array_push($i['Ap']['openvpn_list'], array(
                        'name'          => $vpn_name,
                        'description'   => $vpn_description,
                        'lc_human'      => $lc_human,
                        'state'         => $vpn_state
                    ));
                    }	
			}	
                
			$i['Ap']['update']      = $action_flags['update'];
            $i['Ap']['delete'] 	    = $action_flags['delete'];
			$i['Ap']['owner'] 	    = $owner_tree;
			$i['Ap']['ap_profile']  = $i['ApProfile']['name'];
			
			$i['Ap']['last_contact_human']  = $this->TimeCalculations->time_elapsed_string($i['Ap']["last_contact"]);
			$i['Ap']['state']               = $state;
			$i['Ap']['data_past_hour']      = $data_past_hour;
			$i['Ap']['newest_station']      = $newest_station;
			$i['Ap']['newest_time']         = $newest_time;
			$i['Ap']['newest_vendor']       = $newest_vendor;

			$i['Ap']['hw_human']            = $hw_human;
			$i['Ap']['ssids']               = $array_ssids;
			
			$i['Ap']['country_code']        = $country_code;
            $i['Ap']['country_name']        = $country_name;
            $i['Ap']['city']                = $city;
            $i['Ap']['postal_code']         = $postal_code;
            	
            array_push($items,$i['Ap']);
        }
       
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            'totalCount' => $total,
            '_serialize' => array('items','success','totalCount')
        ));
    }
        
    //-- List available hardware options --
    public function hardware_options(){

        $items = array();
		Configure::load('ApProfiles');
        $ct = Configure::read('ApProfiles.hardware');
        foreach($ct as $i){
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
                            'ApProfile' => array('User'),
                            'ApAction',
                            'OpenvpnServerClient'
                        );

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'Ap.name';
        $dir    = 'DESC';

        if(isset($this->request->query['sort'])){
            if($this->request->query['sort'] == 'ap_profile'){
                $sort = 'ApProfile.name';
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
                    if($f->field == 'ap_profile'){
                        array_push($c['conditions'],array("ApProfile.name LIKE" => '%'.$f->value.'%'));   
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
                    array_push($tree_array,array('ApProfile.user_id' => $i_id, 'ApProfile.available_to_siblings' => true));
                }else{
                    array_push($tree_array,array('ApProfile.user_id' => $i_id));
                }
            }
            //** ALL the AP's children
            $this->children    = $this->User->find_access_provider_children($user['id']);
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $id = $i['id'];
                    array_push($tree_array,array('ApProfile.user_id' => $id));
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
    
    //___________________ AP Settings and related functions _________________
    private function  _build_json($ap_profile){

        //Basic structure
        $json = array();
        $json['timestamp']                      = 1; //FIXME replace with the last change timestamp
        $json['config_settings']                = array();
        $json['config_settings']['wireless']    = array();
        $json['config_settings']['network']     = array();
		$json['config_settings']['system']		= array();

        //============ Network ================
        $net_return         = $this->_build_network($ap_profile);
        $json_network       = $net_return[0];
        $json['config_settings']['network'] = $json_network;

        //=========== Wireless ===================
        $entry_data         = $net_return[1];
        $json_wireless      = $this->_build_wireless($ap_profile,$entry_data);
        $json['config_settings']['wireless'] = $json_wireless;

        //========== Gateway  ======
        $json['config_settings']['gateways']        = $net_return[2]; //Gateways
        $json['config_settings']['captive_portals'] = $net_return[3]; //Captive portals
        
        $openvpn_bridges                            = $this->_build_openvpn_bridges($net_return[4]);
        $json['config_settings']['openvpn_bridges'] = $openvpn_bridges; //Openvpn Bridges
        
		//======== System related settings ======
		$system_data 		= $this->_build_system($ap_profile);
		$json['config_settings']['system'] = $system_data;

        return $json; 
    }
    
     private function _build_openvpn_bridges($openvpn_list){
        $openvpn_bridges = array();    
        foreach($openvpn_list as $o){
        
            $br                 = array(); 
            $br['interface']    = $o['interface'];
            $br['up']           = "ap_".$this->Mac."\n".md5("ap_".$this->Mac)."\n";
            $br['ca']           = $o['ca_crt'];
            $br['vpn_gateway_address'] = $o['vpn_gateway_address'];
            $br['vpn_client_id'] = $o['vpn_client_id'];
            
            Configure::load('OpenvpnClientPresets');
            $config_file    = Configure::read('OpenvpnClientPresets.'.$o['config_preset']); //Read the defaults

            $config_file['remote']  = $o['ip_address'].' '.$o['port'];
            $config_file['up']      = '"/etc/openvpn/up.sh br-'.$o['interface'].'"';
            $config_file['proto']   = $o['protocol'];
            $config_file['ca']      = '/etc/openvpn/'.$o['interface'].'_ca.crt';
            $config_file['auth_user_pass'] = '/etc/openvpn/'.$o['interface'].'_up';  
            $br['config_file']      = $config_file;
            array_push($openvpn_bridges,$br);
        }
        return $openvpn_bridges;
    }
    
    private function _build_system($ap_profile){
        //print_r($ap_profile);
		//Get the root password
		//print_r($ap_profile);
		$ss = array();
		if(array_key_exists('password_hash', $ap_profile['ApProfileSetting'])) {
		    if($ap_profile['ApProfileSetting']['password_hash'] != ''){
			    $ss['password_hash'] 		= $ap_profile['ApProfileSetting']['password_hash'];
			    $ss['heartbeat_interval']	= $ap_profile['ApProfileSetting']['heartbeat_interval'];
			    $ss['heartbeat_dead_after']	= $ap_profile['ApProfileSetting']['heartbeat_dead_after'];
		    }else{
			    Configure::load('ApProfiles');
			    $data = Configure::read('common_ap_settings'); //Read the defaults
			    $ss['password_hash'] 		= $data['password_hash'];
			    $ss['heartbeat_interval']	= $data['heartbeat_interval'];
			    $ss['heartbeat_dead_after']	= $data['heartbeat_dead_after'];
		    }
        }else{
            Configure::load('ApProfiles');
		    $data = Configure::read('common_ap_settings'); //Read the defaults
		    $ss['password_hash'] 		= $data['password_hash'];
		    $ss['heartbeat_interval']	= $data['heartbeat_interval'];
		    $ss['heartbeat_dead_after']	= $data['heartbeat_dead_after'];   
        }

       
        //Timezone
        if(array_key_exists('tz_value', $ap_profile['ApProfileSetting'])) {
            if($ap_profile['ApProfileSetting']['tz_value'] != ''){
                $ss['timezone']             = $ap_profile['ApProfileSetting']['tz_value'];
            }
        }else{
            Configure::load('ApProfiles');
			$data = Configure::read('common_ap_settings'); //Read the defaults
            $ss['timezone']             = $data['tz_value'];
        }

        //Gateway specifics
        if(array_key_exists('gw_dhcp_timeout', $ap_profile['ApProfileSetting'])) {
            if($ap_profile['ApProfileSetting']['gw_dhcp_timeout'] != ''){
                $ss['gw_dhcp_timeout']          = $ap_profile['ApProfileSetting']['gw_dhcp_timeout'];
                $ss['gw_use_previous']          = $ap_profile['ApProfileSetting']['gw_use_previous'];
                $ss['gw_auto_reboot']           = $ap_profile['ApProfileSetting']['gw_auto_reboot'];
                $ss['gw_auto_reboot_time']      = $ap_profile['ApProfileSetting']['gw_auto_reboot_time'];
            } 
        }else{
            Configure::load('ApProfiles');
			$data = Configure::read('common_ap_settings'); //Read the defaults
            $ss['gw_dhcp_timeout']          = $data['gw_dhcp_timeout'];
            $ss['gw_use_previous']          = $data['gw_use_previous'];
            $ss['gw_auto_reboot']           = $data['gw_auto_reboot'];
            $ss['gw_auto_reboot_time']      = $data['gw_auto_reboot_time'];
        }

		foreach($ap_profile['Ap'] as $n){
			if($n['id'] == $this->ApId){
				$ss['hostname'] = $n['name'];
				break;
			}
		}
		return $ss;
	}
    
    private function _build_network($ap_profile){

        $network 				= array();
        $nat_data				= array();
        $captive_portal_data 	= array();
        $openvpn_bridge_data    = array();
		$include_lan_dhcp 		= true;

        //-> loopback if
        array_push( $network,
            array(
                "interface"    => "loopback",
                "options"   => array(
                    "ifname"        => "lo",
                    "proto"         => "static",
                    "ipaddr"        => "127.0.0.1",
                    "netmask"       => "255.0.0.0"
               )
            ));

        if($this->request->query['mac'] == $this->special_mac){ ////Tampa hack
            $br_int = 'eth0';
        }else{
            $br_int = $this->_eth_br_for($this->Hardware);
        }

		if($include_lan_dhcp){
		    array_push( $network,
		        array(
		            "interface"    => "lan",
		            "options"   => array(
		                "ifname"        => "$br_int", 
		                "type"          => "bridge",
		                "proto"         => "dhcp"
		           )
		   	));
		}
		

        //Now we will loop all the defined exits **that has entries assigned** to them and add them as bridges as we loop. 
        //The members of these bridges will be determined by which entries are assigned to them and specified
        //in the wireless configuration file

        $start_number = 0;

        //We create a data structure which will be used to add the entry points and bridge them with
        //The correct network defined here
        $entry_point_data = array();

       /// print_r($ap_profile['ApProfileExit']);

        //Add the auto-attach entry points
        foreach($ap_profile['ApProfileExit'] as $ap_profile_e){

            $has_entries_attached   = false;
            $if_name                = 'ex_'.$this->_number_to_word($start_number);
            $exit_id                = $ap_profile_e['id'];
            $type                   = $ap_profile_e['type'];
            $vlan                   = $ap_profile_e['vlan'];

            //This is used to fetch info eventually about the entry points
            if(count($ap_profile_e['ApProfileExitApProfileEntry']) > 0){
                $has_entries_attached = true;
                foreach($ap_profile_e['ApProfileExitApProfileEntry'] as $entry){    
                    if($type == 'bridge'){ //The gateway needs the entry points to be bridged to the LAN
                        array_push($entry_point_data,array('network' => 'lan','entry_id' => $entry['ap_profile_entry_id']));
                    }else{
                        array_push($entry_point_data,array('network' => $if_name,'entry_id' => $entry['ap_profile_entry_id']));
                    }        
                }
            }
          
            if($has_entries_attached == true){
            
                $captive_portal_count = 1;
                
                if($type == 'tagged_bridge'){
                
					$br_int = $this->_eth_br_for($this->Hardware);
					if(preg_match('/eth1/', $br_int)){	//If it has two add both
                    	$interfaces =  "eth0.".$vlan." eth1.".$vlan;
					}else{
						$interfaces =  "eth0.".$vlan; //only one
					}
                    array_push($network,
                        array(
                            "interface"    => "$if_name",
                            "options"   => array(
                                "ifname"    => $interfaces,
                                "type"      => "bridge"
                        ))
                    );
                    $start_number++;
                    continue;   //We don't care about the other if's
                }

                if($type == 'nat'){
                    $interfaces =  "nat.".$start_number;
                    array_push($network,
                        array(
                            "interface"    => "$if_name",
                            "options"   => array(
                                "ifname"    => $interfaces,
                                "type"      => "bridge",
                                'ipaddr'    =>  "10.200.".(100+$start_number).".1",
                                'netmask'   =>  "255.255.255.0",
                                'proto'     => 'static'
                        ))
                    );

                    //Push the nat data
                    array_push($nat_data,$if_name);
                    $start_number++;
                    continue; //We dont care about the other if's
                }

                if($type=='bridge'){
                /*
                    $current_interfaces = $network[1]['options']['ifname'];
                    $interfaces =  "bridge0.".$start_number;
                    $network[1]['options']['ifname'] = $current_interfaces." ".$interfaces;
                    
                    $start_number++;
                   */
                    continue; //We dont care about the other if's
                }

                if($type == 'captive_portal'){
                
                    //---WIP Start---
                    if($ap_profile_e['ApProfileExitCaptivePortal']['dnsdesk'] == true){ 
                        $if_ip      = "10.$captive_portal_count.0.2";
                    }
                    $captive_portal_count++; //Up it for the next one
                    //---WIP END---
                       
                
                    //Add the captive portal's detail
                    if($type =='captive_portal'){
                        $a = $ap_profile_e['ApProfileExitCaptivePortal'];
                        $a['hslan_if']      = 'br-'.$if_name;
                        $a['network']       = $if_name;
                        
                        //---WIP Start---
                        if($ap_profile_e['ApProfileExitCaptivePortal']['dnsdesk'] == true){
                            $a['dns1']      = $if_ip;
                            //Also sent along the upstream DNS Server to use
                            $a['upstream_dns1'] = Configure::read('dnsfilter.dns1'); //Read the defaults
                            $a['upstream_dns2'] = Configure::read('dnsfilter.dns2'); //Read the defaults
                        }
                        //---WIP END---
                        
                        
                        //Generate the NAS ID
                        $ap_profile_name    = preg_replace('/\s+/', '_', $ap_profile['ApProfile']['name']);
                        $a['radius_nasid']  = $ap_profile_name.'_'.$ap_profile['ApDetail']['name'].'_cp_'.$ap_profile_e['ApProfileExitCaptivePortal']['ap_profile_exit_id'];
                        array_push($captive_portal_data,$a);             
                    }
                    

                    if($ap_profile_e['ApProfileExitCaptivePortal']['dnsdesk'] == true){
                    
                        array_push($network,
                            array(
                                "interface"    => "$if_name",
                                "options"   => array(
                                    "type"      => "bridge",
                                    "proto"     => "none",
                                    
                                    //---WIP Start--- // Add the special bridged interface IP
                                    "ipaddr"    => "$if_ip",
                                    "netmask"   => "255.255.255.0",
                                    "proto"     => "static",
                                    //---WIP END--- 
                            ))
                        ); 
                    
                    }else{
                        array_push($network,
                            array(
                                "interface"    => "$if_name",
                                "options"   => array(
                                    "type"      => "bridge",
                                    "proto"     => "none"
     
                            ))
                        ); 
                    }
                        
                             
                    $start_number++;
                    continue; //We dont care about the other if's
                }
                
                
                //___ OpenVPN Bride ________
                if($type == 'openvpn_bridge'){               
                    $openvpn_server_client = ClassRegistry::init('OpenvpnServerClient');
                    $openvpn_server_client->contain();
                    $q_c = $openvpn_server_client->find('first',
                        array('conditions' => array(
                            'OpenvpnServerClient.ap_profile_id'         => $ap_profile_e['ap_profile_id'],
                            'OpenvpnServerClient.ap_profile_exit_id'    => $ap_profile_e['id'],
                            'OpenvpnServerClient.ap_id'                 => $this->ApId,
                        ))
                    );
                    
                    $a              = $q_c['OpenvpnServerClient'];
                    $a['bridge']    = 'br-'.$if_name;
                    $a['interface'] = $if_name;
                    
                    //Get the info for the OpenvpnServer
                    $openvpn_server = ClassRegistry::init('OpenvpnServer');
                    $q_s            = $openvpn_server->findById($q_c['OpenvpnServerClient']['openvpn_server_id']);
                    
                    $a['protocol']  = $q_s['OpenvpnServer']['protocol'];
                    $a['ip_address']= $q_s['OpenvpnServer']['ip_address'];
                    $a['port']      = $q_s['OpenvpnServer']['port'];
                    $a['vpn_mask']  = $q_s['OpenvpnServer']['vpn_mask'];
                    $a['ca_crt']    = $q_s['OpenvpnServer']['ca_crt'];   
                    
                    $a['config_preset']         = $q_s['OpenvpnServer']['config_preset'];  
                    $a['vpn_gateway_address']   = $q_s['OpenvpnServer']['vpn_gateway_address'];
                    $a['vpn_client_id']         = $q_c['OpenvpnServerClient']['id'];                     
                    array_push($openvpn_bridge_data,$a);             
                    
                    array_push($network,
                        array(
                            "interface"    => "$if_name",
                            "options"   => array(
                                "type"      => "bridge",
                                'ipaddr'    => $q_c['OpenvpnServerClient']['ip_address'],
                                'netmask'   => $a['vpn_mask'],
                                'proto'     => 'static'
                                
                        ))
                    );
                    $start_number++;
                    continue; //We dont care about the other if's
                }            
            }
        } 
        return array($network,$entry_point_data,$nat_data,$captive_portal_data,$openvpn_bridge_data);
    }
    
    private function _build_wireless($ap_profile,$entry_point_data){
    
        //print_r($entry_point_data);
    
        //First get the WiFi settings wether default or specific
        $this->_setWiFiSettings();

		//Determine the radio count and configure accordingly
		$radios   = $this->_get_hardware_setting($this->Hardware,'radios');
		if($radios == 2){
			$wireless = $this->_build_dual_radio_wireless($ap_profile,$entry_point_data);
			return $wireless;
		}

        if($radios == 1){
            $wireless = $this->_build_single_radio_wireless($ap_profile,$entry_point_data);
			return $wireless;
        }
    }
    
    private function _build_single_radio_wireless($ap_profile,$entry_point_data){
    
        //print_r($ap_profile);

        $wireless = array();
        
        $channel = 8;
        $hwmode  = '11g';
        $band    = 'two';
        
        if($this->RadioSettings[0]['radio0_band'] == '24'){
            $channel = $this->RadioSettings[0]['radio0_channel_two'];
            $hwmode  = '11g';
            $band   = 'two';
        }
        
        if($this->RadioSettings[0]['radio0_band'] == '5'){
            $channel = $this->RadioSettings[0]['radio0_channel_five'];
            $hwmode  = '11a';
            $band   = 'five';
        }
        
        //Country
        if(array_key_exists('country', $ap_profile['ApProfileSetting'])) {
            if($ap_profile['ApProfileSetting']['country'] != ''){
                $country  = $ap_profile['ApProfileSetting']['country'];
		    }else{
			    Configure::load('ApProfiles');
			    $data       = Configure::read('common_ap_settings'); //Read the defaults
                $country    = $data['country'];
		    }
	    }else{
	        Configure::load('ApProfiles');
		    $data       = Configure::read('common_ap_settings'); //Read the defaults
            $country    = $data['country'];
	    }

        //Boolean flags
        $diversity  = 0;
        $noscan     = 0;
        $ldpc       = 0;

        if(array_key_exists('radio0_noscan', $this->RadioSettings[0])) {
            if($this->RadioSettings[0]['radio0_noscan'] == true){
                $noscan = 1;
            }
        }

        if(array_key_exists('radio0_diversity', $this->RadioSettings[0])) {
            if($this->RadioSettings[0]['radio0_diversity'] == true){
                $diversity = 1;
            }
        }

        if(array_key_exists('radio0_ldpc', $this->RadioSettings[0])) {
            if($this->RadioSettings[0]['radio0_ldpc'] == true){
                $ldpc = 1;
            }
        }

        $radio_zero_capab = array();
        //Somehow the read thing reads double..
        $allready_there = array();
        foreach($this->RadioSettings[0]['radio0_ht_capab'] as $c){
            if(!in_array($c,$allready_there)){
                array_push($allready_there,$c);
                array_push($radio_zero_capab,array('name'    => 'ht_capab', 'value'  => $c));
            }
        }
        
        if(array_key_exists('radio0_disable_b', $this->RadioSettings[0])) {
            array_push($radio_zero_capab,array('name'    => 'basic_rate', 'value'  => '6000 9000 12000 18000 24000 36000 48000 54000')); 
        }
        
        array_push( $wireless,
                array(
                    "wifi-device"   => "radio0",
                    "options"       => array(
                        'channel'       => intval($channel),
                        'disabled'      => intval($this->RadioSettings[0]['radio0_disabled']),
                        'hwmode'        => $hwmode,
                        'country'       => $country,
                        'distance'      => intval($this->RadioSettings[0]['radio0_distance']),
                        'htmode'        => $this->RadioSettings[0]['radio0_htmode'],
                        'txpower'       => intval($this->RadioSettings[0]['radio0_txpower']),
                        'beacon_int'    => intval($this->RadioSettings[0]['radio0_beacon_int']),
                        'noscan'        => $noscan,
                        'diversity'     => $diversity,
                        'ldpc'          => $ldpc
                    ),
                    'lists'          => $radio_zero_capab
                ));
                
        $start_number = 0;

        //Check if we need to add this wireless VAP
        foreach($ap_profile['ApProfileEntry'] as $ap_profile_e){
            $to_all     = false;
            $if_name    = $this->_number_to_word($start_number);
            $entry_id   = $ap_profile_e['id'];
            $start_number++;
            //Check if it is assigned to an exit point
            foreach($entry_point_data as $epd){
                if($epd['entry_id'] == $entry_id){ //We found our man :-)
                    if(
                        ($ap_profile_e['frequency_band'] == 'both')||
                        ($ap_profile_e['frequency_band'] == $band)){
                        
                            //print_r($ap_profile_e);
                            
                            $base_array = array(
                                        "device"        => "radio0",
                                        "ifname"        => "$if_name"."0",
                                        "mode"          => "ap",
                                        "network"       => $epd['network'],
                                        "encryption"    => $ap_profile_e['encryption'],
                                        "ssid"          => $ap_profile_e['name'],
                                        "key"           => $ap_profile_e['special_key'],
                                        "hidden"        => $ap_profile_e['hidden'],
                                        "isolate"       => $ap_profile_e['isolate'],
                                        "auth_server"   => $ap_profile_e['auth_server'],
                                        "auth_secret"   => $ap_profile_e['auth_secret']
                                   );
                        
                            if($ap_profile_e['chk_maxassoc']){
                                $base_array['maxassoc'] = $ap_profile_e['maxassoc'];
                            }
                            
                            if($ap_profile_e['macfilter'] != 'disable'){
                                $base_array['macfilter']    = $ap_profile_e['macfilter'];
                                //Replace later
                                $pu_id      = $ap_profile_e['permanent_user_id'];
                                $device     = ClassRegistry::init('Device');
                                $device->contain();
                                $q_d        = $device->find('all',array('conditions' => array('Device.permanent_user_id' => $pu_id)));
                                $mac_list   = array();
                                foreach($q_d as $device){
                                    $mac = $device['Device']['name'];
                                    $mac = str_replace('-',':',$mac);
                                    array_push($mac_list,$mac);
                                }
                                if(count($mac_list)>0){
                                    $base_array['maclist'] = implode(" ",$mac_list);
                                }
                            }
                        
                            array_push( $wireless,
                                array(
                                    "wifi-iface"    => "$if_name",
                                    "options"   => $base_array
                                ));
                            
                    }
                    break;
                }
            }
        }
       // print_r($wireless);
        return $wireless;
    }
    
    private function _build_dual_radio_wireless($ap_profile,$entry_point_data){

        $wireless = array();
        
        //$channel = 8;
        //$hwmode  = '11g';
        $band_0  = 'two';
        
        if($this->RadioSettings[0]['radio0_band'] == '24'){
            $channel_0  = $this->RadioSettings[0]['radio0_channel_two'];
            $hwmode_0   = '11g';
            $band_0     = 'two';
        }
        
        if($this->RadioSettings[0]['radio0_band'] == '5'){
            $channel_0 = $this->RadioSettings[0]['radio0_channel_five'];
            $hwmode_0  = '11a';
            $band_0    = 'five';     
        }
        
        if($this->RadioSettings[1]['radio1_band'] == '24'){
            $channel_1 = $this->RadioSettings[1]['radio1_channel_two'];
            $hwmode_1  = '11g';
            $band_1    = 'two';  
        }
        
        if($this->RadioSettings[1]['radio1_band'] == '5'){
            $channel_1 = $this->RadioSettings[1]['radio1_channel_five'];
            $hwmode_1  = '11a';
            $band_1    = 'five';
        }
        
        //Country
        if(array_key_exists('country', $ap_profile['ApProfileSetting'])) {
            if($ap_profile['ApProfileSetting']['country'] != ''){
                $country  = $ap_profile['ApProfileSetting']['country'];
		    }else{
			    Configure::load('ApProfiles');
			    $data       = Configure::read('common_ap_settings'); //Read the defaults
                $country    = $data['country'];
		    }
	    }else{
	        Configure::load('ApProfiles');
		    $data       = Configure::read('common_ap_settings'); //Read the defaults
            $country    = $data['country'];
	    }

        //FIXME ADD The ability to not use on of the RADIOs!!!
		//===== RADIO ZERO====

         //Boolean flags
        $diversity  = 0;
        $noscan     = 0;
        $ldpc       = 0;

        if(array_key_exists('radio0_noscan', $this->RadioSettings[0])) {
            if($this->RadioSettings[0]['radio0_noscan'] == true){
                $noscan = 1;
            }
        }

        if(array_key_exists('radio0_diversity', $this->RadioSettings[0])) {
            if($this->RadioSettings[0]['radio0_diversity'] == true){
                $diversity = 1;
            }
        }

       if(array_key_exists('radio0_ldpc', $this->RadioSettings[0])) {
            if($this->RadioSettings[0]['radio0_ldpc'] == true){
                $ldpc = 1;
            }
        }

        $radio_zero_capab = array();
        //Somehow the read thing reads double..
        $allready_there = array();
        foreach($this->RadioSettings[0]['radio0_ht_capab'] as $c){
            if(!in_array($c,$allready_there)){
                array_push($allready_there,$c);
                array_push($radio_zero_capab,array('name'    => 'ht_capab', 'value'  => $c));
            }
        }
        
        if(array_key_exists('radio0_disable_b', $this->RadioSettings[0])) {
            array_push($radio_zero_capab,array('name'    => 'basic_rate', 'value'  => '6000 9000 12000 18000 24000 36000 48000 54000')); 
        }

		array_push( $wireless,
            array(
                "wifi-device"   => "radio0",
                "options"       => array(
                    'channel'       => intval($channel_0),
                    'disabled'      => intval($this->RadioSettings[0]['radio0_disabled']),
                    'hwmode'        => $hwmode_0,
                    'country'       => $country,
                    'distance'      => intval($this->RadioSettings[0]['radio0_distance']),
                    'htmode'        => $this->RadioSettings[0]['radio0_htmode'],
                    'txpower'       => intval($this->RadioSettings[0]['radio0_txpower']),
                    'beacon_int'    => intval($this->RadioSettings[0]['radio0_beacon_int']),
                    'noscan'        => $noscan,
                    'diversity'     => $diversity,
                    'ldpc'          => $ldpc

                ),
                'lists'          => $radio_zero_capab
      	));


        //FIXME Ability to turn on and off one radio!
		//===== RADIO ONE====
		
		 //Boolean flags
        $diversity1  = 0;
        $noscan1     = 0;
        $ldpc1       = 0;

        if(array_key_exists('radio1_noscan', $this->RadioSettings[1])) {
            if($this->RadioSettings[1]['radio1_noscan'] == true){
                $noscan1 = 1;
            }
        }

        if(array_key_exists('radio1_diversity', $this->RadioSettings[1])) {
            if($this->RadioSettings[1]['radio1_diversity'] == true){
                $diversity1 = 1;
            }
        }

         if(array_key_exists('radio1_ldpc', $this->RadioSettings[1])) {
            if($this->RadioSettings[1]['radio1_ldpc'] == true){
                $ldpc1 = 1;
            }
        }

        $radio_one_capab = array();
        //Somehow the read thing reads double..
        $allready_there = array();
        foreach($this->RadioSettings[1]['radio1_ht_capab'] as $c){
            if(!in_array($c,$allready_there)){
                array_push($allready_there,$c);
                array_push($radio_one_capab,array('name'    => 'ht_capab', 'value'  => $c));
            }
        }
        
        if(array_key_exists('radio1_disable_b', $this->RadioSettings[1])) {
            array_push($radio_one_capab,array('name'    => 'basic_rate', 'value'  => '6000 9000 12000 18000 24000 36000 48000 54000')); 
        }

		array_push( $wireless,
            array(
                "wifi-device"   => "radio1",
                "options"       => array(
                    'channel'       => intval($channel_1),
                    'disabled'      => 0,
                    'hwmode'        => $hwmode_1,
                    'country'       => $country,

                    'disabled'      => intval($this->RadioSettings[1]['radio1_disabled']),
                    'distance'      => intval($this->RadioSettings[1]['radio1_distance']),
                    'htmode'        => $this->RadioSettings[1]['radio1_htmode'],
                    'txpower'       => intval($this->RadioSettings[1]['radio1_txpower']),
                    'beacon_int'    => intval($this->RadioSettings[1]['radio1_beacon_int']),
                    'noscan'        => $noscan1,
                    'diversity'     => $diversity1,
                    'ldpc'          => $ldpc1
                ),
                'lists'          => $radio_one_capab
      	));

        $start_number = 0;
        
		//____ ENTRY POINTS ____
        //Check if we need to add this wireless VAP
        foreach($ap_profile['ApProfileEntry'] as $ap_profile_e){
            $to_all     = false;
            
            $entry_id   = $ap_profile_e['id'];
            
            //Check if it is assigned to an exit point
            foreach($entry_point_data as $epd){
                if($epd['entry_id'] == $entry_id){ //We found our man :-)
                    if(
                        ($ap_profile_e['frequency_band'] == 'both')||
                        ($ap_profile_e['frequency_band'] == $band_0)){
                        $if_name    = $this->_number_to_word($start_number);
                        
                        
                        $base_array_0 = array(
                            "device"        => "radio0",
                            "ifname"        => "$if_name"."0",
                            "mode"          => "ap",
                            "network"       => $epd['network'],
                            "encryption"    => $ap_profile_e['encryption'],
                            "ssid"          => $ap_profile_e['name'],
                            "key"           => $ap_profile_e['special_key'],
                            "hidden"        => $ap_profile_e['hidden'],
                            "isolate"       => $ap_profile_e['isolate'],
                            "auth_server"   => $ap_profile_e['auth_server'],
                            "auth_secret"   => $ap_profile_e['auth_secret']
                        );
                        
                        if($ap_profile_e['chk_maxassoc']){
                            $base_array_0['maxassoc'] = $ap_profile_e['maxassoc'];
                        }
                        
                        if($ap_profile_e['macfilter'] != 'disable'){
                            $base_array_0['macfilter']    = $ap_profile_e['macfilter'];
                            //Replace later
                            $pu_id      = $ap_profile_e['permanent_user_id'];
                            $device     = ClassRegistry::init('Device');
                            $device->contain();
                            $q_d        = $device->find('all',array('conditions' => array('Device.permanent_user_id' => $pu_id)));
                            $mac_list   = array();
                            foreach($q_d as $device){
                                $mac = $device['Device']['name'];
                                $mac = str_replace('-',':',$mac);
                                array_push($mac_list,$mac);
                            }
                            if(count($mac_list)>0){
                                $base_array_0['maclist'] = implode(" ",$mac_list);
                            }
                        }
                    
                        array_push( $wireless,
                            array(
                                "wifi-iface"=> "$if_name",
                                "options"   => $base_array_0
                        ));  
                        $start_number++;
                    }
                        
                    if(
                        ($ap_profile_e['frequency_band'] == 'both')||
                        ($ap_profile_e['frequency_band'] == $band_1)){   
                        $if_name    = $this->_number_to_word($start_number);
                        
                        $base_array_1 = array(
                            "device"        => "radio1",
                            "ifname"        => "$if_name"."0",
                            "mode"          => "ap",
                            "network"       => $epd['network'],
                            "encryption"    => $ap_profile_e['encryption'],
                            "ssid"          => $ap_profile_e['name'],
                            "key"           => $ap_profile_e['special_key'],
                            "hidden"        => $ap_profile_e['hidden'],
                            "isolate"       => $ap_profile_e['isolate'],
                            "auth_server"   => $ap_profile_e['auth_server'],
                            "auth_secret"   => $ap_profile_e['auth_secret']
                        );
                        
                        if($ap_profile_e['chk_maxassoc']){
                            $base_array_1['maxassoc'] = $ap_profile_e['maxassoc'];
                        }
                        
                        if($ap_profile_e['macfilter'] != 'disable'){
                            $base_array_1['macfilter']    = $ap_profile_e['macfilter'];
                            //Replace later
                            $pu_id      = $ap_profile_e['permanent_user_id'];
                            $device     = ClassRegistry::init('Device');
                            $device->contain();
                            $q_d        = $device->find('all',array('conditions' => array('Device.permanent_user_id' => $pu_id)));
                            $mac_list   = array();
                            foreach($q_d as $device){
                                $mac = $device['Device']['name'];
                                $mac = str_replace('-',':',$mac);
                                array_push($mac_list,$mac);
                            }
                            if(count($mac_list)>0){
                                $base_array_1['maclist'] = implode(" ",$mac_list);
                            }
                        }
                    
                        array_push( $wireless,
                            array(
                                "wifi-iface"=> "$if_name",
                                "options"   => $base_array_1
                        ));     
                        $start_number++; 
                    }           
                        
                    break;
                }  
            }
        }
       // print_r($wireless);
        return $wireless;
    }

      
     private function _setWiFiSettings(){
        $ap     = ClassRegistry::init('Ap');
        $ap->contain('ApWifiSetting');
        $q_r    = $ap->findById($this->ApId);

        //There seems to be specific settings for the node
        if($q_r){
            if(count($q_r['ApWifiSetting']) > 0){
                $ht_capab_zero  = array();
                $ht_capab_one   = array();

                foreach($q_r['ApWifiSetting'] as $i){
                    $name  = $i['name'];
                    $value = $i['value'];
                    if(preg_match('/^radio0_/',$name)){
                        $radio_number = 0;
                    }
                    if(preg_match('/^radio1_/',$name)){
                        $radio_number = 1;
                    }

                    if(preg_match('/^radio\d+_ht_capab/',$name)){
                        if($radio_number == 0){
                            array_push($ht_capab_zero,$value);
                        }
                        if($radio_number == 1){
                            array_push($ht_capab_one,$value);
                        }
                    }else{
                        $this->RadioSettings[$radio_number][$name] = $value; 
                    }  
                }
                $this->RadioSettings[0]['radio0_ht_capab'] = $ht_capab_zero;
                $this->RadioSettings[1]['radio1_ht_capab'] = $ht_capab_one;
            }else{
                Configure::load('ApProfiles');
                $hw   = Configure::read('ApProfiles.hardware');
                foreach($hw as $h){
                    $id     = $h['id'];
                    if($this->Hardware == $id){
                        foreach(array_keys($h) as $key){
                            if(preg_match('/^radio\d+_/',$key)){

                                if(preg_match('/^radio0_/',$key)){
                                    $radio_number = 0;
                                }
                                if(preg_match('/^radio1_/',$key)){
                                    $radio_number = 1;
                                }
                                $this->RadioSettings[$radio_number][$key] = $h["$key"]; 
                            }
                        }
                        break;
                    }
                }
            }
        }
    }

    
    
    private function _number_to_word($number) {
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty'
        );

        return($dictionary[$number]);
    }
    
    private function _get_hardware_setting($hw,$setting){
		$return_val = false; //some default
		Configure::load('ApProfiles');
		$ct = Configure::read('ApProfiles.hardware');
        foreach($ct as $i){
            if($i['id'] ==$hw){
				$return_val = $i["$setting"];
				break;
            }
        }
		return $return_val;
	}

    private function _eth_br_for($hw){
		$return_val = 'eth0'; //some default
		Configure::load('ApProfiles');
		$ct = Configure::read('ApProfiles.hardware');
        foreach($ct as $i){
            if($i['id'] ==$hw){
				$return_val = $i['eth_br'];
				break;
            }
        }
		return $return_val;
	}
	
	private function _get_dead_after($ap_profile_id){
		Configure::load('ApProfiles');
		$data 		= Configure::read('common_ap_settings'); //Read the defaults
		$dead_after	= $data['heartbeat_dead_after'];
		$n_s = $this->ApProfileSetting->find('first',array(
            'conditions'    => array(
                'ApProfileSetting.ap_profile_id' => $ap_profile_id
            )
        )); 
        if($n_s){
            $dead_after = $n_s['ApProfileSetting']['heartbeat_dead_after'];
        }
		return $dead_after;
	}
	
	private function _make_hardware_lookup(){
		$hardware = array();
		Configure::load('ApProfiles');        
	    $hw   = Configure::read('ApProfiles.hardware');
	    foreach($hw as $h){
	        $id     = $h['id'];
	        $name   = $h['name']; 
	        $hardware["$id"]= $name;
	    }
		return $hardware;
	}
	
	private function _get_timespan(){

		$hour   = (60*60);
        $day    = $hour*24;
        $week   = $day*7;

		$timespan = 'hour';  //Default
        if(isset($this->request->query['timespan'])){
            $timespan = $this->request->query['timespan'];
        }

        if($timespan == 'hour'){
            //Get entries created modified during the past hour
            $modified = date("Y-m-d H:i:s", time()-$hour);
        }

        if($timespan == 'day'){
            //Get entries created modified during the past hour
            $modified = date("Y-m-d H:i:s", time()-$day);
        }

        if($timespan == 'week'){
            //Get entries created modified during the past hour
            $modified = date("Y-m-d H:i:s", time()-$week);
        }
		return $modified;
	}
    
}
