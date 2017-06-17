<?php
// app/Model/PhraseKey.php
class PhraseKey extends AppModel {

    public $name    = 'PhraseKey';
    public $actsAs  = array('Containable');

    public $hasMany = array(
        'PhraseValue' => array(
            'className'     => 'PhraseValue',
            'foreignKey'    => 'phrase_key_id',
            'dependent'     => true
        )
    );


    public $validate = array(
        'name' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            ),
            'unique' => array(
                'rule'    => 'isUnique',
                'message' => 'This key is already defined'
            )
        )
    );
}
?>
