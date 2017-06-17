<?php
// app/Model/Coutry.php
class Country extends AppModel {

    public $name    = 'Country';
    public $actsAs  = array('Containable');

    public $hasMany = array(
        'PhraseValue' => array(
            'className'     => 'PhraseValue',
            'foreignKey'    => 'country_id',
            'dependent'     => true
        ),
        'User'  => array(
            'className'     => 'User',
            'foreignKey'    => 'language_id'
        ),
		'PermanentUser'  => array(
            'className'     => 'PermanentUser',
            'foreignKey'    => 'language_id'
        )
    );

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
}
?>
