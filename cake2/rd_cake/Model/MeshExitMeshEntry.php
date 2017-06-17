<?php
App::uses('AppModel', 'Model');

class MeshExitMeshEntry extends AppModel {

     public $belongsTo = array(
        'MeshExit' => array(
                    'className' => 'MeshExit',
                    'foreignKey' => 'mesh_exit_id'
                    ),
        'MeshEntry' => array(
                    'className' => 'MeshEntry',
                    'foreignKey' => 'mesh_entry_id'
                    ),
        );
}

?>
