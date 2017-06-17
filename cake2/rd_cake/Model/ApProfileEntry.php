<?php
App::uses('AppModel', 'Model');

class ApProfileEntry extends AppModel {

    public $actsAs = array('Containable');
    public $belongsTo = array(
        'ApProfile' => array(
                    'className' => 'ApProfile',
                    'foreignKey' => 'ap_profile_id'
                    )
        );

    public $hasMany = array(
            'ApProfileExitApProfileEntry'   => array(
                'dependent'     => true   
            )
    );
}

?>
