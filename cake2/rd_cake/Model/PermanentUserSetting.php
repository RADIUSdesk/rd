<?php
App::uses('AppModel', 'Model');

class PermanentUserSetting extends AppModel {

     public $actsAs = array('Containable');
     public $belongsTo = array(
        'PermanentUser' => array(
                    'className' => 'PermanentUser',
                    'foreignKey' => 'permanent_user_id'
                    )
        );
}

?>
