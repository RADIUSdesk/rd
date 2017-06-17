<?php
App::uses('AppModel', 'Model');

class NodeStations extends AppModel {

     public $actsAs = array('Containable');
     public $belongsTo = array(
        'Node' => array(
                    'className' => 'Node',
                    'foreignKey' => 'node_id'
                    ),
        'NodeEntry' => array(
                    'className' => 'NodeEntry',
                    'foreignKey' => 'node_entry_id'
                    )
        );
}

?>
