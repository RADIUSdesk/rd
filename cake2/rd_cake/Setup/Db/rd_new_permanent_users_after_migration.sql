drop procedure if exists permanent_users_managecolumns;

delimiter //
create procedure permanent_users_managecolumns()
begin

if exists (select * from information_schema.columns
    where column_name = 'auth_type' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `auth_type`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'last_accept_time' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `last_accept_time`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'last_reject_time' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `last_reject_time`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'last_accept_nas' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `last_accept_nas`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'last_reject_nas' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `last_reject_nas`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'last_reject_message' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `last_reject_message`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'perc_time_used' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `perc_time_used`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'perc_data_used' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `perc_data_used`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'data_used' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `data_used`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'data_cap' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `data_cap`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'time_used' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `time_used`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'time_cap' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `time_cap`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'time_cap_type' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `time_cap_type`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'data_cap_type' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `data_cap_type`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'realm' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `realm`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'realm_id' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `realm_id`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'profile' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `profile`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'profile_id' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `profile_id`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'profile_id' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `profile_id`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'profile_id' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `profile_id`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'from_date' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `from_date`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'to_date' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `to_date`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'track_auth' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `track_auth`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'track_acct' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `track_acct`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'static_ip' and table_name = 'users' and table_schema = 'rd') then
    alter table users drop column `static_ip`;
end if;

if exists (select * from information_schema.columns
    where column_name = 'user_id' and table_name = 'devices' and table_schema = 'rd') then
    alter table devices CHANGE `user_id` `permanent_user_id` int(11);
end if;

end//

delimiter ;
call permanent_users_managecolumns;

