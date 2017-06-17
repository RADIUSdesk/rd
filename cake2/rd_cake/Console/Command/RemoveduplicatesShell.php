<?php
class RemoveduplicatesShell extends AppShell {

    public $uses    = array('Radacct');
    public function main() {
        $q_r = $this->Radacct->find('all',array( 
                    'conditions'    => array(),
                    'fields'        => array('Radacct.radacctid','Radacct.username','Radacct.acctuniqueid', 'COUNT(Radacct.acctuniqueid) AS count'),
                    'group'         => 'Radacct.acctuniqueid HAVING (COUNT(Radacct.acctuniqueid)>1)',
                    'order'         => 'Radacct.radacctid'
                ));

        foreach($q_r as $entry){
            $id = $entry['Radacct']['radacctid'];
            $this->out("<info>Remove duplicate accountign entry: $id</info>");
            $this->Radacct->delete($id);           
        }
    }
}

?>
