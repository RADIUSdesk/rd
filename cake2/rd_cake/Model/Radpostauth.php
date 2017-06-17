<?php
// app/Model/Radpostauth.php
class Radpostauth extends AppModel {

    public $name        = 'Radpostauth'; 
    public $useTable    = 'radpostauth'; 
    public $actsAs      = array('Containable');

    //We us this dependency to specify in order to determine what type of device it is along with other info like the realm
    public $hasMany = array(
        'Radcheck' => array(
            'className'     => 'Radcheck',
            'foreignKey'	=> false,
            'finderQuery'   => 'SELECT Radcheck.* FROM radcheck AS Radcheck, radpostauth WHERE radpostauth.username=Radcheck.username AND radpostauth.id={$__cakeID__$}',
            'dependent'     => true
        )
    );
}
?>
