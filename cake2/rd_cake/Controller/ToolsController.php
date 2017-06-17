<?php
App::uses('AppController', 'Controller');

class ToolsController extends AppController {

    public $name        = 'Tools';
    public $uses        = array('Radacct','User','Voucher');

	protected $coova_ip = '127.0.0.1';
	protected $secret   = 'testing123';

    public function active_connections_for(){

		$username 	= $this->request->query['username'];
        $q_r  		= $this->Radacct->find('all',
						array('conditions' => 
							array(
								'Radacct.username' 		=> $username,
								'Radacct.acctstoptime' 	=> null
							)
						)
					   );

        $items      = array();

        foreach($q_r as $i){

			$mac 	= $i['Radacct']['callingstationid'];
			$vendor	= $this->_lookup_vendor($mac);
			array_push($items,
                array(
					'callingstationid'  => $i['Radacct']['callingstationid'],
					'username'          => $i['Radacct']['username'],
					'framedipaddress'   => $i['Radacct']['framedipaddress'],
					'acctsessionid'		=> $i['Radacct']['acctsessionid'],
					'vendor'			=> $vendor
				)
			);
        }
       
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

	public function kick_active_connection(){

		$acctsessionid 	= $this->request->query['acctsessionid'];

		$q_r  			= $this->Radacct->find('first',
							array('conditions' => 
								array(
									'Radacct.acctsessionid' 		=> $acctsessionid
								)
							)
						   );

		$data = array();
		

		if($q_r){
			$username 			= $q_r['Radacct']['username'];

			$device_mac			= $q_r['Radacct']['callingstationid'];
			$device_mac 		= strtoupper($device_mac);
			//If this was a device and not a voucher
			$coova_ip			= $this->coova_ip;
			$secret				= $this->secret;

			exec("echo \"User-Name = $device_mac,Acct-Session-ID = $acctsessionid\"  | radclient -r 2 -t 2 $coova_ip:3799 40 $secret",$output);
			
			$data['username'] 	= $username;
			exec("echo \"User-Name = $username,Acct-Session-ID = $acctsessionid\"  | radclient -r 2 -t 2 $coova_ip:3799 40 $secret",$output);
		}

		//___ FINAL PART ___
        $this->set(array(
            'success' => true,
			'data'		=> $data,
            '_serialize' => array('success','data')
        ));
	}

	 private function _lookup_vendor($mac){
        $vendor_file = APP.DS."Setup".DS."Scripts".DS."mac_lookup.txt";
       // $this->out("<info>Looking up vendor from file: $vendor_file </info>");

        //Convert the MAC to be in the same format as the file 
        $mac    = strtoupper($mac);
        $pieces = explode("-", $mac);

        $big_match      = $pieces[0].":".$pieces[1].":".$pieces[2].":".$pieces[3].":".$pieces[4];
        $small_match    = $pieces[0].":".$pieces[1].":".$pieces[2];
        $lines          = file($vendor_file);

        $big_match_found = false;
        foreach($lines as $i){
            if(preg_match("/^$big_match/",$i)){
                $big_match_found = true;
               // $this->out("<info>Found vendor for $mac -> $i</info>");
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
                  //  $this->out("<info>Found vendor for $mac -> $i</info>");
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
