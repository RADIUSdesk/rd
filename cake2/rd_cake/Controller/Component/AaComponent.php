<?php
//----------------------------------------------------------
//---- Author: Dirk van der Walt
//---- License: GPL v3
//---- Description: A component that makes use of tho sub-components to determine Authentication and Authorization of a request
//---- Date: 21-11-2012
//------------------------------------------------------------

App::uses('Component', 'Controller');

class AaComponent extends Component {

    public $components = ['TokenAuth', 'TokenAcl'];

    public function user_for_token($controller){
        return $this->TokenAuth->check_if_valid($controller);
    }

    public function fail_no_rights($controller){
        $this->TokenAcl->fail_no_rights($controller);
    }


    public function admin_check($controller,$hard_fail=true){

        //Check if the supplied token belongs to a user that is part of the Configure::read('group.admin') group
        //-- Authenticate check --
        $token_check = $this->TokenAuth->check_if_valid($controller);
        if(!$token_check){
            return false;
        }else{

            if($token_check['group_name'] == Configure::read('group.admin')){ 
                return true;
            }else{
                if($hard_fail){
                    $this->TokenAcl->fail_no_rights($controller);
                }
                return false;
            }
        }
    }

    public function ap_check($controller,$hard_fail=true){
        //-- Authenticate check --
        $token_check = $this->TokenAuth->check_if_valid($controller);
        if(!$token_check){
            return false;
        }else{

            if($token_check['group_name'] == Configure::read('group.ap')){ 
                return true;
            }else{
                if($hard_fail){
                    $this->TokenAcl->fail_no_rights($controller);
                }
                return false;
            }
        }
    }

    public function aa_check($controller,$realm_id){

        //-- Authenticate check --
        $token_check = $this->TokenAuth->check_if_valid($controller);
        if(!$token_check){
            return false;
        }

        //-- Authorisation check --
        //::A::- Can the person do this action for this realm? --
        if($token_check['group_name'] == Configure::read('group.ap')){    //This is an access provider
            if(!$this->TokenAcl->can_manage_realm($token_check['user']['id'],$realm_id)){ //Does this AP have rights for this realm?

                $this->TokenAcl->fail_no_rights($controller);
                return false;

            }
        }elseif($token_check['group_name'] == Configure::read('group.user')){ //This is a user
                $this->TokenAcl->fail_no_rights($controller);
                return false;
        }

        //::B::-- Can this person do this action? --
        if(!$this->TokenAcl->action_check($controller->name,$controller->request->action)){
            $this->TokenAcl->fail_no_rights($controller);
            return false;
        }
        //-> Authorization check complete - continuie --
        return true;
    }

}
