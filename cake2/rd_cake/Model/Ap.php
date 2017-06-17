<?php
App::uses('AppModel', 'Model');

class Ap extends AppModel {

    public $name   = 'Ap';
    public $actsAs = array('Containable','Limit');
    
    public $validate = array(
        'name' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            ),
            'unique' => array(
                'rule'    => 'isUnique',
                'message' => 'This Device name is already taken'
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
        'ApProfile' => array(
                    'className' => 'ApProfile',
                    'foreignKey' => 'ap_profile_id'
                    )
    );
      
    public $hasMany = array(
			'ApSystem'   => array(
                'dependent'     => true   
            ),
			'ApAction'   => array(
                        'limit'     => 1,
                        'className' => 'ApAction',
                        'order'     => 'ApAction.created DESC',
                        'dependent' => true
          	),			
            'ApWifiSetting'   => array(
                'dependent'     => true   
            ),
            'OpenvpnServerClient' => array(
                'dependent'     => true 
            )
    );
    
    public $hasOne = array(
			'ApLoad'   => array(
                'dependent'     => true   
            )   
    );

}

?>
