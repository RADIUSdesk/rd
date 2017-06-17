<?php
App::uses('AppModel', 'Model');

class Node extends AppModel {

    public $actsAs = array('Containable','Limit');
    public $validate = array(
        'name' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            )
        ),
        'mac' => array(
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
        'Mesh' => array(
                    'className' => 'Mesh',
                    'foreignKey' => 'mesh_id'
                    )
    );

   public $hasMany = array(
            'NodeMeshEntry'   => array(
                'dependent'     => true   
            ),
            'NodeMeshExit'   => array(
                'dependent'     => true   
            ),
            'NodeStation'      => array(
                'dependent'     => true
            ),
			'NodeIbssConnection'      => array(
                'dependent'     => true
            ),
			'NodeNeighbor'   	=> array(
                'dependent'     => true,
				'order'			=> array(
					'NodeNeighbor.modified DESC'
				)
            ),
			'NodeSystem'   => array(
                'dependent'     => true   
            ),
			'NodeAction'   => array(
                        'limit'     => 1,
                        'className' => 'NodeAction',
                        'order'     => 'NodeAction.created DESC',
                        'dependent' => true
          	),
			'NodeMpSetting'   => array(
                'dependent'     => true   
            ),
            'NodeWifiSetting'   => array(
                'dependent'     => true   
            )
    );

    public $hasOne = array(
			'NodeLoad'      => array(
                'dependent'     => true
            )
    );

    public function beforeSave($options = array()){

        //Try to detect if it is an existing (edit):
        $existing_flag = false;
        if(isset($this->data['Node']['id'])){
            if($this->data['Node']['id'] != ''){
                $existing_flag = true;
            }
        }

        if($existing_flag == true){ 
           
        }else{
            //_______________ NEW ONE _______________
            //This is a new one.... lets see if we can re-use some ip
            $this_mesh_id = $this->data['Node']['mesh_id'];
			$this->data['Node']['ip'] = $this->get_ip_for_node($this_mesh_id); 
            return true;
        }
    }

	public function get_ip_for_node($this_mesh_id){

		$q_r = $this->find('first', array('order' => array('Node.ip ASC'),'conditions' => array('Node.mesh_id' => $this_mesh_id)));
        if($q_r){
            $ip         = $q_r['Node']['ip'];
            $mesh_id    = $q_r['Node']['mesh_id'];
            $next_ip    = $this->_get_next_ip($ip);           
            $not_available = true;
            while($not_available){
                if($this->_check_if_available($next_ip,$mesh_id)){
                    $this->data['Node']['ip']     = $next_ip;
                    $not_available = false;
					$ip = $next_ip;
                    break;
                }else{
                    $next_ip = $this->_get_next_ip($next_ip);
                }
            }        
        }else{ //The very first entry
			Configure::load('MESHdesk'); 
            $ip = Configure::read('mesh_node.start_ip');
        }
		return $ip;
	}

    private function _check_if_available($ip,$mesh_id){
        $count = $this->find('count',array('conditions' => array('Node.ip' => $ip,'Node.mesh_id' => $mesh_id)));
        if($count == 0){
            return true;
        }else{
            return false;
        }
    }


    private function _get_next_ip($ip){

        $pieces     = explode('.',$ip);
        $octet_1    = $pieces[0];
        $octet_2    = $pieces[1];
        $octet_3    = $pieces[2];
        $octet_4    = $pieces[3];

        if($octet_4 >= 254){
            $octet_4 = 1;
            $octet_3 = $octet_3 +1;
        }else{

            $octet_4 = $octet_4 +1;
        }
        $next_ip = $octet_1.'.'.$octet_2.'.'.$octet_3.'.'.$octet_4;
        return $next_ip;
    }
}

?>
