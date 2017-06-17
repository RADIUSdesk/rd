
drop procedure if exists meshdesk_addcolumns;

delimiter //
create procedure meshdesk_addcolumns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'password_hash' and table_name = 'node_settings' and table_schema = 'rd') then
    alter table node_settings add column `password_hash` varchar(100) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'eth_br_chk' and table_name = 'node_settings' and table_schema = 'rd') then
    alter table node_settings add column `eth_br_chk` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'eth_br_with' and table_name = 'node_settings' and table_schema = 'rd') then
    alter table node_settings add column `eth_br_with` int(11) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'eth_br_for_all' and table_name = 'node_settings' and table_schema = 'rd') then
    alter table node_settings add column `eth_br_for_all` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'mac_auth' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `mac_auth` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'radio0_enable' and table_name = 'nodes' and table_schema = 'rd') then
    alter table nodes add column `radio0_enable` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'radio0_mesh' and table_name = 'nodes' and table_schema = 'rd') then
    alter table nodes add column `radio0_mesh` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'radio0_entry' and table_name = 'nodes' and table_schema = 'rd') then
    alter table nodes add column `radio0_entry` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'radio0_band' and table_name = 'nodes' and table_schema = 'rd') then
    alter table nodes add column `radio0_band` tinyint(3) NOT NULL DEFAULT '24';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'radio0_two_chan' and table_name = 'nodes' and table_schema = 'rd') then
    alter table nodes add column `radio0_two_chan` int(4) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'radio0_five_chan' and table_name = 'nodes' and table_schema = 'rd') then
    alter table nodes add column `radio0_five_chan` int(4) NOT NULL DEFAULT '44';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'radio1_enable' and table_name = 'nodes' and table_schema = 'rd') then
    alter table nodes add column `radio1_enable` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'radio1_mesh' and table_name = 'nodes' and table_schema = 'rd') then
    alter table nodes add column `radio1_mesh` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'radio1_entry' and table_name = 'nodes' and table_schema = 'rd') then
    alter table nodes add column `radio1_entry` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'radio1_band' and table_name = 'nodes' and table_schema = 'rd') then
    alter table nodes add column `radio1_band` tinyint(3) NOT NULL DEFAULT '5';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'radio1_two_chan' and table_name = 'nodes' and table_schema = 'rd') then
    alter table nodes add column `radio1_two_chan` int(4) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'radio1_five_chan' and table_name = 'nodes' and table_schema = 'rd') then
    alter table nodes add column `radio1_five_chan` int(4) NOT NULL DEFAULT '44';
end if;

end//

delimiter ;
call meshdesk_addcolumns;

DROP TABLE IF EXISTS `node_neighbors`;
CREATE TABLE `node_neighbors` (
  `id`                  int(11) NOT NULL AUTO_INCREMENT,
  `node_id`             int(11) DEFAULT NULL,
  `gateway`             enum('yes','no') DEFAULT 'no',
  `neighbor_id`     	int(11) DEFAULT NULL,
  `metric`              decimal(6,4) NOT NULL,
  `hwmode` 				char(5) DEFAULT '11g',
  `created`             datetime NOT NULL,
  `modified`            datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `mesh_specifics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesh_specifics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesh_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `node_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_actions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `node_id` int(10) NOT NULL,
  `action` enum('execute') DEFAULT 'execute',
  `command` varchar(500) DEFAULT '',
  `status` enum('awaiting','fetched','replied') DEFAULT 'awaiting',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mesh_settings`
--

DROP TABLE IF EXISTS `mesh_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesh_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesh_id` int(11) DEFAULT NULL,
  `aggregated_ogms` tinyint(1) NOT NULL DEFAULT '1',
  `ap_isolation` tinyint(1) NOT NULL DEFAULT '0',
  `bonding` tinyint(1) NOT NULL DEFAULT '0',
  `bridge_loop_avoidance` tinyint(1) NOT NULL DEFAULT '0',
  `fragmentation` tinyint(1) NOT NULL DEFAULT '1',
  `distributed_arp_table` tinyint(1) NOT NULL DEFAULT '1',
  `orig_interval` int(10) NOT NULL DEFAULT '1000',
  `gw_sel_class` int(10) NOT NULL DEFAULT '20',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `node_mp_settings`;
CREATE TABLE `node_mp_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id`int(11) DEFAULT NULL,
  `name`  varchar(50) DEFAULT NULL,
  `value` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

