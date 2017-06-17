<?php
//----------------------------------------------------------
//---- Author: Dirk van der Walt
//---- License: GPL v3
//---- Description: 
//---- Date: 20-11-2012
//------------------------------------------------------------

App::uses('Component', 'Controller');

class TokenAclComponent extends Component {


   public function check_if_can($user_id){

        $u = ClassRegistry::init("User");
        $q_r = $u->find('first',array('conditions' => array('User.id' => $user_id)));
        print_r($q_r);     
    }
}
