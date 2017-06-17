drop procedure if exists realms_suffix_option;

delimiter //
create procedure realms_suffix_option()
begin

if not exists (select * from information_schema.columns
    where column_name = 'suffix' and table_name = 'realms' and table_schema = 'rd') then
    alter table realms add column `suffix` char(200) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'suffix_permanent_users' and table_name = 'realms' and table_schema = 'rd') then
    alter table realms add column `suffix_permanent_users` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'suffix_vouchers' and table_name = 'realms' and table_schema = 'rd') then
    alter table realms add column `suffix_vouchers` tinyint(1) NOT NULL DEFAULT '0';
end if;

end//

delimiter ;
call realms_suffix_option;

