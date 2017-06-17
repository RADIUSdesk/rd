drop procedure if exists add_authorize_net_tag_column;

delimiter //
create procedure add_authorize_net_tag_column()
begin

if not exists (select * from information_schema.columns
    where column_name = 'tag' and table_name = 'fin_authorize_net_transactions' and table_schema = 'rd') then
    alter table fin_authorize_net_transactions add column `tag` varchar(100) NOT NULL DEFAULT 'unknown';
end if;

end//

delimiter ;
call add_authorize_net_tag_column;

