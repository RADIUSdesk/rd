<?php
App::uses('AppModel', 'Model');

class DynamicClientRealm extends AppModel {

     public $belongsTo = array(
        'DynamicClient' => array(
                    'className' => 'DynamicClient',
                    'foreignKey' => 'dynamic_client_id'
                    ),
        'Realm' => array(
                    'className' => 'Realm',
                    'foreignKey' => 'realm_id'
                    ),
        );
}

?>
