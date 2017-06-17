drop procedure if exists node_settings_add_columns;

delimiter //
create procedure node_settings_add_columns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'client_key' and table_name = 'node_settings' and table_schema = 'rd') then
    alter table node_settings add column `client_key` varchar(255) NOT NULL DEFAULT 'radiusdesk';
end if;

end//

delimiter ;
call node_settings_add_columns;


