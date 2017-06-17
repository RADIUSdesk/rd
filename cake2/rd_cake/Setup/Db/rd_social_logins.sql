drop procedure if exists add_social_logins;

delimiter //
create procedure add_social_logins()
begin

set names utf8;

if not exists (select * from information_schema.columns
    where table_name = 'social_login_users' and table_schema = 'rd') then
	CREATE TABLE `social_login_users` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`provider` enum('Facebook','Google','Twitter') DEFAULT 'Facebook',
		`uid` varchar(100) NOT NULL DEFAULT '',
		`name` varchar(100) NOT NULL DEFAULT '',
		`first_name` varchar(100) NOT NULL DEFAULT '',
		`last_name` varchar(100) NOT NULL DEFAULT '',
		`email` varchar(100) NOT NULL DEFAULT '',
		`image` varchar(100) NOT NULL DEFAULT '',
		`locale` varchar(5) NOT NULL DEFAULT '',
		`timezone` tinyint(3) NOT NULL DEFAULT '0',
		`date_of_birth` date DEFAULT NULL,
		`gender` enum('male','female') DEFAULT 'male',
		`last_connect_time` datetime DEFAULT NULL,
		`extra_name` varchar(100) NOT NULL DEFAULT '',
		`extra_value` varchar(100) NOT NULL DEFAULT '',
		`created` datetime NOT NULL,
		`modified` datetime NOT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
end if;

if not exists (select * from information_schema.columns
    where table_name = 'social_login_user_realms' and table_schema = 'rd') then
	CREATE TABLE `social_login_user_realms` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`social_login_user_id` int(11) DEFAULT NULL,
		`realm_id` int(11) DEFAULT NULL,
		`created` datetime NOT NULL,
		`modified` datetime NOT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
end if;

end//

delimiter ;
call add_social_logins;

