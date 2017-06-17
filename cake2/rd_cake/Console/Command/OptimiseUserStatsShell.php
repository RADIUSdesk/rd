<?php
class OptimiseUserStatsShell extends AppShell {

    public $uses    = array('UserStat','Check','Radacct');

    private $start_time = false;

    public function main() {
    
        $this->_remove_old_records();
    
        $this->out("<comment>Goes through the user_stats table and reduce the entries per hour where it can</comment>");
        
        //Get the last end time(which should be this start time) else get the fist entry as a start time
        $q_r = $this->Check->find('first',array('conditions' => array('name' => 'user_stats_end_time')));
        
        if($q_r){
            $this->out("<info>Found and entry </info>");
            $this->start_time = $q_r['Check']['value'];
        
        }else{
            //Find the earliest entry
            $this->out("<info>Could not find an entry - use the first one if there is one</info>");         
            $q_r = $this->UserStat->find('first',array('order' => 'timestamp ASC'));
            if($q_r){
                $start_time = $q_r['UserStat']['timestamp'];  
                $date_time  = explode(" ", $start_time);
                $time       = $date_time[1];
                $time_pieces= explode(":",$time);
                $hour_start = $time_pieces[0].":00:00";
                $start_time = $date_time[0]." $hour_start";
                $this->start_time = $start_time;
            }
        }
   
        if($this->start_time){
            $this->_do_optimisation();
        }   
    }
    
    private function _remove_old_records(){
    
        $this->out("<info>Remove old entriesin user_stats and radacct</info>");    
        $now_wip        = date("Y-m-d H:i:s", time());
      //  $now_wip        = "2014-09-03 15:00:00";
        $now            = strtotime($now_wip);
        $start_stamp    = strtotime($this->start_time);
            
        //If the value of server_settings.user_stats_cut_off_days and server_settings.radacct_cut_off_days is set to something 
        //other than zero; remove records older than those days specified
        $us_cut_off = Configure::read('server_settings.user_stats_cut_off_days');
        if($us_cut_off){
            if($us_cut_off > 0){
                $us_cut_off_stamp = $now - ($us_cut_off * 86400);
                $us_cut_off_time  = date("Y-m-d H:i:s", $us_cut_off_stamp);
                print("Delete entries older than $us_cut_off_time\n");
                $this->UserStat->deleteAll(array('timestamp <= ' => $us_cut_off_time));
            }
        }
        
        $radacct_cut_off = Configure::read('server_settings.radacct_cut_off_days');
        if($radacct_cut_off){
            if($radacct_cut_off > 0){
                $radacct_cut_off_stamp = $now - ($radacct_cut_off * 86400);
                $radacct_cut_off_time  = date("Y-m-d H:i:s", $radacct_cut_off_stamp);
                print("Delete entries older than $radacct_cut_off_time\n");
                $this->Radacct->deleteAll(array('acctstoptime <= ' => $radacct_cut_off_time));
            }
        }
    }
    
    
    private function _do_optimisation() {
        $this->out("<info>Start optimisation process</info>");
        
        //Now must be the start of the current hour...
        $now_wip        = date("Y-m-d H:00:00", time());
      //  $now_wip        = "2014-09-03 15:00:00";
        $now            = strtotime($now_wip);
        $start_stamp    = strtotime($this->start_time);
        
        //Sometimes there are records that had acctinputoctets and acctoutputoctets as zero -> Delete them all they are useless
        $this->UserStat->deleteAll(array('UserStat.acctinputoctets' => 0,'UserStat.acctoutputoctets' => 0));
        
        //Add the user_stats_end_time if not there
        $q_r = $this->Check->find('first',array('conditions' => array('name' => 'user_stats_end_time')));
        if($q_r){
            $this->Check->id = $q_r['Check']['id'];
            $id = $q_r['Check']['id'];
            $this->Check->save(array('id' => $id,'value' => $now_wip));
        }else{ //Add it
            $this->Check->save(array('name' => 'user_stats_end_time','value' => $now_wip));
        }
        
        while($start_stamp < $now){
            $hour_start =  date("Y-m-d H:i:s", $start_stamp);
            $hour_end   =  date("Y-m-d H:59:59", $start_stamp);
            $this->out("<info>Optimising starting $hour_start ending $hour_end</info>");
            $start_stamp = $start_stamp + 3600;
            
            $q_r = $this->UserStat->find('all',array('conditions' => 
                array('UserStat.timestamp >=' => $hour_start,'UserStat.timestamp <=' => $hour_end)
            ));
            if($q_r){
                $this->out("<info>Found some data starting $hour_start ending $hour_end</info>");
                $data_cluster = array();
                foreach($q_r as $entry){
                
                    $callingstationid   = $entry['UserStat']['callingstationid'];
                    $nasidentifier      = $entry['UserStat']['nasidentifier'];
                    $id                 = $entry['UserStat']['id'];
                    $acctinputoctets    = $entry['UserStat']['acctinputoctets'];
                    $acctoutputoctets   = $entry['UserStat']['acctoutputoctets'];
                    $timestamp          = $entry['UserStat']['timestamp'];
                   
                    if(array_key_exists($nasidentifier,$data_cluster)){
            
                        if(array_key_exists($callingstationid,$data_cluster[$nasidentifier])){
                            //We need to 1.) Add to existing entry; 2.) then delete this entry
                            $this->out("<info>Found Existing data this slot for  $callingstationid</info>");
                            
                            $this->out("<info>Need to add $acctinputoctets AND $acctoutputoctets</info>");
                            $current_in     = $data_cluster[$nasidentifier][$callingstationid]['acctinputoctets'];
                            $current_out    = $data_cluster[$nasidentifier][$callingstationid]['acctoutputoctets'];
                            $current_count  = $data_cluster[$nasidentifier][$callingstationid]['count'];
                            
                            $new_in         = $current_in + $acctinputoctets;
                            $new_out        = $current_out + $acctoutputoctets;
                            $new_count      = $current_count + 1;
                            
                            $data_cluster[$nasidentifier][$callingstationid]['acctinputoctets']     = $new_in;
                            $data_cluster[$nasidentifier][$callingstationid]['acctoutputoctets']    = $new_out;
                            $data_cluster[$nasidentifier][$callingstationid]['count']               = $new_count;
                            
                            $this->out("<info>Need to delete ID  $id</info>");
                            $this->UserStat->delete($id);
                            
                        }else{
                            //This is the first entry for this MAC
                            $data_cluster[$nasidentifier][$callingstationid] = array(
                                'id'                => $id,
                                'acctinputoctets'   => $acctinputoctets,
                                'acctoutputoctets'  => $acctoutputoctets,
                                'count'             => 1,
                                'timestamp'         => $timestamp
                            );
                        }
                    
                    }else{
                        //This is the first entry for this NAS
                        $data_cluster[$nasidentifier]=array();
                        $data_cluster[$nasidentifier][$callingstationid] = array(
                            'id'                => $id,
                            'acctinputoctets'   => $acctinputoctets,
                            'acctoutputoctets'  => $acctoutputoctets,
                            'count'             => 1,
                            'timestamp'         => $timestamp
                        );
                    }
                }
                
                //print_r($data_cluster);
                foreach(array_keys($data_cluster) as $nas){
                    foreach($data_cluster[$nas] as $mac){
                        if($mac['count'] > 1){
                            $id = $mac['id'];
                            
                            $acctoutputoctets   = $mac['acctoutputoctets'];
                            $acctinputoctets    = $mac['acctinputoctets'];
                            $timestamp          = $mac['timestamp'];
                            
                            $this->out("<info>====Need to UPDATE  $id===</info>");
                            $this->UserStat->id = 2;
                            $this->UserStat->save(array(
                                'id'                => $id,
                                'acctinputoctets'   => $acctinputoctets,
                                'acctoutputoctets'  => $acctoutputoctets,
                                'timestamp'         => $timestamp      
                            ));
                        }
                    
                    }
                }  
            }        
        }
    }
    
}

?>
