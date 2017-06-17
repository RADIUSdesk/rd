<?php

try {

    print("Start Migration of Permanent Users\n");
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

    $sth_users      = $dbh->prepare("SELECT * FROM users where group_id=10");
    $sth_users->execute();
    $results        = $sth_users->fetchAll(PDO::FETCH_ASSOC);

	$stmt_p_u = $dbh->prepare(
		"INSERT INTO permanent_users  
		(id, username,password,token,name,surname,address,phone,email,auth_type,active,
		last_accept_time, last_reject_time,last_accept_nas,last_reject_nas,last_reject_message,
		perc_time_used,perc_data_used,data_used,data_cap,time_used,time_cap,time_cap_type,data_cap_type,
		realm,realm_id,profile,profile_id,from_date,to_date, track_auth,track_acct,
		static_ip,country_id,language_id,user_id,created,modified
		) 
		VALUES (
		:id, :username,	:password,	:token,	:name,	:surname,	:address,	:phone,	:email,	:auth_type,	:active,
		:last_accept_time,	:last_reject_time,	:last_accept_nas,	:last_reject_nas,	:last_reject_message,
		:perc_time_used,	:perc_data_used,	:data_used, :data_cap,	:time_used,	:time_cap,	:time_cap_type,	:data_cap_type,
		:realm, :realm_id,	:profile,	:profile_id,	:from_date,	:to_date, :track_auth,	:track_acct,
		:static_ip,	:country_id,	:language_id,	:user_id, :created,	:modified
		)");


	$stmt_p_u->bindParam(":id", 		$pu_id);
	$stmt_p_u->bindParam(":username", 	$pu_username);
	$stmt_p_u->bindParam(":password", 	$pu_password);
	$stmt_p_u->bindParam(":token", 		$pu_token);
	$stmt_p_u->bindParam(":name", 		$pu_name);
	$stmt_p_u->bindParam(":surname", 	$pu_surname);
	$stmt_p_u->bindParam(":address", 	$pu_address);
	$stmt_p_u->bindParam(":phone", 		$pu_phone);
	$stmt_p_u->bindParam(":email", 		$pu_email);
	$stmt_p_u->bindParam(":auth_type", 	$pu_auth_type);
	$stmt_p_u->bindParam(":active", 	$pu_active);

	$stmt_p_u->bindParam(":last_accept_time", 	$pu_last_accept_time);
	$stmt_p_u->bindParam(":last_reject_time", 	$pu_last_reject_time);
	$stmt_p_u->bindParam(":last_accept_nas", 	$pu_last_accept_nas);
	$stmt_p_u->bindParam(":last_reject_nas", 	$pu_last_reject_nas);
	$stmt_p_u->bindParam(":last_reject_message",$pu_last_reject_message);

	$stmt_p_u->bindParam(":perc_time_used", 	$pu_perc_time_used);
	$stmt_p_u->bindParam(":perc_data_used", 	$pu_perc_data_used);
	$stmt_p_u->bindParam(":data_used", 			$pu_data_used);
	$stmt_p_u->bindParam(":data_cap", 			$pu_data_cap);
	$stmt_p_u->bindParam(":time_used", 			$pu_time_used);
	$stmt_p_u->bindParam(":time_cap", 			$pu_time_cap);
	$stmt_p_u->bindParam(":time_cap_type",		$pu_time_cap_type);
	$stmt_p_u->bindParam(":data_cap_type",		$pu_data_cap_type);

	$stmt_p_u->bindParam(":realm", 				$pu_realm);

	$stmt_p_u->bindParam(":realm_id", 			$pu_realm_id);
	$stmt_p_u->bindParam(":profile", 			$pu_profile);
	$stmt_p_u->bindParam(":profile_id", 		$pu_profile_id);
	$stmt_p_u->bindParam(":from_date",			$pu_from_date);
	$stmt_p_u->bindParam(":to_date",			$pu_to_date);
	$stmt_p_u->bindParam(":track_auth",			$pu_track_auth);
	$stmt_p_u->bindParam(":track_acct", 		$pu_track_acct);


	$stmt_p_u->bindParam(":static_ip", 			$pu_static_ip);

	$stmt_p_u->bindParam(":language_id", 		$pu_language_id);
	$stmt_p_u->bindParam(":country_id", 		$pu_country_id);
	$stmt_p_u->bindParam(":user_id",			$pu_user_id);

	$stmt_p_u->bindParam(":created",			$pu_created);
	$stmt_p_u->bindParam(":modified",			$pu_modified);

    foreach($results as $r){

        $pu_username   	= $r['username'];
        $pu_id     		= $r['id'];
		$pu_password   	= $r['password'];
        $pu_token     	= $r['token'];
		$pu_name   		= $r['name'];
        $pu_surname     = $r['surname'];
		$pu_address   	= $r['address'];
        $pu_phone     	= $r['phone'];
		$pu_email   	= $r['email'];
        $pu_auth_type   = $r['auth_type'];
		$pu_active   	= $r['active'];

        $pu_last_accept_time    = $r['last_accept_time'];
		$pu_last_reject_time    = $r['last_reject_time'];
		$pu_last_accept_nas     = $r['last_accept_nas'];
		$pu_last_reject_nas     = $r['last_reject_nas'];
		$pu_last_reject_message = $r['last_reject_message'];

		$pu_perc_time_used    	= $r['perc_time_used'];
		$pu_perc_data_used    	= $r['perc_data_used'];
		$pu_data_cap     		= $r['data_cap'];
		$pu_time_cap     		= $r['time_cap'];
		$pu_data_used     		= $r['data_used'];
		$pu_time_used     		= $r['time_used'];
		$pu_time_cap_type 		= $r['time_cap_type'];
		$pu_data_cap_type 		= $r['data_cap_type'];

		$pu_realm    	= $r['realm'];
		$pu_realm_id    = $r['realm_id'];
		$pu_profile     = $r['profile'];
		$pu_profile_id  = $r['profile_id'];
		$pu_from_date 	= $r['from_date'];
		$pu_to_date 	= $r['to_date'];
		$pu_track_auth 	= $r['track_auth'];
		$pu_track_acct  = $r['track_acct'];

		$pu_static_ip   = $r['static_ip'];
		$pu_language_id = $r['language_id'];
		$pu_country_id  = $r['country_id'];
		$pu_user_id 	= $r['parent_id'];
		$pu_created 	= $r['created'];
		$pu_modified 	= $r['modified'];


        print("Migrating $pu_username\n");
		$stmt_p_u->execute();
    }

	print("Deleting old Permanent Users from users table\n");	
	$sth_users      = $dbh->prepare("Delete FROM users where group_id=10");
    $sth_users->execute();
    print("Permanent Users  patch complete\n");
}
catch(PDOException $e){
    echo $e->getMessage();
    
}


?>
