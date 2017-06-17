drop procedure if exists add_language_selection;

delimiter //
create procedure add_language_selection()
begin

if not exists (select * from information_schema.columns
    where column_name = 'available_languages' and table_name = 'dynamic_details' and table_schema = 'rd') then
    alter table dynamic_details add column `available_languages` varchar(255) NOT NULL DEFAULT '';
end if;

end//

delimiter ;
call add_language_selection

