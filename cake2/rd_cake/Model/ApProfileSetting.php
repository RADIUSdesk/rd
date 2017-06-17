<?php
App::uses('AppModel', 'Model');

class ApProfileSetting extends AppModel {

     public $actsAs = array('Containable');
     public $belongsTo = array(
        'ApProfile' => array(
                    'className' => 'ApProfile',
                    'foreignKey' => 'ap_profile_id'
                    )
        );
}

?>
