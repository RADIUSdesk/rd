<?php
App::uses('AppModel', 'Model');
/**
 * Na Model
 *
 * @property User $User
 */
class Na extends AppModel {

    public $actsAs = array('Containable','Limit');

	public $displayField = 'nasname';

	public $validate = array(
        'nasname' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            ),
            'unique' => array(
                'rule'    => 'isUnique',
                'message' => 'This name is already taken'
            )
        ),
        'shortname' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            ),
            'unique' => array(
                'rule'    => 'isUnique',
                'message' => 'This name is already taken'
            )
        )
    );

    public $belongsTo = array(
        'User' => array(
            'className'     => 'User',
			'foreignKey'    => 'user_id'
        )
	);

    public $hasMany = array(
        'NaRealm'   => array(
            'dependent'     => true   
        ),
        'NaTag' => array(
            'dependent'     => true   
        ),
        'NaNote'    => array(
            'dependent'     => true   
        ),
        'OpenvpnClient' => array(
            'dependent'     => true   
        ),
        'PptpClient'    => array(
            'dependent'     => true   
        ),
        //We are only interested in the last entry
        'NaState'   => array(
                        'limit'     => 1,
                        'className' => 'NaState',
                        'order'     => 'NaState.created DESC',
                        'dependent' => true
                    ),
        'Action'    => array(
            'dependent'     => true   
        ),
    );

    //Get the note ID before we delete it
    public function beforeDelete($cascade = true){
        if($this->id){
            $class_name     = $this->name;
            $q_r            = $this->findById($this->id);
            if($q_r[$class_name]['connection_type'] == 'openvpn'){ //Open VPN needs special treatment when deleting
                $this->openvpn_id    = $q_r[$class_name]['id'];
            }

            if($q_r[$class_name]['connection_type'] == 'pptp'){ //Open VPN needs special treatment when deleting
                $this->pptp_id    = $q_r[$class_name]['id'];
            }
        }
        return true;
    }

    public function afterDelete(){
        if($this->openvpn_id){ //Clean up openvpn
            $vpn = ClassRegistry::init('OpenvpnClient');
            $vpn->contain();
            $q_r = $vpn->find('first',array('conditions' => array('OpenvpnClient.na_id' => $this->openvpn_id)));
            if($q_r){ //DeleteAll does not trigger the before and after delete callbacks!
                 $vpn->id = $q_r['OpenvpnClient']['id'];
                 $vpn->delete($q_r['OpenvpnClient']['id']);
            }
        }

        if($this->pptp_id){ //Clean up pptp
            $pptp = ClassRegistry::init('PptpClient');
            $pptp->contain();
            $q_r = $pptp->find('first',array('conditions' => array('PptpClient.na_id' => $this->pptp_id)));
            if($q_r){ //DeleteAll does not trigger the before and after delete callbacks!
                 $pptp->id = $q_r['PptpClient']['id'];
                 $pptp->delete($q_r['PptpClient']['id']);
            }
        }
    }
}
