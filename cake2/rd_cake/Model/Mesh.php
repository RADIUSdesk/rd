<?php
// app/Model/Mesh.php
class Mesh extends AppModel {

    public $name        = 'Mesh';
    public $actsAs      = array('Containable','Limit');

    public $belongsTo = array(
        'User' => array(
            'className'     => 'User',
			'foreignKey'    => 'user_id'
        )
	);

    public $hasMany = array(
        'MeshNote'   => array(
            'dependent'     => true   
        ),
        'MeshEntry'   => array(
            'dependent'     => true   
        ),
        'MeshExit'   => array(
            'dependent'     => true   
        ),
        'Node'   => array(
            'dependent'     => true   
        ),
		'MeshSpecific'	=> array(
			'dependent'		=> true
		),
		'OpenvpnServerClient' => array(
            'dependent'     => true 
        )
		
    );

    public $hasOne = array(
        'MeshSetting'   => array(
            'dependent'     => true   
        ),
        'NodeSetting'   => array(
            'dependent'     => true   
        )
    );

    public $validate = array(
        'name' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            ),
            'unique' => array(
                'rule'    => 'isUnique',
                'message' => 'This name is already taken'
            )
        ),
        'ssid' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            ),
            'unique' => array(
                'rule'    => 'isUnique',
                'message' => 'This SSID is already taken'
            )
        ),
        'bssid' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            ),
            'unique' => array(
                'rule'    => 'isUnique',
                'message' => 'This BSSID is already taken'
            )
        )
    );

    public function beforeSave($options = array()) {
        if(isset($this->data['Mesh']['id'])){
            if($this->data['Mesh']['id'] == ''){
                $bssid = $this->_determine_mesh_bssid();
                $ssid  = str_replace(":", "_", "$bssid");
                $this->data['Mesh']['ssid']  = $ssid;
                $this->data['Mesh']['bssid'] = $bssid; 
            }
        }else{
            $bssid = $this->_determine_mesh_bssid();
            $ssid  = str_replace(":", "_", "$bssid");
            $this->data['Mesh']['ssid']  = $ssid;
            $this->data['Mesh']['bssid'] = $bssid; 
        }
    }


    private function _determine_mesh_ssid(){
        return "00_00";
    }

    private function _determine_mesh_bssid(){
        //Get the first one:
		Configure::load('MESHdesk'); 
        $current_value  = Configure::read('MEHSdesk.bssid');
        $reply          =   $this->find('first',array(
                            'order'         => array('Mesh.bssid DESC'))
                        );

        //Check if valid reply
        if($reply){
            $current_value = $reply['Mesh']['bssid']; //Override the current value
        }
        $pieces     = explode(":", $current_value);

        $scnd_last  = $pieces[4];
        $last       = $pieces[5];

        //Get the dec value
        if(hexdec($last) == 255){
            $last = '00';
            $scnd_last = hexdec($scnd_last)+1;
            $scnd_last = dechex($scnd_last);
        }else{
            $last = hexdec($last)+1;
            $last = dechex($last);
        }
        return $pieces[0].":".$pieces[1].":".$pieces[2].":".$pieces[3].":".sprintf("%02d", $scnd_last).":".sprintf("%02d", $last);
    }

}
?>
