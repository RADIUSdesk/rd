drop procedure if exists add_notifications;

delimiter //
create procedure add_notifications()
begin

if not exists (select * from information_schema.columns
    where table_name = 'permanent_user_notifications' and table_schema = 'rd') then
	CREATE TABLE `permanent_user_notifications` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `permanent_user_id` int(11) DEFAULT NULL,
      `active` tinyint(1) NOT NULL DEFAULT '1',
      `method` enum('whatsapp','email','sms') DEFAULT 'email', 
      `type` enum('daily','usage') DEFAULT 'daily',
      `address_1` varchar(255) DEFAULT NULL,
      `address_2` varchar(255) DEFAULT NULL,
      `start` int(3) DEFAULT 80,
      `increment`  int(3) DEFAULT 10,
      `last_value` int(3) DEFAULT NULL,
      `last_notification`   DATETIME NULL default NULL,
	  `created` datetime NOT NULL,
	  `modified` datetime NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

end if;
end//

delimiter ;
call add_notifications;








