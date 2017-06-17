<?php
App::uses('AppModel', 'Model');
/**
 * Na Model
 *
 * @property User $User
 */
class Note extends AppModel {

    public $actsAs = array('Containable');
	public $displayField = 'note';

	public $validate = array(
        'note' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
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
        'NaNote'                    		=> array('dependent'    => true),
        'TagNote'                   		=> array('dependent'    => true),
        'RealmNote'                 		=> array('dependent'    => true),
        'UserNote'                  		=> array('dependent'    => true),
        'DeviceNote'                		=> array('dependent'    => true),
        'ProfileNote'               		=> array('dependent'    => true),
        'ProfileComponentNote'      		=> array('dependent'    => true),
        'DynamicDetailNote'         		=> array('dependent'    => true),
        'MeshNote'                  		=> array('dependent'    => true),
        'FinPaypalTransactionNote'  		=> array('dependent'    => true),
		'FinPayUTransactionNote'  			=> array('dependent'    => true),
		'FinPremiumSmsTransactionNote'  	=> array('dependent'    => true),
		'FinAuthorizeNetTransactionNote'	=> array('dependent'    => true),
		'PermanentUserNote'					=> array('dependent'    => true),
		'DynamicClientNote'					=> array('dependent'    => true),
    );
}
