drop procedure if exists add_data_limit;

delimiter //
create procedure add_data_limit()
begin

if not exists (select * from information_schema.columns
    where column_name = 'data_limit_active' and table_name = 'dynamic_clients' and table_schema = 'rd') then
    alter table dynamic_clients add column `data_limit_active` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'data_limit_amount' and table_name = 'dynamic_clients' and table_schema = 'rd') then
    alter table dynamic_clients add column `data_limit_amount` float(14,3) NOT NULL DEFAULT 1;
end if;


if not exists (select * from information_schema.columns
    where column_name = 'data_limit_unit' and table_name = 'dynamic_clients' and table_schema = 'rd') then
    alter table dynamic_clients add column `data_limit_unit` enum('kb','mb','gb','tb') DEFAULT 'mb';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'data_limit_reset_on' and table_name = 'dynamic_clients' and table_schema = 'rd') then
    alter table dynamic_clients add column `data_limit_reset_on` int(3) NOT NULL DEFAULT 1;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'data_limit_reset_hour' and table_name = 'dynamic_clients' and table_schema = 'rd') then
    alter table dynamic_clients add column `data_limit_reset_hour` int(3) NOT NULL DEFAULT 0;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'data_limit_reset_minute' and table_name = 'dynamic_clients' and table_schema = 'rd') then
    alter table dynamic_clients add column `data_limit_reset_minute` int(3) NOT NULL DEFAULT 0;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'data_used' and table_name = 'dynamic_clients' and table_schema = 'rd') then
    alter table dynamic_clients add column `data_used` bigint(20) DEFAULT NULL;
end if;


end//

delimiter ;
call add_data_limit;

