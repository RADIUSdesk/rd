<?php
//----------------------------------------------------------
//---- Author: Dirk van der Walt
//---- License: GPL v3
//---- Description: A component that is intended to be called by the Aa component  to determine the owner of the token The token
//---- should be passed along with the request. If the token is not valid, an error will be reported through the controller.
//---- this component is suppose to work hand-in-hand with the TokenAcl and Aa component which will determine the rights of the user
//---- Date: 20-11-2012
//------------------------------------------------------------

App::uses('Component', 'Controller');

class TokenAuthComponent extends Component {

   public function check_if_valid($controller){
        //First we will ensure there is a token in the request
        $request = Router::getRequest();
        $token = false;

        if(isset($request->data['token'])){
            $token = $request->data['token'];
        }elseif(isset($request->query['token'])){ 
            $token = $request->query['token'];
        }
        
        if($token != false){
            if(strlen($token) != 36){
                $result = array('success' => false, 'message' => array('message'    => __('Token in wrong format')));
            }else{
                //Find the owner of the token
                $result = $this->find_token_owner($token);
            }
        }else{
            $result = array('success' => false, 'message' => array('message'        => __('Token missing')));
        }

        //If it failed - set the controller up
        if($result['success'] == false){
            $controller->set(array(
                'success'   => $result['success'],
                'message'   => $result['message'],
                '_serialize' => array('success', 'message')
            ));
            return false;
        }else{
            return $result['user']; //Return the user detail
        }   
    }

    protected function find_token_owner($token){
        $u = ClassRegistry::init("User");
        $u->contain('Group');
        $q_r = $u->find('first',array(
            'conditions'    => array('User.token' => $token),
            'fields'        => array('User.id','User.monitor','User.active','Group.name','Group.id')
        ));

        if($q_r == ''){
            return array('success' => false, 'message' => array('message' => __('No user for token')));
        }else{

            //Check if account is active or not:
            if($q_r['User']['active']==0){
                return array('success' => false, 'message' => array('message' => __('Account disabled')));
            }else{
                $user = array(
                    'id'            => $q_r['User']['id'],
                    'group_name'    => $q_r['Group']['name'],
                    'group_id'      => $q_r['Group']['id'],
                    'monitor'       => $q_r['User']['monitor'],
                );  
                return array('success' => true, 'user' => $user);
            }
        }
    }
}
