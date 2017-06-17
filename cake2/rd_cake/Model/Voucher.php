<?php
App::uses('AppModel', 'Model');

class Voucher extends AppModel {

    public $actsAs          = array('Containable','Limit');
	public $displayField    = 'name';
    
    public $Radcheck;  
    public $Radreply;

    public $CreatedVouchers = array();

	public $validate = array(
        'name' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            ),
            'unique' => array(
                'rule'    => 'isUnique',
                'message' => 'This Voucher is already taken'
            )
        )
    );

    public $belongsTo = array(
        'User' => array(
            'className'     => 'User',
			'foreignKey'    => 'user_id'
        ),
        'Realm' => array(
            'className'     => 'Realm',
			'foreignKey'    => 'realm_id'
        ),
	);
/*
    public $hasOne = array(
        'FinPaypalTransaction'   => array(
            'dependent'     => false   
        )
    );
*/

    public function beforeSave($options = array()){
        //Try to detect if it is an existing (edit):
        //$existing_flag = false;
        if(!isset($this->data['Voucher']['id'])){
			//Single field (where voucher name and password are the same) are pre-populated with [Voucher]['name']
			//If not we generate a name and random password
			if(!isset($this->data['Voucher']['name'])){
            	$this->data['Voucher']['name']      = $this->_determine_voucher_name(); 
            	$this->data['Voucher']['password']  = $this->_generatePassword();
			}   
        }
        $this->_build_time_valid(); //Do this regardless    
    }

    public function afterSave($created,$options = array()){
        if($created){
            $this->_add_radius_user();
            //Push it on the create voucher stack
            array_push($this->CreatedVouchers,$this->data['Voucher']);
        }else{
            $this->_update_radius_user();
        }
    }

    private function _update_radius_user(){

        if(!(array_key_exists('do_radcheck',$this->data['Voucher']))){     
            return;
        }

        $this->Radcheck = ClassRegistry::init('Radcheck');
        $this->Radreply = ClassRegistry::init('Radreply');

        $voucher_id  = $this->data['Voucher']['id']; //The  ID should always be present!
        //Get the username
        $this->contain();
        $q_r        = $this->findById($voucher_id);
        $username   = $q_r['Voucher']['name'];

        //Realm (Rd-Realm)
        if(array_key_exists('realm',$this->data['Voucher'])){ //It may be missing; you never know... 
            $this->_replace_radcheck_item($username,'Rd-Realm',$this->data['Voucher']['realm']);
        }

        //Profile name (User-Profile)
        if(array_key_exists('profile',$this->data['Voucher'])){ //It may be missing; you never know... 
            $this->_replace_radcheck_item($username,'User-Profile',$this->data['Voucher']['profile']);
        }

        //Use time_valid to create small values for vouchers after first login
        if(array_key_exists('time_valid',$this->data['Voucher'])){ //It may be missing; you never know...  
            if(preg_match('/[0-9]-[0-9]{2}-[0-9]{2}-[0-9]{2}/', $this->data['Voucher']['time_valid'])){      
                $this->_replace_radcheck_item($username,'Rd-Voucher',$this->data['Voucher']['time_valid']);
            }
        }else{
            $this->_remove_radcheck_item($username,'Rd-Voucher');
        }

        //Expiration date (Expiration)
        if(array_key_exists('expire',$this->data['Voucher'])){ //It may be missing; you never know...
            if($this->data['Voucher']['expire'] != ''){       
                $expiration = $this->_radius_format_date($this->data['Voucher']['expire']);
                $this->_replace_radcheck_item($username,'Expiration',$expiration);
            }
        }else{
            $this->_remove_radcheck_item($username,'Expiration');
        }

		//SSID list
		$count     = 0;
		$ssid_list = array();
		if (
			(array_key_exists('ssid_only', $this->data['Voucher']))&&
			(array_key_exists('ssid_list', $this->data['Voucher']))
		){
            if($this->data['Voucher']['ssid_only'] == '1'){       
                $this->_replace_radcheck_item($username,'Rd-Ssid-Check','1');
				$ssid_list = array();
			    foreach($this->data['Voucher']['ssid_list'] as $s){
			        if($this->data['Voucher']['ssid_list'][$count] == 0){
			            $empty_flag = true;
			            break;
			        }else{
			            array_push($ssid_list,$this->data['Voucher']['ssid_list'][$count]);
			        }
			        $count++;
			    }
				$this->_replace_user_ssids($username,$ssid_list);
            }else{
				$this->_remove_radcheck_item($username,'Rd-Ssid-Check');
			}
        }else{
            $this->_remove_radcheck_item($username,'Rd-Ssid-Check');
        }
    }

    private function _add_radius_user(){

        $this->Radcheck = ClassRegistry::init('Radcheck');
        $this->Radreply = ClassRegistry::init('Radreply');

        $username       = $this->data['Voucher']['name'];

        $this->_add_radcheck_item($username,'Cleartext-Password',$this->data['Voucher']['password']);
        $this->_add_radcheck_item($username,'Rd-User-Type','voucher');

        //Realm (Rd-Realm)
        $this->_add_radcheck_item($username,'Rd-Realm',$this->data['Voucher']['realm']);
        //Profile name (User-Profile)
        $this->_add_radcheck_item($username,'User-Profile',$this->data['Voucher']['profile']);

        //Use time_valid to create small values for vouchers after first login
        if(array_key_exists('time_valid',$this->data['Voucher'])){ //It may be missing; you never know...  
            if(preg_match('/[0-9]-[0-9]{2}-[0-9]{2}-[0-9]{2}/', $this->data['Voucher']['time_valid'])){      
                $this->_add_radcheck_item($username,'Rd-Voucher',$this->data['Voucher']['time_valid']);
            }
        }

        //Expiration date (Expiration)
        if(array_key_exists('expire',$this->data['Voucher'])){ //It may be missing; you never know...
            if($this->data['Voucher']['expire'] != ''){       
                $expiration = $this->_radius_format_date($this->data['Voucher']['expire']);
                $this->_add_radcheck_item($username,'Expiration',$expiration);
            }
        }

		//If this is restriction for SSID ....
		if(array_key_exists('ssid_only',$this->data['Voucher'])){ //It may be missing; you never know...
            if($this->data['Voucher']['ssid_only'] != ''){       
                $this->_add_radcheck_item($username,'Rd-Ssid-Check','1');
            }
        }

		//_____ New addition where we can supply SSID ids _____
		$count     = 0;
		$ssid_list = array();
		if (
			(array_key_exists('ssid_only', $this->data['Voucher']))&&
			(array_key_exists('ssid_list', $this->data['Voucher']))
		) {
			//--We force checking--
			$this->_add_radcheck_item($username,'Rd-Ssid-Check','1');

			$ssid_list = array();

	        foreach($this->data['Voucher']['ssid_list'] as $s){
	            if($this->data['Voucher']['ssid_list'][$count] == 0){
	                $empty_flag = true;
	                break;
	            }else{
	                array_push($ssid_list,$this->data['Voucher']['ssid_list'][$count]);
	            }
	            $count++;
	        }
			$this->_replace_user_ssids($username,$ssid_list);
	    }

    }

    private function _radius_format_date($d){
        //Format will be month/date/year eg 03/06/2013 we need it to be 6 Mar 2013
        $arr_date   = explode('/',$d);
        $month      = intval($arr_date[0]);
        $m_arr      = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $day        = intval($arr_date[1]);
        $year       = intval($arr_date[2]);
        return "$day ".$m_arr[($month-1)]." $year";
    }

    private function _add_radcheck_item($username,$item,$value,$op = ":="){

        $this->Radcheck->create();
        $d['Radcheck']['username']  = $username;
        $d['Radcheck']['op']        = $op;
        $d['Radcheck']['attribute'] = $item;
        $d['Radcheck']['value']     = $value;
        $this->Radcheck->save($d);
        $this->Radcheck->id         = null;
    }

    private function _replace_radcheck_item($username,$item,$value,$op = ":="){

        $this->Radcheck->deleteAll(
            array('Radcheck.username' => $username,'Radcheck.attribute' => $item), false
        );
        $this->Radcheck->create();
        $d['Radcheck']['username']  = $username;
        $d['Radcheck']['op']        = $op;
        $d['Radcheck']['attribute'] = $item;
        $d['Radcheck']['value']     = $value;
        $this->Radcheck->save($d);
        $this->Radcheck->id         = null;
    }

    private function _remove_radcheck_item($username,$item){
        $this->Radcheck->deleteAll(
            array('Radcheck.username' => $username,'Radcheck.attribute' => $item), false
        );
    }

    function _determine_voucher_name(){

        $precede = @$this->data['Voucher']['precede'];
        if($precede == ''){
            $this->contain();
            $reply  =   $this->find('first',array(
                            'order'         => array('Voucher.name DESC'),
                            'conditions'    => array('Voucher.name REGEXP'  =>'^[0-9]{6}$')
                        ));
            $last_value = 0;
            if($reply){
                $last_value = $reply['Voucher']['name'];    
            }
            
            $next_number = sprintf("%06d", $last_value+1); 
            return $next_number;
        }else{

            $precede        = $precede.'-';
            $this->contain();
            $reply          = $this->find('first',array(
                                                        'fields'=>array('Voucher.name'),
                                                        'conditions'=>array('Voucher.name LIKE' => $precede.'%'),
                                                        'order'=> array( 'Voucher.name DESC'))
                                            );
            $last_entry = 0;
            $last_entry =@$reply['Voucher']['name'];
            $voucher_name;

            if(!$last_entry){
                $voucher_name = $precede."000001";
            }else{

                //Get the last number
                $number = preg_replace("/^$precede/i",'',$last_entry); //SQL will find capital and non-capital so we also need to search for that
                $number = sprintf("%06d", $number+1);
                $voucher_name = $precede.$number;
            }
            return $voucher_name;
        }
    }

    function _generatePassword ($length = 8){

        $length = $this->data['Voucher']['pwd_length'];
        if($length == ''){
            $length = 8;
        }
        // start with a blank password
        $password = "";
        // define possible characters
       // $possible = "!#$%^&*()+=?0123456789bBcCdDfFgGhHjJkmnNpPqQrRstTvwxyz";
        $possible = "0123456789bBcCdDfFgGhHjJkmnNpPqQrRstTvwxyz";
        // set up a counter
        $i = 0; 
        // add random characters to $password until $length is reached
        while ($i < $length) { 

            // pick a random character from the possible ones
            $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
            // we don't want this character if it's already in the password
            if (!strstr($password, $char)) { 
                $password .= $char;
                $i++;
            }
        }
        // done!
        return $password;
    }

    function _build_time_valid(){
        if(array_key_exists('days_valid',$this->data['Voucher'])){ //It may be missing; you never know...
            if($this->data['Voucher']['days_valid'] != ''){

                $hours      = 0;
                $minutes    = 0;
                if(isset($this->data['Voucher']['hours_valid'])){
                    $hours = $this->data['Voucher']['hours_valid'];
                }

                if(isset($this->data['Voucher']['minutes_valid'])){
                    $minutes = $this->data['Voucher']['minutes_valid'];
                }

                $hours      = sprintf("%02d", $hours);
                $minutes    = sprintf("%02d", $minutes);
                $valid      = $this->data['Voucher']['days_valid']."-".$hours."-".$minutes."-00";
                $this->data['Voucher']['time_valid'] = $valid;
                
            }
        }
		//Auto-populate the time_cap field with the value for time_valid
		if(array_key_exists('time_valid', $this->data['Voucher'])){
			$expire		= $this->data['Voucher']['time_valid'];
			$pieces     = explode("-", $expire);
            $time_avail = ($pieces[0] * 86400)+($pieces[1] * 3600)+($pieces[2] * 60)+($pieces[3]);
			$this->data['Voucher']['time_cap'] = $time_avail;
		}
    }

	private function _replace_user_ssids($username,$ssid_list){
		$u = ClassRegistry::init('UserSsid');

		//Clean up previous ones
		$u->deleteAll(
			array('UserSsid.username' => $username), false
		);

		//Get all the SSID names from the $ssid_list
		$s = ClassRegistry::init('Ssid');
		$s->contain();
		$id_list = array();
		foreach($ssid_list as $i){
			array_push($id_list, array('Ssid.id' => strval($i)));
		}

		$q_r = $s->find('all', array('conditions' => array('OR' =>$id_list)));

		foreach($q_r as $j){
			$name = $j['Ssid']['name'];
			$data = array();
			$data['username'] = $username;
			$data['ssidname'] = $name;
			$u->create();
			$u->save($data);
			$u->id = null;			
		}
	}
}
