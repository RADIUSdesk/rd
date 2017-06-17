drop procedure if exists captive_portal_dns;

delimiter //
create procedure captive_portal_dns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'dns_manual' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `dns_manual` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'dns1' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `dns1` varchar(128) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'dns2' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `dns2` varchar(128) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'uamanydns' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `uamanydns` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'dnsparanoia' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `dnsparanoia` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'dnsdesk' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `dnsdesk` tinyint(1) NOT NULL DEFAULT '0';
end if;


if not exists (select * from information_schema.columns
    where column_name = 'dns_manual' and table_name = 'ap_profile_exit_captive_portals' and table_schema = 'rd') then
    alter table ap_profile_exit_captive_portals add column `dns_manual` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'dns1' and table_name = 'ap_profile_exit_captive_portals' and table_schema = 'rd') then
    alter table ap_profile_exit_captive_portals add column `dns1` varchar(128) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'dns2' and table_name = 'ap_profile_exit_captive_portals' and table_schema = 'rd') then
    alter table ap_profile_exit_captive_portals add column `dns2` varchar(128) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'uamanydns' and table_name = 'ap_profile_exit_captive_portals' and table_schema = 'rd') then
    alter table ap_profile_exit_captive_portals add column `uamanydns` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'dnsparanoia' and table_name = 'ap_profile_exit_captive_portals' and table_schema = 'rd') then
    alter table ap_profile_exit_captive_portals add column `dnsparanoia` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'dnsdesk' and table_name = 'ap_profile_exit_captive_portals' and table_schema = 'rd') then
    alter table ap_profile_exit_captive_portals add column `dnsdesk` tinyint(1) NOT NULL DEFAULT '0';
end if;

end//

delimiter ;
call captive_portal_dns;
