drop procedure if exists exit_point_enhancements;

delimiter //
create procedure exit_point_enhancements()
begin

if not exists (select * from information_schema.columns
    where column_name = 'ipaddr' and table_name = 'mesh_exits' and table_schema = 'rd') then
    alter table mesh_exits add column `proto` enum('static', 'dhcp','dhcpv6') DEFAULT 'dhcp';
    alter table mesh_exits add column `ipaddr` varchar(50) NOT NULL DEFAULT '';
    alter table mesh_exits add column `netmask` varchar(50) NOT NULL DEFAULT '';
    alter table mesh_exits add column `gateway` varchar(50) NOT NULL DEFAULT '';
    alter table mesh_exits add column `dns_1` varchar(50) NOT NULL DEFAULT '';
    alter table mesh_exits add column `dns_2` varchar(50) NOT NULL DEFAULT '';
    alter table mesh_exits CHANGE  type type enum('bridge','tagged_bridge','nat','captive_portal','openvpn_bridge','tagged_bridge_l3');
end if;

if not exists (select * from information_schema.columns
    where column_name = 'mesh_exit_upstream_id' and table_name = 'mesh_exit_captive_portals' and table_schema = 'rd') then
    alter table mesh_exit_captive_portals add column `mesh_exit_upstream_id` int(11) DEFAULT NULL;
end if;


if not exists (select * from information_schema.columns
    where column_name = 'ipaddr' and table_name = 'ap_profile_exits' and table_schema = 'rd') then
    alter table ap_profile_exits add column `proto` enum('static', 'dhcp','dhcpv6') DEFAULT 'dhcp';
    alter table ap_profile_exits add column `ipaddr` varchar(50) NOT NULL DEFAULT '';
    alter table ap_profile_exits add column `netmask` varchar(50) NOT NULL DEFAULT '';
    alter table ap_profile_exits add column `gateway` varchar(50) NOT NULL DEFAULT '';
    alter table ap_profile_exits add column `dns_1` varchar(50) NOT NULL DEFAULT '';
    alter table ap_profile_exits add column `dns_2` varchar(50) NOT NULL DEFAULT '';
    alter table ap_profile_exits CHANGE  type type enum('bridge','tagged_bridge','nat','captive_portal','openvpn_bridge','tagged_bridge_l3');
end if;

if not exists (select * from information_schema.columns
    where column_name = 'ap_profile_exit_upstream_id' and table_name = 'ap_profile_exit_captive_portals' and table_schema = 'rd') then
    alter table ap_profile_exit_captive_portals add column `ap_profile_exit_upstream_id` int(11) DEFAULT NULL;
end if;


end//

delimiter ;

call exit_point_enhancements;


