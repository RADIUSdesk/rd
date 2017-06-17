<?php
App::uses('AppModel', 'Model');

class MeshExit extends AppModel {

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
            )
    );

    public $hasOne = array(
            'MeshExitCaptivePortal' => array(
                'dependent'     => true
            ),
            'OpenvpnServerClient' => array(
                'dependent'     => true
            )
    );
}

?>
