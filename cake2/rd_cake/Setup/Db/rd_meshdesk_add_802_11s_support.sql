drop procedure if exists meshdesk_add_802_11s_support;

delimiter //
create procedure meshdesk_add_802_11s_support()
begin

if not exists (select * from information_schema.columns
    where column_name = 'connectivity' and table_name = 'mesh_settings' and table_schema = 'rd') then
    alter table mesh_settings add `connectivity` enum('IBSS','mesh_point') DEFAULT 'mesh_point';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'encryption' and table_name = 'mesh_settings' and table_schema = 'rd') then
    alter table mesh_settings add `encryption` tinyint(1) NOT NULL DEFAULT '0';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'encryption_key' and table_name = 'mesh_settings' and table_schema = 'rd') then
    alter table mesh_settings add `encryption_key` varchar(63) NOT NULL DEFAULT '';
end if;

end//

delimiter ;
call meshdesk_add_802_11s_support;
