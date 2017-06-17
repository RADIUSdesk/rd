drop procedure if exists dynamic_details_addcolumns;

delimiter //
create procedure dynamic_details_addcolumns()
begin

if not exists (select * from information_schema.columns
    where column_name = 't_c_check' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `t_c_check` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 't_c_url' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `t_c_url` char(50) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'redirect_check' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `redirect_check` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'redirect_url' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `redirect_url` char(200) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'slideshow_check' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `slideshow_check` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'seconds_per_slide' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `seconds_per_slide` int(3) NOT NULL DEFAULT '30';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'connect_check' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `connect_check` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'connect_username' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `connect_username` char(50) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'connect_suffix' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `connect_suffix` char(50) NOT NULL DEFAULT 'nasid';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'connect_delay' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `connect_delay` int(3) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'connect_only' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `connect_only` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'user_login_check' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `user_login_check` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'voucher_login_check' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `voucher_login_check` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'auto_suffix_check' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `auto_suffix_check` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'auto_suffix' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `auto_suffix` char(200) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'usage_show_check' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `usage_show_check` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'usage_refresh_interval' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `usage_refresh_interval` int(3) NOT NULL DEFAULT '120';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'theme' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `theme` char(200) NOT NULL DEFAULT 'Default';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'register_users' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `register_users` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'lost_password' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `lost_password` tinyint(1) NOT NULL DEFAULT '0';
end if;


if not exists (select * from information_schema.columns
    where column_name = 'url' and table_name = 'dynamic_photos' and table_schema = 'rd') then
    alter table dynamic_photos add column `url` varchar(250) NOT NULL DEFAULT '';
end if;

end//

delimiter ;
call dynamic_details_addcolumns;

