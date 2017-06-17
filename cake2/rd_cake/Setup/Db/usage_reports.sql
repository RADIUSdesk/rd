DROP TABLE IF EXISTS `new_accountings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new_accountings` (
  `mac` varchar(17) NOT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`mac`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `mac_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mac_usages` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `mac` varchar(17) NOT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `data_used` bigint(20) DEFAULT NULL,
  `data_cap`  bigint(20) DEFAULT NULL,
  `time_used` int(12) DEFAULT NULL,
  `time_cap`  int(12) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


drop procedure if exists add_usage_columns;

delimiter //
create procedure add_usage_columns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'data_used' and table_name = 'users' and table_schema = 'rd') then
    alter table users add column `data_used` bigint(20) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'data_cap' and table_name = 'users' and table_schema = 'rd') then
    alter table users add column `data_cap` bigint(20) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'time_used' and table_name = 'users' and table_schema = 'rd') then
    alter table users add column `time_used`  int(12) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'time_cap' and table_name = 'users' and table_schema = 'rd') then
    alter table users add column `time_cap`  int(12) DEFAULT NULL;
end if;



if not exists (select * from information_schema.columns
    where column_name = 'data_used' and table_name = 'vouchers' and table_schema = 'rd') then
    alter table vouchers add column `data_used` bigint(20) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'data_cap' and table_name = 'vouchers' and table_schema = 'rd') then
    alter table vouchers add column `data_cap` bigint(20) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'time_used' and table_name = 'vouchers' and table_schema = 'rd') then
    alter table vouchers add column `time_used`  int(12) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'time_cap' and table_name = 'vouchers' and table_schema = 'rd') then
    alter table vouchers add column `time_cap`  int(12) DEFAULT NULL;
end if;



if not exists (select * from information_schema.columns
    where column_name = 'data_used' and table_name = 'devices' and table_schema = 'rd') then
    alter table devices add column `data_used` bigint(20) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'data_cap' and table_name = 'devices' and table_schema = 'rd') then
    alter table devices add column `data_cap` bigint(20) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'time_used' and table_name = 'devices' and table_schema = 'rd') then
    alter table devices add column `time_used`  int(12) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'time_cap' and table_name = 'devices' and table_schema = 'rd') then
    alter table devices add column `time_cap`  int(12) DEFAULT NULL;
end if;


end//

delimiter ;

call add_usage_columns;
