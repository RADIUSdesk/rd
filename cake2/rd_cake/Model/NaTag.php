<?php
App::uses('AppModel', 'Model');

class NaTag extends AppModel {

     public $actsAs = array('Containable');

     public $belongsTo = array(
        'Na' => array(
                    'className' => 'Na',
                    'foreignKey' => 'na_id'
                    ),
        'Tag' => array(
                    'className' => 'Tag',
                    'foreignKey' => 'tag_id'
                    ),
        );
}

?>
