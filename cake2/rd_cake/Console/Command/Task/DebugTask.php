<?php

class DebugTask extends Shell {
    public $uses = array('Check');

    public function check() {
        $this->_show_header();
        $this->_check();     
    }

    private function _show_header(){
        $this->out('<comment>==============================</comment>');
        $this->out('<comment>---Debug Timeout Checking-----</comment>');
        $this->out('<comment>-------RADIUSdesk 2013--------</comment>');
        $this->out('<comment>______________________________</comment>');
    }

    

    private function _check(){

        $this->out("<info>Debug::Check for entry</info>");
        
        $q_r = $this->Check->find('first', array('conditions' => array('Check.name' => 'debug_timeout')));

        if($q_r){
            $this->out("<info>Debug::Found debug trace entry</info>");
            $value  = $q_r['Check']['value'];
            $id     = $q_r['Check']['id'];
            $now    = time();
            if($value < $now){
                $this->out("<info>Debug::Debug timed out - disabling it</info>"); 
                exec("sudo /usr/share/nginx/html/cake2/rd_cake/Setup/Scripts/radmin_wrapper.pl debug stop",$output);
                $this->out("<info>Debug::Deleting the intry in Checks table</info>");
                $this->Check->deleteAll(array('Check.name' => 'debug_timeout'));
            }else{
                $timeout = $value - $now;
                $this->out("<info>Debug::Debug expires in ".$timeout." seconds</info>");
            }
        }else{
            $this->out("<info>Debug::No current debug trace found</info>");
        }
    }
}

?>
