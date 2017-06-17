<?php
class AutoCleanMESHdeskShell extends AppShell {

    public $uses    = array('NodeIbssConnection','NodeStation');

    public function main() {
		$hour   	= (60*60);
        $day    	= $hour*24;
        $week   	= $day*7;
		$modified 	= date("Y-m-d H:i:s", time()-$week);
        $this->out("<comment>Auto Clean-up of MESHdesk data older than one week".APP."</comment>");
        $this->NodeStation->deleteAll(array('NodeStation.modified <' => $modified),false);
		$this->NodeIbssConnection->deleteAll(array('NodeIbssConnection.modified <' => $modified),false);
    }
}

?>
