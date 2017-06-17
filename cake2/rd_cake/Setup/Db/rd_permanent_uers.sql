
drop procedure if exists users_addcolumns;

delimiter //
create procedure users_addcolumns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'data_used' and table_name = 'users' and table_schema = 'rd') then
    alter table users add `data_used` bigint(20) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'data_cap' and table_name = 'users' and table_schema = 'rd') then
    alter table users add `data_cap` bigint(20) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'data_cap_type' and table_name = 'users' and table_schema = 'rd') then
    alter table users add `data_cap_type` enum('hard','soft') DEFAULT 'soft';
end if;


if not exists (select * from information_schema.columns
    where column_name = 'time_used' and table_name = 'users' and table_schema = 'rd') then
    alter table users add `time_used` int(12) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'time_cap' and table_name = 'users' and table_schema = 'rd') then
    alter table users add `time_cap` int(12) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'time_cap_type' and table_name = 'users' and table_schema = 'rd') then
    alter table users add `time_cap_type` enum('hard','soft') DEFAULT 'soft';
end if;


if not exists (select * from information_schema.columns
    where column_name = 'realm' and table_name = 'users' and table_schema = 'rd') then
    alter table users add column `realm` varchar(50) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'realm_id' and table_name = 'users' and table_schema = 'rd') then
    alter table users add column `realm_id` int(11) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'profile' and table_name = 'users' and table_schema = 'rd') then
    alter table users add column `profile` varchar(50) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'profile_id' and table_name = 'users' and table_schema = 'rd') then
    alter table users add column `profile_id` int(11) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'from_date' and table_name = 'users' and table_schema = 'rd') then
    alter table users add column `from_date` datetime DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'to_date' and table_name = 'users' and table_schema = 'rd') then
    alter table users add column `to_date` datetime DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'track_auth' and table_name = 'users' and table_schema = 'rd') then
    alter table users add column `track_auth` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'track_acct' and table_name = 'users' and table_schema = 'rd') then
    alter table users add column `track_acct` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'static_ip' and table_name = 'users' and table_schema = 'rd') then
    alter table users add column `static_ip` varchar(50) NOT NULL DEFAULT '';
end if;

end//

delimiter ;
call users_addcolumns;





