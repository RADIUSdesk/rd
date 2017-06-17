<?php
// app/Model/Profile.php
class Profile extends AppModel {

    public $name        = 'Profile';
    public $actsAs      = array('Containable');

    public $belongsTo = array(
        'User' => array(
            'className'     => 'User',
			'foreignKey'    => 'user_id'
        )
	);

    public $hasMany = array(
        'ProfileNote'   => array(
            'dependent'     => true   
        ),
        'Radusergroup'  => array(
            'className'     => 'Radusergroup',
            'foreignKey'	=> false,
            'finderQuery'   => 'SELECT Radusergroup.* FROM radusergroup AS Radusergroup, profiles WHERE profiles.name=Radusergroup.username AND profiles.id={$__cakeID__$} ORDER BY Radusergroup.priority ASC',
            'dependent'     => true
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
        )
	);
}
?>
