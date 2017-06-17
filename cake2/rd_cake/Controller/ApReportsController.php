<?php
class ApReportsController extends AppController {

    public $name    	= 'ApReports';
	public $components  = array('Aa','MacVendors','Formatter','TimeCalculations');
    public  $uses    	= array(
		'Ap',					
		'ApLoad',		'ApStation',	'ApSystem',
		'ApProfileEntry',
		'ApSetting',	'ApAction', 'ApProfileExit',
		'ApProfileSetting'
	);
	
    public function submit_report(){

        //Source the vendors file and keep in memory
        $vendor_file        = APP.DS."Setup".DS."Scripts".DS."mac_lookup.txt";
        $this->vendor_list  = file($vendor_file);

        $this->log('Got a new report submission', 'debug');
        $fb = $this->_new_report();

		//Handy for debug to see what has been submitted
        file_put_contents('/tmp/ap_report.txt', print_r($this->request->data, true));
        $this->set(array(
           // 'items' => $this->request->data,
            'items'   => $fb,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }
    
    //______ AP View ______________
       
    //List of SSIDs (Entry Points) defined for Access Point
    public function view_entry_points(){
    
        $items = array(array('id' => 0,'name' => '(All)'));
    
        if(isset($this->request->query['ap_id'])){
            $this->Ap->contain();
            $q_r = $this->Ap->findById($this->request->query['ap_id']);
            $ap_profile_id = $q_r['Ap']['ap_profile_id'];
            $this->ApProfileEntry->contain();
            $q_ent = $this->ApProfileEntry->find('all',array(
                'conditions' => array('ApProfileEntry.ap_profile_id' => $ap_profile_id),
                'fields'     => array(
                    'id',
                    'name'
                )
            ));
            foreach($q_ent as $ent){
                $id     = $ent['ApProfileEntry']['id'];
                $name   = $ent['ApProfileEntry']['name'];
                array_push($items,array('id' => $id,'name' => $name));
            }
         
        }
        $this->set(array(
            'items'   => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }
    
    //Overview of Access Point Hardware and Firmware
    public function view_overview(){
        $data = array();
        if(isset($this->request->query['ap_id'])){
            $q_r = $this->Ap->findById($this->request->query['ap_id']);
          
            if(count($q_r['ApSystem'])>0){
                $data['components'][0]['name'] = "Device"; 
                $hardware_and_firmware = $this->_build_cpu_settings($q_r['ApSystem'],0);
                $data['components'][0]['items'] = $hardware_and_firmware;
            }
            
            if(array_key_exists('load_1',$q_r['ApLoad'])){
                $mem_and_system = array();
                //Is this device up or down
                $ap_profile_id  = $q_r['Ap']['ap_profile_id'];
			
			    $l_contact      = $q_r['Ap']['last_contact'];
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
                
                if($state == 'up'){
                    $lc = $this->TimeCalculations->time_elapsed_string($q_r['Ap']["last_contact"]);
                    array_push($mem_and_system, array('description' => 'Last contact','value' => $lc,'style' => 'rdOk'));
                } 
                
                if($state == 'down'){
                    $lc = $this->TimeCalculations->time_elapsed_string($q_r['Ap']["last_contact"]);
                    array_push($mem_and_system, array('description' => 'Last contact','value' => $lc,'style' => 'rdWarn'));
                } 
                
                if($state == 'never'){
                    array_push($mem_and_system, array('description' => 'Last contact','value' => "Never before",'style' => 'rdInfo'));
                } 
                
                //Get the load Memory etc
                $cpu_load   = $q_r['ApLoad']['load_2'];
                array_push($mem_and_system, array('description' => 'Load','value' => $cpu_load,'style' => 'rdInfo' ));
                $mem_total  = $this->Formatter->formatted_bytes($q_r['ApLoad']['mem_total']);
                
                $mem_free   = $this->Formatter->formatted_bytes($q_r['ApLoad']['mem_free']);
                array_push($mem_and_system, array('description' => 'Memory','value' => "Total $mem_total/Free $mem_free",'style' => 'rdInfo' ));
                $uptime     = $q_r['ApLoad']['uptime'];
                array_push($mem_and_system, array('description' => 'Uptime','value' => $uptime,'style' => 'rdInfo' ));
                $system_time= $q_r['ApLoad']['system_time'];
                array_push($mem_and_system, array('description' => 'System time','value' => $system_time,'style' => 'rdInfo' )); 
                array_push($mem_and_system, array('description' => 'IP','value' => $q_r['Ap']['last_contact_from_ip'],'style' => 'rdInfo' ));
                
                $data['components'][1]['name'] = "System";
                $data['components'][1]['items'] = $mem_and_system;               
            }
            
            if(count($q_r['ApWifiSetting'])>0){
                $radio_zero_items = $this->_build_radio_settings($q_r['ApWifiSetting'],0);
                if(count($radio_zero_items) > 0){
                    $data['components'][2]['name'] = "Radio 0";
                    $data['components'][2]['items'] = $radio_zero_items; 
                }
                
                $radio_one_items = $this->_build_radio_settings($q_r['ApWifiSetting'],1);
                 if(count($radio_one_items) > 0){
                    $data['components'][3]['name'] = "Radio 1";
                    $data['components'][3]['items'] = $radio_one_items ; 
                }
            }           
            $data['img']    = $q_r['Ap']['hardware'];
            $data['name']   = $q_r['Ap']['name'];
            $data['model']  = $q_r['Ap']['mac'];                   
        }
         $this->set(array(
            'data'   => $data,
            'success' => true,
            '_serialize' => array('data','success')
        ));
    }
      
    //Chart for AccessPoint Data Usage
    public function view_data_usage(){
    
        $ap_id      = $this->request->query['ap_id'];  
        
        $items      = array();
        $totalIn    = 0;
        $totalOut   = 0;
        $totalInOut = 0;
         
        $time_span = 'hour';
        if(isset($this->request->query['timespan'])){
            $time_span = $this->request->query['timespan'];
        }
        
        $ap_profile_entry_id = 0;
        if(isset($this->request->query['entry_id'])){
            $ap_profile_entry_id = $this->request->query['entry_id'];
        }
        
        if($time_span == 'week'){
            $span       = 7;
            $unit       = 'days';
            $slot       = (60*60)*24;//A day
            $start_time = time();
        }
        
        if($time_span == 'day'){
            $span       = 24;
            $unit       = 'hours';
            $slot       = (60*60);//An hour
           // $start_time = strtotime("tomorrow", time()) - 1;
           //$start_time = time();
           $start_time  = mktime(date("H"), 0, 0);
        }
        
        if($time_span == 'hour'){
            $span       = 4;
            $unit       = 'quater_hours';
            $slot       = (60*15);//15 minutes
            $start_time = time();
            
        }
        
        $carry_overs = array();
        
        for ($x = 0; $x <= $span; $x++) {
                     
            if($time_span == 'week'){
                $beginOfPeriod  = strtotime("midnight", $start_time);
                $endOfPeriod    = strtotime("tomorrow", $beginOfPeriod) - 1;
                $unit           = "$x Day";
            }
            
            if($time_span == 'day'){
                $beginOfPeriod  = $start_time-$slot;
                $endOfPeriod    = $start_time;
                $unit           = "$x Hour";
            }
            
            if($time_span == 'hour'){
                $beginOfPeriod  = $start_time-$slot;
                $endOfPeriod    = $start_time;
                $unit           = ($x * 15)." Min";
            }
            
            $start_time     = $start_time - $slot;
            
            //print("=========\n");
            //print(date("Y-m-d H:i:s", $beginOfPeriod)." ".date("Y-m-d H:i:s", $endOfPeriod)."\n");
            
            $tx_bytes = 0;
            $rx_bytes = 0;
            
            foreach($carry_overs as $co){
                //print_r($co);
                if($co['started'] <= $beginOfPeriod){
                  //  print("Carry over taking place ...");
                    $tx_bytes = $tx_bytes + $co['tx_for_period'];
                    $rx_bytes = $rx_bytes + $co['rx_for_period'];
                }
            }
            
            
            $conditions = array( 
                'ApStation.ap_id'           => $ap_id,
                'ApStation.modified >='     => date("Y-m-d H:i:s", $beginOfPeriod),
                'ApStation.modified <='     => date("Y-m-d H:i:s", $endOfPeriod)
            );
           
            if($ap_profile_entry_id != 0){
                array_push($conditions,array('ApStation.ap_profile_entry_id' => $ap_profile_entry_id));
            }
           
            $q_s = $this->ApStation->find('all',array(
                'conditions'    => $conditions,
                'fields'        => array(
                    'mac',
                    'tx_bytes',
                    'rx_bytes',
                    'created',
                    'modified'
                )
            ));
                        
            foreach($q_s as $i){
                //We need to determine if the created and modified stamps fall within this slot
                if(strtotime($i['ApStation']['created']) >= $beginOfPeriod){
                   $tx_bytes = $tx_bytes +$i['ApStation']['tx_bytes'];
                   $rx_bytes = $rx_bytes +$i['ApStation']['rx_bytes'];
                   //print_r($i);
                }else{
                    //print("We need to work out a weight for the timespan\n");
                    //print_r($i);
                    $start  = strtotime($i['ApStation']['created']);
                    $end    = strtotime($i['ApStation']['modified']);
                    //get the bytes per second
                    $time_period = $end - $start;
                    $tx_per_second = ($i['ApStation']['tx_bytes']) / $time_period;
                    $rx_per_second = ($i['ApStation']['rx_bytes']) / $time_period;
                    //Now we know the bytes per second we can multiply it with the period in the slot we occupied
                    $slot_period   = $end - $beginOfPeriod;
                    
                    $tx_for_period = intval($tx_per_second * $slot_period);
                    $rx_for_period = intval($rx_per_second * $slot_period);
                    array_push($carry_overs, array('started' => $start,'tx_for_period' => $tx_for_period, 'rx_for_period' => $rx_for_period));   
                    $tx_bytes = $tx_bytes + $tx_for_period;
                    $rx_bytes = $rx_bytes + $rx_for_period;    
                }
            }
            array_push($items,array('id' => $x,'time_unit' => "$unit",'tx_bytes' => $tx_bytes,'rx_bytes' => $rx_bytes));           
            $totalIn    = $totalIn+$rx_bytes;
            $totalOut   = $totalOut+$tx_bytes;
            $totalInOut = $totalOut+($tx_bytes+$rx_bytes); 
        } 
        $this->set(array(
            'items'   => $items,
            'success' => true,
            'totalIn'   => $totalIn,
            'totalOut'  => $totalOut,
            'totalInOut'=> $totalInOut,
            '_serialize' => array('items','success','totalIn','totalOut','totalInOut')
        ));
    }
    
    //Chart for AccessPiont Connected users
    public function view_connected_users(){
    
        $totalUsers = 0;
        $items      = array(); 
        $ap_id      = $this->request->query['ap_id'];    
        $time_span = 'hour';
        
        if(isset($this->request->query['timespan'])){
            $time_span = $this->request->query['timespan'];
        }
        
        $ap_profile_entry_id = 0;
        if(isset($this->request->query['entry_id'])){
            $ap_profile_entry_id = $this->request->query['entry_id'];
        }
        
        if($time_span == 'week'){
            $span       = 7;
            $unit       = 'days';
            $slot       = (60*60)*24;//A day
            $start_time = time();
        }
        
        if($time_span == 'day'){
            $span       = 24;
            $unit       = 'hours';
            $slot       = (60*60);//An hour
           // $start_time = strtotime("tomorrow", time()) - 1;
           //$start_time = time();
           $start_time  = mktime(date("H"), 0, 0);
        }
        
        if($time_span == 'hour'){
            $span       = 4;
            $unit       = 'quater_hours';
            $slot       = (60*15);//15 minutes
            $start_time = time();
            
        }
        
        $carry_overs = array();
        
        $master_mac_count = array();
        
        for ($x = 0; $x <= $span; $x++) {
                     
            if($time_span == 'week'){
                $beginOfPeriod  = strtotime("midnight", $start_time);
                $endOfPeriod    = strtotime("tomorrow", $beginOfPeriod) - 1;
                $unit           = "$x Day";
            }
            
            if($time_span == 'day'){
                $beginOfPeriod  = $start_time-$slot;
                $endOfPeriod    = $start_time;
                $unit           = "$x Hour";
            }
            
            if($time_span == 'hour'){
                $beginOfPeriod  = $start_time-$slot;
                $endOfPeriod    = $start_time;
                $unit           = ($x * 15)." Min";
            }
            
            $start_time     = $start_time - $slot;
            
           // print("=========\n");
          //  print(date("Y-m-d H:i:s", $beginOfPeriod)." ".date("Y-m-d H:i:s", $endOfPeriod)."\n");
            
            $mac_count = array();
            
            foreach($carry_overs as $co){
                //print_r($co);
                if($co['started'] <= $beginOfPeriod){
                  //  print("Carry over taking place ...");
                   $mac = $co['mac'];
                   $mac_count[$mac] = '';
                }
            }
            
            $conditions = array( 
                'ApStation.ap_id'           => $ap_id,
                'ApStation.modified >='     => date("Y-m-d H:i:s", $beginOfPeriod),
                'ApStation.modified <='     => date("Y-m-d H:i:s", $endOfPeriod)
            );
           
            if($ap_profile_entry_id != 0){
                array_push($conditions,array('ApStation.ap_profile_entry_id' => $ap_profile_entry_id));
            }
            
            $this->ApStation->contain();
            $q_s = $this->ApStation->find('all',array(
                'conditions'    => $conditions,
                'fields'        => array(
                    'mac',
                    'created',
                    'modified'
                )
            ));    
                        
            foreach($q_s as $i){
                $mac                    = $i['ApStation']['mac'];
                $mac_count[$mac]        = '';
                $master_mac_count[$mac] = '';
                if(strtotime($i['ApStation']['created']) <= $beginOfPeriod){    
                    $start  = strtotime($i['ApStation']['created']); 
                    array_push($carry_overs, array('started' => $start,'mac' => $mac));      
                }
            } 
            $users = count($mac_count);
            array_push($items,array('id' => $x,'time_unit' => "$unit",'users' => $users));           
            
                
        } 
        
        $totalUsers    = count($master_mac_count);
        
        $this->set(array(
            'items'         => $items,
            'success'       => true,
            'totalUsers'    => $totalUsers,
            '_serialize' => array('items','success','totalUsers')
        ));
    }
    
    //------- END AP View ---------------

	
    public function view_entries(){

		$user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

		if(!isset($this->request->query['ap_id'])){
			$this->set(array(
		        'message'	=> array("message"	=>"AP ID (ap_id) missing"),
		        'success' => false,
		        '_serialize' => array('success','message')
		    ));
			return;
		}

        $items  	= array();
		$modified 	= $this->_get_timespan();
		$ap_id      = $this->request->query['ap_id'];
		$id         = 1;
		
		$this->Ap->contain('ApProfile.ApProfileEntry');
		$q_ap       = $this->Ap->findById($ap_id);
		foreach($q_ap['ApProfile']['ApProfileEntry'] as $entry){
		    $ap_profile_entry_id = $entry['id'];
		    $entry_name          = $entry['name'];
		    $q_s = $this->ApStation->find('all',array(
                'conditions'    => array(
                    'ApStation.ap_id'               => $ap_id,
                    'ApStation.ap_profile_entry_id' => $ap_profile_entry_id,
                    'ApStation.modified >='         => $modified
                ),
                'fields'        => array(
                    'DISTINCT(ApStation.mac)'
                )
            ));
		    //print_r($q_s);
		    
		    if($q_s){
		    
		        foreach($q_s as $s){
		           // print_r($s);
		            $mac = $s['ApStation']['mac'];
		            $this->ApStation->contain();
                    $q_t = $this->ApStation->find('first', array(
                        'conditions'    => array(
                            'ApStation.mac'                 => $mac,
                            'ApStation.ap_id'               => $ap_id,
                            'ApStation.ap_profile_entry_id' => $ap_profile_entry_id,
                            'ApStation.modified >='         => $modified
                        ),
                        'fields'    => array(
                            'SUM(ApStation.tx_bytes) as tx_bytes',
                            'SUM(ApStation.rx_bytes)as rx_bytes',
                            'AVG(ApStation.signal_avg)as signal_avg',
                        )
                    ));
                    $t_bytes    = $q_t[0]['tx_bytes'];
                    $r_bytes    = $q_t[0]['rx_bytes'];
                    $signal_avg = round($q_t[0]['signal_avg']); 
                    if($signal_avg < -95){
                        $signal_avg_bar = 0.01;
                    }
                    if(($signal_avg >= -95)&($signal_avg <= -35)){
                            $p_val = 95-(abs($signal_avg));
                            $signal_avg_bar = round($p_val/60,1);
                    }
                    if($signal_avg > -35){
                        $signal_avg_bar = 1;
                    }
                    
                    //Get the latest entry
				    $this->ApStation->contain();
                    $lastCreated = $this->ApStation->find('first', array(
                        'conditions'    => array(
                            'ApStation.mac'                 => $mac,
                            'ApStation.ap_id'               => $ap_id,
                            'ApStation.ap_profile_entry_id' => $ap_profile_entry_id,
                        ),
                        'order' => array('ApStation.created' => 'desc')
                    ));

                   // print_r($lastCreated);

                    $signal = $lastCreated['ApStation']['signal'];

                    if($signal < -95){
                        $signal_bar = 0.01;
                    }
                    if(($signal >= -95)&($signal <= -35)){
                            $p_val = 95-(abs($signal));
                            $signal_bar = round($p_val/60,1);
                    }
                    if($signal > -35){
                        $signal_bar = 1;
                    }
                    
                     array_push($items,array(
                        'id'                => $id,
                        'name'              => $entry_name, 
                        'ap_profile_entry_id'=> $ap_profile_entry_id, 
                        'mac'               => $mac,
                        'vendor'            => $lastCreated['ApStation']['vendor'],
                        'tx_bytes'          => $t_bytes,
                        'rx_bytes'          => $r_bytes, 
                        'signal_avg'        => $signal_avg ,
                        'signal_avg_bar'    => $signal_avg_bar,
                        'signal_bar'        => $signal_bar,
                        'signal'            => $signal,
                        'l_tx_bitrate'      => $lastCreated['ApStation']['tx_bitrate'],
                        'l_rx_bitrate'      => $lastCreated['ApStation']['rx_bitrate'],
                        'l_signal'          => $lastCreated['ApStation']['signal'],
                        'l_signal_avg'      => $lastCreated['ApStation']['signal_avg'],
                        'l_MFP'             => $lastCreated['ApStation']['MFP'],
                        'l_tx_failed'       => $lastCreated['ApStation']['tx_failed'],
                        'l_tx_retries'      => $lastCreated['ApStation']['tx_retries'],
                        'l_modified'        => $lastCreated['ApStation']['modified'],
                        'l_authenticated'   => $lastCreated['ApStation']['authenticated'],
                        'l_authorized'      => $lastCreated['ApStation']['authorized'],
                        'l_tx_bytes'        => $lastCreated['ApStation']['tx_bytes'],
                        'l_rx_bytes'        => $lastCreated['ApStation']['rx_bytes']
                    ));
                    $id++;
		        }
		        
	        }else{
	             array_push($items,array(
                        'id'                => $id,
                        'name'              => $entry_name, 
                        'ap_profile_entry_id'=> $ap_profile_entry_id, 
                        'mac'               => 'N/A',
                        'tx_bytes'          => 0,
                        'rx_bytes'          => 0, 
                        'signal_avg'        => null ,
                        'signal_bar'        => 'N/A' ,
                        'signal_avg_bar'    => 'N/A',
                        'signal_bar'        => 'N/A',
                        'signal'            => null,
                        'tx_bitrate'        => 0,
                        'rx_bitrate'        => 0,
                        'vendor'            => 'N/A'
                    ));
                    $id++;
	        }		
		}
		 
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }


    //---------- Private Functions --------------

    private function _new_report(){

        //--- Check if the 'network_info' array is in the data ----
        $this->log('AP: Checking for network_info in log', 'debug');
        if(array_key_exists('network_info',$this->request->data)){
            $this->log('AP: Found network_info', 'debug');
            foreach($this->request->data['network_info'] as $ni){
                $id = $this->_format_mac($ni['eth0']);
                $this->log('AP: Locating the ap with MAC '.$id, 'debug');
                $this->Ap->contain();
                $q_r = $this->Ap->findByMac($id);
                if($q_r){
                    $ap_id          = $q_r['Ap']['id'];
                    $ap_profile_id  = $q_r['Ap']['ap_profile_id'];
                    $this->log('AP: The ap id of '.$id.' is '.$ap_id, 'debug');
                    $rad_zero_int = $ni['radios'][0]['interfaces'];
                    $this->_do_radio_interfaces($ap_profile_id,$ap_id,$rad_zero_int);

					//If it is a dual radio --- report on it also ----
					if(array_key_exists(1,$ni['radios'])){
						$this->log('AP: Second RADIO reported for '.$id.' is '.$ap_id, 'debug');
						$rad_one_int = $ni['radios'][1]['interfaces'];
						$this->_do_radio_interfaces($ap_profile_id,$ap_id,$rad_one_int);
					}
                }else{
                    $this->log('AP: ap with MAC '.$id.' was not found', 'debug');
                }
            }
        }
        
        
        //--- Check if the 'vpn_info' array is in the data ----
        $this->log('AP: Checking for vpn_info in log', 'debug');
        if(array_key_exists('vpn_info',$this->request->data)){
            $this->log('AP: Found vpn_info', 'debug');    
            $openvpn_server_client = ClassRegistry::init('OpenvpnServerClient');  
            foreach($this->request->data['vpn_info'] as $vpn_i){
                $vpn_gw_list = $vpn_i['vpn_gateways'];
                foreach($vpn_gw_list as $gw){
                    $vpn_client_id  = $gw['vpn_client_id'];
                    $state          = $gw['state'];
                    $timestamp      = $gw['timestamp'];
                    $date           = date('Y-m-d H:i:s',$timestamp);
                    
                    $d              = array();
                    $d['id']        = $vpn_client_id;
                    $d['last_contact_to_server'] =  $date;
                    $d['state']     = $state;
                    $openvpn_server_client->save($d); 
                }    
            }  
        }
        
        //--- Check if the 'system_info' array is in the data ----
        $this->log('AP: Checking for system_info in log', 'debug');
        if(array_key_exists('system_info',$this->request->data)){
            $this->log('AP: Found system_info', 'debug');
            $ap_profile_id = false;
            foreach($this->request->data['system_info'] as $si){
                $id = $this->_format_mac($si['eth0']);
                $this->log('AP: Locating the node with MAC '.$id, 'debug');
                $this->Ap->contain();
                $q_r = $this->Ap->findByMac($id);
                if($q_r){ 
                    $ap_id          = $q_r['Ap']['id'];
                    $ap_profile_id  = $q_r['Ap']['ap_profile_id'];
                    $this->log('Ap: The ap id of '.$id.' is '.$ap_id, 'debug');
                    $this->_do_ap_system_info($ap_id,$si['sys']);
                    $this->_do_ap_load($ap_id,$si['sys']);
                    $this->_update_last_contact($ap_id);
                }else{
                    $this->log('AP: ap with MAC '.$id.' was not found', 'debug');
                }
            }  
        }
        
/*
        //See if there are any heartbeats associated with the NAS Clients of Cavtive Portals defined for this Access Point
        if($ap_profile_id){ 
            $this->_update_any_nas_heartbeats($ap_profile_id);
        }
*/      
		//--- Finally we may have some commands waiting for the nodes----
		//--- We assume $this->request->data['network_info'][0]['eth0'] will contain one of the nodes of the mesh
		$items = false;
		
		if(array_key_exists('network_info',$this->request->data)){
            $this->log('AP: Looking for commands waiting for this mesh', 'debug');
            
			$id 	= $this->_format_mac($this->request->data['network_info'][0]['eth0']);
			$this->Ap->contain();
		    $q_r 	= $this->Ap->findByMac($id);
		    if($q_r){
				$items	        = array();
		        $ap_id          = $q_r['Ap']['id'];
		        $ap_profile_id  = $q_r['Ap']['ap_profile_id'];
				$this->ApAction->contain('Ap');
				$q_r = $this->ApAction->find('all', 
					array('conditions' => array('Ap.ap_profile_id' => $ap_profile_id,'ApAction.status' => 'awaiting')
				)); //Only awaiting actions
				foreach($q_r as $i){
					$mac 		= strtoupper($i['Ap']['mac']);
					$action_id	= $i['ApAction']['id'];
					if(array_key_exists($mac,$items)){
						array_push($items[$mac],$action_id);
					}else{
						$items[$mac] = array($action_id); //First one
					}
				}
		    }else{
                $this->log('AP: Node with MAC '.$id.' was not found', 'debug');
            }
		}
		
		return $items;       
    }

    private function _do_radio_interfaces($ap_profile_id,$ap_id,$interfaces){

        foreach($interfaces as $i){
            if(count($i['stations']) > 0){
                //Try to find (if type=AP)the Entry ID of the Mesh
                if($i['type'] == 'AP'){
                    $this->ApProfileEntry->contain();
                    $q_r = $this->ApProfileEntry->find('first', array(
                        'conditions'    => array(
                            'ApProfileEntry.name'           => $i['ssid'],
                            'ApProfileEntry.ap_profile_id'  => $ap_profile_id
                        )
                    ));

                    if($q_r){
                        $entry_id = $q_r['ApProfileEntry']['id'];
                        foreach($i['stations'] as $s){
                            $data = $this->_prep_station_data($s);
                            $data['ap_profile_entry_id']  = $entry_id;
                            $data['ap_id']        = $ap_id;
                            //--Check the last entry for this MAC
                            $q_mac = $this->ApStation->find('first',array(
                                'conditions'    => array(
                                    'ApStation.ap_profile_entry_id' => $entry_id,
                                    'ApStation.ap_id'      => $ap_id,
                                    'ApStation.mac'        => $data['mac'],
                                ),
                                'order' => array('ApStation.created' => 'desc')
                            ));
                            $new_flag = true;
                            if($q_mac){
                                $old_tx = $q_mac['ApStation']['tx_bytes'];
                                $old_rx = $q_mac['ApStation']['rx_bytes'];
                                if(($data['tx_bytes'] >= $old_tx)&($data['rx_bytes'] >= $old_rx)){
                                    $data['id'] =  $q_mac['ApStation']['id'];
                                    $new_flag = false;   
                                }
                            }
                            if($new_flag){
                                $this->ApStation->create();
                            }   
                            $this->ApStation->save($data);
                        }
                    }      
                    
                }  
            }
        }
    }

    private function _do_ap_load($ap_id,$info){
        $this->log('AP: ====Doing the ap load info for===: '.$ap_id, 'debug');
        $mem_total  = $this->_mem_kb_to_bytes($info['memory']['total']);
        $mem_free   = $this->_mem_kb_to_bytes($info['memory']['free']);
        $u          = $info['uptime'];
        $time       = preg_replace('/\s+up.*/', "", $u);
        $load       = preg_replace('/.*.\s+load average:\s+/', "", $u);
        $loads      = explode(", ",$load);
        $up         = preg_replace('/.*\s+up\s+/', "", $u);
        $up         = preg_replace('/,\s*.*/', "", $up);
        $data       = array();
        $data['mem_total']  = $mem_total;
        $data['mem_free']   = $mem_free;
        $data['uptime']     = $up;
        $data['system_time']= $time;
        $data['load_1']     = $loads[0];
        $data['load_2']     = $loads[1];
        $data['load_3']     = $loads[2];
        $data['ap_id']      = $ap_id;


        $n_l = $this->ApLoad->find('first',array(
            'conditions'    => array(
                'ApLoad.ap_id' 	=> $ap_id
            )
        ));

        $new_flag = true;
        if($n_l){  
		    $data['id'] =  $n_l['ApLoad']['id'];
		    $new_flag 	= false;   
        }
        if($new_flag){
            $this->ApLoad->create();
        }   
        $this->ApLoad->save($data);
    }

    private function _do_ap_system_info($ap_id,$info){
        $this->log('AP: Doing the system info for ap id: '.$ap_id, 'debug');

        $q_r = $this->ApSystem->findByApId($ap_id);
        if(!$q_r){
            $this->log('AP: EMPTY ApSystem - Add first one', 'debug');
            $this->_new_ap_system($ap_id,$info);

        }else{
            $this->log('AP: ApSystem info exists - Update if needed', 'debug');
            //We will check the value of DISTRIB_REVISION
            $dist_rev = false;
            if(array_key_exists('release',$info)){ 
                $release_array = explode("\n",$info['release']);
                foreach($release_array as $r){  
                    $this->log("AP: There are ".$r, 'debug'); 
                    $r_entry    = explode('=',$r);
                    $elements   = count($r_entry);
                    if($elements == 2){
                        $value          = preg_replace('/"|\'/', "", $r_entry[1]);
                        if(preg_match('/DISTRIB_REVISION/',$r_entry[0])){
                            $dist_rev = $value;
                            $this->log('AP: Submitted DISTRIB_REVISION '.$dist_rev, 'debug');
                            break;
                        }                
                    }
                }
            }

            //Find the current  DISTRIB_REVISION
            $q_r = $this->ApSystem->find('first', array('conditions' => 
                        array(
                            'ApSystem.ap_id'    => $ap_id,
                            'ApSystem.name'     => 'DISTRIB_REVISION'
            )));        
            if($q_r){
                $current = $q_r['ApSystem']['value'];

                $this->log('AP: Current DISTRIB_REVISION '.$dist_rev, 'debug');
                if($current !== $dist_rev){
                    $this->log('AP: Change in DISTRIB_REVISION -> renew', 'debug');
                    $this->ApSystem->deleteAll(array('ApSystem.ap_id' => $ap_id), false);
                    $this->_new_ap_system($ap_id,$info);
                }else{
                    $this->log('AP: DISTRIB_REVISION unchanged', 'debug');
                }
            }
        }
    }

    private function _new_ap_system($ap_id,$info){
        //--CPU Info--
        if(array_key_exists('cpu',$info)){
             $this->log('AP: Adding  CPU info', 'debug');
            foreach(array_keys($info['cpu']) as $key){
              //  $this->log('Adding first CPU info '.$key, 'debug');
                $this->ApSystem->create();
                $d['group']     = 'cpu';
                $d['name']      = $key;
                $d['value']     = $info['cpu']["$key"];
                $d['ap_id']     = $ap_id;
                $this->ApSystem->save($d);
            }
        }

        //--
        if(array_key_exists('release',$info)){ 
            $release_array = explode("\n",$info['release']);
            foreach($release_array as $r){  
               // $this->log("There are ".$r, 'debug'); 
                $r_entry    = explode('=',$r);
                $elements   = count($r_entry);
                if($elements == 2){
                   // $this->log('Adding  Release info '.$r, 'debug');
                    $value          = preg_replace('/"|\'/', "", $r_entry[1]);
                    $this->ApSystem->create();
                    $d['group']     = 'release';
                    $d['name']      = $r_entry[0];
                    $d['value']     = $value;
                    $d['ap_id']   = $ap_id;
                    $this->ApSystem->save($d);
                }
            }
        }           
    }
      
    private function _update_any_nas_heartbeats($mesh_id){
        $this->MeshExit->contain('MeshExitCaptivePortal');
        //Only captive portal types
        $q_r = $this->MeshExit->find('all', array('conditions' => array('MeshExit.mesh_id' => $mesh_id, 'MeshExit.type' => 'captive_portal')));
        
        if($q_r){
            $na = ClassRegistry::init('Na');
            $na->contain();
            foreach($q_r as $i){
                if(array_key_exists('radius_nasid',$i['MeshExitCaptivePortal'] )){
                    $nas_id = $i['MeshExitCaptivePortal']['radius_nasid'];
                    $n_q    = $na->find('first', 
                        array('conditions' => 
                            array(
                                'Na.nasidentifier'  => $nas_id,
                                'Na.type'           => 'CoovaChilli-Heartbeat',
                                'Na.monitor'        => 'heartbeat'
                            )
                        ));
                    if($n_q){
                        $na->id = $n_q['Na']['id'];
                        $na->saveField('last_contact', date('Y-m-d H:i:s'));
                    }  
                }    
            } 
        }
    }

    private function _update_last_contact($ap_id){
        $this->Ap->id = $ap_id;
        if($this->Ap->id){
            $this->Ap->saveField('last_contact', date("Y-m-d H:i:s", time()));
        }
    }

    private function _format_mac($mac){
        return preg_replace('/:/', '-', $mac);
    }

    private function _mem_kb_to_bytes($kb_val){
        $kb = preg_replace('/\s*kb/i', "", $kb_val);
        return($kb * 1024);
    }

    private function _prep_station_data($station_info){
        $data       = array();
        $tx_proc    = $station_info['tx bitrate'];
        $tx_bitrate = preg_replace('/\s+.*/','',$tx_proc);
        $tx_extra   = preg_replace('/.*\s+/','',$tx_proc);
        $rx_proc    = $station_info['rx bitrate'];
        $rx_bitrate = preg_replace('/\s+.*/','',$rx_proc);
        $rx_extra   = preg_replace('/.*\s+/','',$rx_proc);
        $incative   = preg_replace('/\s+ms.*/','',$station_info['inactive time']);
        $s          = preg_replace('/\s+\[.*/','',$station_info['signal']);
        $a          = preg_replace('/\s+\[.*/','',$station_info['avg']);

        $mac_formatted        = $this->_format_mac($station_info['mac']);

        $data['vendor']        = $this->MacVendors->vendorFor($mac_formatted);
        $data['mac']           = $mac_formatted;
        $data['tx_bytes']      = $station_info['tx bytes'];
        $data['rx_bytes']      = $station_info['rx bytes'];
        $data['tx_packets']    = $station_info['tx packets'];
        $data['rx_packets']    = $station_info['rx packets'];
        $data['tx_bitrate']    = $tx_bitrate;
        $data['rx_bitrate']    = $rx_bitrate;
        $data['tx_extra_info'] = $tx_extra;
        $data['rx_extra_info'] = $rx_extra;
        $data['authorized']    = $station_info['authorized'];
        $data['authenticated'] = $station_info['authenticated'];
        $data['tdls_peer']     = $station_info['TDLS peer'];
        $data['preamble']      = $station_info['preamble'];
        $data['tx_failed']     = $station_info['tx failed'];
        $data['tx_failed']     = $station_info['tx failed'];
        $data['inactive_time'] = $incative;
        $data['WMM_WME']       = $station_info['WMM/WME'];
        $data['tx_retries']    = $station_info['tx retries'];
        $data['MFP']           = $station_info['MFP'];
        $data['signal']        = $s;
        $data['signal_avg']    = $a;
        return $data;
    }

    private function _lookup_vendor($mac){
        //Convert the MAC to be in the same format as the file 
        $mac    = strtoupper($mac);
        $pieces = explode(":", $mac);

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
	
    private function _build_cpu_settings($system_settings){
        $return_array = array();
        $find_these = array(
            'cpu_model',
            'system_type',
            'machine',
            'DISTRIB_RELEASE',
            'DISTRIB_REVISION'
        );
        
        foreach($system_settings as $i){
            $name = $i['name'];
            if (in_array($name, $find_these)) {
                $value = $i['value'];
                if($name ==  'cpu_model'){
                    array_push($return_array, array('description' => 'CPU Model','value' => $value,'style' => 'rdInfo' ));
                }
                if($name ==  'system_type'){
                    array_push($return_array, array('description' => 'System','value' => $value,'style' => 'rdInfo' ));
                }
                if($name ==  'machine'){
                    array_push($return_array, array('description' => 'Hardware','value' => $value,'style' => 'rdInfo' ));
                }
                if($name ==  'DISTRIB_RELEASE'){
                    array_push($return_array, array('description' => 'Firmware','value' => $value,'style' => 'rdInfo' ));
                }
                if($name ==  'DISTRIB_REVISION'){
                    array_push($return_array, array('description' => 'Revision','value' => $value,'style' => 'rdInfo' ));
                }
            }
        }
        return $return_array;
    }
    
    private function _build_radio_settings($radio_data,$nr=0){
    
        $return_array = array();
    
        $find_these = array(
            'radio'.$nr.'_band',
            'radio'.$nr.'_htmode',
            'radio'.$nr.'_txpower',
            'radio'.$nr.'_disabled',
            'radio'.$nr.'_channel_two',
            'radio'.$nr.'_channel_five'
        ); 
    
        foreach($radio_data as $i){
            $name = $i['name'];
            if (in_array($name, $find_these)) {
                $value = $i['value'];
                //Band
                if($name ==  'radio'.$nr.'_band'){
                    if($value == '24'){
                        array_push($return_array, array('description' => 'Band','value' => '2.4G','style' => 'rdInfo' ));
                    }
                     if($value == '5'){
                        array_push($return_array, array('description' => 'Band','value' => '5G','style' => 'rdInfo' ));
                    }
                }
                //Enabled
                if($name ==  'radio'.$nr.'_disabled'){
                    if($value == '0'){
                        array_push($return_array, array('description' => 'Enabled','value' => 'Yes','style' => 'rdOk'));
                    }
                     if($value == '1'){
                        array_push($return_array, array('description' => 'Enabled','value' => 'No','style' => 'rdWarn'));
                    }
                }
                //HT-Mode
                if($name ==  'radio'.$nr.'_htmode'){
                    array_push($return_array, array('description' => 'HT-Mode','value' => $value,'style' => 'rdInfo'));
                }                
                //HT-Mode
                if($name ==  'radio'.$nr.'_txpower'){
                    array_push($return_array, array('description' => 'Power','value' => $value.'dBm','style' => 'rdInfo'));
                }               
                //Channel(2.4)
                if($name ==  'radio'.$nr.'_channel_two'){
                    array_push($return_array, array('description' => 'Channel','value' => $value,'style' => 'rdInfo'));
                }              
                //Channel(5)
                if($name ==  'radio'.$nr.'_channel_five'){
                    array_push($return_array, array('description' => 'Channel','value' => $value,'style' => 'rdInfo'));
                }   
            }
        }     
        return $return_array;
    }
}
?>
