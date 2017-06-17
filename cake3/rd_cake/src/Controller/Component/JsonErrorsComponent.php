<?php
//----------------------------------------------------------
//---- Author: Dirk van der Walt
//---- License: GPL v3
//---- Description: A component that is used to create JSON on the controller that has it included to handle the error messages for ExtJs
//---- Date: 05-05-2017
//------------------------------------------------------------

namespace App\Controller\Component;
use Cake\Controller\Component;

class JsonErrorsComponent extends Component {

    public function initialize(array $config){
        $this->controller = $this->_registry->getController();
    }

    public function errorMessage($message="An error has occured"){
        $this->controller->set(array(
            'success' => false,
           // 'message'   => array('message' => $message),
            'message'   => $message,
            '_serialize' => array('success','message')
        ));
    }

    public function entityErros($entity,$message="An error has occured"){
        
            $errors     = $entity->errors();
            $a          = [];
            foreach(array_keys($errors) as $field){
                $detail_string = '';
                $error_detail =  $errors[$field];
                foreach(array_keys($error_detail) as $error){
                    $detail_string = $detail_string." ".$error_detail[$error];   
                }
                $a[$field] = $detail_string;
            }   
            $this->controller->set(array(
                'errors'    => $a,
                'success'   => false,
              //  'message'   => array('message' => $message),
                'message'   => $message,
                '_serialize' => array('errors','success','message')
            ));   
    }

}
