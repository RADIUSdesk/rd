<?php
// app/Model/Radacct.php
class Radacct extends AppModel {

    public $name        = 'Radacct'; 
    public $useTable    = 'radacct'; 
    public $primaryKey  = 'radacctid'; 
    public $actsAs      = array('Containable');

    public $hasMany = array(
        'Radcheck' => array(
            'className'     => 'Radcheck',
            'foreignKey'	=> false,
            'finderQuery'   => 'SELECT Radcheck.* FROM radcheck AS Radcheck, radacct WHERE (radacct.username=Radcheck.username OR radacct.callingstationid=Radcheck.username) AND radacct.radacctid={$__cakeID__$}',
            'dependent'     => true
        )
    );
}
?>
