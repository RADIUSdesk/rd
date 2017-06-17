<?php
class FreeradiusShell extends AppShell {

    public $uses    = array('Check','Na');
    public function main() {
        $q_r = $this->Check->find('first',array( 
                    'conditions'    => array('Check.name' => 'radius_restart' ),
                    'fields'        => array('id','UNIX_TIMESTAMP(modified) as modified')
                ));

       // print_r($q_r);

        if(!$q_r){
            $this->out("<info>Empty Check table; add radius_restart entry</info>");
            $d                      = array();
            $d['Check']['name']     = 'radius_restart';
            $d['Check']['value']    = '1';
            $this->Check->save($d);
        }else{
            //Get the UNIX timestamp of the last modified field
            $last_restart   = $q_r[0]['modified'];

            $this->out("<info>Last restart was $last_restart</info>");

            //Get the most recent saved NAS
            $this->Na->contain();
            $n_q_r = $this->Na->find('first',array( 
                    'fields'        => array('id','UNIX_TIMESTAMP(modified) as m'),
                    'order'         => 'Na.modified DESC'
                ));

            if($n_q_r){
                $last_save  = $n_q_r[0]['m'];
                if($last_save > $last_restart){
                    $this->out("<info>More recent NAS table entry than last restart -> Restart FreeRADIUS</info>");
                   // $restart_string = "sudo /etc/init.d/radiusd restart";
                   // system($restart_string);
                    //Change this so that cron can run as www-data
                    exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl stop freeradius");
                    exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl start freeradius");
                    //Update the entry to the latest timestamp
                    $this->Check->id = $q_r['Check']['id'];
                    $this->Check->save();
                }
            }

        }
    }
}

?>
