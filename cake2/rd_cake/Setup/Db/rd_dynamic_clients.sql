drop procedure if exists add_dynamic_clients;

delimiter //
create procedure add_dynamic_clients()
begin

if not exists (select * from information_schema.columns
    where table_name = 'dynamic_clients' and table_schema = 'rd') then
	CREATE TABLE `dynamic_clients` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(64) NOT NULL DEFAULT '',
	  `nasidentifier` varchar(128) NOT NULL DEFAULT '',
	  `calledstationid` varchar(128) NOT NULL DEFAULT '',
	  `last_contact` DATETIME NULL default NULL,
	  `last_contact_ip` varchar(128) NOT NULL DEFAULT '',
	  `timezone` varchar(255) NOT NULL DEFAULT '',
	  `monitor` enum('off','heartbeat','socket') DEFAULT 'off',
	  `session_auto_close` tinyint(1) NOT NULL DEFAULT '0',
      `session_dead_time` int(5) NOT NULL DEFAULT '3600',
      `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
      `active` tinyint(1) NOT NULL DEFAULT '1',
      `on_public_maps` tinyint(1) NOT NULL DEFAULT '0',
      `lat` double DEFAULT NULL,
      `lon` double DEFAULT NULL,
      `photo_file_name` varchar(128) NOT NULL DEFAULT 'logo.jpg',
      `user_id` int(11) DEFAULT NULL,
	  `created` datetime NOT NULL,
	  `modified` datetime NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

end if;

if not exists (select * from information_schema.columns
    where table_name = 'dynamic_client_notes' and table_schema = 'rd') then
	CREATE TABLE `dynamic_client_notes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `dynamic_client_id` int(11) NOT NULL,
      `note_id` int(11) NOT NULL,
      `created` datetime NOT NULL,
      `modified` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

end if;

if not exists (select * from information_schema.columns
    where table_name = 'dynamic_client_realms' and table_schema = 'rd') then
	CREATE TABLE `dynamic_client_realms` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `dynamic_client_id` int(11) NOT NULL,
      `realm_id` int(11) NOT NULL,
      `created` datetime NOT NULL,
      `modified` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;
end if;

if not exists (select * from information_schema.columns
    where table_name = 'dynamic_client_states' and table_schema = 'rd') then
    CREATE TABLE `dynamic_client_states` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `dynamic_client_id` char(36) NOT NULL,
      `state` tinyint(1) NOT NULL DEFAULT '0',
      `created` datetime NOT NULL,
      `modified` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
end if;


if not exists (select * from information_schema.columns
    where table_name = 'unknown_dynamic_clients' and table_schema = 'rd') then
	CREATE TABLE `unknown_dynamic_clients` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `nasidentifier` varchar(128) NOT NULL DEFAULT '',
	  `calledstationid` varchar(128) NOT NULL DEFAULT '',
	  `last_contact` DATETIME NULL default NULL,
	  `last_contact_ip` varchar(128) NOT NULL DEFAULT '',
	  `created` datetime NOT NULL,
	  `modified` datetime NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `nasidentifier` (`nasidentifier`),
	  UNIQUE KEY `calledstationid` (`calledstationid`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
end if;


if not exists (select * from information_schema.columns
    where column_name = 'acctupdatetime' and table_name = 'radacct' and table_schema = 'rd') then
    alter table radacct add column `acctupdatetime` datetime NULL default NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'acctinterval' and table_name = 'radacct' and table_schema = 'rd') then
    alter table radacct add column `acctinterval` int(12) default NULL;
end if;

end//

delimiter ;
call add_dynamic_clients;
