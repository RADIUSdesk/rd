<?php
class FreeRadiusController extends AppController {


    public $name       = 'PhpPhrases';
    public $components = array('Aa');
    protected $base    = "Access Providers/Controllers/FreeRadius/"; //Required for AP Rights

    public function index(){
    //== AP + Root ==

        //First the auth
        $type = 'auth';
        exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl stats $type",$output_auth);
        $items = array();
        $items['auth_basic']  = array();
        $items['auth_detail'] = array(); 

        if (preg_match("/requests/i", $output_auth[0])) {
            foreach($output_auth as $i){
                $clean = trim($i);
                $clean = preg_replace("/\s+/", ";", $clean);
                $e = explode(';',$clean);
                
                //First the basics
                if(($e[0] == 'accepts')&&(intval($e[1]) != 0)){
                    array_push($items['auth_basic'], array('name' => __("Accepted"), 'data' => intval($e[1])));
                }
                if(($e[0] == 'rejects')&&(intval($e[1]) != 0)){
                    array_push($items['auth_basic'], array('name' => __("Rejected"), 'data' => intval($e[1])));
                }
                
                //Then the detail
                if(($e[0] == 'responses')&&(intval($e[1]) != 0)){
                    array_push($items['auth_detail'], array('name' => __("Responses"), 'data' => intval($e[1])));
                }

                if(($e[0] == 'challenges')&&(intval($e[1]) != 0)){
                    array_push($items['auth_detail'], array('name' => __("Challenges"), 'data' => intval($e[1])));
                }

                if(($e[0] == 'dup')&&(intval($e[1]) != 0)){
                    array_push($items['auth_detail'], array('name' => __("Duplicates"), 'data' => intval($e[1])));
                }

                if(($e[0] == 'invalid')&&(intval($e[1]) != 0)){
                    array_push($items['auth_detail'], array('name' => __("Invalid"), 'data' => intval($e[1])));
                }

                if(($e[0] == 'malformed')&&(intval($e[1]) != 0)){
                    array_push($items['auth_detail'], array('name' => __("Malformed"), 'data' => intval($e[1])));
                }

                if(($e[0] == 'bad_signature')&&(intval($e[1]) != 0)){
                    array_push($items['auth_detail'], array('name' => __("Bad Signature"), 'data' => intval($e[1])));
                }

                if(($e[0] == 'dropped')&&(intval($e[1]) != 0)){
                    array_push($items['auth_detail'], array('name' => __("Dropped"), 'data' => intval($e[1])));
                }

                if(($e[0] == 'unknown_types')&&(intval($e[1]) != 0)){
                    array_push($items['auth_detail'], array('name' => __("Unknown types"), 'data' => intval($e[1])));
                }
                
                if(($e[0] == 'bad_authenticator')&&(intval($e[1]) != 0)){
                    array_push($items['auth_detail'], array('name' => __("Bad Authenticator"), 'data' => intval($e[1])));
                }
            }
        }

        $type = 'acct';
        exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl stats $type",$output_acct);

        $items['acct_detail'] = array();

        if (preg_match("/requests/i", $output_acct[0])) {
            foreach($output_acct as $i){
                $clean = trim($i);
                $clean = preg_replace("/\s+/", ";", $clean);
                $e = explode(';',$clean);
              
                //Then the detail
                if(($e[0] == 'responses')&&(intval($e[1]) != 0)){
                    array_push($items['acct_detail'], array('name' => __("Responses"), 'data' => intval($e[1])));
                }
                if(($e[0] == 'dup')&&(intval($e[1]) != 0)){
                    array_push($items['acct_detail'], array('name' => __("Duplicates"), 'data' => intval($e[1])));
                }

                if(($e[0] == 'invalid')&&(intval($e[1]) != 0)){
                    array_push($items['acct_detail'], array('name' => __("Invalid"), 'data' => intval($e[1])));
                }

                if(($e[0] == 'malformed')&&(intval($e[1]) != 0)){
                    array_push($items['acct_detail'], array('name' => __("Malformed"), 'data' => intval($e[1])));
                }

                if(($e[0] == 'bad_signature')&&(intval($e[1]) != 0)){
                    array_push($items['acct_detail'], array('name' => __("Bad Signature"), 'data' => intval($e[1])));
                }

                if(($e[0] == 'dropped')&&(intval($e[1]) != 0)){
                    array_push($items['acct_detail'], array('name' => __("Dropped"), 'data' => intval($e[1])));
                }

                 if(($e[0] == 'unknown_types')&&(intval($e[1]) != 0)){
                    array_push($items['acct_detail'], array('name' => __("Unknown types"), 'data' => intval($e[1])));
                }
            }
        }

        $this->set(array(
            'items'         => $items,
            'success'       => true,
            '_serialize'    => array('success', 'items')
        )); 
    }

    public function status(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $pid = exec('pidof freeradius');
        $items = array();
        $items['pid'] = intval($pid);
        if($pid == ''){
            $items['running'] = false; 
        }else{
            $items['running'] = true; 
        }

        $this->set(array(
            'data'          => $items,
            'success'       => true,
            '_serialize'    => array('success', 'data')
        ));
    }

    public function start(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl start freeradius");
        $items = array();
        
        $this->set(array(
            'data'          => $items,
            'success'       => true,
            '_serialize'    => array('success', 'data')
        ));
    }

    public function stop(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl stop freeradius");
        $items = array();
        
        $this->set(array(
            'data'          => $items,
            'success'       => true,
            '_serialize'    => array('success', 'data')
        ));
    }

    public function info(){
    
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $items = array();

        exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl uptime freeradius",$output);
        if(count($output)>0){
            $uptime = $output[0];
            $items['uptime'] = $uptime;
        }else{
            $this->set(array(
                'success'       => false,
                '_serialize'    => array('success')
            ));
            return;
        }
        
        unset($output);
        exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl version freeradius",$output);
        if(count($output)>0){
            $version = $output[0];
            $items['version'] = $version;
        }

        unset($output);
        exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl clients freeradius",$output);
        if(count($output)>0){
            $clients = array();
            $id = 1;
            foreach($output as $i){
                $t_val = trim($i, " \t.");
                array_push($clients,array('id' => $id, 'name' => $t_val));
                $id++;
            }
            $items['clients'] = $clients;
        }
        
        unset($output);
        exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl modules freeradius",$output);
        if(count($output)>0){
            $modules = array();
            $id = 1;
            foreach($output as $i){
                $t_val = trim($i, " \t.");
                array_push($modules,array('id' => $id, 'name' => $t_val));
                $id++;
            }
            $items['modules'] = $modules;
        }
         
        
        $this->set(array(
            'data'          => $items,
            'success'       => true,
            '_serialize'    => array('success', 'data')
        ));
    }

    public function status_debug(){
    //== Only Root ==
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $items = array();
        exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl debug level",$output);
        if(count($output)>0){
            $level = $output[0];
            $items['level'] = intval($level);
            if($items['level'] > 0){
                //Check for a timeout value (there should be one)
                $c          = ClassRegistry::init('Check');
                //Check for existing ones
                $q_r        = $c->find('first',array('conditions' =>array('Check.name' => 'debug_timeout')));
                if($q_r){
                    $time_added = $q_r['Check']['value']-time();
                    if($time_added > 0){
                        $items['time_added'] = $time_added;
                    }    
                }
            }
        }

        unset($output);
        exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl debug condition",$output);
        if(count($output)>0){
            $condition = $output[0];
            $items['condition'] = $condition;
        }      
        $this->set(array(
            'data'          => $items,
            'success'       => true,
            '_serialize'    => array('success', 'data')
        ));
    }

    public function start_debug(){
    //== Only Root ==

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $items = array();
        exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl debug start",$output);

        //Check for filters
        if((isset($this->request->query['nas_id']))&&(!isset($this->request->query['username']))){
            $q = ClassRegistry::init('Na')->findById($this->request->query['nas_id']);
            $ip = $q['Na']['nasname'];
            exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl debug condition '(Packet-Src-IP-Address == $ip)'",$output);
        }

        if((isset($this->request->query['username']))&&(!isset($this->request->query['nas_id']))){
            $username = $this->request->query['username'];
            exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl debug condition '(User-Name == $username)'",$output);
        }

        if((isset($this->request->query['username']))&&(isset($this->request->query['nas_id']))){
            $q = ClassRegistry::init('Na')->findById($this->request->query['nas_id']);
            $ip = $q['Na']['nasname'];
            $username = $this->request->query['username'];
            $condition = "((User-Name == $username)&&(Packet-Src-IP-Address == $ip))";
            exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl debug condition '$condition'",$output);
        }

        //Start the timeout
        $c          = ClassRegistry::init('Check');
        $d          = array();
        //Check for existing ones
        $q_r        = $c->find('first',array('conditions' =>array('Check.name' => 'debug_timeout')));
        if($q_r){
            $d['id'] = $q_r['Check']['id'];    
        }

        $timeout = time()+360;
        $d['name']  = 'debug_timeout';
        $d['value'] = $timeout;
        $c->save($d);

        $items['timeout']   = $timeout;
        $items['time_added']= 360;
    
        $this->set(array(
            'data'          => $items,
            'success'       => true,
            '_serialize'    => array('success', 'data')
        ));
    }

    public function stop_debug(){
    //== Only Root ==

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $items = array();
        exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl debug stop",$output);

        //Clear the timeout
        $c = ClassRegistry::init('Check');
        $c->deleteAll(array('Check.name' => 'debug_timeout'));

        $this->set(array(
            'data'          => $items,
            'success'       => true,
            '_serialize'    => array('success', 'data')
        ));
    }

    public function time_debug(){
    //== Only Root ==
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        $items = array();

        //Clear the timeout
        $c = ClassRegistry::init('Check');
        //Check for existing ones
        $q_r        = $c->find('first',array('conditions' =>array('Check.name' => 'debug_timeout')));
        if($q_r){
            $id     = $q_r['Check']['id'];
            $value  = $q_r['Check']['value']+360;
            $time_added = $value-time();
            $c->id = $id;
            $c->saveField('value', $value);
            $items['time_added'] = $time_added;    
        }

        $this->set(array(
            'data'          => $items,
            'success'       => true,
            '_serialize'    => array('success', 'data')
        ));
    }

    public function test_radius(){

        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if(isset($this->request->data['user_type'])){

            if($this->request->data['user_type'] == 'permanent'){
                $q_r        = ClassRegistry::init('PermanentUser')->findById($this->request->data['user_id']);
                $username   = $q_r['PermanentUser']['username'];
                $q_r        = ClassRegistry::init('Radcheck')->find('first', 
                    array('conditions' =>
                        array('Radcheck.username' => $username,'Radcheck.attribute' => 'Cleartext-Password')
                    )
                );
                $pwd        = $q_r['Radcheck']['value'];
            }

            if($this->request->data['user_type'] == 'device'){
                $q_r        = ClassRegistry::init('Device')->findById($this->request->data['device_id']);
                $username   = $q_r['Device']['name'];
                $pwd        = $username;
            }

            if($this->request->data['user_type'] == 'voucher'){
                $v = ClassRegistry::init('Voucher');
                $v->contain();
                $q_r        = $v->findById($this->request->data['voucher_id']);
                $username   = $q_r['Voucher']['name'];
                $pwd        = $q_r['Voucher']['password'];  
            }

        }

        $items = array();

        $items['request']['username']   = $username;
        $items['request']['password']   = $pwd;
        exec("perl /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radscenario.pl $username $pwd",$output);

        $send_flag      = false;
        $receive_flag   = false;
        $fail_flag      = true;

        $send_data      = array();
        $receive_data   = array();

        $line           = 0;

        foreach($output as $i){
            $i = trim($i);
            if (preg_match("/Sent Access-Request/", $i)) {
                $send_flag  = true;
                $send_line  = $line;
            }

            if (preg_match("/^Received/", $i)) { //Failure
                $send_flag      = false;
                $receive_flag   = true;
                $receive_line   = $line;
            }

            if (preg_match("/^Received Access-Accept/", $i)) { //Failure
                $fail_flag      = false;
            }

            if(($send_flag == true)&&($line > $send_line)){
                if($i !=''){
                    array_push($send_data,$i);
                }   
            }

            if(($receive_flag == true)&&($line > $receive_line)){
                if($i !=''){
                    array_push($receive_data,$i);
                }    
            }

            $line++;
        }

        $items['send']      = $send_data;
        $items['received']  = $receive_data;
        $items['failed']    = $fail_flag;

        $this->set(array(
            'data'          => $items,
            'success'       => true,
            '_serialize'    => array('success', 'data')
        ));
    }
}
?>
