<?php
App::uses('AppModel', 'Model');

class ApProfile extends AppModel {

    public $name        = 'ApProfile';
    public $actsAs      = array('Containable','Limit');

    public $belongsTo = array(
        'User' => array(
            'className'     => 'User',
			'foreignKey'    => 'user_id'
        )
	);

    public $hasMany = array(
        'ApProfileNote'   => array(
            'dependent'     => true   
        ),
        'ApProfileEntry'   => array(
            'dependent'     => true   
        ),
        'ApProfileExit'   => array(
            'dependent'     => true   
        ),
        'Ap'   => array(
            'dependent'     => true   
        ),
		'ApProfileSpecific'	=> array(
			'dependent'		=> true
		),
		'OpenvpnServerClient' => array(
            'dependent'     => true 
        )	
    );
    
    public $hasOne = array(
        'ApProfileSetting'	=> array(
			'dependent'		=> true
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
        )
    );
}
?>
