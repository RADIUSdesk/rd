drop procedure if exists meshdesk_enhancements;

delimiter //
create procedure meshdesk_enhancements()
begin

if not exists (select * from information_schema.columns
    where column_name = 'new_server' and table_name = 'unknown_nodes' and table_schema = 'rd') then
    alter table unknown_nodes add column `new_server` varchar(255) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'new_server_status' and table_name = 'unknown_nodes' and table_schema = 'rd') then
    alter table unknown_nodes add column `new_server_status` enum('awaiting','fetched','replied') DEFAULT 'awaiting';
end if;


end//

delimiter ;
call meshdesk_enhancements;

