<?php
// app/Model/OpenvpnServerClient.php
class OpenvpnServerClient extends AppModel {

    public $name        = 'OpenvpnServerClient';
    public $actsAs      = array('Containable');

    public $belongsTo = array(
        'OpenvpnServer' => array(
            'className'     => 'OpenvpnServer',
			'foreignKey'    => 'openvpn_server_id'
        ),
        'Mesh' => array(
            'className'     => 'Mesh',
			'foreignKey'    => 'mesh_id'
        ),
        'MeshExit' => array(
            'className'     => 'MeshExit',
			'foreignKey'    => 'mesh_exit_id'
        ),
        'ApProfile' => array(
            'className'     => 'ApProfile',
			'foreignKey'    => 'ap_profile_id'
        ),
        'ApProfileExit' => array(
            'className'     => 'ApProfileExit',
			'foreignKey'    => 'ap_profile_id'
        ),
        'Ap' => array(
            'className'     => 'Ap',
			'foreignKey'    => 'ap_id'
        ),
	);

/*
 `mesh_id` int(11) DEFAULT NULL,
	  `mesh_exit_id` int(11) DEFAULT NULL,
	  `ap_profile_id` int(11) DEFAULT NULL,
	  `ap_profile_exit_id` int(11) DEFAULT NULL,
	  `ap_id` int(11) DEFAULT NULL,

*/
}
?>
