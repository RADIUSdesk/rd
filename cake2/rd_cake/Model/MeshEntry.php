<?php
App::uses('AppModel', 'Model');

class MeshEntry extends AppModel {

    public $actsAs = array('Containable');
    public $belongsTo = array(
        'Mesh' => array(
                    'className' => 'Mesh',
                    'foreignKey' => 'mesh_id'
                    )
        );

    public $hasMany = array(
            'MeshExitMeshEntry'   => array(
                'dependent'     => true   
            ),
            'NodeMeshEntry'   => array(
                'dependent'     => true   
            ),
    );
}

?>
