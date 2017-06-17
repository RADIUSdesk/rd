<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

    public $components = array(
        'RequestHandler',    //Needed for to serve JSON
        'Acl'
    );

    //Set this on the controller that will use the _ap_right_check()...
    protected $base     = "Access Providers/Controllers/Your Controller/";

    //List of parents  (for APs)
    protected $parents  = array();
    protected $children = array();

    public function beforeFilter() {

        //If it was requested as a POST or PUT it will be in the $this->data array
        
        //____POTENTIAL PUT BUG____
        //WARNING It looks like PUT request is not converted correct but rather kept in JSON 
        if ($this->request->is('put')) {
            if(!is_array($this->request->data)){
                $converted = json_decode($this->request->data,true);
                if(is_array($converted)){
                    $this->request->data = $converted;
                }
            }
        }
        //--- END POTENTIAL PUT BUG ----

        if(array_key_exists('sel_language',$this->request->data)){
            $language = $this->request->data['sel_language'];
            $this->_set_language($language);
            return; //This gets preference over query string
        }

        //Check the query string:
        if(array_key_exists('sel_language',$this->request->query)){
            $language = $this->request->query['sel_language'];
            $this->_set_language($language);
        }   
 
    }

    private function _set_language($language){

        $country_language   = explode( '_', $language );

        $country            = $country_language[0];
        $language           = $country_language[1];
        $this->Language     = ClassRegistry::init('Language');
        $this->Country      = ClassRegistry::init('Country');

        $this->Country->contain();
        $qr         = $this->Country->findById($country);
        $c_iso      = $qr['Country']['iso_code'];

        $this->Language->contain();
        $qr         = $this->Language->findById($language);
        $l_iso      = $qr['Language']['iso_code'];
        $locale     = "$l_iso".'_'."$c_iso";
        Configure::write('Config.language', "$locale");

    }


    protected function _ap_right_check(){
        //This is a common function which will check the right for an access provider on the called action.
        //We have this as a common function but beware that each controlleer which uses it; 
        //have to set the value of 'base' in order for it to work correct.

        $action = $this->request->action;
        //___AA Check Starts ___
        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }
        $user_id = null;
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            $user_id = $user['id'];
        }elseif($user['group_name'] == Configure::read('group.ap')){  //Or AP
            $user_id = $user['id'];
            if(!$this->Acl->check(array('model' => 'Users', 'foreign_key' => $user_id), $this->base.$action)){  //Does AP have right?
                $this->Aa->fail_no_rights($this);
                return;
            }
        }else{
           $this->Aa->fail_no_rights($this);
           return;
        }

        return $user;
        //__ AA Check Ends ___
    }

    protected function _test_for_private_parent($item,$user){

        
        //Most tables that has entries which belongs to an Access Provider as the user_id also includes
        // and available_to_siblings flag which if not set; makes the entry private
        // This piece of code will take the current user making the request; and compare it with fields in an entry from a table
        // It will then evaluate where it is in the hirarchy and is below the item marked as private; not display it
        if($user['group_name'] == Configure::read('group.admin')){  //Admin
            return false;
        }
        

        if($user['group_name'] == Configure::read('group.ap')){  //AP
 
            $user_id = $user['id'];
            $owner_id= $item['user_id'];
            $open    = $item['available_to_siblings'];

            //test for self
            if($owner_id == $user_id){
                return false;
            }

            //Test Parents

            //Check if parents is perhaps empty, then prime it first
            if(count($this->parents) == 0){
                $this->parents = $this->User->getPath($user_id,'User.id');
            }

            //**AP and upward in the tree**
            foreach($this->parents as $i){
                if($i['User']['id'] == $owner_id){
                    if($open == false){
                        return true; //private item
                    }else{
                        return false;
                    }
                }
            }
        }
    }

    /**
    * This Behavior writes tmp files to take advantage of the built-in fputcsv function.
    *
    */
    protected function ensureTmp() {
        $tmpDir = TMP . $this->tmpDir;
        if ( !file_exists($tmpDir ) ) {
            mkdir( $tmpDir, 0777);
        }
    }


    /**
    * Delete the tmp file, only if $tmp_file lives in TMP directory
    * otherwise throw an Exception
    *
    * @param mixed $tmp_file
    */
    protected function cleanupTmp( $tmp_file='' ) {
        $realpath = realpath( $tmp_file );
         
        if ( substr( $realpath, 0, strlen( TMP ) ) != TMP ) {
            throw new Exception('I refuse to delete a file outside of ' . TMP );
        }
         
        if ( file_exists( $tmp_file ) ) {
            unlink( $tmp_file );
        }
    }



}
