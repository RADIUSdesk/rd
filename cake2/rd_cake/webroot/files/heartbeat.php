<?php

try {

    header("Content-type: text/plain");


    //We will only allow mac or nasid in the query string
    if((isset($_GET['mac']))||(isset($_GET['nasid']))){
      
        //=== MAC =====
        $mac_addr = false;
        if(isset($_GET['mac'])){
            $mac_addr = $_GET['mac'];
            //Check if the MAC is in the correct format
            $pattern = '/^([0-9a-fA-F]{2}[-]){5}[0-9a-fA-F]{2}$/i';
            if(preg_match($pattern, $mac_addr)< 1){
                $error = "ERROR: MAC missing or wrong";
                echo "$error";
                return;
            }
        }

        //=== NASID ===
        $n_id = false;
        if(isset($_GET['nasid'])){
            $n_id = $_GET['nasid'];
            //NASID should be at least 3 characters long
            if(strlen($n_id)<3){
                $error = "ERROR: NASID should be more than 3 charaters long";
                echo "$error";
                return;
            }
        }

    }else{
        echo "Page called in a wrong way!";
        return;
    }


    //=====================
    //Basic sanity checks complete, now connect....

    //Find the credentials to connect with
    include_once("/usr/share/nginx/html/cake2/rd_cake/Config/database.php");
    $dbc    = & new DATABASE_CONFIG();
    $host   = $dbc->default['host'];
    $login  = $dbc->default['login'];
    $pwd    = $dbc->default['password'];
    $db     = $dbc->default['database'];

    $dbh    = new PDO("mysql:host=$host;dbname=$db", $login, $pwd, array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //Check if any of the NAS devices has this MAC defined as it's community
    if($mac_addr){
        $stmt_nas_id        = $dbh->prepare("SELECT id,type FROM nas WHERE community= :mac_addr");
        $stmt_nas_id->bindParam(':mac_addr',$mac_addr);
        $stmt_nas_id->execute();
        $result             = $stmt_nas_id->fetch(PDO::FETCH_ASSOC);
        if($result == ''){
            header("Content-type: text/plain"); //send command
            echo("ERROR: MAC not listed in database");
            return;
        }
        $nas_id         = $result['id'];
        $type           = $result['type'];
    }

    //Check if any of the NAS devices has this nasidentifier defined as it's nasidentifier
    if($n_id){
        $stmt_nas_id        = $dbh->prepare("SELECT id,type FROM nas WHERE nasidentifier= :nas_id");
        $stmt_nas_id->bindParam(':nas_id',$n_id);
        $stmt_nas_id->execute();
        $result             = $stmt_nas_id->fetch(PDO::FETCH_ASSOC);
        if($result == ''){
            header("Content-type: text/plain"); //send command
            echo("ERROR: nasidentifier not listed in database");
            return;
        }
        $nas_id         = $result['id'];
        $type           = $result['type'];
    }

    //==================
    //== Update the last_contact field
    //==================
    $nas_id         = $result['id'];
    $stmt_hb_upd    = $dbh->prepare("UPDATE nas SET last_contact=now() WHERE id=:nas_id");
    $stmt_hb_upd->bindParam(':nas_id',$nas_id);
    $stmt_hb_upd->execute();

    //=================
    //== Check for any actions for this one....
    //================== 

    $stmt_actions       = $dbh->prepare("SELECT id, action, command, na_id FROM actions WHERE na_id= :nas_id and status='awaiting'");
    $stmt_actions->bindParam(':nas_id',$nas_id);
    $stmt_actions->execute();

    $result             = $stmt_actions->fetchAll(PDO::FETCH_ASSOC);
    $return_string      = "";

    $stmt_upd_fetched   = $dbh->prepare("UPDATE actions SET actions.status='fetched' where actions.id= :action_id");
    $stmt_upd_fetched->bindParam(':action_id',$action_id);

    foreach($result as $item){
            $id         = $item['id'];
            $action     = $item['action'];
            $command    = $item['command'];

            //Commands for CoovaChilli-Heartbeat
            if($type == 'CoovaChilli-Heartbeat'){
                $return_string = $return_string."unique_id: $id\naction: $action\n$command\n";
            }

            //Commands for Mikrotik-Heartbeat are returned different
            if($type == 'Mikrotik-Heartbeat'){
                $return_string = $return_string."$command\n";
            }

            $nas_id     = $item['nasid'];
            //Mark this action as fetched
            $action_id  = $id;
            $stmt_upd_fetched->execute();
    }
    header("Content-type: text/plain"); //send command
    print $return_string;
    $dbh = null;
}
catch(PDOException $e){
    echo $e->getMessage();
    
}

?>
