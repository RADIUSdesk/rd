<?php
App::uses('AppController', 'Controller');

class UserStatsController extends AppController {

    public $name       = 'UserStats';
    public $components = array('Aa');
    protected $base    = "Access Providers/Controllers/UserStats/";

    protected   $fields = array(
                            'sum(UserStat.acctinputoctets)  as data_in',
                            'sum(UserStat.acctoutputoctets) as data_out',
                            'sum(UserStat.acctoutputoctets)+ sum(UserStat.acctinputoctets) as total',
                        );

    public function index(){

        $day    = '2013-09-18'; //Temp value
        $span   = 'daily';      //Can be daily weekly or monthly
       
        if(isset($this->request->query['day'])){
            //Format will be: 2013-09-18T00:00:00
            $pieces = explode('T',$this->request->query['day']);
            $day = $pieces[0];
        }

        if(isset($this->request->query['span'])){
            $span = $this->request->query['span'];
        }

        $ret_info = array();

        //Daily Stats
        if($span == 'daily'){
            $ret_info   = $this->_getDaily($day);
        }

        //Weekly
        if($span == 'weekly'){
           $ret_info    = $this->_getWeekly($day);
        }

        //Monthly
        if($span == 'monthly'){
            $ret_info    = $this->_getMonthly($day);  
        }

        if($ret_info){
            $this->set(array(
                'items'         => $ret_info['items'],
                'success'       => true,
                'totalIn'       => $ret_info['total_in'],
                'totalOut'      => $ret_info['total_out'],
                'totalInOut'    => $ret_info['total_in_out'],
                '_serialize'    => array('items','totalIn','totalOut', 'totalInOut','success')
            ));
        }else{
            $this->set(array(
                'success'       => false,
                '_serialize'    => array('success')
            ));
        }
    }

    private function _getDaily($day){

        $items          = array();
        $total_in       = 0;
        $total_out      = 0;
        $total_in_out   = 0;

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
                $d_in           = $q_r[0]['data_in'];
                $total_in       = $total_in + $d_in;

                $d_out          = $q_r[0]['data_out'];
                $total_out      = $total_out + $d_out;
                $total_in_out   = $total_in_out + ($d_in + $d_out);
                array_push($items, array('id' => $start, 'time_unit' => $start, 'data_in' => $d_in, 'data_out' => $d_out));     
            }
        }
        return(array('items' => $items, 'total_in' => $total_in, 'total_out' => $total_out, 'total_in_out' => $total_in_out));
    }

    private function _getWeekly($day){

        $items          = array();
        $total_in       = 0;
        $total_out      = 0;
        $total_in_out   = 0;

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
                $d_in           = $q_r[0]['data_in'];
                $total_in       = $total_in + $d_in;

                $d_out          = $q_r[0]['data_out'];
                $total_out      = $total_out + $d_out;
                $total_in_out   = $total_in_out + ($d_in + $d_out);
                array_push($items, array('id' => $count, 'time_unit' => $d, 'data_in' => $d_in, 'data_out' => $d_out));     
            }

            //Get the nex day in the slots (we move one day on)
            $pieces     = explode('-',$start_day);
            $start_day  = date('Y-m-d',strtotime('+1 day', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));
            $slot_start = "$start_day 00:00:00";
            $slot_end   = "$start_day 59:59:59";
            $count++;
        }
        return(array('items' => $items, 'total_in' => $total_in, 'total_out' => $total_out, 'total_in_out' => $total_in_out));             
    }

    private function _getMonthly($day){

        $items          = array();
        $total_in       = 0;
        $total_out      = 0;
        $total_in_out   = 0;

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
                $d_in           = $q_r[0]['data_in'];
                $total_in       = $total_in + $d_in;

                $d_out          = $q_r[0]['data_out'];
                $total_out      = $total_out + $d_out;
                $total_in_out   = $total_in_out + ($d_in + $d_out);
                array_push($items, array('id' => $start, 'time_unit' => $start, 'data_in' => $d_in, 'data_out' => $d_out));     
            }

            //Get the nex day in the slots (we move one day on)
            $pieces     = explode('-',$start_day);
            $start_day  = date('Y-m-d',strtotime('+1 day', mktime(0, 0, 0, $pieces[1],$pieces[2], $pieces[0])));
            $slot_start = "$start_day 00:00:00";
            $slot_end   = "$start_day 59:59:59";
            $start++;
        }
        return(array('items' => $items, 'total_in' => $total_in, 'total_out' => $total_out, 'total_in_out' => $total_in_out));           
    }

    private function _base_search(){

        $type           = 'permanent';
        $base_search    = array();
        $username       = $this->request->query['username'];

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
                    array_push($base_search,array('UserStat.nasidentifier' => $nas_identifier));
                }
            }
            
            //Dynamic clients
            if($type == 'dynamic_client'){
                $dc = ClassRegistry::init('DynamicClient');
                $dc->contain();
                $q_r = $dc->findById($username);
                if($q_r){  
                    $nas_identifier = $q_r['DynamicClient']['nasidentifier'];
                    array_push($base_search,array('UserStat.nasidentifier' => $nas_identifier));
                }
            }     
        }
        return $base_search;
    }

}
