<?php
App::uses('AppModel', 'Model');
/**
 * OpenvpnClients Model
 *
 * @property User $User
 */
class OpenvpnClient extends AppModel {

    public $actsAs = array('Containable');

	public $displayField = 'username';

    private $ccd_folder = '/etc/openvpn/ccd/';
    private $ip_half    = '10.8.';

   
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
        if(isset($this->data['OpenvpnClient']['id'])){
            if($this->data['OpenvpnClient']['id'] != ''){
                $existing_flag = true;
            }
        }

        //First check if the $this->data['OpenvpnClient']['id'] is set
        if($existing_flag == true){ 
            //______ EXISTING ONE _______
            //This is the save of an existing one, check if the name did not change
            $new_username = '';
            if(isset($this->data['OpenvpnClient']['username'])){ //It may not always be set...
                $new_username = $this->data['OpenvpnClient']['username'];
            }else{
                return true; //They are not saving this field... return without doing the callback!
            }

            $qr = $this->findById($this->data['OpenvpnClient']['id']);
            $username = $qr['OpenvpnClient']['username'];
            if($username != $new_username){
                //Name changed, remove old file
                $file       = $username;
                $filename   = $this->ccd_folder.$file;
                unlink($filename);
                //Create new file
                $this->_createNewFile();
            }
        }else{
            //_______________ NEW ONE _______________
            //This is a new one.... lets see if we can re-use some ip
            $q_r = $this->find('first', array('order' => array('OpenvpnClient.subnet DESC', 'OpenvpnClient.peer1 DESC')));
            if($q_r){
                $top_subnet = $q_r['OpenvpnClient']['subnet'];
                $top_peer1  = $q_r['OpenvpnClient']['peer1'];
                if(($top_subnet == '')||($top_peer1 =='')){ //Return on empty values
                    return true;
                }
                //Start at the bottom
                $peer_start     = 1;
                $subnet_start   = 1;

                //Open find flag
                $find_flag = false;

                while($peer_start < $top_peer1){
                    if($this->_check_if_available($subnet_start,$peer_start)){
                        $this->data['OpenvpnClient']['subnet']     =   $subnet_start;
                        $this->data['OpenvpnClient']['peer1']      =   $peer_start;
                        $this->data['OpenvpnClient']['peer2']      =   $peer_start+1;
                        $find_flag = true;
                        break;
                    }
                    $peer_start = $peer_start+4;
                    if($peer_start > 253){
                        $subnet_start = $subnet_start + 1;
                    }
                    if($subnet_start > $top_subnet){
                        break;
                    }
                }
                
                if($find_flag == false){
                    $new_peer_start = $top_peer1+4;
                    if($new_peer_start > 253){ //Roll over
                        $new_peer_start =  1;
                        $subnet_start  = $subnet_start+ 1; 
                    }
                    $this->data['OpenvpnClient']['subnet']     =   $subnet_start;
                    $this->data['OpenvpnClient']['peer1']      =   $new_peer_start;
                    $this->data['OpenvpnClient']['peer2']      =   $new_peer_start+1;
                }
                
            }else{ //The very first entry
                $this->data['OpenvpnClient']['subnet']     =   1;
                $this->data['OpenvpnClient']['peer1']      =   1;
                $this->data['OpenvpnClient']['peer2']      =   2;
            }
            return true;
        }
    }

    public function afterSave($created,$options = array()) {

        if($created){
            //New addition; create a new file
            $this->_createNewFile();   
        }  
    }

    public function beforeDelete($cascade = true) {
        //Find the username which is just the filename
        $qr         = $this->findById($this->id);
        $file       = $qr['OpenvpnClient']['username'];
        $filename   = $this->ccd_folder.$file;
        unlink($filename);
        return true;  
    }

    private function _check_if_available($subnet, $peer){

        $count = $this->find('count',array('conditions' => array('OpenvpnClient.subnet' => $subnet,'OpenvpnClient.peer1' => $peer)));
        if($count == 0){
            return true;
        }else{
            return false;
        }
    }

    private function _createNewFile(){
        $file       = $this->data['OpenvpnClient']['username'];
        $filename   = $this->ccd_folder.$file;

        //ifconfig-push 10.8.0.1 10.8.0.2
        $p1 = $this->ip_half.$this->data['OpenvpnClient']['subnet'].'.'.$this->data['OpenvpnClient']['peer1'];
        $p2 = $this->ip_half.$this->data['OpenvpnClient']['subnet'].'.'.$this->data['OpenvpnClient']['peer2'];
        file_put_contents($filename,"ifconfig-push $p1 $p2\n",false);
    }

}
