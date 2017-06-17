<?php
// app/Model/TopUp.php
class TopUp extends AppModel {
    public $name        = 'TopUp';
    public $actsAs      = array('Containable');

    public $belongsTo   = array(
        'User'          => array(
            'className'     => 'User',
			'foreignKey'    => 'user_id'
        ),
        'PermanentUser' => array(
            'className'     => 'PermanentUser',
            'foreignKey'    => 'permanent_user_id'
        )
	);


    public function beforeValidate($options = array()){

        //We need BOTH the permanent username and the permanent userid else we cannot continiue!
    
        if(
            (array_key_exists('permanent_user',$this->data['TopUp']))&&
            (!array_key_exists('permanent_user_id',$this->data['TopUp']))
        ){
            $this->PermanentUser = ClassRegistry::init('PermanentUser'); 
            $this->PermanentUser->contain();
            $username = $this->data['TopUp']['permanent_user'];
			$q_r = $this->PermanentUser->find('first',array('conditions' => array('PermanentUser.username' => $username)));
            if($q_r){
                $this->data['TopUp']['permanent_user_id'] = $q_r['PermanentUser']['id'];
            }else{
                return false;
            }        
        }

        if(
            (array_key_exists('permanent_user_id',$this->data['TopUp']))&&
            (!array_key_exists('permanent_user',$this->data['TopUp']))
        ){
            $this->PermanentUser = ClassRegistry::init('PermanentUser');
            $this->PermanentUser->contain(); 
            $id = $this->data['TopUp']['permanent_user_id'];
            $q_r = $this->PermanentUser->findById($id);
            if($q_r){
                $this->data['TopUp']['permanent_user'] = $q_r['PermanentUser']['username'];
            }else{
                return false;
            }        
        }
    }

    public function afterSave($created,$options = array()){
        if($created){
            $this->_update_radius_attributes();
        }
    }

    public function beforeDelete($cascade = true){

        $id                 = $this->getID();
        $permanent_user_id  = $this->field('permanent_user_id');
        $data               = $this->field('data');
        $time               = $this->field('time');
        $this->PermanentUser = ClassRegistry::init('PermanentUser');
        $this->PermanentUser->contain();
        $q_r = $this->PermanentUser->findById($permanent_user_id);
        if($q_r){
            $username = $q_r['PermanentUser']['username'];
            $this->Radcheck = ClassRegistry::init('Radcheck');

            //Are we dealing with data or time
            if($data > 0){
                $q = $this->Radcheck->find('first',
                        array('conditions' => 
                            array('Radcheck.username' =>  $username,'Radcheck.attribute' => 'Rd-Total-Data')));
                if($q){
                    $reduced_value = $q['Radcheck']['value'] - $data; //Remove value
                    if($reduced_value <= 0){
                            $this->Radcheck->deleteAll(
                                array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Total-Data'), false
                            );

                    }else{
                        $this->_replace_radcheck_item($username,'Rd-Total-Data',$reduced_value);
                    }
                }  
            }

            if($time > 0){
                $q = $this->Radcheck->find('first',
                        array('conditions' => 
                            array('Radcheck.username' =>  $username,'Radcheck.attribute' => 'Rd-Total-Time')));
                if($q){
                    $reduced_value = $q['Radcheck']['value'] - $time; //Remove value
                    if($reduced_value <= 0){ //Remove all traces
                            $this->Radcheck->deleteAll(
                                array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-Total-Time'), false
                            );

                    }else{ //Reduce
                        $this->_replace_radcheck_item($username,'Rd-Total-Time',$reduced_value);
                    }
                } 
            }
        }   
    }

    private function _update_radius_attributes(){
        //The permanent user's username should be set by now. We need now to determine the type of top-up
        $username = $this->data['TopUp']['permanent_user'];
        if(
        (array_key_exists('data',$this->data['TopUp']))&&
        ($this->data['TopUp']['data'] > 0)
        ){
                //Assume data
                $this->Radcheck = ClassRegistry::init('Radcheck');
                $q_r = $this->Radcheck->find('first',
                    array('conditions' => 
                        array('Radcheck.username' =>  $username,'Radcheck.attribute' => 'Rd-Total-Data')));

                if($q_r){
                    $this->data['TopUp']['data'] = $q_r['Radcheck']['value'] + $this->data['TopUp']['data']; //Add previous value
                } 
                $this->_replace_radcheck_item($username,'Rd-Total-Data',$this->data['TopUp']['data']);
        }

        if(
        (array_key_exists('time',$this->data['TopUp']))&&
        ($this->data['TopUp']['time'] > 0)
        ){
                //Assume time
                $this->Radcheck = ClassRegistry::init('Radcheck');
                $q_r = $this->Radcheck->find('first',
                    array('conditions' => 
                        array('Radcheck.username' =>  $username,'Radcheck.attribute' => 'Rd-Total-Time')));

                if($q_r){
                    $this->data['TopUp']['time'] = $q_r['Radcheck']['value'] + $this->data['TopUp']['time']; //Add previous value
                } 
                $this->_replace_radcheck_item($username,'Rd-Total-Time',$this->data['TopUp']['time']);
        }
        
    }

    private function _replace_radcheck_item($username,$item,$value,$op = ":="){

        $this->Radcheck = ClassRegistry::init('Radcheck');
        $this->Radcheck->deleteAll(
            array('Radcheck.username' => $username,'Radcheck.attribute' => $item), false
        );
        $this->Radcheck->create();
        $d['Radcheck']['username']  = $username;
        $d['Radcheck']['op']        = $op;
        $d['Radcheck']['attribute'] = $item;
        $d['Radcheck']['value']     = $value;
        $this->Radcheck->save($d);
        $this->Radcheck->id         = null;
    }
}
?>
