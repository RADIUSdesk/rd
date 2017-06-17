<?php
// app/Model/Radgroupcheck.php
class Radgroupreply extends AppModel {

    public $name        = 'Radgroupreply';
    public $useTable    = 'radgroupreply'; 
    public $actsAs      = array('Containable');

    public $belongsTo = array(
        'ProfileComponent' => array(
            'className'    => 'ProfileComponent',
            'foreignKey'   => 'groupname'
        )
    );
}
?>
