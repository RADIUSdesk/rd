<?php
App::uses('AppModel', 'Model');
/**
 * Na Model
 *
 * @property User $User
 */
class Tag extends AppModel {

    public $actsAs = array('Containable');
	public $displayField = 'name';

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

    public $belongsTo = array(
        'User' => array(
            'className'     => 'User',
			'foreignKey'    => 'user_id'
        )
	);

    public $hasMany = array(
        'NaTag',
        'TagNote'   => array(
            'dependent'     => true   
        )
    );
}
