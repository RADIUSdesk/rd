<?php
App::uses('AppModel', 'Model');

class SocialLoginUser extends AppModel {

    public $name        = 'SocialLoginUser';
    public $actsAs      = array('Containable');
    
     public $hasMany = array(
        'SocialLoginUserRealm' => array(
            'dependent'     => true   
        ) 
    );

}
?>
