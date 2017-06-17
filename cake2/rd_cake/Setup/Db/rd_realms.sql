drop procedure if exists realms_addcolumns;

delimiter //
create procedure realms_addcolumns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'twitter' and table_name = 'realms' and table_schema = 'rd') then
    alter table realms add column `twitter` varchar(255) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'facebook' and table_name = 'realms' and table_schema = 'rd') then
    alter table realms add column `facebook` varchar(255) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'youtube' and table_name = 'realms' and table_schema = 'rd') then
    alter table realms add column `youtube` varchar(255) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'google_plus' and table_name = 'realms' and table_schema = 'rd') then
    alter table realms add column `google_plus` varchar(255) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'linkedin' and table_name = 'realms' and table_schema = 'rd') then
    alter table realms add column `linkedin` varchar(255) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 't_c_title' and table_name = 'realms' and table_schema = 'rd') then
    alter table realms add column `t_c_title` varchar(255) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 't_c_content' and table_name = 'realms' and table_schema = 'rd') then
    alter table realms add column `t_c_content` text NOT NULL NOT NULL DEFAULT '';
end if;

end//

delimiter ;
call realms_addcolumns;


