drop procedure if exists add_openvpn_servers;

delimiter //
create procedure add_openvpn_servers()
begin

if not exists (select * from information_schema.columns
    where table_name = 'openvpn_servers' and table_schema = 'rd') then
	CREATE TABLE `openvpn_servers` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(64) NOT NULL DEFAULT '',
	  `description` varchar(255) NOT NULL DEFAULT '',
      `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
	  `user_id` int(11) DEFAULT NULL,
	  `local_remote` enum('local','remote') DEFAULT 'local',
	  `protocol` enum('udp','tcp') DEFAULT 'udp',
	  `ip_address` varchar(40) NOT NULL,
	  `port` int(6) NOT NULL,
	  `vpn_gateway_address` varchar(40) NOT NULL,
	  `vpn_bridge_start_address` varchar(40) NOT NULL,
	  `vpn_mask` varchar(40) NOT NULL,
	  `config_preset` varchar(100) NOT NULL DEFAULT 'default',
	  `ca_crt` text NOT NULL DEFAULT '',
      `extra_name` varchar(100) NOT NULL DEFAULT '',
	  `extra_value` varchar(100) NOT NULL DEFAULT '',
	  `created` datetime NOT NULL,
	  `modified` datetime NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
end if;


if not exists (select * from information_schema.columns
    where table_name = 'openvpn_server_clients' and table_schema = 'rd') then
	CREATE TABLE `openvpn_server_clients` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `mesh_ap_profile` enum('mesh','ap_profile') DEFAULT 'mesh',
	  `openvpn_server_id` int(11) DEFAULT NULL,
	  `mesh_id` int(11) DEFAULT NULL,
	  `mesh_exit_id` int(11) DEFAULT NULL,
	  `ap_profile_id` int(11) DEFAULT NULL,
	  `ap_profile_exit_id` int(11) DEFAULT NULL,
	  `ap_id` int(11) DEFAULT NULL,
	  `ip_address` varchar(40) NOT NULL,
	  `last_contact_to_server` DATETIME NULL default NULL,
	  `state` tinyint(1) NOT NULL DEFAULT '0',
	  `created` datetime NOT NULL,
	  `modified` datetime NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
end if;


if not exists (select * from information_schema.columns
    where column_name = 'openvpn_server_id' and table_name = 'mesh_exits' and table_schema = 'rd') then
    alter table mesh_exits add column `openvpn_server_id` int(11) DEFAULT NULL;
    alter table mesh_exits CHANGE  type type  enum('bridge','tagged_bridge','nat','captive_portal','openvpn_bridge');
end if;

if not exists (select * from information_schema.columns
    where column_name = 'openvpn_server_id' and table_name = 'ap_profile_exits' and table_schema = 'rd') then
    alter table ap_profile_exits add column `openvpn_server_id` int(11) DEFAULT NULL;
    alter table ap_profile_exits CHANGE  type type enum('bridge','tagged_bridge','nat','captive_portal','openvpn_bridge');
end if;

end//

delimiter ;

call add_openvpn_servers;








