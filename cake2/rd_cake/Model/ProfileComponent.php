<?php
// app/Model/ProfileComponent.php
class ProfileComponent extends AppModel {

    public $name        = 'ProfileComponent';
    public $actsAs      = array('Containable');
   // public $primaryKey  = 'name';

    public $belongsTo = array(
        'User' => array(
            'className'     => 'User',
			'foreignKey'    => 'user_id'
        )
	);

    public $hasMany = array(
        'ProfileComponentNote'  => array(
            'dependent'     => true   
        ),
        'Radgroupcheck' => array(
            'className'     => 'Radgroupcheck',
            'foreignKey'	=> false,
            'finderQuery'   => 'SELECT Radgroupcheck.* FROM radgroupcheck AS Radgroupcheck, profile_components WHERE profile_components.name=Radgroupcheck.groupname AND profile_components.id={$__cakeID__$}',
            'dependent'     => true
        ),
        'Radgroupreply' => array(
            'className'     => 'Radgroupreply',
            'foreignKey'    => false,
            'finderQuery'   => 'SELECT Radgroupreply.* FROM radgroupreply AS Radgroupreply, profile_components WHERE profile_components.name=Radgroupreply.groupname AND profile_components.id={$__cakeID__$}',
            'dependent'     => true
        ),
    );

/*
    public $validate = array(
        'name' => array(
            'validate' => array(
                'rule' => array('validateUnique', array('name','iso_code')),
                'message' => 'You already have an entry',
                'required' => true
            ),
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Country name is required',
                'required' => true
            )
        ),
        'iso_code' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'ISO code is required',
                'required' => true
            ),
            'maxl' => array(
                'rule'    => array('maxLength', 2),
                'message' => 'Iso codes can not be more than two characters'
            ),
            'minl' => array(
                'rule'    => array('minLength', 2),
                'message' => 'Minimum length of 2 characters'
            )
        ),
        'icon_file' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Supply a name for the icon file'
            )
        )
    );
*/
}
?>
