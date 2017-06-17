<?php

try {

    $realm_cache    = array();
    $profile_cache  = array();
    print("Start Permanent Users Patch\n");
    //=====================

    //Find the credentials to connect with
    //include_once("/var/www/cake2/rd_cake/Config/database.php");
    //include_once("/usr/share/nginx/www/cake2/rd_cake/Config/database.php");
    include_once("/usr/share/nginx/html/cake2/rd_cake/Config/database.php");
    $dbc    = & new DATABASE_CONFIG();
    $host   = $dbc->default['host'];
    $login  = $dbc->default['login'];
    $pwd    = $dbc->default['password'];
    $db     = $dbc->default['database'];

    $dbh    = new PDO("mysql:host=$host;dbname=$db", $login, $pwd, array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sth_users      = $dbh->prepare("SELECT username,id FROM users where group_id=10");
    $sth_users->execute();
    $results        = $sth_users->fetchAll(PDO::FETCH_ASSOC);

    foreach($results as $r){
        $username   = $r['username'];
        $id     = $r['id'];

        $stmt_radcheck   = $dbh->prepare("SELECT * FROM radcheck WHERE username= :username");
        $stmt_radcheck->bindParam(':username',$username);
        $stmt_radcheck->execute();
        $checks         = $stmt_radcheck->fetchAll(PDO::FETCH_ASSOC);
       // print_r($checks);
        print("Updating $username\n");

        $expire     = '';
        $password   = false;
        $profile    = false;
        $realm      = false;
        $profile_id = null;
        $realm_id   = null;

        foreach($checks as $check){
            $a = $check['attribute'];
            $v = $check['value'];

            if($a == 'User-Profile'){
                $profile = $v;
                if(!(array_key_exists($profile,$profile_cache))){
                    $stmt_profile_id    = $dbh->prepare("SELECT id FROM profiles WHERE name= :profile");
                    $stmt_profile_id->bindParam(':profile',$profile);
                    $stmt_profile_id->execute();
                    $rp             = $stmt_profile_id->fetch(PDO::FETCH_ASSOC);
                    if($rp != ''){
                        $profile_cache["$profile"] = $rp['id'];
                    }
                }
            }
            if($a == 'Rd-Realm'){
                $realm = $v;
                if(!(array_key_exists($realm,$realm_cache))){
                    $stmt_realm_id    = $dbh->prepare("SELECT id FROM realms WHERE name= :realm");
                    $stmt_realm_id->bindParam(':realm',$realm);
                    $stmt_realm_id->execute();
                    $rr             = $stmt_realm_id->fetch(PDO::FETCH_ASSOC);
                    if($rr != ''){
                        $realm_cache["$realm"] = $rr['id'];
                    }
                }
            }
            if($a == 'Expiration'){
                $expire = _extjs_format_radius_date($v);
            }
         
            //Now we can update Users
            $stmt_hb_upd    = $dbh->prepare("UPDATE users 
                SET profile     =:profile,
                    realm       =:realm,
                    profile_id  =:profile_id,
                    realm_id    =:realm_id
                WHERE id=:id"
            );
            $stmt_hb_upd->bindParam(':id',$id);
            $stmt_hb_upd->bindParam(':profile',$profile);
            $stmt_hb_upd->bindParam(':realm',$realm);
            $stmt_hb_upd->bindParam(':profile_id',$profile_cache["$profile"]);
            $stmt_hb_upd->bindParam(':realm_id',$realm_cache["$realm"]);
          //  $stmt_hb_upd->bindParam(':to_date',$expire);
            $stmt_hb_upd->execute();
        }
    }
    print("Permanent Users  patch complete\n");
}
catch(PDOException $e){
    echo $e->getMessage();
    
}

function _extjs_format_radius_date($d){
        //Format will be day month year 20 Mar 2013 and need to be month/date/year eg 03/06/2013 
        $arr_date   = explode(' ',$d);
        $month      = $arr_date[1];
        $m_arr      = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $day        = intval($arr_date[0]);
        $year       = intval($arr_date[2]);

        $month_count = 1;
        foreach($m_arr as $m){
            if($month == $m){
                break;
            }
            $month_count ++;
        }
        return "$month_count/$day/$year";
    }

?>
