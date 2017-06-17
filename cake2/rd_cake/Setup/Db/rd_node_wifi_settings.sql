drop procedure if exists add_node_wifi_settings;

delimiter //
create procedure add_node_wifi_settings()
begin

if not exists (select * from information_schema.columns
    where table_name = 'node_wifi_settings' and table_schema = 'rd') then
	CREATE TABLE `node_wifi_settings` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`node_id` int(11) DEFAULT NULL,
		`name` varchar(50) DEFAULT NULL,
        `value` varchar(255) DEFAULT NULL,
		`created` datetime NOT NULL,
		`modified` datetime NOT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
end if;

end//

delimiter ;
call add_node_wifi_settings;

