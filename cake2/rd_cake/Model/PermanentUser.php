<?php
App::uses('AppModel', 'Model');
App::uses('AuthComponent', 'Controller/Component');

/**
 * PermanentUser Model
 *
 */
class PermanentUser extends AppModel {

	public $actsAs 		= array('Containable','Limit');

	public $validate 	= array(
		'username' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            ),
            'unique' => array(
                'rule'    => 'isUnique',
                'message' => 'This name is already taken'
            )
        ),
		'password' => array(
			'notBlank' => array(
				'rule' => array('notBlank')
			),
		),
		'realm_id' => array(
			'numeric' => array(
				'rule' => array('numeric')
			),
		),
		'profile_id' => array(
			'numeric' => array(
				'rule' => array('numeric')
			),
		),
		'country_id' => array(
			'numeric' => array(
				'rule' => array('numeric')
			),
		),
		'language_id' => array(
			'numeric' => array(
				'rule' => array('numeric')
			),
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric')
			),
		),
	);


	public $belongsTo = array(
        'Country' => array(
            'className'     => 'Country',
			'foreignKey'    => 'country_id'
        ),
        'Language' => array(
            'className'     => 'Language',
			'foreignKey'    => 'language_id'
        ),
        'User' => array(
            'className'     => 'User',
            'foreignKey'    => 'user_id'
        ),
		'Profile' => array(
            'className'     => 'Profile',
            'foreignKey'    => 'profile_id'
        ),
		'Realm' => array(
            'className'     => 'Realm',
            'foreignKey'    => 'realm_id'
        )     
	);

    public $hasMany = array(
        'PermanentUserNote' => array(
            'dependent'     => true   
        ),
        'PermanentUserSetting' => array(
            'dependent'     => true   
        ),
        'Radcheck' => array(
            'className'     => 'Radcheck',
            'foreignKey'	=> false,
            'finderQuery'   => 'SELECT Radcheck.* FROM radcheck AS Radcheck, permanent_users WHERE permanent_users.username=Radcheck.username AND permanent_users.id={$__cakeID__$}',
            'dependent'     => true
        ),
        'Radreply' => array(
            'className'     => 'Radreply',
            'foreignKey'	=> false,
            'finderQuery'   => 'SELECT Radreply.* FROM radreply AS Radreply, permanent_users WHERE permanent_users.username=Radreply.username AND permanent_users.id={$__cakeID__$}',
            'dependent'     => true
        ),
        'Device' => array(
            'dependent'     => true   
        ),
    );

    public function beforeSave($options = array()) {

        if((isset($this->data['PermanentUser']['token']))&&($this->data['PermanentUser']['token']=='')){
            App::uses('CakeText', 'Utility');
            $this->data['PermanentUser']['token'] = CakeText::uuid();
        }else{ //If it is not set at all
            App::uses('CakeText', 'Utility');
            $this->data['PermanentUser']['token'] = CakeText::uuid();
        }

        if(isset($this->data['PermanentUser']['password'])){
            $this->clearPwd = $this->data['PermanentUser']['password']; //Keep a copy of the original one
            $this->data['PermanentUser']['password'] = AuthComponent::password($this->data['PermanentUser']['password']);
        }
        return true;
    }

    public function afterSave($created,$options = array()){

        if($created){    
        	$this->_add_radius_user();
        }else{    
          	$this->_update_radius_user();
        }
    }

    private function _update_radius_user(){

        $user_id    = $this->data['PermanentUser']['id']; //The user's ID should always be present!
        //Get the username
        $q_r        = $this->findById($user_id);
        $username   = $q_r['PermanentUser']['username'];

        //enabled or disabled (Rd-Account-Disabled)
        if(array_key_exists('active',$this->data['PermanentUser'])){ //It may be missing; you never know... 
            if($this->data['PermanentUser']['active'] == 1){ //Reverse the logic...
                $dis = 0;
            }else{
                $dis = 1;
            }                
           	$this->_replace_radcheck_item($username,'Rd-Account-Disabled',$dis);
        }
        //Password (Cleartext-Password)
        if(array_key_exists('password',$this->data['PermanentUser'])){ //Usually used to change the password               
            $this->_replace_radcheck_item($username,'Cleartext-Password',$this->clearPwd);
        }

    }

    private function _add_radius_user(){
        //The username with it's password (Cleartext-Password)
        $username                   = $this->data['PermanentUser']['username'];
        $this->_add_radcheck_item($username,'Cleartext-Password',$this->clearPwd);
        $this->_add_radcheck_item($username,'Rd-User-Type','user');

        //Realm (Rd-Realm)
        if(array_key_exists('realm_id',$this->data['PermanentUser'])){ //It may be missing; you never know...
            if($this->data['PermanentUser']['realm_id'] != ''){
                $q_r = ClassRegistry::init('Realm')->findById($this->data['PermanentUser']['realm_id']);
                $realm_name = $q_r['Realm']['name'];
                $this->_add_radcheck_item($username,'Rd-Realm',$realm_name);
            }
        }

        //Profile name (User-Profile)
        if(array_key_exists('profile_id',$this->data['PermanentUser'])){ //It may be missing; you never know...
            if($this->data['PermanentUser']['profile_id'] != ''){
                $q_r = ClassRegistry::init('Profile')->findById($this->data['PermanentUser']['profile_id']);
                $profile_name = $q_r['Profile']['name']; 
                $this->_add_radcheck_item($username,'User-Profile',$profile_name);
            }
        }

        //cap type (Rd-Cap-Type-Time this will dertermine if we enforce a counter or not) 
        if(array_key_exists('cap_time',$this->data['PermanentUser'])){ //It may be missing; you never know...
            if($this->data['PermanentUser']['cap_time'] != ''){      
                $this->_add_radcheck_item($username,'Rd-Cap-Type-Time',$this->data['PermanentUser']['cap_time']);
            }
        } 

        //cap type (Rd-Cap-Type-Data this will dertermine if we enforce a counter or not) 
        if(array_key_exists('cap_data',$this->data['PermanentUser'])){ //It may be missing; you never know...
            if($this->data['PermanentUser']['cap_data'] != ''){      
                $this->_add_radcheck_item($username,'Rd-Cap-Type-Data',$this->data['PermanentUser']['cap_data']);
            }
        }  
        
        //enabled or disabled (Rd-Account-Disabled)
        if(array_key_exists('active',$this->data['PermanentUser'])){ //It may be missing; you never know...
            if($this->data['PermanentUser']['active'] != ''){
                if($this->data['PermanentUser']['active'] == 1){ //Reverse the logic...
                    $dis = 0;
                }else{
                    $dis = 1;
                }
                $this->_add_radcheck_item($username,'Rd-Account-Disabled',$dis);
            }
        }

        //Activation date (Rd-Account-Activation-Time)
        if(array_key_exists('from_date',$this->data['PermanentUser'])){ //It may be missing; you never know...
            if($this->data['PermanentUser']['from_date'] != ''){       
                $expiration = $this->_radius_format_date($this->data['PermanentUser']['from_date']);
                $this->_add_radcheck_item($username,'Rd-Account-Activation-Time',$expiration);
            }
        }  

        //Expiration date (Expiration)
        if(array_key_exists('to_date',$this->data['PermanentUser'])){ //It may be missing; you never know...
            if($this->data['PermanentUser']['to_date'] != ''){       
                $expiration = $this->_radius_format_date($this->data['PermanentUser']['to_date']);
                $this->_add_radcheck_item($username,'Expiration',$expiration);
            }
        }

        //Not Track auth (Rd-Not-Track-Auth) *By default we will (in post-auth)
        if(!array_key_exists('track_auth',$this->data['PermanentUser'])){ //It may be missing; you never know...     
            $this->_add_radcheck_item($username,'Rd-Not-Track-Auth',1);
        }

        //Not Track acct (Rd-Not-Track-Acct) *By default we will (in pre-acct)
        if(!array_key_exists('track_acct',$this->data['PermanentUser'])){ //It may be missing; you never know...
            $this->_add_radcheck_item($username,'Rd-Not-Track-Acct',1);
        }

		//Static IP
        if(array_key_exists('static_ip',$this->data['PermanentUser'])){ //It may be missing; you never know...
            if($this->data['PermanentUser']['static_ip'] != ''){       
                $static_ip = $this->data['PermanentUser']['static_ip'];
				$this->_add_radreply_item($username,'Service-Type','Framed-User');
                $this->_add_radreply_item($username,'Framed-IP-Address',$static_ip);
            }
        }

		//Auto add MAC
		if(array_key_exists('auto_add',$this->data['PermanentUser'])){ 
            $this->_add_radcheck_item($username,'Rd-Auto-Mac',1);
        }

		//If this is an LDAP user
		if(array_key_exists('auth_type',$this->data['PermanentUser'])){ 
			if($this->data['PermanentUser']['auth_type'] != 'sql'){ //SQL is the default so we do not need to add the default
            	$this->_add_radcheck_item($username,'Rd-Auth-Type',$this->data['PermanentUser']['auth_type']);
			}
        }

		//If this is restriction for SSID ....
		if(array_key_exists('ssid_only',$this->data['PermanentUser'])){ //It may be missing; you never know...
            if($this->data['PermanentUser']['ssid_only'] != ''){       
                $this->_add_radcheck_item($username,'Rd-Ssid-Check','1');
            }
        }

		//_____ New addition where we can supply SSID ids _____
		$count     = 0;
		$ssid_list = array();
		if (
			(array_key_exists('ssid_only', $this->data['PermanentUser']))&&
			(array_key_exists('ssid_list', $this->data['PermanentUser']))
		) {
			//--We force checking--
			$this->_add_radcheck_item($username,'Rd-Ssid-Check','1');

			$ssid_list = array();

	        foreach($this->data['PermanentUser']['ssid_list'] as $s){
	            if($this->data['PermanentUser']['ssid_list'][$count] == 0){
	                $empty_flag = true;
	                break;
	            }else{
	                array_push($ssid_list,$this->data['PermanentUser']['ssid_list'][$count]);
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

        $this->Radcheck = ClassRegistry::init('Radcheck');
        $this->Radcheck->create();
        $d['Radcheck']['username']  = $username;
        $d['Radcheck']['op']        = $op;
        $d['Radcheck']['attribute'] = $item;
        $d['Radcheck']['value']     = $value;
        $this->Radcheck->save($d);
        $this->Radcheck->id         = null;
    }

	private function _add_radreply_item($username,$item,$value,$op = ":="){

        $this->Radreply = ClassRegistry::init('Radreply');
        $this->Radreply->create();
        $d['Radreply']['username']  = $username;
        $d['Radreply']['op']        = $op;
        $d['Radreply']['attribute'] = $item;
        $d['Radreply']['value']     = $value;
        $this->Radreply->save($d);
        $this->Radreply->id         = null;
    }


    private function _replace_radcheck_item($username,$item,$value,$op = ":="){
        $this->Radcheck = ClassRegistry::init('Radcheck');
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
