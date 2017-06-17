<?php
App::uses('AppModel', 'Model');

class NaRealm extends AppModel {

     public $belongsTo = array(
        'Na' => array(
                    'className' => 'Na',
                    'foreignKey' => 'na_id'
                    ),
        'Realm' => array(
                    'className' => 'Realm',
                    'foreignKey' => 'realm_id'
                    ),
        );
}

?>
