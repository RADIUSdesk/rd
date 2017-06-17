<?php
class RadchecksController extends AppController {

    public $name       = 'Radchecks';
    public $uses       = array('Radcheck');

	public function get_profile_for_user(){

		if(
			isset($this->request->query['username'])
		){

			$username		= $this->request->query['username'];
			$profile		= false;
			$q_r 			= $this->Radcheck->find('first',
				array('conditions' => array('Radcheck.username' => $username,'Radcheck.attribute' => 'User-Profile'))
			);
			if($q_r){
			    $profile = $q_r['Radcheck']['value'];
			}

			$data = array('profile' => $profile);
			$this->set(array(
                'success'   => true,
                'data'      => $data,
                '_serialize' => array('success','data')
            ));

		}else{
			$this->set(array(
                'success'   => false,
                'message'   => array('message' => "Require a valid MAC address and username in the query string"),
                '_serialize' => array('success','message')
            ));
		}
	}
}
?>
