drop procedure if exists add_indexes;


delimiter //
create procedure add_indexes()
begin


set @exist := (select count(*) from information_schema.statistics where table_name = 'user_stats' and index_name = 'user_stats_index' and table_schema = 'rd');

if(@exist > 0) then
    drop index user_stats_index on user_stats;
end if;

set @exist := (select count(*) from information_schema.statistics where table_name = 'user_stats' and index_name = 'iUserStatsRealm' and table_schema = 'rd');

if(@exist = 0) then
    alter table user_stats add index iUserStatsRealm (realm,timestamp,acctinputoctets,acctoutputoctets); 
end if;

set @exist := (select count(*) from information_schema.statistics where table_name = 'user_stats' and index_name = 'iUserStatsNasidentifier' and table_schema = 'rd');

if(@exist = 0) then
    alter table user_stats add index iUserStatsNasidentifier (nasidentifier,timestamp,acctinputoctets,acctoutputoctets); 
end if;

set @exist := (select count(*) from information_schema.statistics where table_name = 'user_stats' and index_name = 'iUserStatsUsername' and table_schema = 'rd');

if(@exist = 0) then
    alter table user_stats add index iUserStatsUsername (username,timestamp,acctinputoctets,acctoutputoctets); 
end if;

set @exist := (select count(*) from information_schema.statistics where table_name = 'user_stats' and index_name = 'iUserStatsCallingstationid' and table_schema = 'rd');

if(@exist = 0) then
    alter table user_stats add index iUserStatsCallingstationid (callingstationid,timestamp,acctinputoctets,acctoutputoctets); 
end if;

set @exist := (select count(*) from information_schema.statistics where table_name = 'devices' and index_name = 'iDevicesName' and table_schema = 'rd');

if(@exist = 0) then
    alter table devices add index iDevicesName (name); 
end if;

set @exist := (select count(*) from information_schema.statistics where table_name = 'users' and index_name = 'iUsersUsername' and table_schema = 'rd');

if(@exist = 0) then
    alter table users add index iUsersUsername (username); 
end if;

set @exist := (select count(*) from information_schema.statistics where table_name = 'radacct' and index_name = 'iRadacctCallingstationid' and table_schema = 'rd');

if(@exist = 0) then
    alter table radacct add index iRadacctCallingstationid (callingstationid); 
end if;

set @exist := (select count(*) from information_schema.statistics where table_name = 'radacct' and index_name = 'iRadacctRadacctid' and table_schema = 'rd');

if(@exist = 0) then
    alter table radacct add index iRadacctRadacctid (radacctid); 
end if;

set @exist := (select count(*) from information_schema.statistics where table_name = 'radcheck' and index_name = 'iRadcheckUsername' and table_schema = 'rd');

if(@exist = 0) then
    alter table radcheck add index iRadcheckUsername (username,attribute); 
end if;

set @exist := (select count(*) from information_schema.statistics where table_name = 'devices' and index_name = 'iDevicesUserId' and table_schema = 'rd');

if(@exist = 0) then
    alter table devices add index iDevicesUserId (permanent_user_id); 
end if;

set @exist := (select count(*) from information_schema.statistics where table_name = 'radacct' and index_name = 'iRadacctCounters' and table_schema = 'rd');

if(@exist = 0) then
    alter table radacct add index iRadacctCounters (username,acctinputoctets,acctstarttime,acctoutputoctets,acctstoptime,acctsessiontime);
end if;

end//

delimiter ;
call add_indexes;
