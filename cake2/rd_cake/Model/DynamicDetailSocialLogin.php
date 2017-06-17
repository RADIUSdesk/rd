<?php
App::uses('AppModel', 'Model');

class DynamicDetailSocialLogin extends AppModel {

    public $actsAs 			= array('Containable');
	public $displayField 	= 'name';
	public $validate = array(
        'name' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            )
        )
    );
	//The Associations below have been created with all possible keys, those that are not needed can be removed

    public $belongsTo = array(
        'DynamicDetail' => array(
            'className'     => 'DynamicDetail',
			'foreignKey'    => 'dynamic_detail_id'
        ),
		'Profile'	=>  array(
            'className'     => 'Profile',
			'foreignKey'    => 'profile_id'
        ),
		'Realm'	=>  array(
            'className'     => 'Realm',
			'foreignKey'    => 'realm_id'
        )
	);
}
