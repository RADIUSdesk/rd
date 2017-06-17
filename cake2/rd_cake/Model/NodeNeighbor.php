<?php
App::uses('AppModel', 'Model');

class NodeNeighbor extends AppModel {
     public $actsAs = array('Containable');
     public $belongsTo = array(
        'Node' => array(
                    'className' => 'Node',
                    'foreignKey' => 'node_id'
                    ),
		'Neighbor' => array(
                    'className' => 'Node',
                    'foreignKey' => 'neighbor_id'
                    )
        );
}

?>
