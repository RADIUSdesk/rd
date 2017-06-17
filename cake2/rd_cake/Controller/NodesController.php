<?php
App::uses('AppController', 'Controller');

class NodesController extends AppController {

    public $name        = 'Nodes';
    public $uses        = array('Mesh','UnknownNode');
    public $components  = array('OpenWrt');
    protected $NodeId   = '';
	protected $Hardware = 'dragino'; //Some default value
	protected $Power	= '10'; //Some default
    protected $RadioSettings = array();


    public function get_config_for_node(){

        if(isset($this->request->query['mac'])){

            $mac    = $this->request->query['mac'];
           // $mac    = 'AC-86-74-10-03-10'; //manual override
            $mac    = strtoupper($mac);
           
            $node   = ClassRegistry::init('Node');
            $mesh   = ClassRegistry::init('Mesh');
            $node->contain();
            $q_r    = $node->findByMac($mac);

			$gw = false;
            if(isset($this->request->query['gateway'])){
                if($this->request->query['gateway'] == 'true'){
                    $gw = true;
                }
            }

            if($q_r){
               // print_r($q_r);
                $mesh_id        = $q_r['Node']['mesh_id'];

                $this->NodeId   = $q_r['Node']['id'];
				$this->Hardware	= $q_r['Node']['hardware'];
				$this->Power	= $q_r['Node']['power'];

                $mesh->contain(
                    'Node.NodeMeshEntry',
                    'Node.NodeMeshExit',
                    'MeshExit.MeshExitMeshEntry',
                    'MeshEntry',
                    'NodeSetting',
                    'MeshSetting',
                    'MeshExit.MeshExitCaptivePortal',
                    'MeshExit.OpenvpnServerClient'
                );
                $m          = $mesh->findById($mesh_id);

                $m['NodeDetail'] = $q_r['Node'];
                //print_r($m);
                

				//Update the last_contact field
				$data = array();
				$data['id'] 			= $this->NodeId;
				$data['last_contact']	= date("Y-m-d H:i:s", time());
				$this->Mesh->Node->save($data);
				
				$this->Mac = $mac;
                
                $json = $this->_build_json($m,$gw);
                $this->set(array(
                    'config_settings'   => $json['config_settings'],
                    'timestamp'         => $json['timestamp'],
                    'success' => true,
                    '_serialize' => array('config_settings','success','timestamp')
                ));

            }else{
                //Write this to an "unknown nodes" table....
				$ip 					= $this->request->clientIp();
				$data 					= array();
				$data['mac'] 			= $mac;
				$data['from_ip']		= $ip;
				$data['gateway']		= $gw;
				$data['last_contact']	= date("Y-m-d H:i:s", time());

				$q_r 	= $this->UnknownNode->find('first',array('conditions' => array('UnknownNode.mac' => $mac)));

                $include_new_server     = false;

				if($q_r){
					$id = $q_r['UnknownNode']['id'];

                    $new_server = $q_r['UnknownNode']['new_server'];
                    if($new_server != ''){
                        
                        $data['new_server_status'] = 'fetched';
                        $include_new_server = true;
                    }

					$data['id'] = $id;
					$this->UnknownNode->save($data);
				}else{
					$data['vendor']  = $this->_lookup_vendor($mac);
					$this->UnknownNode->create();
					$this->UnknownNode->save($data);
				}

                if($include_new_server){
                    $this->set(array(
                        'new_server' => $new_server,
                        'success' => false,
                        '_serialize' => array('new_server','success')
                    ));
                }else{
                     $this->set(array(
                        'error' => "MAC Address: ".$mac." not defined on system",
                        'success' => false,
                        '_serialize' => array('error','success')
                    ));
                }
            }

        }else{

			//We record this in the unknown_nodes table to grab and attach....
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
        if ($this->UnknownNode->save($this->request->data)) {
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


    private function  _build_json($mesh,$gateway = false){

        //Basic structure
        $json = array();
        $json['timestamp']                      = 1; //FIXME replace with the last change timestamp
        $json['config_settings']                = array();
        $json['config_settings']['wireless']    = array();
        $json['config_settings']['network']     = array();
		$json['config_settings']['system']		= array();

        //============ Network ================
        $net_return         = $this->_build_network($mesh,$gateway);
        $json_network       = $net_return[0];
        $json['config_settings']['network'] = $json_network;

        //=========== Wireless ===================
        $entry_data         = $net_return[1];
        $json_wireless      = $this->_build_wireless($mesh,$entry_data);
        $json['config_settings']['wireless'] = $json_wireless;
        
        //========== Gateway or NOT? ======
        if($gateway){   
            $json['config_settings']['gateways']        = $net_return[2]; //Gateways
            $json['config_settings']['captive_portals'] = $net_return[3]; //Captive portals
                        
            $openvpn_bridges                            = $this->_build_openvpn_bridges($net_return[4]);
            $json['config_settings']['openvpn_bridges'] = $openvpn_bridges; //Openvpn Bridges
        }

		//======== System related settings ======
		$system_data 		= $this->_build_system($mesh);
		$json['config_settings']['system'] = $system_data;

		//====== Batman-adv specific config settings ======
		Configure::load('MESHdesk');
        $batman_adv       = Configure::read('mesh_settings'); //Read the defaults
		if($mesh['MeshSetting']['id']!=null){
			unset($mesh['MeshSetting']['id']);
			unset($mesh['MeshSetting']['mesh_id']);
			unset($mesh['MeshSetting']['created']);
			unset($mesh['MeshSetting']['modified']);
			$batman_adv = $mesh['MeshSetting'];
		}
		$json['config_settings']['batman_adv'] = $batman_adv;


		//=====What if is a MP2 -> do we have settings for the mesh_potato?
		if(
			($this->Hardware == 'mp2_phone')||
			($this->Hardware == 'mp2_basic')
		){
			$mp_data 								= $this->_build_mesh_potato($mesh);
			$json['config_settings']['mesh_potato'] = $mp_data;
		}

        return $json; 
    }
    
    private function _build_openvpn_bridges($openvpn_list){
        $openvpn_bridges = array();    
        foreach($openvpn_list as $o){
        
            $br                 = array(); 
            $br['interface']    = $o['interface'];
            $br['up']           = "mesh_".$this->Mac."\n".md5("mesh_".$this->Mac)."\n";
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
    

	private function _build_system($mesh){
		//Get the root password
		$ss = array();
		if($mesh['NodeSetting']['password_hash'] != ''){
			$ss['password_hash'] 		= $mesh['NodeSetting']['password_hash'];
			$ss['heartbeat_interval']	= $mesh['NodeSetting']['heartbeat_interval'];
			$ss['heartbeat_dead_after']	= $mesh['NodeSetting']['heartbeat_dead_after'];
		}else{
			Configure::load('MESHdesk');
			$data = Configure::read('common_node_settings'); //Read the defaults
			$ss['password_hash'] 		= $data['password_hash'];
			$ss['heartbeat_interval']	= $data['heartbeat_interval'];
			$ss['heartbeat_dead_after']	= $data['heartbeat_dead_after'];
		}

       
        //Timezone
        if($mesh['NodeSetting']['tz_value'] != ''){
            $ss['timezone']             = $mesh['NodeSetting']['tz_value'];
        }else{
            Configure::load('MESHdesk');
			$data = Configure::read('common_node_settings'); //Read the defaults
            $ss['timezone']             = $data['tz_value'];
        }

        //Gateway specifics
        if($mesh['NodeSetting']['gw_dhcp_timeout'] != ''){
            $ss['gw_dhcp_timeout']          = $mesh['NodeSetting']['gw_dhcp_timeout'];
            $ss['gw_use_previous']          = $mesh['NodeSetting']['gw_use_previous'];
            $ss['gw_auto_reboot']           = $mesh['NodeSetting']['gw_auto_reboot'];
            $ss['gw_auto_reboot_time']      = $mesh['NodeSetting']['gw_auto_reboot_time']; 
        }else{
            Configure::load('MESHdesk');
			$data = Configure::read('common_node_settings'); //Read the defaults
            $ss['gw_dhcp_timeout']          = $data['gw_dhcp_timeout'];
            $ss['gw_use_previous']          = $data['gw_use_previous'];
            $ss['gw_auto_reboot']           = $data['gw_auto_reboot'];
            $ss['gw_auto_reboot_time']      = $data['gw_auto_reboot_time'];
        }

		foreach($mesh['Node'] as $n){
			if($n['id'] == $this->NodeId){
				$ss['hostname'] = $n['name'];
				break;
			}
		}
		//print_r($mesh);
		return $ss;
	}

    private function _build_network($mesh,$gateway = false){

        $network 				= array();
        $nat_data				= array();
        $captive_portal_data 	= array();
        $openvpn_bridge_data    = array();
		$include_lan_dhcp 		= true;


//=================================

        //loopback if
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

//========================
		//We add a new feature - we can specify for NON Gateway nodes to which their LAN port should be connected with
		if($mesh['NodeSetting']['eth_br_chk'] != ''){
			$eth_br_chk 		= $mesh['NodeSetting']['eth_br_chk'];
			$eth_br_with	    = $mesh['NodeSetting']['eth_br_with'];
			$eth_br_for_all	    = $mesh['NodeSetting']['eth_br_for_all'];
		}else{
			Configure::load('MESHdesk');
			$c_n_s 				= Configure::read('common_node_settings'); //Read the defaults
			$eth_br_chk 		= $c_n_s['eth_br_chk'];
			$eth_br_with	    = $c_n_s['eth_br_with'];
			$eth_br_for_all	    = $c_n_s['eth_br_for_all'];
		}

		$lan_bridge_flag 	= false;

		//If we need to bridge and it is with the LAN (the easiest)
		if(
			($eth_br_chk)&&
			($eth_br_with == 0)
		){
			$lan_bridge_flag = true;
		}

        //LAN
		$br_int = $this->_eth_br_for($this->Hardware);
		if($lan_bridge_flag){
			$br_int = "$br_int bat0.100";
		}

		//If we need to bridge and it is NOT with the LAN (more involved)
		if(
			($eth_br_chk)&&
			($eth_br_with != 0)&&
			($gateway == false) //Only on non-gw nodes
		){
			$include_lan_dhcp = false; //This case we do not include the lan dhcp bridge
		}
//==================

		if($include_lan_dhcp){

			//We need to se the non-gw nodes to have:
			//1.) DNS Masq must not be running
			//2.) The LAN must now have DHCP client since this will trigger the setup script as soon as the interface get an IP
			//3.) This will cause a perpetiual loop since it will kick off the setup script and reconfigure itself.
			//4.) The gateway however still needs to maintain its dhcp client status.
			$proto = 'dhcp';
			if(($lan_bridge_flag)&&($gateway == false)){
				$proto = 'static';
			}

		    array_push( $network,
		        array(
		            "interface"    => "lan",
		            "options"   => array(
		                "ifname"        => "$br_int", 
		                "type"          => "bridge",
		                "proto"         => "$proto"
		           )
		   	));
		}

		//Add an interface called b to list the batman interface
		array_push( $network,
            array(
                "interface"    => "b",
                "options"   => array(
                    "ifname"    => "bat0"
               )
            ));
		
        //Mesh
        array_push( $network,
            array(
                "interface"    => "mesh",
                "options"   => array(
                    //"ifname"    => "mesh0", //This thing caused major problems on Barrier Breaker
                    "mtu"       => "1560",
                    "proto"     => "batadv",
                    "mesh"      => "bat0"
               )
            ));

        $ip = $mesh['NodeDetail']['ip'];

        //Admin interface
        array_push($network,
            array(
                "interface"    => "one",
                "options"   => array(
                    "ifname"    => "bat0.1",
                    "proto"     => "static",
                    "ipaddr"    => $ip,
                    "netmask"   => "255.255.255.0",
                    "type"      => "bridge"
               )
            ));

		//***With its VLAN***
		 array_push($network,
            array(
                "interface"    => "bat_vlan_one",
                "options"   => array(
                    "ifname"    	=> "bat0.1",
                    "proto"     	=> "batadv_vlan",
                    'ap_isolation' 	=> '0'
               )
            ));

//================================

        //Now we will loop all the defined exits **that has entries assigned** to them and add them as bridges as we loop. 
        //The members of these bridges will be determined by which entries are assigned to them and specified
        //in the wireless configuration file

        $start_number = 2;

        //We create a data structure which will be used to add the entry points and bridge them with
        //The correct network defined here
        $entry_point_data = array();

        

     //   print_r($mesh['MeshExit']);

        //Add the auto-attach entry points
        foreach($mesh['MeshExit'] as $me){
        
            $has_entries_attached   = false;
            $if_name                = 'ex_'.$this->_number_to_word($start_number);
            $exit_id                = $me['id'];
            $type                   = $me['type'];
            $vlan                   = $me['vlan'];
            $openvpn_server_id      = $me['openvpn_server_id'];

            //This is used to fetch info eventually about the entry points
            if(count($me['MeshExitMeshEntry']) > 0){
                $has_entries_attached = true;
                foreach($me['MeshExitMeshEntry'] as $entry){
                    if(($type == 'bridge')&&($gateway)){ //The gateway needs the entry points to be bridged to the LAN
                        array_push($entry_point_data,array('network' => 'lan','entry_id' => $entry['mesh_entry_id']));
                    }else{
                        array_push($entry_point_data,array('network' => $if_name,'entry_id' => $entry['mesh_entry_id']));
                    }
                }
            }
            
            if($has_entries_attached == true){

				
                //=======================================
                //========= GATEWAY NODES ===============
                //=======================================
                $captive_portal_count = 1;

                if(($type == 'tagged_bridge')&&($gateway)){

					$br_int = $this->_eth_br_for($this->Hardware);
					if(preg_match('/eth1/', $br_int)){	//If it has two add both
                    	$interfaces =  "bat0.".$start_number." eth0.".$vlan." eth1.".$vlan;
					}else{
						$interfaces =  "bat0.".$start_number." eth0.".$vlan; //only one
					}
                    array_push($network,
                        array(
                            "interface"    => "$if_name",
                            "options"   => array(
                                "ifname"    => $interfaces,
                                "type"      => "bridge"
                        ))
                    );

					//***With its VLAN***
					$nr = $this->_number_to_word($start_number);
					array_push($network,
						array(
							"interface"    => "bat_vlan_".$nr,
							"options"   => array(
							    "ifname"    	=> "bat0.".$start_number,
							    "proto"     	=> "batadv_vlan",
							    'ap_isolation' 	=> '0'
						   )
					));


                    $start_number++;
                    continue;   //We don't car about the other if's
                }

                if(($type == 'nat')&&($gateway)){

                    $interfaces =  "bat0.".$start_number;
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

					//***With its VLAN***
					$nr = $this->_number_to_word($start_number);
					array_push($network,
						array(
							"interface"    => "bat_vlan_".$nr,
							"options"   => array(
							    "ifname"    	=> $interfaces,
							    "proto"     	=> "batadv_vlan",
							    'ap_isolation' 	=> '0'
						   )
					));


                    //Push the nat data
                    array_push($nat_data,$if_name);
                    $start_number++;
                    continue; //We dont care about the other if's
                }

                if(($type=='bridge')&&($gateway)){
                    $current_interfaces = $network[1]['options']['ifname'];
                    $interfaces =  "bat0.".$start_number;
                    $network[1]['options']['ifname'] = $current_interfaces." ".$interfaces;
                    $start_number++;
                    continue; //We dont care about the other if's
                }

                if(($type == 'captive_portal')&&($gateway)){
                
                    //---WIP Start---
                    if($me['MeshExitCaptivePortal']['dnsdesk'] == true){
                        $if_ip      = "10.$captive_portal_count.0.2";
                    }
                    $captive_portal_count++; //Up it for the next one
                    //---WIP END---

                    //Add the captive portal's detail
                    if($type =='captive_portal'){
                        $a = $me['MeshExitCaptivePortal'];
                        $a['hslan_if'] = 'br-'.$if_name;
                        $a['network']  = $if_name;
                        
                        //---WIP Start---
                        if($me['MeshExitCaptivePortal']['dnsdesk'] == true){
                            $a['dns1']      = $if_ip;
                            //Also sent along the upstream DNS Server to use
                            $a['upstream_dns1'] = Configure::read('dnsfilter.dns1'); //Read the defaults
                            $a['upstream_dns2'] = Configure::read('dnsfilter.dns2'); //Read the defaults
                        }
                        //---WIP END---
                        
                        array_push($captive_portal_data,$a);             
                    }

                    $interfaces =  "bat0.".$start_number;
                                      
                    array_push($network,
                        array(
                            "interface"    => "$if_name",
                            "options"   => array(
                                "ifname"    => $interfaces,
                                "type"      => "bridge",       
                        ))
                    );

					//***With its VLAN***
					$nr = $this->_number_to_word($start_number);
					array_push($network,
						array(
							"interface"    => "bat_vlan_".$nr,
							"options"   => array(
							    "ifname"    	=> $interfaces,
							    "proto"     	=> "batadv_vlan",
							    'ap_isolation' 	=> '0'
						   )
					));
                    $start_number++;
                    continue; //We dont care about the other if's
                }
                
                //___ OpenVPN Bridge ________
                if(($type == 'openvpn_bridge')&&($gateway)){

                    //Add the OpenvpnServer detail
                    if($type =='openvpn_bridge'){
                    
                        $a              = $me['OpenvpnServerClient'];
                        $a['bridge']    = 'br-'.$if_name;
                        $a['interface'] = $if_name;
                        
                        //Get the info for the OpenvpnServer
                        $openvpn_server = ClassRegistry::init('OpenvpnServer');
                        $q_s            = $openvpn_server->findById($me['OpenvpnServerClient']['openvpn_server_id']);
                        
                        $a['protocol']  = $q_s['OpenvpnServer']['protocol'];
                        $a['ip_address']= $q_s['OpenvpnServer']['ip_address'];
                        $a['port']      = $q_s['OpenvpnServer']['port'];
                        $a['vpn_mask']  = $q_s['OpenvpnServer']['vpn_mask'];
                        $a['ca_crt']    = $q_s['OpenvpnServer']['ca_crt'];   
                        
                        $a['config_preset']        = $q_s['OpenvpnServer']['config_preset'];  
                        $a['vpn_gateway_address']  = $q_s['OpenvpnServer']['vpn_gateway_address'];
                        $a['vpn_client_id']        = $me['OpenvpnServerClient']['id'];                      
                        array_push($openvpn_bridge_data,$a);             
                    }
                    $interfaces =  "bat0.".$start_number;
                    array_push($network,
                        array(
                            "interface"    => "$if_name",
                            "options"   => array(
                                "ifname"    => $interfaces,
                                "type"      => "bridge",
                                'ipaddr'    => $me['OpenvpnServerClient']['ip_address'],
                                'netmask'   => $a['vpn_mask'],
                                'proto'     => 'static'
                                
                        ))
                    );

					//***With its VLAN***
					$nr = $this->_number_to_word($start_number);
					array_push($network,
						array(
							"interface"    => "bat_vlan_".$nr,
							"options"   => array(
							    "ifname"    	=> $interfaces,
							    "proto"     	=> "batadv_vlan",
							    'ap_isolation' 	=> '0'
						   )
					));
                    $start_number++;
                    continue; //We dont care about the other if's
                }


                //=======================================
                //==== STANDARD NODES ===================
                //=======================================

                if(($type == 'nat')||($type == 'tagged_bridge')||($type == 'bridge')||($type =='captive_portal')||($type =='openvpn_bridge')){
                    $interfaces =  "bat0.".$start_number;

					//===Check if this standard node has an ethernet bridge that has to be included here (NON LAN bridge)
					if(
						($eth_br_chk)&& 			//Eth br specified
						($eth_br_with == $exit_id) 	//Only if the current one is the one to be bridged
					){
						$interfaces = "$br_int $interfaces";
					}

                    array_push($network,
                        array(
                            "interface"    => "$if_name",
                            "options"   => array(
                                "ifname"    => $interfaces,
                                "type"      => "bridge" 
                        ))
                    );

					//***With its VLAN***
					$nr = $this->_number_to_word($start_number);
					array_push($network,
						array(
							"interface"    => "bat_vlan_".$nr,
							"options"   => array(
							    "ifname"    	=> $interfaces,
							    "proto"     	=> "batadv_vlan",
							    'ap_isolation' 	=> '0'
						   )
					));
                    $start_number++;
                    continue; //We dont care about the other if's
                }
            }
        }
        return array($network,$entry_point_data,$nat_data,$captive_portal_data,$openvpn_bridge_data);
    }


	private function _build_wireless($mesh,$entry_point_data){

        //$wireless = array();

		//Configure::load('MESHdesk');
		//$client_key = Configure::read('MESHdesk.client_key');

        //First get the WiFi settings wether default or specific
        $this->_setWiFiSettings();

		//Determine the radio count and configure accordingly
		$radios   = $this->_get_hardware_setting($this->Hardware,'radios');
		if($radios == 2){
			$wireless = $this->_build_dual_radio_wireless($mesh,$entry_point_data);
			return $wireless;
		}

        if($radios == 1){
            $wireless = $this->_build_single_radio_wireless($mesh,$entry_point_data);
			return $wireless;
        }
    }

    private function _setWiFiSettings(){
        //First we chack if the node had the Wfi Settings
        $node   = ClassRegistry::init('Node');
        $node->contain('NodeWifiSetting');
        $q_r    = $node->findById($this->NodeId);

        //There seems to be specific settings for the node
        if($q_r){
            //print_r($q_r);
            if(count($q_r['NodeWifiSetting']) > 0){
                $ht_capab_zero  = array();
                $ht_capab_one   = array();

                foreach($q_r['NodeWifiSetting'] as $i){
                    $name  = $i['name'];
                    $value = $i['value'];
                    
                    if($name == 'device_type'){
                        continue;
                    }
                    
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
                Configure::load('MESHdesk');
                $hw   = Configure::read('hardware');
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
        //print_r($this->RadioSettings);
    }

    private function _build_single_radio_wireless($mesh,$entry_point_data){
    
        $wireless = array();
        
        if($mesh['NodeSetting']['client_key']!='') {        
            $client_key = $mesh['NodeSetting']['client_key'];
        }else{
            Configure::load('MESHdesk');
		    $client_key = Configure::read('common_node_settings.client_key');
        }

        //Get the channel
        if($mesh['NodeSetting']['two_chan']!='') {        
            $channel    = $mesh['NodeSetting']['two_chan'];
        }else{
            Configure::load('MESHdesk');
		    $channel = Configure::read('common_node_settings.two_chan');
        }

	
		//Hardware mode for 5G
		$hwmode		= '11g';	//Sane default
		$hw_temp    = $this->_get_hardware_setting($this->Hardware,'hwmode');
		if($hw_temp){
			$hwmode	= $hw_temp;
		}

		//Channel (if 5)
		if($this->_get_hardware_setting($this->Hardware,'five')){		
		    if($mesh['NodeSetting']['five_chan']!='') {        
                $channel    = $mesh['NodeSetting']['five_chan'];
            }else{
                Configure::load('MESHdesk');
		        $channel = Configure::read('common_node_settings.five_chan');
            }	
		}

        //Country
        if($mesh['NodeSetting']['country'] != ''){
            $country  = $mesh['NodeSetting']['country'];
		}else{
			Configure::load('MESHdesk');
			$data       = Configure::read('common_node_settings'); //Read the defaults
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
                        'disabled'      => 0,
                        'hwmode'        => $hwmode,
                        'htmode'        => $this->RadioSettings[0]['radio0_htmode'],
                        
                        
                        'country'       => $country,
                        'distance'      => intval($this->RadioSettings[0]['radio0_distance']), 
                        'txpower'       => intval($this->RadioSettings[0]['radio0_txpower']),
                        'beacon_int'    => intval($this->RadioSettings[0]['radio0_beacon_int']),
                        'noscan'        => $noscan,
                        'diversity'     => $diversity,
                        'ldpc'          => $ldpc
                    ),
                    'lists'          => $radio_zero_capab
                ));




        //Get the mesh's BSSID and SSID
        $bssid      = $mesh['Mesh']['bssid'];
        $ssid       = $mesh['Mesh']['ssid'];
        
        //Get the connection type (IBSS or mesh_point);
        if($mesh['MeshSetting']['id']!=null){  
            $connectivity   = $mesh['MeshSetting']['connectivity'];
            $encryption     = $mesh['MeshSetting']['encryption'];
            $encryption_key = $mesh['MeshSetting']['encryption_key'];
        }else{
            Configure::load('MESHdesk');
		    $connectivity   = Configure::read('mesh_settings.connectivity');
		    $encryption     = Configure::read('mesh_settings.encryption');
            $encryption_key = Configure::read('mesh_settings.encryption_key');
        }

        //Add the ad-hoc if for mesh
        $zero = $this->_number_to_word(0);
        
        if($connectivity == 'IBSS'){
            array_push( $wireless,
                    array(
                        "wifi-iface"   => "$zero",
                        "options"       => array(
                            "device"        => "radio0",
                            "ifname"        => "mesh0",
                            "network"       => "mesh",
                            "mode"          => "adhoc",
                            "ssid"          => $ssid,
                            "bssid"         => $bssid
                        )
                    ));
        }
         
        if(($connectivity == 'mesh_point')&&(!$encryption)){
            array_push( $wireless,
                    array(
                        "wifi-iface"   => "$zero",
                        "options"       => array(
                            "device"        => "radio0",
                            "ifname"        => "mesh0",
                            "network"       => "mesh",
                            "mode"          => "mesh",
                            "mesh_id"       => $ssid,
                            "mcast_rate"    => 18000,
                            "disabled"      => 0,
                            "mesh_ttl"      => 1,
                            "mesh_fwding"   => 0,
                            "encryption"    => 'none'
                        )
                    ));
        }
        
        if(($connectivity == 'mesh_point')&&($encryption)){
            array_push( $wireless,
                    array(
                        "wifi-iface"   => "$zero",
                        "options"       => array(
                            "device"        => "radio0",
                            "ifname"        => "mesh0",
                            "network"       => "mesh",
                            "mode"          => "mesh",
                            "mesh_id"       => $ssid,
                            "mcast_rate"    => 18000,
                            "disabled"      => 0,
                            "mesh_ttl"      => 1,
                            "mesh_fwding"   => 0,
                            "encryption"    => 'psk2/aes',
                            "key"           => $encryption_key
                        )
                    ));
        }
        
        //Add the hidden config VAP
        $one = $this->_number_to_word(1);
        array_push( $wireless,
                array(
                    "wifi-iface"    => "$one",
                    "options"   => array(
                        "device"        => "radio0",
                        "ifname"        => "$one"."0",
                        "mode"          => "ap",
                        "encryption"    => "psk-mixed",
                        "network"       => $one,
                        "ssid"          => "meshdesk_config",
                        "key"           => $client_key,
                        "hidden"        => "1"
                   )
                ));

        $start_number = 2;

        //Check if we need to add this wireless VAP
        foreach($mesh['MeshEntry'] as $me){
        
            $to_all     = false;
            $if_name    = $this->_number_to_word($start_number);
            $entry_id   = $me['id'];
            $start_number++;
            if($me['apply_to_all'] == 1){

                //Check if it is assigned to an exit point
                foreach($entry_point_data as $epd){
                  //  print_r($epd);
                    if($epd['entry_id'] == $entry_id){ //We found our man :-)
                    
                        $base_array = array(
                            "device"        => "radio0",
                            "ifname"        => "$if_name"."0",
                            "mode"          => "ap",
                            "network"       => $epd['network'],
                            "encryption"    => $me['encryption'],
                            "ssid"          => $me['name'],
                            "key"           => $me['special_key'],
                            "hidden"        => $me['hidden'],
                            "isolate"       => $me['isolate'],
                            "auth_server"   => $me['auth_server'],
                            "auth_secret"   => $me['auth_secret']
                        );
                        
                        if($me['chk_maxassoc']){
                            $base_array['maxassoc'] = $me['maxassoc'];
                        }
                        
                        if($me['macfilter'] != 'disable'){
                            $base_array['macfilter']    = $me['macfilter'];
                            //Replace later
                            $pu_id      = $me['permanent_user_id'];
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
                                "wifi-iface"=> "$if_name",
                                "options"   => $base_array
                        ));    
                        break;
                    }
                }
            }else{
                //Check if this entry point is statically attached to the node
               // print_r($mesh['Node']);
                foreach($mesh['Node'] as $node){
                    if($node['id'] == $this->NodeId){   //We have our node
                        foreach($node['NodeMeshEntry'] as $nme){
                            if($nme['mesh_entry_id'] == $entry_id){
                                //Check if it is assigned to an exit point
                                foreach($entry_point_data as $epd){
                                    //We have a hit; we have to  add this entry
                                    if($epd['entry_id'] == $entry_id){ //We found our man :-)
                                    
                                        $base_array = array(
                                            "device"        => "radio0",
                                            "ifname"        => "$if_name"."0",
                                            "mode"          => "ap",
                                            "network"       => $epd['network'],
                                            "encryption"    => $me['encryption'],
                                            "ssid"          => $me['name'],
                                            "key"           => $me['special_key'],
                                            "hidden"        => $me['hidden'],
                                            "isolate"       => $me['isolate'],
                                            "auth_server"   => $me['auth_server'],
                                            "auth_secret"   => $me['auth_secret']
                                        );
                                        
                                        if($me['chk_maxassoc']){
                                            $base_array['maxassoc'] = $me['maxassoc'];
                                        }
                                        
                                        if($me['macfilter'] != 'disable'){
                                            $base_array['macfilter']    = $me['macfilter'];
                                            //Replace later
                                            $pu_id      = $me['permanent_user_id'];
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
                                                "wifi-iface"=> "$if_name",
                                                "options"   => $base_array
                                        ));    
                                               
                                        break;
                                    }
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }
       // print_r($wireless);
        return $wireless;
    }

	private function _build_dual_radio_wireless($mesh,$entry_point_data){

        $wireless = array();

		if($mesh['NodeSetting']['client_key']!='') {        
            $client_key = $mesh['NodeSetting']['client_key'];
        }else{
            Configure::load('MESHdesk');
		    $client_key = Configure::read('common_node_settings.client_key');
        }

        //Get the channel that the mesh needs to be on
        
        //Get the channel
        if($mesh['NodeSetting']['two_chan']!='') {        
            $mesh_channel_two   = $mesh['NodeSetting']['two_chan'];
        }else{
            Configure::load('MESHdesk');
		    $mesh_channel_two   = Configure::read('common_node_settings.two_chan');
        }
        
        if($mesh['NodeSetting']['five_chan']!='') {        
            $mesh_channel_five   = $mesh['NodeSetting']['five_chan'];
        }else{
            Configure::load('MESHdesk');
		    $mesh_channel_five   = Configure::read('common_node_settings.five_chan');
        }
        

        //Get the country setting
        if($mesh['NodeSetting']['country'] != ''){
            $country  = $mesh['NodeSetting']['country'];
		}else{
			Configure::load('MESHdesk');
			$data       = Configure::read('common_node_settings'); //Read the defaults
            $country    = $data['country'];
		}

		//===== RADIO ZERO====
		//Check which of the two is active
		if($mesh['NodeDetail']['radio0_enable'] == 0){
			$r0_disabled = '1';
		}else{
			$r0_disabled = '0';
		}

		//-Determine the channel-
		if($mesh['NodeDetail']['radio0_mesh'] == 0){ //No mesh - use manual channel
			if($mesh['NodeDetail']['radio0_band'] == '24'){
				$r0_channel =  $mesh['NodeDetail']['radio0_two_chan'];
			}else{
				$r0_channel =  $mesh['NodeDetail']['radio0_five_chan'];
			}
		}else{
			if($mesh['NodeDetail']['radio0_band'] == '24'){
				$r0_channel =  $mesh_channel_two;
			}else{
				$r0_channel =  $mesh_channel_five;
			} 
		}

		//-Determine the hwmode 
		$r0_hwmode 		= $this->_get_hardware_setting($this->Hardware,'hwmode');

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

        //FIXME 
/* 
        //802.11AC experiment
        array_push( $wireless,
            array(
                "wifi-device"   => "radio0",
                "options"       => array(
                    'channel'       => intval($r0_channel),
                    'disabled'      => $r0_disabled,
                    'hwmode'        => $r0_hwmode,
                    'htmode'        => $this->RadioSettings[0]['radio0_htmode']
                ),
                'lists'          => array()
      	));
*/

		array_push( $wireless,
            array(
                "wifi-device"   => "radio0",
                "options"       => array(
                    'channel'       => intval($r0_channel),
                    'disabled'      => $r0_disabled,
                    'hwmode'        => $r0_hwmode,
                    'htmode'        => $this->RadioSettings[0]['radio0_htmode'],
                    
                    
                    'country'       => $country,
                    'distance'      => intval($this->RadioSettings[0]['radio0_distance']), 
                    'txpower'       => intval($this->RadioSettings[0]['radio0_txpower']),
                    'beacon_int'    => intval($this->RadioSettings[0]['radio0_beacon_int']),
                    'noscan'        => $noscan,
                    'diversity'     => $diversity,
                    'ldpc'          => $ldpc

                ),
                'lists'          => $radio_zero_capab
      	));

		//===== RADIO ONE====
		if($mesh['NodeDetail']['radio1_enable'] == 0){
			$r1_disabled = '1';
		}else{
			$r1_disabled = '0';
		}

		//-Determine the channel-
		if($mesh['NodeDetail']['radio1_mesh'] == 0){ //No mesh - use manual channel
			if($mesh['NodeDetail']['radio1_band'] == '24'){
				$r1_channel =  $mesh['NodeDetail']['radio1_two_chan'];
			}else{
				$r1_channel =  $mesh['NodeDetail']['radio1_five_chan'];
			}
		}else{
			if($mesh['NodeDetail']['radio1_band'] == '24'){
				$r1_channel =  $mesh_channel_two;
			}else{
				$r1_channel =  $mesh_channel_five;
			} 
		}

		//-Determine the hwmode
		$r1_hwmode 		= $this->_get_hardware_setting($this->Hardware,'hwmode1');

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
                    'channel'       => intval($r1_channel),
                    'disabled'      => $r1_disabled,
                    'hwmode'        => $r1_hwmode,
                    'htmode'        => $this->RadioSettings[1]['radio1_htmode'],
                    
                    'country'       => $country,
                    'distance'      => intval($this->RadioSettings[1]['radio1_distance']),
                    'txpower'       => intval($this->RadioSettings[1]['radio1_txpower']),
                    'beacon_int'    => intval($this->RadioSettings[1]['radio1_beacon_int']),
                    'noscan'        => $noscan1,
                    'diversity'     => $diversity1,
                    'ldpc'          => $ldpc1
                ),
                'lists'          => $radio_one_capab
      	));

		//_____ MESH _______
        //Get the mesh's BSSID and SSID
        $bssid      = $mesh['Mesh']['bssid'];
        $ssid       = $mesh['Mesh']['ssid'];
        
        //Get the connection type (IBSS or mesh_point);
        if($mesh['MeshSetting']['id']!=null){  
            $connectivity   = $mesh['MeshSetting']['connectivity'];
            $encryption     = $mesh['MeshSetting']['encryption'];
            $encryption_key = $mesh['MeshSetting']['encryption_key'];
        }else{
            Configure::load('MESHdesk');
		    $connectivity   = Configure::read('mesh_settings.connectivity');
		    $encryption     = Configure::read('mesh_settings.encryption');
            $encryption_key = Configure::read('mesh_settings.encryption_key');
        }

		if(($mesh['NodeDetail']['radio0_enable'] == 1)&&($mesh['NodeDetail']['radio0_mesh'] == 1)){
		    $zero = $this->_number_to_word(0);	    
		    if($connectivity == 'IBSS'){
                array_push( $wireless,
                        array(
                            "wifi-iface"   => "$zero",
                            "options"       => array(
                                "device"        => "radio0",
                                "ifname"        => "mesh0",
                                "network"       => "mesh",
                                "mode"          => "adhoc",
                                "ssid"          => $ssid,
                                "bssid"         => $bssid
                            )
                        ));
            }
         
            if(($connectivity == 'mesh_point')&&(!$encryption)){
                array_push( $wireless,
                        array(
                            "wifi-iface"   => "$zero",
                            "options"       => array(
                                "device"        => "radio0",
                                "ifname"        => "mesh0",
                                "network"       => "mesh",
                                "mode"          => "mesh",
                                "mesh_id"       => $ssid,
                                "mcast_rate"    => 18000,
                                "disabled"      => 0,
                                "mesh_ttl"      => 1,
                                "mesh_fwding"   => 0,
                                "encryption"    => 'none'
                            )
                        ));
            }
            
            if(($connectivity == 'mesh_point')&&($encryption)){
                array_push( $wireless,
                        array(
                            "wifi-iface"   => "$zero",
                            "options"       => array(
                                "device"        => "radio0",
                                "ifname"        => "mesh0",
                                "network"       => "mesh",
                                "mode"          => "mesh",
                                "mesh_id"       => $ssid,
                                "mcast_rate"    => 18000,
                                "disabled"      => 0,
                                "mesh_ttl"      => 1,
                                "mesh_fwding"   => 0,
                                "encryption"    => 'psk2/aes',
                                "key"           => $encryption_key
                            )
                        ));
            }
		}

		if(($mesh['NodeDetail']['radio1_enable'] == 1)&&($mesh['NodeDetail']['radio1_mesh'] == 1)){
		    $zero = $this->_number_to_word(0);
			$zero = $zero."_1";
			if($connectivity == 'IBSS'){
                array_push( $wireless,
                        array(
                            "wifi-iface"   => "$zero",
                            "options"       => array(
                                "device"        => "radio1",
                                "ifname"        => "mesh1",
                                "network"       => "mesh",
                                "mode"          => "adhoc",
                                "ssid"          => $ssid,
                                "bssid"         => $bssid
                            )
                        ));
            }
         
            if(($connectivity == 'mesh_point')&&(!$encryption)){
                array_push( $wireless,
                        array(
                            "wifi-iface"   => "$zero",
                            "options"       => array(
                                "device"        => "radio1",
                                "ifname"        => "mesh1",
                                "network"       => "mesh",
                                "mode"          => "mesh",
                                "mesh_id"       => $ssid,
                                "mcast_rate"    => 18000,
                                "disabled"      => 0,
                                "mesh_ttl"      => 1,
                                "mesh_fwding"   => 0,
                                "encryption"    => 'none'
                            )
                        ));
            }
            
            if(($connectivity == 'mesh_point')&&($encryption)){
                array_push( $wireless,
                        array(
                            "wifi-iface"   => "$zero",
                            "options"       => array(
                                "device"        => "radio1",
                                "ifname"        => "mesh1",
                                "network"       => "mesh",
                                "mode"          => "mesh",
                                "mesh_id"       => $ssid,
                                "mcast_rate"    => 18000,
                                "disabled"      => 0,
                                "mesh_ttl"      => 1,
                                "mesh_fwding"   => 0,
                                "encryption"    => 'psk2/aes',
                                "key"           => $encryption_key
                            )
                        ));
            }
		}

		//____ HIDDEN VAP ______

      	if(($mesh['NodeDetail']['radio0_enable'] == 1)&&($mesh['NodeDetail']['radio0_mesh'] == 1)){
		    $one = $this->_number_to_word(1);
		    
		    //The ATH10K does not like this VAP so we try to avoid it on 5G
		    //Only if the other radio is enabled but without mesh
		    if(
		        ($mesh['NodeDetail']['radio0_band'] == 5)&&
		        ($mesh['NodeDetail']['radio1_enable'] == 1)&&
		        ($mesh['NodeDetail']['radio1_mesh'] !== 1)
		    ){ 
                array_push( $wireless,
                    array(
                        "wifi-iface"    => "$one",
                        "options"   => array(
                            "device"        => "radio1",
                            "ifname"        => "$one"."1",
                            "mode"          => "ap",
                            "encryption"    => "psk-mixed",
                            "network"       => $one,
                            "ssid"          => "meshdesk_config",
                            "key"           => $client_key,
                            "hidden"        => "1"
                       )
                ));
		    
		    }else{		        
	            array_push( $wireless,
	                array(
	                    "wifi-iface"    => "$one",
	                    "options"   => array(
	                        "device"        => "radio0",
	                        "ifname"        => "$one"."0",
	                        "mode"          => "ap",
	                        "encryption"    => "psk-mixed",
	                        "network"       => $one,
	                        "ssid"          => "meshdesk_config",
	                        "key"           => $client_key,
	                        "hidden"        => "1"
	                   )
	                ));
		    }    
		}

		if(($mesh['NodeDetail']['radio1_enable'] == 1)&&($mesh['NodeDetail']['radio1_mesh'] == 1)){
		    $one = $this->_number_to_word(1);
		    
		    //The ATH10K does not like this VAP so we try to avoid it on 5G
		    //Only if the other radio is enabled but without mesh
		    if(
		        ($mesh['NodeDetail']['radio1_band'] == 5)&&
		        ($mesh['NodeDetail']['radio0_enable'] == 1)&&
		        ($mesh['NodeDetail']['radio0_mesh'] !== 1)
		    ){
		    
		        array_push( $wireless,
	                array(
	                    "wifi-iface"    => "$one"."_1",
	                    "options"   => array(
	                        "device"        => "radio0",
	                        "ifname"        => "$one"."0",
	                        "mode"          => "ap",
	                        "encryption"    => "psk-mixed",
	                        "network"       => $one,
	                        "ssid"          => "meshdesk_config",
	                        "key"           => $client_key,
	                        "hidden"        => "1"
	                   )
	            ));
		       
		    }else{
		        array_push( $wireless,
	                array(
	                    "wifi-iface"    => "$one"."_1",
	                    "options"   => array(
	                        "device"        => "radio1",
	                        "ifname"        => "$one"."1",
	                        "mode"          => "ap",
	                        "encryption"    => "psk-mixed",
	                        "network"       => $one,
	                        "ssid"          => "meshdesk_config",
	                        "key"           => $client_key,
	                        "hidden"        => "1"
	                   )
	            ));
		    }
		}

        $start_number = 2;

		//____ ENTRY POINTS ____

        //Check if we need to add this wireless VAP
        foreach($mesh['MeshEntry'] as $me){
            $to_all     = false;
            $if_name    = $this->_number_to_word($start_number);
            $entry_id   = $me['id'];
            $start_number++;
            if($me['apply_to_all'] == 1){

                //Check if it is assigned to an exit point
                foreach($entry_point_data as $epd){
                    if($epd['entry_id'] == $entry_id){ //We found our man :-)

						if(($mesh['NodeDetail']['radio0_enable'] == 1)&&($mesh['NodeDetail']['radio0_entry'] == 1)){
												    
						    $base_array = array(
                                "device"        => "radio0",
                                "ifname"        => "$if_name"."0",
                                "mode"          => "ap",
                                "network"       => $epd['network'],
                                "encryption"    => $me['encryption'],
                                "ssid"          => $me['name'],
                                "key"           => $me['special_key'],
                                "hidden"        => $me['hidden'],
                                "isolate"       => $me['isolate'],
                                "auth_server"   => $me['auth_server'],
                                "auth_secret"   => $me['auth_secret']
                            );
                            
                            if($me['chk_maxassoc']){
                                $base_array['maxassoc'] = $me['maxassoc'];
                            }
                            
                            if($me['macfilter'] != 'disable'){
                                $base_array['macfilter']    = $me['macfilter'];
                                //Replace later
                                $pu_id      = $me['permanent_user_id'];
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
                                    "wifi-iface"=> "$if_name",
                                    "options"   => $base_array
                            ));    
						  
						}

						if(($mesh['NodeDetail']['radio1_enable'] == 1)&&($mesh['NodeDetail']['radio1_entry'] == 1)){

						    $base_array = array(
                                "device"        => "radio1",
                                "ifname"        => "$if_name"."1",
                                "mode"          => "ap",
                                "network"       => $epd['network'],
                                "encryption"    => $me['encryption'],
                                "ssid"          => $me['name'],
                                "key"           => $me['special_key'],
                                "hidden"        => $me['hidden'],
                                "isolate"       => $me['isolate'],
                                "auth_server"   => $me['auth_server'],
                                "auth_secret"   => $me['auth_secret']
                            );
                            
                            if($me['chk_maxassoc']){
                                $base_array['maxassoc'] = $me['maxassoc'];
                            }
                            
                            if($me['macfilter'] != 'disable'){
                                $base_array['macfilter']    = $me['macfilter'];
                                //Replace later
                                $pu_id      = $me['permanent_user_id'];
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
                                    "wifi-iface"=> "$if_name"."_1",
                                    "options"   => $base_array
                            ));   
		                        
						}
                        break;
                    }
                }
            }else{
                //Check if this entry point is statically attached to the node
               // print_r($mesh['Node']);
                foreach($mesh['Node'] as $node){
                    if($node['id'] == $this->NodeId){   //We have our node
                        foreach($node['NodeMeshEntry'] as $nme){
                            if($nme['mesh_entry_id'] == $entry_id){
                                //Check if it is assigned to an exit point
                                foreach($entry_point_data as $epd){
                                    //We have a hit; we have to  add this entry
                                    if($epd['entry_id'] == $entry_id){ //We found our man :-)

										if(($mesh['NodeDetail']['radio0_enable'] == 1)&&($mesh['NodeDetail']['radio0_entry'] == 1)){
										
										    $base_array = array(
                                                "device"        => "radio0",
                                                "ifname"        => "$if_name"."0",
                                                "mode"          => "ap",
                                                "network"       => $epd['network'],
                                                "encryption"    => $me['encryption'],
                                                "ssid"          => $me['name'],
                                                "key"           => $me['special_key'],
                                                "hidden"        => $me['hidden'],
                                                "isolate"       => $me['isolate'],
                                                "auth_server"   => $me['auth_server'],
                                                "auth_secret"   => $me['auth_secret']
                                            );
                                            
                                            if($me['chk_maxassoc']){
                                                $base_array['maxassoc'] = $me['maxassoc'];
                                            }
                                            
                                            if($me['macfilter'] != 'disable'){
                                                $base_array['macfilter']    = $me['macfilter'];
                                                //Replace later
                                                $pu_id      = $me['permanent_user_id'];
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
                                                    "wifi-iface"=> "$if_name",
                                                    "options"   => $base_array
                                            ));   
								   
										}

										if(($mesh['NodeDetail']['radio1_enable'] == 1)&&($mesh['NodeDetail']['radio1_entry'] == 1)){
										
										    $base_array = array(
                                                "device"        => "radio1",
                                                "ifname"        => "$if_name"."1",
                                                "mode"          => "ap",
                                                "network"       => $epd['network'],
                                                "encryption"    => $me['encryption'],
                                                "ssid"          => $me['name'],
                                                "key"           => $me['special_key'],
                                                "hidden"        => $me['hidden'],
                                                "isolate"       => $me['isolate'],
                                                "auth_server"   => $me['auth_server'],
                                                "auth_secret"   => $me['auth_secret']
                                            );
                                            
                                            if($me['chk_maxassoc']){
                                                $base_array['maxassoc'] = $me['maxassoc'];
                                            }
                                            
                                            if($me['macfilter'] != 'disable'){
                                                $base_array['macfilter']    = $me['macfilter'];
                                                //Replace later
                                                $pu_id      = $me['permanent_user_id'];
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
		                                            "wifi-iface"    => "$if_name"."_1",
		                                            "options"       => $base_array
		                                    ));     
		                                        
										}
                                        break;
                                    }
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }
       // print_r($wireless);
        return $wireless;
    }

	private function _build_mesh_potato($mesh){

		$mp_settings = array();
		$node_mp_setting	= ClassRegistry::init('NodeMpSetting');
		$node_mp_setting->contain();
		$q_r	= $node_mp_setting->find('all', array('conditions' => array('NodeMpSetting.node_id' => $this->NodeId)));
		foreach($q_r as $i){
			$key = $i['NodeMpSetting']['name'];
			$val = $i['NodeMpSetting']['value'];
			$mp_settings["$key"] = $val;
		}
		return $mp_settings;
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
		Configure::load('MESHdesk');
		$ct = Configure::read('hardware');
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
		Configure::load('MESHdesk');
		$ct = Configure::read('hardware');
        foreach($ct as $i){
            if($i['id'] ==$hw){
				$return_val = $i['eth_br'];
				break;
            }
        }
		return $return_val;
	}

	private function _lookup_vendor($mac){
        //Convert the MAC to be in the same format as the file 
        $mac    = strtoupper($mac);
        $pieces = explode("-", $mac);

		$vendor_file        = APP.DS."Setup".DS."Scripts".DS."mac_lookup.txt";
        $this->vendor_list  = file($vendor_file);

        $big_match      = $pieces[0].":".$pieces[1].":".$pieces[2].":".$pieces[3].":".$pieces[4];
        $small_match    = $pieces[0].":".$pieces[1].":".$pieces[2];
        $lines          = $this->vendor_list;

        $big_match_found = false;
        foreach($lines as $i){
            if(preg_match("/^$big_match/",$i)){
                $big_match_found = true;
                //Transform this line
                $vendor = preg_replace("/$big_match\s?/","",$i);
                $vendor = preg_replace( "{[ \t]+}", ' ', $vendor );
                $vendor = rtrim($vendor);
                return $vendor;   
            }
        }
       
        if(!$big_match_found){
            foreach($lines as $i){
                if(preg_match("/^$small_match/",$i)){
                    //Transform this line
                    $vendor = preg_replace("/$small_match\s?/","",$i);
                    $vendor = preg_replace( "{[ \t]+}", ' ', $vendor );
                    $vendor = rtrim($vendor);
                    return $vendor;
                }
            }
        }
        $vendor = "Unkown";
    }

}
