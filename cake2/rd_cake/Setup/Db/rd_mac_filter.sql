drop procedure if exists rd_mac_filter;
delimiter //
create procedure rd_mac_filter()
begin

if not exists (select * from information_schema.columns
    where column_name = 'chk_maxassoc' and table_name = 'ap_profile_entries' and table_schema = 'rd') then
    alter table ap_profile_entries add column `chk_maxassoc` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'maxassoc' and table_name = 'ap_profile_entries' and table_schema = 'rd') then
    alter table ap_profile_entries add column `maxassoc` int(6) DEFAULT 100;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'macfilter' and table_name = 'ap_profile_entries' and table_schema = 'rd') then
    alter table ap_profile_entries add column `macfilter` enum('disable','allow','deny') DEFAULT 'disable';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'permanent_user_id' and table_name = 'ap_profile_entries' and table_schema = 'rd') then
    alter table ap_profile_entries add column `permanent_user_id` int(11) NOT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'chk_maxassoc' and table_name = 'mesh_entries' and table_schema = 'rd') then
    alter table mesh_entries add column `chk_maxassoc` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'maxassoc' and table_name = 'mesh_entries' and table_schema = 'rd') then
    alter table mesh_entries add column `maxassoc` int(6) DEFAULT 100;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'macfilter' and table_name = 'mesh_entries' and table_schema = 'rd') then
    alter table mesh_entries add column `macfilter` enum('disable','allow','deny') DEFAULT 'disable';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'permanent_user_id' and table_name = 'mesh_entries' and table_schema = 'rd') then
    alter table mesh_entries add column `permanent_user_id` int(11) NOT NULL;
end if;

end//
delimiter ;
call rd_mac_filter;

