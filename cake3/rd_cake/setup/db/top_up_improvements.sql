drop procedure if exists top_up_improvements;

delimiter //
create procedure top_up_improvements()
begin

if not exists (select * from information_schema.columns
    where column_name = 'type' and table_name = 'top_ups' and table_schema = 'rd') then
    alter table top_ups add column `type` enum('data','time','days_to_use') DEFAULT 'data';
end if;

if not exists (select * from information_schema.columns
    where table_name = 'top_up_transactions' and table_schema = 'rd') then
    CREATE TABLE `top_up_transactions` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `user_id` int(11) DEFAULT NULL,
     `permanent_user_id` int(11) DEFAULT NULL,
     `permanent_user` varchar(255) DEFAULT NULL,
     `top_up_id` int(11) DEFAULT NULL,
     `type` enum('data','time','days_to_use') DEFAULT 'data',
     `action` enum('create','update','delete') DEFAULT 'create',
     `radius_attribute` varchar(30) NOT NULL DEFAULT '',
     `old_value` varchar(30) DEFAULT NULL,
     `new_value` varchar(30) DEFAULT NULL,
     `created` datetime NOT NULL,
	 `modified` datetime NOT NULL,
     PRIMARY KEY (`id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
end if;

end//

delimiter ;
call top_up_improvements

