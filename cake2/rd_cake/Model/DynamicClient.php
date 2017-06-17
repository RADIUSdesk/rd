<?php
// app/Model/DynamicClient.php
class DynamicClient extends AppModel {

    public $name        = 'DynamicClient';
    public $actsAs      = array('Containable','Limit');

    public $belongsTo = array(
        'User' => array(
            'className'     => 'User',
			'foreignKey'    => 'user_id'
        )
	);
	
    public $hasMany = array(
        'DynamicClientRealm'   => array(
            'dependent'     => true   
        ),
        'DynamicClientNote'    => array(
            'dependent'     => true   
        ),
        //We are only interested in the last entry
        'DynamicClientState'   => array(
            'limit'     => 1,
            'className' => 'DynamicClientState',
            'order'     => 'DynamicClientState.created DESC',
            'dependent' => true
        )
    );
	
    public $validate 	= array(
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
        'nasidentifier' => array(
            'unique' => array(
                'rule'          => 'isUnique',
                'allowEmpty'    => true,
                'message'       => 'This name is already taken'
            )
        ),
        'calledstationid' => array(
            'unique' => array(
                'rule'          => 'isUnique',
                'allowEmpty'    => true,
                'message'       => 'This name is already taken'
            )
        )
	);
}
?>
