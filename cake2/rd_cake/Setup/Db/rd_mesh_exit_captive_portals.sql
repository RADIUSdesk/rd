drop procedure if exists mesh_exit_captive_portals_addcolumns;

delimiter //
create procedure mesh_exit_captive_portals_addcolumns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'proxy_enable' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `proxy_enable` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'proxy_ip' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `proxy_ip` varchar(128) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'proxy_port' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `proxy_port` int(11) NOT NULL DEFAULT '3128';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'proxy_auth_username' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `proxy_auth_username` varchar(128) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'proxy_auth_password' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `proxy_auth_password` varchar(128) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'coova_optional' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `coova_optional` varchar(255) NOT NULL DEFAULT '';
end if;


end//

delimiter ;
call mesh_exit_captive_portals_addcolumns;

