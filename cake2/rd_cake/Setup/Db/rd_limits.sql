drop procedure if exists rd_limits;
delimiter //
create procedure rd_limits()
begin
if not exists (select * from information_schema.columns
    where table_name = 'limits' and table_schema = 'rd') then   
        CREATE TABLE `limits` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `alias` varchar(100) NOT NULL DEFAULT '',
          `active` tinyint(1) NOT NULL DEFAULT '0',
          `count` int(11) DEFAULT NULL,
          `created` datetime NOT NULL,
          `modified` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
end if;
end//
delimiter ;
call rd_limits;

