<?php
App::uses('AppModel', 'Model');

class NodeMeshExit extends AppModel {

     public $belongsTo = array(
        'Node' => array(
                    'className' => 'Node',
                    'foreignKey' => 'node_id'
                    ),
        'MeshExit' => array(
                    'className' => 'MeshExit',
                    'foreignKey' => 'mesh_exit_id'
                    ),
        );
}

?>
