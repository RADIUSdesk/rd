<?php
App::uses('AppModel', 'Model');

class DynamicPair extends AppModel {

    public $actsAs = array('Containable');


/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';

/**
 * Validation rules
 *
 * @var array
 */
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
        )
	);
}
