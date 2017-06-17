<?php
App::uses('AppModel', 'Model');

class NodeMpSetting extends AppModel {

     public $actsAs = array('Containable');
     public $belongsTo = array(
        'Node' => array(
                    'className' => 'Node',
                    'foreignKey' => 'node_id'
                    )
        );
}

?>
