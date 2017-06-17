<?php
App::uses('AppModel', 'Model');

class PermanentUserNotification extends AppModel {
    public $actsAs      = array('Containable');
    public $belongsTo   = array(
        'PermanentUser' => array(
            'className'     => 'PermanentUser',
			'foreignKey'    => 'permanent_user_id'
        )
	);
}
