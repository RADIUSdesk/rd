ALTER TABLE vouchers MODIFY name VARCHAR(64);
CREATE UNIQUE INDEX ak_vouchers ON vouchers(name);
CREATE INDEX FK_radcheck_ref_vouchers ON radcheck(username);
CREATE INDEX FK_radreply_ref_vouchers ON radreply(username);


drop procedure if exists vouchers_addcolumns;

delimiter //
create procedure vouchers_addcolumns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'extra_name' and table_name = 'vouchers' and table_schema = 'rd') then
    alter table vouchers add column `extra_name` varchar(100) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'extra_value' and table_name = 'vouchers' and table_schema = 'rd') then
    alter table vouchers add column `extra_value` varchar(100) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'password' and table_name = 'vouchers' and table_schema = 'rd') then
    alter table vouchers add column `password` varchar(30) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'realm' and table_name = 'vouchers' and table_schema = 'rd') then
    alter table vouchers add column `realm` varchar(50) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'realm_id' and table_name = 'vouchers' and table_schema = 'rd') then
    alter table vouchers add column `realm_id` int(11) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'profile' and table_name = 'vouchers' and table_schema = 'rd') then
    alter table vouchers add column `profile` varchar(50) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'profile_id' and table_name = 'vouchers' and table_schema = 'rd') then
    alter table vouchers add column `profile_id` int(11) DEFAULT NULL;
end if;

if not exists (select * from information_schema.columns
    where column_name = 'expire' and table_name = 'vouchers'and table_schema = 'rd') then
    alter table vouchers add column `expire` varchar(10) NOT NULL DEFAULT '';
end if;

if not exists (select * from information_schema.columns
    where column_name = 'time_valid' and table_name = 'vouchers' and table_schema = 'rd') then
    alter table vouchers add column `time_valid` varchar(10) NOT NULL DEFAULT '';
end if;

end//

delimiter ;
call vouchers_addcolumns;





