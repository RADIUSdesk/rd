<?php
App::uses('AppModel', 'Model');

class NodeMeshEntry extends AppModel {

     public $belongsTo = array(
        'Node' => array(
                    'className' => 'Node',
                    'foreignKey' => 'node_id'
                    ),
        'MeshEntry' => array(
                    'className' => 'MeshEntry',
                    'foreignKey' => 'mesh_entry_id'
                    ),
        );
}

?>
