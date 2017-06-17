<?php
App::uses('AppModel', 'Model');

class ApLoad extends AppModel {

     public $actsAs = array('Containable');
     public $belongsTo = array(
        'Ap' => array(
                    'className' => 'Ap',
                    'foreignKey' => 'ap_id'
                    )
        );
}

?>
