<?php
App::uses('AppModel', 'Model');

class ApProfileExit extends AppModel {

    public $actsAs = array('Containable');
    public $belongsTo = array(
        'ApProfile' => array(
                'className' => 'ApProfile',
                'foreignKey' => 'ap_profile_id'
                ),
        'DynamicDetail' => array(
                'className' => 'DynamicDetail',
                'foreignKey' => 'dynamic_detail_id'
                ),
        'OpenvpnServer' => array(
                'className'     => 'OpenvpnServer',
                'foreignKey'    => 'openvpn_server_id'
                ),         
    );

    public $hasMany = array(
            'ApProfileExitApProfileEntry'   => array(
                'dependent'     => true   
            ),
            'OpenvpnServerClient'   => array(
                'dependent'     => true   
            ),
    );

    public $hasOne = array(
            'ApProfileExitCaptivePortal' => array(
                'dependent'     => true
            )
    );
}

?>
