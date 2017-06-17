<?php

//__________________________________________
//RADIUSdesk shell used for housekeeping etc
//==========================================

class RdShell extends AppShell {

    public $tasks = array('Monitor','Debug','Autoclose');

    public function main() {

        if($this->args[0] == 'mon'){

            if ($this->params['heartbeat']) {
                $this->Monitor->heartbeat();
                return;
            }

            if ($this->params['ping']) {
                $this->Monitor->ping();
                return;
            }

            $this->Monitor->execute();
        }

        //Check if a debug trace was started and stop it after timeout.
        if($this->args[0] == 'debug_check'){
            $this->Debug->check();     
        }

        //Check if a debug trace was started and stop it after timeout.
        if($this->args[0] == 'auto_close'){
            $this->Autoclose->check();     
        }

    }

    public function getOptionParser() {
		$parser = new ConsoleOptionParser($this->name);
		$parser->description(__('RADIUSdesk console for various tasks'));
        $parser->addOption('ping', array(
            'help' => 'Do a ping monitor test',
            'boolean' => true
        ));
        $parser->addOption('heartbeat', array(
            'help' => 'Do a heartbeat monitor test',
            'boolean' => true
        ));

        $parser->addArgument('action', array(
            'help' => 'The action to do',
            'required' => true,
            'choices' => array('mon','restart_check','debug_check','auto_close')
        ));

        $parser->epilog('Have alot of fun....');
		return $parser;
	}

    
}

?>
