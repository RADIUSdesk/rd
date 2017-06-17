<?php
App::uses('AppModel', 'Model');

class MeshSpecific extends AppModel {

     public $actsAs = array('Containable');
     public $belongsTo = array(
        'Mesh' => array(
                    'className' => 'Mesh',
                    'foreignKey' => 'mesh_id'
                    )
        );
}

?>
