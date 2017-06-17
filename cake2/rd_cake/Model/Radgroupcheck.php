<?php
// app/Model/Radgroupcheck.php
class Radgroupcheck extends AppModel {

    public $name        = 'Radgroupcheck';
    public $useTable    = 'radgroupcheck'; 
    public $actsAs      = array('Containable');

    public $belongsTo = array(
        'ProfileComponent' => array(
            'className'    => 'ProfileComponent',
            'foreignKey'   => 'groupname'
        )
    );
}
?>
