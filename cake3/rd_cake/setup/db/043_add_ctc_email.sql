drop procedure if exists add_ctc_email;

delimiter //
create procedure add_ctc_email()
begin

if not exists (select * from information_schema.columns
    where column_name = 'ctc_require_email' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `ctc_require_email` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'ctc_resupply_email_interval' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `ctc_resupply_email_interval` int(4) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where table_name = 'data_collectors' and table_schema = 'rd') then
	CREATE TABLE `data_collectors` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `dynamic_detail_id` int(11) DEFAULT NULL,
      `email` varchar(255) NOT NULL,
      `mac` varchar(36) NOT NULL, 
      `cp_mac` varchar(36) DEFAULT NULL,
      `public_ip` varchar(36) DEFAULT NULL,
      `nasid` varchar(255) DEFAULT NULL,
      `ssid` varchar(255) DEFAULT NULL,
      `is_mobile` tinyint(1) NOT NULL DEFAULT '0',
      `created` datetime NOT NULL,
      `modified` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB CHARSET=utf8;

end if;

end//

delimiter ;
call add_ctc_email;

