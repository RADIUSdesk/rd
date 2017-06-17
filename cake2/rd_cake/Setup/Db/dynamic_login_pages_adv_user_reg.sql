drop procedure if exists dynamic_login_pages_adv_user_reg_addcolumns;

delimiter //
create procedure dynamic_login_pages_adv_user_reg_addcolumns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'realm_id' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `realm_id` int(11) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'profile_id' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `profile_id` int(11) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'reg_auto_suffix_check' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `reg_auto_suffix_check` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'reg_auto_suffix' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `reg_auto_suffix` char(200) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'reg_mac_check' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `reg_mac_check` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'reg_auto_add' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `reg_auto_add` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'reg_email' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `reg_email` tinyint(1) NOT NULL DEFAULT '0';
end if;

end//

delimiter ;
call dynamic_login_pages_adv_user_reg_addcolumns;

