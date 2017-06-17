<?php
class DataUsageController extends AppController {

    public $name        = 'DataUsage';
    public $uses        = array('UserStat','Voucher','PermanentUser','Device');
    public $components  = array('TimeCalculations','Formatter');
    protected $base     = "Access Providers/Controllers/DataUsage/";
    
    protected   $type       = false;
    protected   $item_name  = false;
    protected   $base_search= false;

    protected $fields   = array(
        'sum(UserStat.acctinputoctets)  as data_in',
        'sum(UserStat.acctoutputoctets) as data_out',
        'sum(UserStat.acctoutputoctets)+ sum(UserStat.acctinputoctets) as data_total',
    );


    //--Read (the whole lot)
    public function usage_for_realm() {

        $data = array();
           
        $this->base_search = $this->_base_search();
        $today = date('Y-m-d', time());
       
            
        //________ DAILY _________      
        
        //-- Only if $this->type = 'realm' do we need theser --
        if($this->type == 'realm'){
            $data['daily']['top_ten']   = $this->_getTopTen($today,'day');
            $data['weekly']['top_ten']  = $this->_getTopTen($today,'week');
            $data['monthly']['top_ten'] = $this->_getTopTen($today,'month');
            
            //Also the active sessions
            $active_sessions = array();
            $radacct = ClassRegistry::init('Radacct');
            $radacct->contain();
            $q_acct = $radacct->find('all',array(
                'conditions' => array(
                    'Radacct.realm' => $this->item_name,
                    'Radacct.acctstoptime' => NULL
                )
            ));
            foreach($q_acct as $i){
                $online_time    = time()-strtotime($i['Radacct']['acctstarttime']);
                $active         = true; 
                $online_human   = $this->TimeCalculations->time_elapsed_string($i['Radacct']['acctstarttime'],false,true);
                array_push($active_sessions,array(
                    'id'                => intval($i['Radacct']['radacctid']),
                    'username'          => $i['Radacct']['username'],
                    'callingstationid'  => $i['Radacct']['callingstationid'],
                    'online_human'      => $online_human,
                    'online'            => $online_time
                ));
            }
            $data['daily']['active_sessions'] = $active_sessions;
            
        }
        
        //____ Get some Dope on the user if it is a user
        if($this->type == 'user'){
            
            $data['user_detail'] = $this->_getUserDetail();
        
        }
        
        
        $data['daily']['graph']     = $this->_getDailyGraph($today);
        $data['daily']['totals']    = $this->_getTotal($today,'day');
        
        //______ WEEKLY ____
        $data['weekly']['totals']   = $this->_getTotal($today,'week');
        $data['weekly']['graph']    =  $this->_getWeeklyGraph($today);
        
        //_____ MONTHLY ___
        $data['monthly']['graph']   =  $this->_getMonthlyGraph($today);
        $data['monthly']['totals']  = $this->_getTotal($today,'month');
  
        $this->set(array(
            'data' => $data,
            'success' => true,
            '_serialize' => array('data','success')
        ));
    }
     
    private function _getTotal($day,$span){
    
        $totals         = array();
        $conditions     = $this->base_search;
        
        if($span == 'day'){
            $slot_start     = "$day ".'00:00:00';
            $slot_end       = "$day ".'23:59:59';
        }
        
        if($span == 'week'){
            $pieces         = explode('-', $day);
            $start_day      = date('Y-m-d', strtotime('this week', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));
            $pieces         = explode('-',$start_day);
            $end_day        = date('Y-m-d',strtotime('+7 day', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));
              
            $slot_start     = "$start_day ".'00:00:00';
            $slot_end       = "$end_day ".'23:59:59';
        }
        
         if($span == 'month'){
            //$givenday = date("w", mktime(0, 0, 0, MM, dd, yyyy));
            $pieces         = explode('-', $day);
            $start_day      = date('Y-m-d', strtotime('first day of', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));
            $end            = cal_days_in_month(CAL_GREGORIAN, $pieces[1], $pieces[0]); 
            $end_day        = date('Y-m-d',strtotime('+'.$end.' day', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));
              
            $slot_start     = "$start_day ".'00:00:00';
            $slot_end       = "$end_day ".'23:59:59';
        }
        
        
        array_push($conditions,array('UserStat.timestamp >=' => $slot_start));
        array_push($conditions,array('UserStat.timestamp <=' => $slot_end));
        
        $q_r = $this->{$this->modelClass}->find('first', 
                array(
                    'conditions'    => $conditions,            
                    'fields'        => $this->fields
                ));
                
        $totals['type']         = $this->type;
        $totals['item_name']    = $this->item_name;
                 
        if($q_r){
            $totals['data_in']      = $q_r[0]['data_in'];
            $totals['data_out']     = $q_r[0]['data_out'];
            $totals['data_total']   = $q_r[0]['data_total'];
        } 
        return $totals;
    
    }
    
    private function _getTopTen($day,$span){
    
        $top_ten        = array();
        $conditions     = $this->base_search;
        
        if($span == 'day'){
            $slot_start     = "$day ".'00:00:00';
            $slot_end       = "$day ".'23:59:59';
        }
        
        if($span == 'week'){
            $pieces         = explode('-', $day);
            $start_day      = date('Y-m-d', strtotime('this week', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));
            $pieces         = explode('-',$start_day);
            $end_day        = date('Y-m-d',strtotime('+7 day', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));
              
            $slot_start     = "$start_day ".'00:00:00';
            $slot_end       = "$end_day ".'23:59:59';
        }
        
         if($span == 'month'){
            //$givenday = date("w", mktime(0, 0, 0, MM, dd, yyyy));
            $pieces         = explode('-', $day);
            $start_day      = date('Y-m-d', strtotime('first day of', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));
            $end            = cal_days_in_month(CAL_GREGORIAN, $pieces[1], $pieces[0]); 
            $end_day        = date('Y-m-d',strtotime('+'.$end.' day', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));
              
            $slot_start     = "$start_day ".'00:00:00';
            $slot_end       = "$end_day ".'23:59:59';
        }
        
        array_push($conditions,array('UserStat.timestamp >=' => $slot_start));
        array_push($conditions,array('UserStat.timestamp <=' => $slot_end));
        
        $fields = $this->fields;
        array_push($fields, 'UserStat.username');
        
        $q_r = $this->{$this->modelClass}->find('all', 
                array(
                    'conditions'    => $conditions,            
                    'fields'        => $fields,
                    'group'         => array('UserStat.username'),
                    'order'         => array('data_total DESC'),
                    'limit'         => 10
                ));
    
        $id = 1;
        foreach($q_r as $tt){
            $username = $tt['UserStat']['username'];
            array_push($top_ten, 
                array(
                    'id'            => $id,
                    'username'      => $username,
                    'data_in'       => $tt[0]['data_in'],
                    'data_out'      => $tt[0]['data_out'],
                    'data_total'    => $tt[0]['data_total'],
                )
            );
            $id++;
        } 
        return $top_ten;
    }
    
    private function _getDailyGraph($day){

        $items  = array();
        $start  = 0;
        $end    = 24;
        $base_search = $this->_base_search();

        while($start < $end){
            $slot_start  = "$day ".sprintf('%02d', $start).':00:00';
            $slot_end    = "$day ".sprintf('%02d', $start).':59:59';
            $start++;
        
            $conditions = $base_search;
            array_push($conditions,array('UserStat.timestamp >=' => $slot_start));
            array_push($conditions,array('UserStat.timestamp <=' => $slot_end));

            $q_r = $this->{$this->modelClass}->find('first', 
                array(
                    'conditions'    => $conditions,            
                    'fields'        => $this->fields
                ));
                
            if($q_r){
                $d_in   = $q_r[0]['data_in'];
                $d_out  = $q_r[0]['data_out'];
                array_push($items, array('id' => $start, 'time_unit' => $start, 'data_in' => $d_in, 'data_out' => $d_out));     
            }
        }
        return(array('items' => $items));
    }
    
    private function _getWeeklyGraph($day){

        $items          = array();
    
        //With weekly we need to find the start of week for the specified date
        $pieces     = explode('-', $day);
        $start_day  = date('Y-m-d', strtotime('this week', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));

        //Prime the days
        $slot_start = "$start_day 00:00:00";
        $slot_end   = "$start_day 59:59:59";
        $days       = array("Monday", "Tuesday","Wednesday", "Thusday", "Friday", "Saturday", "Sunday");
        $count      = 1;

        $base_search = $this->_base_search();
       
        foreach($days as $d){

            $conditions = $base_search;
            array_push($conditions,array('UserStat.timestamp >=' => $slot_start));
            array_push($conditions,array('UserStat.timestamp <=' => $slot_end));

            $q_r = $this->{$this->modelClass}->find('first', 
                array(
                    'conditions'    => $conditions,            
                    'fields'        => $this->fields
                ));
            if($q_r){
                $d_in   = $q_r[0]['data_in'];
                $d_out  = $q_r[0]['data_out'];
                array_push($items, array('id' => $count, 'time_unit' => $d, 'data_in' => $d_in, 'data_out' => $d_out));     
            }

            //Get the nex day in the slots (we move one day on)
            $pieces     = explode('-',$start_day);
            $start_day  = date('Y-m-d',strtotime('+1 day', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));
            $slot_start = "$start_day 00:00:00";
            $slot_end   = "$start_day 59:59:59";
            $count++;
        }
        return(array('items' => $items));             
    }
    
    private function _getMonthlyGraph($day){

        $items          = array();
        //With weekly we need to find the start of week for the specified date
        //$givenday = date("w", mktime(0, 0, 0, MM, dd, yyyy));
        $pieces     = explode('-', $day);
        $start_day  = date('Y-m-d', strtotime('first day of', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));


        //Prime the days
        $slot_start = "$start_day 00:00:00";
        $slot_end   = "$start_day 59:59:59";

        $start  = 1;
        $end    = cal_days_in_month(CAL_GREGORIAN, $pieces[1], $pieces[0]); 
        $base_search = $this->_base_search();

        while($start <= $end){

            $conditions = $base_search;
            array_push($conditions,array('UserStat.timestamp >=' => $slot_start));
            array_push($conditions,array('UserStat.timestamp <=' => $slot_end));
            
            $this->{$this->modelClass}->contain();
            //print_r($conditions);
            
            
            $q_r = $this->{$this->modelClass}->find('first', 
                array(
                    'conditions'    => $conditions,            
                    'fields'        => $this->fields
                ));
            if($q_r){
                $d_in   = $q_r[0]['data_in'];
                $d_out  = $q_r[0]['data_out'];

                array_push($items, array('id' => $start, 'time_unit' => $start, 'data_in' => $d_in, 'data_out' => $d_out));     
            }

            //Get the nex day in the slots (we move one day on)
            $pieces     = explode('-',$start_day);
            $start_day  = date('Y-m-d',strtotime('+1 day', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));
            $slot_start = "$start_day 00:00:00";
            $slot_end   = "$start_day 59:59:59";
            $start++;
        }
        return(array('items' => $items));           
    }

    
    
    private function _base_search(){

        $type           = 'realm';
        $base_search    = array();
        $username       = $this->request->query['username'];
        $this->item_name= $username;

        if(isset($this->request->query['type'])){
            $type = $this->request->query['type'];
            //Permanent users an vouchers
            if(($type == 'permanent')||($type == 'voucher')||($type == 'user')||($type == 'activity_viewer')){
                array_push($base_search,array('UserStat.username' => $username));
        
            }
            //Devices
            if($type == 'device'){
                array_push($base_search,array('UserStat.callingstationid' => $username));
            }
            //Realms
            if($type == 'realm'){
                $realm = ClassRegistry::init('Realm');
                $realm->contain();
                $q_r = $realm->findById($username);
                        
                if($q_r){ 
                    $realm_name = $q_r['Realm']['name'];
                    $this->item_name= $realm_name;
                    array_push($base_search,array('UserStat.realm' => $realm_name));
                }
            }
            //Nas
            if($type == 'nas'){
                $na = ClassRegistry::init('Na');
                $na->contain();
                $q_r = $na->findById($username);
                if($q_r){ 
                    $nas_identifier = $q_r['Na']['nasidentifier'];
                    $this->item_name= $nas_identifier;
                    array_push($base_search,array('UserStat.nasidentifier' => $nas_identifier));
                }
            }
            
            //Dynamic clients
            if($type == 'dynamic_client'){
                $dc = ClassRegistry::init('DynamicClient');
                $dc->contain();
                $q_r = $dc->findById($username);
                if($q_r){ 
                    $this->item_name= $nas_identifier; 
                    $nas_identifier = $q_r['DynamicClient']['nasidentifier'];
                    array_push($base_search,array('UserStat.nasidentifier' => $nas_identifier));
                }
            } 
            
            $this->type = $type;    
        }
        return $base_search;
    }
    
    private function _getUserDetail(){
    
        $found = false;
    
        $user_detail = array();
        $username = $this->item_name;
        
        //Test to see if it is a Voucher
        $this->Voucher->contain();
        $q_v = $this->Voucher->find('first',array('conditions' =>array('Voucher.name' => $username)));
       // print_r($q_v);
        if($q_v){
        
            $user_detail['username'] = $username;
            
            $user_detail['type']    = 'voucher';
            $user_detail['profile'] = $q_v['Voucher']['profile'];
            $user_detail['created'] = $this->TimeCalculations->time_elapsed_string($q_v['Voucher']['created'],false,false);
            $user_detail['status']  = $q_v['Voucher']['status'];
            if($q_v['Voucher']['last_reject_time'] != null){
                $user_detail['last_reject_time'] = $this->TimeCalculations->time_elapsed_string($q_v['Voucher']['last_reject_time'],false,false);
                $user_detail['last_reject_message'] = $q_v['Voucher']['last_reject_message'];
            }
            
            if($q_v['Voucher']['last_accept_time'] != null){
                $user_detail['last_accept_time'] = $this->TimeCalculations->time_elapsed_string($q_v['Voucher']['last_accept_time'],false,false);
            }
            
            if($q_v['Voucher']['data_cap'] != null){
                $user_detail['data_cap'] = $this->Formatter->formatted_bytes($q_v['Voucher']['data_cap']);
            }
            
            if($q_v['Voucher']['data_used'] != null){
                $user_detail['data_used'] = $this->Formatter->formatted_bytes($q_v['Voucher']['data_used']);
            }
            
            if($q_v['Voucher']['perc_data_used'] != null){
                $user_detail['perc_data_used'] = $q_v['Voucher']['perc_data_used'];
            }
            
            if($q_v['Voucher']['time_cap'] != null){
                $user_detail['time_cap'] = $this->Formatter->formatted_seconds($q_v['Voucher']['time_cap']);
            }
            
            if($q_v['Voucher']['time_used'] != null){
                $user_detail['time_used'] = $this->Formatter->formatted_seconds($q_v['Voucher']['time_used']);
            }
            
            if($q_v['Voucher']['perc_time_used'] != null){
                $user_detail['perc_time_used'] = $q_v['Voucher']['perc_time_used'];
            }
            $found = true;
   
        }
        
        if(!$found){
            $this->PermanentUser->contain();
            $q_pu = $this->PermanentUser->find('first',array('conditions' =>array('PermanentUser.username' => $username)));
        
           // print_r($q_pu);
            if($q_pu){
            
                $user_detail['username']    = $username;
                $user_detail['type']        = 'user';
                $user_detail['profile']     = $q_pu['PermanentUser']['profile'];
                $user_detail['created']     = $this->TimeCalculations->time_elapsed_string($q_pu['PermanentUser']['created'],false,false);
                if($q_pu['PermanentUser']['last_reject_time'] != null){
                    $user_detail['last_reject_time'] = $this->TimeCalculations->time_elapsed_string($q_pu['PermanentUser']['last_reject_time'],false,false);
                    $user_detail['last_reject_message'] = $q_pu['PermanentUser']['last_reject_message'];
                }
            
                if($q_pu['PermanentUser']['last_accept_time'] != null){
                    $user_detail['last_accept_time'] = $this->TimeCalculations->time_elapsed_string($q_pu['PermanentUser']['last_accept_time'],false,false);
                }
            
                if($q_pu['PermanentUser']['data_cap'] != null){
                    $user_detail['data_cap'] = $this->Formatter->formatted_bytes($q_pu['PermanentUser']['data_cap']);
                }
            
                if($q_pu['PermanentUser']['data_used'] != null){
                    $user_detail['data_used'] = $this->Formatter->formatted_bytes($q_pu['PermanentUser']['data_used']);
                }
            
                if($q_pu['PermanentUser']['perc_data_used'] != null){
                    $user_detail['perc_data_used'] = $q_pu['PermanentUser']['perc_data_used'];
                }
            
                if($q_pu['PermanentUser']['time_cap'] != null){
                    $user_detail['time_cap'] = $this->Formatter->formatted_seconds($q_pu['PermanentUser']['time_cap']);
                }
            
                if($q_pu['PermanentUser']['time_used'] != null){
                    $user_detail['time_used'] = $this->Formatter->formatted_seconds($q_pu['PermanentUser']['time_used']);
                }
            
                if($q_pu['PermanentUser']['perc_time_used'] != null){
                    $user_detail['perc_time_used'] = $q_pu['PermanentUser']['perc_time_used'];
                }
                $found = true;
            
            }
        
        }   
        return $user_detail;
    }

    
}
?>
