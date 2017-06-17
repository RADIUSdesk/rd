<?php
App::uses('AppModel', 'Model');

class NodeIbssConnections extends AppModel {
     public $actsAs = array('Containable');
     public $belongsTo = array(
        'Node' => array(
                    'className' => 'Node',
                    'foreignKey' => 'node_id'
                    )
        );
}

?>
