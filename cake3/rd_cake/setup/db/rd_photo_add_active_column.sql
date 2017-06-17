drop procedure if exists photo_add_active_column;

delimiter //
create procedure photo_add_active_column()
begin

if not exists (select * from information_schema.columns
    where column_name = 'active' and table_name = 'dynamic_photos' and table_schema = 'rd') then
    alter table dynamic_photos add column `active` tinyint(1) NOT NULL DEFAULT '1';
end if;


end//

delimiter ;
call photo_add_active_column

