<?php
App::uses('AppModel', 'Model');
class Action extends AppModel {

    public $actsAs = array('Containable');
    var $belongsTo = array(
        'Na' => array(
                    'className' => 'Na',
                    'foreignKey' => 'na_id'
                    )
        );
}

?>
