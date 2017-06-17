drop procedure if exists add_email_messages;

delimiter //
create procedure add_email_messages()
begin

if not exists (select * from information_schema.columns
    where table_name = 'email_messages' and table_schema = 'rd') then
	CREATE TABLE `email_messages` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(64) NOT NULL DEFAULT '',
	  `title` varchar(64) NOT NULL DEFAULT '',
	  `message` varchar(255) NOT NULL DEFAULT '',
	  `created` datetime NOT NULL,
	  `modified` datetime NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

end if;

end//

delimiter ;
call add_email_messages;
