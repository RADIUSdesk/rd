<?php
// app/Model/Language.php
class Language extends AppModel {

    public $name    = 'Language';
    public $actsAs  = array('Containable');

    public $hasMany = array(
        'PhraseValue' => array(
            'className'     => 'PhraseValue',
            'foreignKey'    => 'language_id',
            'dependent'     => true
        ),
        'User'  => array(
            'className'     => 'User',
            'foreignKey'    => 'language_id'
        )
    );

    public $validate = array(
        'name' => array(
            'validate' => array(
                'rule' => array('validateUnique', array('name','iso_code')),
                'message' => 'You already have an entry',
            ),
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Language name is required'
            )
        ),
        'iso_code' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'ISO code is required'
            ),
            'maxl' => array(
                'rule'    => array('maxLength', 2),
                'message' => 'Maximum lenght of 2 characters'
            ),
            'minl' => array(
                'rule'    => array('minLength', 2),
                'message' => 'Minimum length of 2 characters'
            )
        )
    );
}
?>
