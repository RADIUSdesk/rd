<?php
App::uses('AppModel', 'Model');

class SocialLoginUserRealm extends AppModel {

     public $actsAs = array('Containable');
     public $belongsTo = array(
        'SocialLoginUser' => array(
                    'className' => 'SocialLoginUser',
                    'foreignKey' => 'social_login_user_id'
                    ),
        'Realm' => array(
                    'className' => 'Realm',
                    'foreignKey' => 'realm_id'
                    ),
        );
}

?>
