drop procedure if exists dynamic_details_addcolumns;

delimiter //
create procedure dynamic_details_addcolumns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'social_enable' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `social_enable` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'social_temp_permanent_user_id' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `social_temp_permanent_user_id` int(11) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where table_name = 'dynamic_detail_social_logins' and table_schema = 'rd') then
	CREATE TABLE `dynamic_detail_social_logins` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`dynamic_detail_id` int(11) NOT NULL,
		`profile_id` int(11) NOT NULL,
		`realm_id` int(11) NOT NULL,
		`name` varchar(50) NOT NULL,
		`enable` tinyint(1) NOT NULL DEFAULT '0',
		`record_info` tinyint(1) NOT NULL DEFAULT '0',
		`key` varchar(100) NOT NULL DEFAULT '',
		`secret` varchar(100) NOT NULL DEFAULT '',
		`type` enum('voucher','user') DEFAULT 'voucher',
		`extra_name` varchar(100) NOT NULL DEFAULT '',
		`extra_value` varchar(100) NOT NULL DEFAULT '',
		`created` datetime NOT NULL,
		`modified` datetime NOT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
end if;


end//

delimiter ;
call dynamic_details_addcolumns;

