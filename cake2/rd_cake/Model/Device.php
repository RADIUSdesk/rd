<?php
App::uses('AppModel', 'Model');

class Device extends AppModel {

    public $actsAs          = array('Containable','Limit');
	public $displayField    = 'name';

	public $validate = array(
        'name' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            ),
            'unique' => array(
                'rule'    => 'isUnique',
                'message' => 'This MAC is already taken'
            )
        )
    );

    public $belongsTo = array(
        'PermanentUser' => array(
            'className'     => 'PermanentUser',
			'foreignKey'    => 'permanent_user_id'
        )
	);


    public $hasMany = array(
        'DeviceNote' => array(
            'dependent'     => true   
        ),
        'Radcheck' => array(
            'className'     => 'Radcheck',
            'foreignKey'	=> false,
            'finderQuery'   => 'SELECT Radcheck.* FROM radcheck AS Radcheck, devices WHERE devices.name=Radcheck.username AND devices.id={$__cakeID__$}',
            'dependent'     => true
        ),
        'Radreply' => array(
            'className'     => 'Radreply',
            'foreignKey'	=> false,
            'finderQuery'   => 'SELECT Radreply.* FROM radreply AS Radreply, devices WHERE devices.name=Radreply.username AND devices.id={$__cakeID__$}',
            'dependent'     => true
        ),
    );

    public function afterSave($created,$options = array()){
        if($created){
            $this->_add_radius_user();
        }else{
            $this->_update_radius_user();
        }
    }

    private function _update_radius_user(){

        $device_id  = $this->data['Device']['id']; //The user's ID should always be present!
        //Get the username
        $q_r        = $this->findById($device_id);
        $name       = $q_r['Device']['name'];

        //enabled or disabled (Rd-Account-Disabled)
        if(array_key_exists('active',$this->data['Device'])){ //It may be missing; you never know...    
                if($this->data['Device']['active'] == 1){ //Reverse the logic...
                    $dis = 0;
                }else{
                    $dis = 1;
                }
                $this->_replace_radcheck_item($name,'Rd-Account-Disabled',$dis);
        }
    }

    private function _add_radius_user(){
        $username   = $this->data['Device']['name'];
        $this->_add_radcheck_item($username,'Rd-User-Type','device');


        //We add the MAC with the same profile name that the owner belongs to:
        $this->PermanentUser->contain('Radcheck');
        $q_r = $this->PermanentUser->findById($this->data['Device']['permanent_user_id']);
        if($q_r){
            $realm = false;
            foreach($q_r['Radcheck'] as $rc){
                if($rc['attribute'] == 'Rd-Realm'){
                    $realm = $rc['value'];
                }
            }
            if($realm){
                $this->_add_radcheck_item($username,'Rd-Realm',$realm);
            }
            //Add the owner for FreeRADIUS to check owner restrictions....
            $this->_add_radcheck_item($username,'Rd-Device-Owner',$q_r['PermanentUser']['username']);
        }

        //Auth Type (Rd-Auth-Type) = sql by default
        //$this->_add_radcheck_item($username,'Rd-Auth-Type','sql');

        //Profile name (User-Profile)
        if(array_key_exists('profile_id',$this->data['Device'])){ //It may be missing; you never know...
            if($this->data['Device']['profile_id'] != ''){
                $q_r = ClassRegistry::init('Profile')->findById($this->data['Device']['profile_id']);
                $profile_name = $q_r['Profile']['name'];
                $this->_add_radcheck_item($username,'User-Profile',$profile_name);
            }
        }

        //cap type (Rd-Cap-Type-Time this will dertermine if we enforce a counter or not) 
        if(array_key_exists('cap_time',$this->data['Device'])){ //It may be missing; you never know...
            if($this->data['Device']['cap_time'] != ''){      
                $this->_add_radcheck_item($username,'Rd-Cap-Type-Time',$this->data['Device']['cap_time']);
            }
        }  

         //cap type (Rd-Cap-Type-Data this will dertermine if we enforce a counter or not) 
        if(array_key_exists('cap_data',$this->data['Device'])){ //It may be missing; you never know...
            if($this->data['Device']['cap_data'] != ''){      
                $this->_add_radcheck_item($username,'Rd-Cap-Type-Data',$this->data['Device']['cap_data']);
            }
        }  
        
        //enabled or disabled (Rd-Account-Disabled)
        if(array_key_exists('active',$this->data['Device'])){ //It may be missing; you never know...
            if($this->data['Device']['active'] != ''){
                if($this->data['Device']['active'] == 1){ //Reverse the logic...
                    $dis = 0;
                }else{
                    $dis = 1;
                }
                $this->_add_radcheck_item($username,'Rd-Account-Disabled',$dis);
            }
        }

        //Activation date (Rd-Account-Activation-Time)
        if(array_key_exists('from_date',$this->data['Device'])){ //It may be missing; you never know...
            if($this->data['Device']['from_date'] != ''){       
                $expiration = $this->_radius_format_date($this->data['Device']['from_date']);
                $this->_add_radcheck_item($username,'Rd-Account-Activation-Time',$expiration);
            }
        }  

        //Expiration date (Expiration)
        if(array_key_exists('to_date',$this->data['Device'])){ //It may be missing; you never know...
            if($this->data['Device']['to_date'] != ''){       
                $expiration = $this->_radius_format_date($this->data['Device']['to_date']);
                $this->_add_radcheck_item($username,'Expiration',$expiration);
            }
        }

        //Not Track auth (Rd-Not-Track-Auth) *By default we will (in post-auth)
        if(!array_key_exists('track_auth',$this->data['Device'])){ //It may be missing; you never know...     
            $this->_add_radcheck_item($username,'Rd-Not-Track-Auth',1);
        }

        //Not Track acct (Rd-Not-Track-Acct) *By default we will (in pre-acct)
        if(!array_key_exists('track_acct',$this->data['Device'])){ //It may be missing; you never know...
            $this->_add_radcheck_item($username,'Rd-Not-Track-Acct',1);
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


}
