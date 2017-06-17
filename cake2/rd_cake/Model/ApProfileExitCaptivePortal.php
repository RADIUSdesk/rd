<?php
App::uses('AppModel', 'Model');

class ApProfileExitCaptivePortal extends AppModel {

     public $belongsTo = array(
        'ApProfileExit' => array(
                    'className' => 'ApProfileExit',
                    'foreignKey' => 'ap_profile_exit_id'
                    )
        );

     public $validate = array(
        'radius_nasid' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            ),
            'unique' => array(
                'rule'    => 'isUnique',
                'message' => 'This value is already taken'
            )
        ),
        'radius1' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            )
        ),
        'secret' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            )
        ),
        'uam_url' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            )
        ),
        'uam_secret' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            )
        )   
    );

}

?>
