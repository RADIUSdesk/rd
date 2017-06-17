drop procedure if exists dynamic_details_addcolumns;

delimiter //
create procedure dynamic_details_addcolumns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'coova_desktop_url' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `coova_desktop_url` varchar(255) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'coova_mobile_url' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `coova_mobile_url` varchar(255) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'mikrotik_desktop_url' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `mikrotik_desktop_url` varchar(255) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'mikrotik_mobile_url' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `mikrotik_mobile_url` varchar(255) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'default_language' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `default_language` varchar(255) NOT NULL DEFAULT '';
end if;


end//

delimiter ;
call dynamic_details_addcolumns;


