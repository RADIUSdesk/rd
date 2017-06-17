<?php
// app/Model/Radusergroup.php
class Radusergroup extends AppModel {

    public $name        = 'Radgusergroup';
    public $useTable    = 'radusergroup'; 
    public $actsAs      = array('Containable');
    public $belongsTo = array(
        'Profile' => array(
            'className'    => 'Profile',
            'foreignKey'   => 'username'
        )
    );

    public $hasMany = array(
        'Radgroupcheck'  => array(
            'className'     => 'Radgroupcheck',
            'foreignKey'	=> false,
            'finderQuery'   => 'SELECT Radgroupcheck.groupname,Radgroupcheck.attribute FROM radgroupcheck AS Radgroupcheck, radusergroup WHERE radusergroup.groupname=Radgroupcheck.groupname AND radusergroup.id={$__cakeID__$} AND ((Radgroupcheck.attribute="Rd-Reset-Type-Time") OR (Radgroupcheck.attribute="Rd-Reset-Type-Data")) ORDER BY Radgroupcheck.attribute ASC',
            'dependent'     => true
        )
    );


}
?>
