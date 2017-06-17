<?php
class OpenWrtComponent extends Component {


    private $includes       = array();
    
    public function getEntries($mesh_name){

        $commands = array();

        $mesh       = ClassRegistry::init('Mesh');
        $mesh->contain();
        $q_r        = $mesh->findByName($mesh_name);

        //Is it a valid mesh?
        if($q_r){
            $mesh_id    = $q_r['Mesh']['id'];
            $m_ssid     = $q_r['Mesh']['ssid'];
            $m_bssid    = $q_r['Mesh']['bssid'];
            //Remove the wmesh entry; add it again
            array_push($commands,array("action" => "execute", "data" => "uci delete wireless.wmesh"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wmesh=wifi-iface"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wmesh.device='radio0'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wmesh.ifname='adhoc0'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wmesh.network='mesh'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wmesh.mode='adhoc'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wmesh.ssid='$m_ssid'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wmesh.bssid='$m_bssid'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wmesh.hidden='1'"));
            
            //Remove the wconf entry; add it again
            array_push($commands,array("action" => "execute", "data" => "uci delete wireless.wconf"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf=wifi-iface"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.device='radio0'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.network='conf'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.mode='ap'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.ssid='meshadmin'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.encryption='psk2'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.key='veryvery'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.hidden='1'"));

            array_push($commands,array("action" => "execute", "data" => "uci commit wireless"));

        }

        return $commands;
        $entry      = ClassRegistry::init('MeshEntry');
        $entry->contain();
        $q_r        = $entry->find('all',array('conditions' => array('MeshEntry.mesh_id' => $mesh_id)));

        foreach($q_r as $i){
/*
            //Remove the wconf entry; add it again
            array_push($commands,array("action" => "execute", "data" => "uci delete wireless.wconf"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf=wifi-iface"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.device='radio0'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.network='conf'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.mode='ap'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.ssid='meshadmin'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.encryption='psk2'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.key='veryvery'"));
            array_push($commands,array("action" => "execute", "data" => "uci set wireless.wconf.hidden='1'"));

            print_r($i);
*/

        }
    }


   
}
?>
