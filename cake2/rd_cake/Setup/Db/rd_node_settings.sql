drop procedure if exists node_settings_addcolumns;

delimiter //
create procedure node_settings_addcolumns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'tz_name' and table_name = 'node_settings' and table_schema = 'rd') then
    alter table node_settings add column `tz_name` varchar(128) NOT NULL DEFAULT 'America/New York';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'tz_value' and table_name = 'node_settings' and table_schema = 'rd') then
    alter table node_settings add column `tz_value` varchar(128) NOT NULL DEFAULT 'EST5EDT,M3.2.0,M11.1.0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'country' and table_name = 'node_settings' and table_schema = 'rd') then
    alter table node_settings add column `country` varchar(5) NOT NULL DEFAULT 'US';
end if;


end//

delimiter ;
call node_settings_addcolumns;

