drop procedure if exists rename_key_column_two;

delimiter //
create procedure rename_key_column_two()
begin

if not exists (select * from information_schema.columns
    where column_name = 'special_key' and table_name = 'ap_profile_entries' and table_schema = 'rd') then
    alter table ap_profile_entries change `key` `special_key` VARCHAR(100)  NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'special_key' and table_name = 'mesh_entries' and table_schema = 'rd') then
    alter table mesh_entries change `key` `special_key` VARCHAR(100)  NOT NULL DEFAULT '';
end if;

end//

delimiter ;
call rename_key_column_two;

