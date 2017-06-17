<?php
// app/Model/IpPool.php
class IpPool extends AppModel {
    public $name        = 'IpPool';
    public $actsAs      = array('Containable');
	public $useTable    = 'radippool'; // This model uses a database table 'radippool'

    public $belongsTo   = array(
        'PermanentUser' => array(
            'className'     => 'PermanentUser',
            'foreignKey'    => 'permanent_user_id'
        )
	);
}
?>
