drop procedure if exists ap_profiles;

delimiter //
create procedure ap_profiles()
begin


if not exists (select * from information_schema.columns
    where table_name = 'ap_actions' and table_schema = 'rd') then
    
        CREATE TABLE `ap_actions` (
          `id` int(10) NOT NULL AUTO_INCREMENT,
          `ap_id` int(10) NOT NULL,
          `action` enum('execute') DEFAULT 'execute',
          `command` varchar(500) DEFAULT '',
          `status` enum('awaiting','fetched','replied') DEFAULT 'awaiting',
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

end if;

if not exists (select * from information_schema.columns
    where table_name = 'ap_loads' and table_schema = 'rd') then

        CREATE TABLE `ap_loads` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ap_id` int(11) DEFAULT NULL,
          `mem_total` int(11) DEFAULT NULL,
          `mem_free` int(11) DEFAULT NULL,
          `uptime` varchar(255) DEFAULT NULL,
          `system_time` varchar(255) NOT NULL,
          `load_1` float(2,2) NOT NULL,
          `load_2` float(2,2) NOT NULL,
          `load_3` float(2,2) NOT NULL,
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

end if;

if not exists (select * from information_schema.columns
    where table_name = 'ap_profile_entries' and table_schema = 'rd') then

        CREATE TABLE `ap_profile_entries` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ap_profile_id` int(11) DEFAULT NULL,
          `name` varchar(128) NOT NULL,
          `hidden` tinyint(1) NOT NULL DEFAULT '0',
          `isolate` tinyint(1) NOT NULL DEFAULT '0',
          `encryption` enum('none','wep','psk','psk2','wpa','wpa2') DEFAULT 'none',
          `key` varchar(255) NOT NULL DEFAULT '',
          `auth_server` varchar(255) NOT NULL DEFAULT '',
          `auth_secret` varchar(255) NOT NULL DEFAULT '',
          `dynamic_vlan` tinyint(1) NOT NULL DEFAULT '0',
          `frequency_band` enum('both','two','five') DEFAULT 'both',
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

end if;

if not exists (select * from information_schema.columns
    where table_name = 'ap_profile_exit_ap_profile_entries' and table_schema = 'rd') then

        CREATE TABLE `ap_profile_exit_ap_profile_entries` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ap_profile_exit_id` int(11) NOT NULL,
          `ap_profile_entry_id` int(11) NOT NULL,
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
        
end if;

if not exists (select * from information_schema.columns
    where table_name = 'ap_profile_exit_captive_portals' and table_schema = 'rd') then

        CREATE TABLE `ap_profile_exit_captive_portals` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ap_profile_exit_id` int(11) NOT NULL,
          `radius_1` varchar(128) NOT NULL,
          `radius_2` varchar(128) NOT NULL DEFAULT '',
          `radius_secret` varchar(128) NOT NULL,
          `radius_nasid` varchar(128) NOT NULL,
          `uam_url` varchar(255) NOT NULL,
          `uam_secret` varchar(255) NOT NULL,
          `walled_garden` varchar(255) NOT NULL,
          `swap_octets` tinyint(1) NOT NULL DEFAULT '0',
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          `mac_auth` tinyint(1) NOT NULL DEFAULT '0',
          `proxy_enable` tinyint(1) NOT NULL DEFAULT '0',
          `proxy_ip` varchar(128) NOT NULL DEFAULT '',
          `proxy_port` int(11) NOT NULL DEFAULT '3128',
          `proxy_auth_username` varchar(128) NOT NULL DEFAULT '',
          `proxy_auth_password` varchar(128) NOT NULL DEFAULT '',
          `coova_optional` varchar(255) NOT NULL DEFAULT '',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

end if;

if not exists (select * from information_schema.columns
    where table_name = 'ap_profile_exits' and table_schema = 'rd') then
    
        CREATE TABLE `ap_profile_exits` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ap_profile_id` int(11) DEFAULT NULL,
          `type` enum('bridge','tagged_bridge','nat','captive_portal') DEFAULT 'bridge',
          `vlan` int(4) DEFAULT NULL,
          `auto_dynamic_client` tinyint(1) NOT NULL DEFAULT '0',
          `realm_list` varchar(128) NOT NULL DEFAULT '',
          `auto_login_page` tinyint(1) NOT NULL DEFAULT '0',
          `dynamic_detail_id` int(11) DEFAULT NULL,
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
        
end if;

if not exists (select * from information_schema.columns
    where table_name = 'ap_profile_notes' and table_schema = 'rd') then
    
        CREATE TABLE `ap_profile_notes` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ap_profile_id` int(11) NOT NULL,
          `note_id` int(11) NOT NULL,
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

end if;

if not exists (select * from information_schema.columns
    where table_name = 'ap_profile_settings' and table_schema = 'rd') then

        CREATE TABLE `ap_profile_settings` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ap_profile_id` int(11) DEFAULT NULL,
          `password` varchar(128) NOT NULL,
          `heartbeat_interval` int(5) NOT NULL DEFAULT '60',
          `heartbeat_dead_after` int(5) NOT NULL DEFAULT '600',
          `password_hash` varchar(100) NOT NULL DEFAULT '',
          `tz_name` varchar(128) NOT NULL DEFAULT 'America/New York',
          `tz_value` varchar(128) NOT NULL DEFAULT 'EST5EDT,M3.2.0,M11.1.0',
          `country` varchar(5) NOT NULL DEFAULT 'US',
          `gw_dhcp_timeout` int(5) NOT NULL DEFAULT '120',
          `gw_use_previous` tinyint(1) NOT NULL DEFAULT '1',
          `gw_auto_reboot` tinyint(1) NOT NULL DEFAULT '1',
          `gw_auto_reboot_time` int(5) NOT NULL DEFAULT '600',
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

end if;

if not exists (select * from information_schema.columns
    where table_name = 'ap_profile_specifics' and table_schema = 'rd') then

        CREATE TABLE `ap_profile_specifics` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ap_profile_id` int(11) NOT NULL,
          `name` varchar(255) NOT NULL,
          `value` varchar(255) NOT NULL,
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

end if;

if not exists (select * from information_schema.columns
    where table_name = 'ap_profiles' and table_schema = 'rd') then

        CREATE TABLE `ap_profiles` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(128) NOT NULL,
          `user_id` int(11) DEFAULT NULL,
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          `available_to_siblings` tinyint(1) NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

end if;

if not exists (select * from information_schema.columns
    where table_name = 'ap_stations' and table_schema = 'rd') then
    
        CREATE TABLE `ap_stations` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ap_id` int(11) DEFAULT NULL,
          `ap_profile_entry_id` int(11) DEFAULT NULL,
          `vendor` varchar(255) DEFAULT NULL,
          `mac` varchar(17) NOT NULL,
          `tx_bytes` bigint(20) NOT NULL,
          `rx_bytes` bigint(20) NOT NULL,
          `tx_packets` int(11) NOT NULL,
          `rx_packets` int(11) NOT NULL,
          `tx_bitrate` int(11) NOT NULL,
          `rx_bitrate` int(11) NOT NULL,
          `tx_extra_info` varchar(255) NOT NULL,
          `rx_extra_info` varchar(255) NOT NULL,
          `authenticated` enum('yes','no') DEFAULT 'no',
          `authorized` enum('yes','no') DEFAULT 'no',
          `tdls_peer` varchar(255) NOT NULL,
          `preamble` enum('long','short') DEFAULT 'long',
          `tx_failed` int(11) NOT NULL,
          `inactive_time` int(11) NOT NULL,
          `WMM_WME` enum('yes','no') DEFAULT 'no',
          `tx_retries` int(11) NOT NULL,
          `MFP` enum('yes','no') DEFAULT 'no',
          `signal` int(11) NOT NULL,
          `signal_avg` int(11) NOT NULL,
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;

end if;

if not exists (select * from information_schema.columns
    where table_name = 'ap_systems' and table_schema = 'rd') then
    
        CREATE TABLE `ap_systems` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ap_id` int(11) DEFAULT NULL,
          `name` varchar(255) NOT NULL,
          `value` varchar(255) NOT NULL,
          `group` varchar(255) NOT NULL,
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;
        
end if;


if not exists (select * from information_schema.columns
    where table_name = 'ap_wifi_settings' and table_schema = 'rd') then
    
        CREATE TABLE `ap_wifi_settings` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ap_id` int(11) DEFAULT NULL,
          `name` varchar(50) DEFAULT NULL,
          `value` varchar(255) DEFAULT NULL,
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=856 DEFAULT CHARSET=utf8;
        
end if;


if not exists (select * from information_schema.columns
    where table_name = 'aps' and table_schema = 'rd') then
    
        CREATE TABLE `aps` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ap_profile_id` int(11) DEFAULT NULL,
          `name` varchar(255) NOT NULL,
          `description` varchar(255) NOT NULL,
          `mac` varchar(255) NOT NULL,
          `hardware` varchar(255) DEFAULT NULL,
          `last_contact_from_ip` varchar(255) DEFAULT NULL,
          `last_contact` datetime DEFAULT NULL,
          `on_public_maps` tinyint(1) NOT NULL DEFAULT '0',
          `lat` double DEFAULT NULL,
          `lon` double DEFAULT NULL,
          `photo_file_name` varchar(128) NOT NULL DEFAULT 'logo.jpg',
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
        
end if;

if not exists (select * from information_schema.columns
    where table_name = 'unknown_aps' and table_schema = 'rd') then
    
        CREATE TABLE `unknown_aps` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `mac` varchar(255) NOT NULL,
          `vendor` varchar(255) DEFAULT NULL,
          `last_contact_from_ip` varchar(255) DEFAULT NULL,
          `last_contact` datetime DEFAULT NULL,
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          `new_server` varchar(255) NOT NULL DEFAULT '',
          `new_server_status` enum('awaiting','fetched','replied') DEFAULT 'awaiting',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
        
end if;

end//

delimiter ;
call ap_profiles;

