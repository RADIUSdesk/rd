<?php
App::uses('AppModel', 'Model');

class ApProfileExitApProfileEntry extends AppModel {

     public $belongsTo = array(
        'ApProfileExit' => array(
                    'className' => 'ApProfileExit',
                    'foreignKey' => 'ap_profile_exit_id'
                    ),
        'ApProfileEntry' => array(
                    'className' => 'ApProfileEntry',
                    'foreignKey' => 'ap_profile_entry_id'
                    ),
        );
}

?>
