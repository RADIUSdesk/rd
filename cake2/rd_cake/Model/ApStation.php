<?php
App::uses('AppModel', 'Model');

class ApStation extends AppModel {

     public $actsAs = array('Containable');
     public $belongsTo = array(
        'Ap' => array(
                    'className' => 'Ap',
                    'foreignKey' => 'ap_id'
                    ),
        'ApProfileEntry' => array(
                    'className' => 'ApProfileEntry',
                    'foreignKey' => 'ap_profile_entry_id'
                    )
        );
}

?>
