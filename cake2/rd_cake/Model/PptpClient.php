<?php
App::uses('AppModel', 'Model');
/**
 * OpenvpnClients Model
 *
 * @property User $User
 */
class PptpClient extends AppModel {

    public $actsAs          = array('Containable');
	public $displayField    = 'username';
   
	public $validate = array(
        'username' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            ),
            'unique' => array(
                'rule'    => 'isUnique',
                'message' => 'This name is already taken'
            )
        ),
        'password' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            )
        )
    );

    public $belongsTo = array(
        'Na' => array(
            'className'     => 'Na',
			'foreignKey'    => 'na_id'
        )
	);

    public function beforeSave($options = array()){

        //Try to detect if it is an existing (edit):
        $existing_flag = false;
        if(isset($this->data['PptpClient']['id'])){
            if($this->data['PptpClient']['id'] != ''){
                $existing_flag = true;
            }
        }

        //First check if the $this->data['PptpClient']['id'] is set
        if($existing_flag == true){ 
            //______ EXISTING ONE _______
            //This is the save of an existing one, check if the name did not change
            $new_username = '';
            if(isset($this->data['PptpClient']['username'])){ //It may not always be set...
                $new_username = $this->data['PptpClient']['username'];
            }else{
                return true; //They are not saving this field... return without doing the callback!
            }

            $qr = $this->findById($this->data['PptpClient']['id']);
            $username = $qr['PptpClient']['username'];
            if($username != $new_username){
                //Name changed, remove old entry
               // $this->id = $this->data['PptpClient']['id'];
                $this->_removeFromChapSecrets();
                //Add new one
                $this->_addToChapSecrets();
                
            }
        }else{
            //_______________ NEW ONE _______________
            //This is a new one.... lets see if we can re-use some ip
            $q_r = $this->find('first', array('order' => array('PptpClient.ip ASC')));
            if($q_r){
                $ip         = $q_r['PptpClient']['ip'];
                $next_ip    = $this->_get_next_ip($ip);           
                $not_available = true;
                while($not_available){
                    if($this->_check_if_available($next_ip)){
                        $this->data['PptpClient']['ip']     = $next_ip;
                        $not_available = false;
                        break;
                    }else{
                        $next_ip = $this->_get_next_ip($next_ip);
                    }
                }              
            }else{ //The very first entry
                $ip                                  = Configure::read('pptp.start_ip');
                $this->data['PptpClient']['ip']      = $ip;
            }
            return true;
        }
    }

    public function afterSave($created,$options = array()) {
        if($created){
            //New addition; add to chap secrets
            $this->_addToChapSecrets();   
        }  
    }

    public function beforeDelete($cascade = true) {
        $this->_removeFromChapSecrets();
        return true;  
    }

    private function _check_if_available($ip){

        $count = $this->find('count',array('conditions' => array('PptpClient.ip' => $ip)));
        if($count == 0){
            return true;
        }else{
            return false;
        }
    }

    private function _addToChapSecrets(){
        $chap_file  = Configure::read('pptp.chap_secrets');
        $un         = $this->data['PptpClient']['username'];
        $pwd        = $this->data['PptpClient']['password'];
        $ip         = $this->data['PptpClient']['ip'];
        $handle     = fopen($chap_file, 'a');
        $data       = $un.' pptpd '.$pwd.' '.$ip."\n";
        fwrite($handle, $data);
        fclose($handle);
    }

    private function _removeFromChapSecrets(){

         //Find the username which is just the filename
        $qr         = $this->findById($this->id);
        $ip         = $qr['PptpClient']['ip'];
        $un         = $qr['PptpClient']['username'];
        $chap_file  = Configure::read('pptp.chap_secrets');
        $content    = file($chap_file);

        $new_content= array();

        foreach($content as $line){

            $line = ltrim($line);
            $match_found = false;
            if(preg_match("/^$un/",$line)){

                if(preg_match("/$ip/",$line)){
                    $match_found = true;
                }
            }
            if($match_found == false){
                array_push($new_content,$line);
            }
        }

         // open the file for reading
        if (!$fp = fopen($chap_file, 'w+')){
            // print an error
            print "Cannot open file ($chap_file)";
            // exit the function
            exit;
        }
        // if $fp is valid
        if($fp)
        {
            // write the array to the file
            foreach($new_content as $line) { fwrite($fp,$line); }
            // close the file
            fclose($fp);
        }

    }

    private function _get_next_ip($ip){

        $pieces     = explode('.',$ip);
        $octet_1    = $pieces[0];
        $octet_2    = $pieces[1];
        $octet_3    = $pieces[2];
        $octet_4    = $pieces[3];

        if($octet_4 >= 254){
            $octet_4 = 1;
            $octet_3 = $octet_3 +1;
        }else{

            $octet_4 = $octet_4 +1;
        }
        $next_ip = $octet_1.'.'.$octet_2.'.'.$octet_3.'.'.$octet_4;
        return $next_ip;
    }

}
