<?php
App::uses('AppModel', 'Model');
class DynamicClientState extends AppModel {

    public $actsAs = array('Containable');
    var $belongsTo = array(
        'DynamicClient' => array(
                    'className' => 'DynamicClient',
                    'foreignKey' => 'dynamic_client_id'
                    )
        );
}

?>
