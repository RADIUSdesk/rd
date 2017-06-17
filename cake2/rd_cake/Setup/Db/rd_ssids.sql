drop procedure if exists add_ssids;

delimiter //
create procedure add_ssids()
begin

if not exists (select * from information_schema.columns
    where table_name = 'ssids' and table_schema = 'rd') then
	CREATE TABLE `ssids` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(64) NOT NULL DEFAULT '',
      `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
	  `user_id` int(11) DEFAULT NULL,
      `extra_name` varchar(100) NOT NULL DEFAULT '',
	  `extra_value` varchar(100) NOT NULL DEFAULT '',
	  `created` datetime NOT NULL,
	  `modified` datetime NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

end if;

if not exists (select * from information_schema.columns
    where table_name = 'user_ssids' and table_schema = 'rd') then
    CREATE TABLE `user_ssids` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `username` varchar(64) NOT NULL DEFAULT '',
     `ssidname` varchar(64) NOT NULL DEFAULT '',
     `priority` int(11) NOT NULL DEFAULT '1',
     PRIMARY KEY (`id`),
      KEY `username` (`username`(32))
      ) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
end if;

end//

delimiter ;
call add_ssids;








