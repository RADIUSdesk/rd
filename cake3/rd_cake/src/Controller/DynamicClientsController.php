<?php

namespace App\Controller;
use App\Controller\AppController;

use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class DynamicClientsController extends AppController{
  
    protected $base         = "Access Providers/Controllers/DynamicClients/";   
    protected $owner_tree   = array();
    protected $main_model   = 'DynamicClients';
  
    public function initialize(){  
        parent::initialize();
        $this->loadModel('DynamicClients'); 
        $this->loadModel('Users');
                 
        $this->loadComponent('Aa');
        $this->loadComponent('GridButtons');
        $this->loadComponent('CommonQuery', [ //Very important to specify the Model
            'model' => 'DynamicClients'
        ]);
        
        $this->loadComponent('Notes', [
            'model'     => 'DynamicClientNotes',
            'condition' => 'dynamic_client_id'
        ]);        
    }
    
    public function noteIndex(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $items = $this->Notes->index($user); 
    }
    
    public function noteAdd(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }   
        $this->Notes->add($user);
    }
    
    public function noteDel(){  
        if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $this->Notes->del($user);
    }
    
}
