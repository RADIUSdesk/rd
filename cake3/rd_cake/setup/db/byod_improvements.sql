drop procedure if exists byod_improvements;

delimiter //
create procedure byod_improvements()
begin

if not exists (select * from information_schema.columns
    where column_name = 'time_cap_type' and table_name = 'devices' and table_schema = 'rd') then
    alter table devices add column `time_cap_type` enum('hard','soft') DEFAULT 'soft';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'data_cap_type' and table_name = 'devices' and table_schema = 'rd') then
    alter table devices add column `data_cap_type` enum('hard','soft') DEFAULT 'soft';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'realm' and table_name = 'devices' and table_schema = 'rd') then
    alter table devices add column `realm` varchar(100) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'realm_id' and table_name = 'devices' and table_schema = 'rd') then
    alter table devices add column `realm_id` int(11) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'profile' and table_name = 'devices' and table_schema = 'rd') then
    alter table devices add column `profile` varchar(100) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'profile_id' and table_name = 'devices' and table_schema = 'rd') then
    alter table devices add column `profile_id` int(11) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'from_date' and table_name = 'devices' and table_schema = 'rd') then
    alter table devices add column `from_date` datetime DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'to_date' and table_name = 'devices' and table_schema = 'rd') then
    alter table devices add column `to_date` datetime DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'suffix_devices' and table_name = 'realms' and table_schema = 'rd') then
    alter table realms add column `suffix_devices` tinyint(1) NOT NULL DEFAULT '0';
end if;

end//

delimiter ;
call byod_improvements

