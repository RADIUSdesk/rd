<?php
//----------------------------------------------------------
//---- Author: Dirk van der Walt
//---- License: GPL v3
//---- Description: 
//---- Date: 29-05-2013
//------------------------------------------------------------

App::uses('Component', 'Controller');

class KickerComponent extends Component {


    var $radclient;

    function kick($radacct_entry){

        //---Location of radclient----
        $this->radclient = Configure::read('freeradius.radclient');

        //Check if there is a NAS with this IP
        $nas_ip             = $radacct_entry['nasipaddress'];
        $username           = $radacct_entry['username'];
        $nasportid          = $radacct_entry['nasportid'];
        $framedipaddress    = $radacct_entry['framedipaddress'];
		$device_mac			= $radacct_entry['callingstationid'];
        $nas_mac			= $radacct_entry['calledstationid'];
        $nas_identifier     = $radacct_entry['nasidentifier'];


        //_____ CoovaChilli-Heartbeat ______________
        $hb_q_r = ClassRegistry::init('Na')->find('first',array('conditions' => 
            array('Na.nasidentifier' => $nas_identifier,'Na.type' => 'CoovaChilli-Heartbeat')
        ));

        if($hb_q_r){
            $nas_id                 = $hb_q_r['Na']['id'];
            $d['Action']['na_id']   = $nas_id;
            $d['Action']['action']  = 'execute';
            $d['Action']['command'] = "chilli_query logout $device_mac";
            ClassRegistry::init('Action')->save($d);
            return;
        }

        //_____ Mikrotik-Heartbeat ___________
        $hb_q_r = ClassRegistry::init('Na')->find('first',array('conditions' => 
            array('Na.nasidentifier' => $nas_identifier,'Na.type' => 'Mikrotik-Heartbeat')
        ));

        if($hb_q_r){
            $nas_id                 = $hb_q_r['Na']['id'];
            $d['Action']['na_id']   = $nas_id;
            $d['Action']['action']  = 'execute';
            $d['Action']['command'] = ':foreach HOST in=[/ip hotspot host find address="'.$framedipaddress.'"] do={/ip hotspot host remove $HOST}';
            ClassRegistry::init('Action')->save($d);
            return;
        }
        


        //_____ Direct Connected Clients _____
        $q_r                = ClassRegistry::init('Na')->findByNasname($nas_ip);

        if($q_r){

            //Check the type
            $type = $q_r['Na']['type'];
            //======================================================================================
            //=======Different Types of NAS devices Require different type of disconnect actions====
            //======================================================================================
            if(($type == 'CoovaChilli-AP')|($type == 'CoovaChilli')){

                //Check the port of the device's COA
                $port   = $q_r['Na']['ports'];
                $secret = $q_r['Na']['secret'];

                //Send the NAS a POD packet
                //-------------------------------------------
                if($nas_ip == '0.0.0.0'){   //This is a hack for Chillispot since it reports 0.0.0.0
                    $nas_ip='127.0.0.1';
                }
                //Now we can attempt to disconnect the person
                $output = array();
                //Get the location of the radpod script
                // print("Disconnecting $username");
                //You may need to fine-tune the -t and -r switches - See man radclient for more detail
                $rc = $this->radclient;

                //Just send both to the device to be sure...
                //Coova wants the device to be UC
                $device_mac = strtoupper($device_mac);
			    exec("echo \"User-Name = $device_mac\"  | $rc -r 2 -t 2 $nas_ip:$port 40 $secret",$output);
                exec("echo \"User-Name = $username\"    | $rc -r 2 -t 2 $nas_ip:$port 40 $secret",$output);
                //----------------------------------------------
            }

             //____ Mikrotik _____ 
    		if($type == 'Mikrotik'){
        		$port   = $q_r['Na']['ports'];
        		$secret = $q_r['Na']['secret'];
        		//Mikrotik requires that we need to know the IP the user comes in with
        		$rc = $this->radclient;

				//If it is a MAC authenticated device - also send a disconnect command using the device MAC as username
				if($device_flag >= 1){
					exec("echo \"Framed-IP-Address=$framedipaddress,User-Name=$device_mac\" | $rc -r 2 -t 2 $nas_ip:$port disconnect $secret",$output);
				} 
        		exec("echo \"Framed-IP-Address=$framedipaddress,User-Name=$username\" | $rc -r 2 -t 2 $nas_ip:$port disconnect $secret",$output);
    		}

            //==========================================================================================
        }
        
        
        //Telkom
        //==========================================================================================
        if(preg_match("/^196/",$nas_ip)){
            // Uncommnet this for Telkom (South Africa) implementations /
            //Assume this is a telkom entry where the $nas_ip is not defined inside the NAS table since the 
            //RADIUS request is proxied for the NAS
            //Some variables to define
            $pod_server = "196.43.3.86";
            $pod_port   = "1700";
            $xascend    = $radacct_entry['xascendsessionsvrkey'];
            $secret     = "greatsecret"; //Change me
            $rc = $this->radclient;
            exec("echo \"User-Name = $username,X-Ascend-Session-Svr-Key=$xascend,NAS-IP-Address=$nas_ip,Framed-IP-Address=$framedipaddress\" | $rc -r 2 -t 2 $pod_server:$pod_port 40 $secret",$output);
            
        }
            
    }

}

?>
