drop procedure if exists node_settings_add_gateway_columns;

delimiter //
create procedure node_settings_add_gateway_columns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'gw_dhcp_timeout' and table_name = 'node_settings' and table_schema = 'rd') then
    alter table node_settings add column `gw_dhcp_timeout` int(5) NOT NULL DEFAULT '120';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'gw_use_previous' and table_name = 'node_settings' and table_schema = 'rd') then
    alter table node_settings add column `gw_use_previous` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'gw_auto_reboot' and table_name = 'node_settings' and table_schema = 'rd') then
    alter table node_settings add column `gw_auto_reboot` tinyint(1) NOT NULL DEFAULT '1';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'gw_auto_reboot_time' and table_name = 'node_settings' and table_schema = 'rd') then
    alter table node_settings add column `gw_auto_reboot_time` int(5) NOT NULL DEFAULT '600';
end if;

end//

delimiter ;
call node_settings_add_gateway_columns;

