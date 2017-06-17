<?php
//----------------------------------------------------------
//---- Author: Dirk van der Walt
//---- License: GPL v3
//---- Description: 
//---- Date: 3-4-2015
//------------------------------------------------------------

App::uses('Component', 'Controller');

class CountersComponent extends Component {

	public function return_counter_data($profile_name,$type) {
        $counters = array();
        if(($type == 'voucher')||($type == 'user')||($type == 'device')){ //nothing fancy here initially
			$this->_init_models();
            $counters = $this->_find_counters($profile_name);
        }
        return $counters;    
    }

	private function _init_models(){
		$this->Radusergroup  = ClassRegistry::init('Radusergroup');
		$this->Radgroupcheck = ClassRegistry::init('Radgroupcheck');
	}

	private function _find_counters($username){

        $counters = array();
        //First we need to find all the goupnames associated with this profile
        $this->Radusergroup->contain();
        $q_r = $this->Radusergroup->find('all',array('conditions' => array('Radusergroup.username' => $username)));

        foreach($q_r as $i){
            $g  = $i['Radusergroup']['groupname'];
            $tc = $this->_look_for_time_counters($g);
            if($tc){
                $counters['time'] = $tc;
            }

            $dc = $this->_look_for_data_counters($g);
            if($dc){
                $counters['data'] = $dc;
            }
        }
        return $counters;
    }


    private function _look_for_time_counters($groupname){
        $counter = false;
        $cap     = $this->_query_radgroupcheck($groupname,'Rd-Cap-Type-Time');
        if($cap){
            $counter            = array();
            $counter['cap']     = $cap;
            $counter['reset']   = $this->_query_radgroupcheck($groupname,'Rd-Reset-Type-Time');
            $counter['value']   = $this->_query_radgroupcheck($groupname,'Rd-Total-Time');

			//Defaults for mac_counter and reset_interval
			$mac_counter		= false;
			$reset_interval		= false;
			if($counter['reset'] == 'dynamic'){
				$reset_interval = $this->_query_radgroupcheck($groupname,'Rd-Reset-Interval-Time');
			}
			$mac_counter		= $this->_query_radgroupcheck($groupname,'Rd-Mac-Counter-Time');
			$counter['reset_interval'] 	= $reset_interval;
			$counter['mac_counter'] 	= $mac_counter;

            //Rd-Used-Time := "%{sql:SELECT IFNULL(SUM(AcctSessionTime),0) FROM radacct WHERE username='%{request:User-Name}'}"
        }
        return $counter;
    }

    private function _look_for_data_counters($groupname){
        $counter = false;
        $cap     = $this->_query_radgroupcheck($groupname,'Rd-Cap-Type-Data');
        if($cap){
            $counter = array();
            $counter['cap']     = $cap;
            $counter['reset']   = $this->_query_radgroupcheck($groupname,'Rd-Reset-Type-Data');
            $counter['value']   = $this->_query_radgroupcheck($groupname,'Rd-Total-Data');

			//Defaults for mac_counter and reset_interval
			$mac_counter		= false;
			$reset_interval		= false;
			if($counter['reset'] == 'dynamic'){
				$reset_interval = $this->_query_radgroupcheck($groupname,'Rd-Reset-Interval-Data');
			}
			$mac_counter		= $this->_query_radgroupcheck($groupname,'Rd-Mac-Counter-Data');
			$counter['reset_interval'] 	= $reset_interval;
			$counter['mac_counter'] 	= $mac_counter;
        }
        return $counter;
    }

    private function _query_radgroupcheck($groupname,$attribute){
        $retval = false;
        $this->Radgroupcheck->contain();
        $q_r = $this->Radgroupcheck->find('first',
            array('conditions' => array('Radgroupcheck.groupname' => $groupname, 'Radgroupcheck.attribute' => $attribute)
        ));
        if($q_r){
            $retval = $q_r['Radgroupcheck']['value'];
        }
        return $retval;
    }

}

?>
