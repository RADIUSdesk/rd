drop procedure if exists add_radippool;

delimiter //
create procedure add_radippool()
begin

if not exists (select * from information_schema.columns
    where table_name = 'radippool' and table_schema = 'rd') then
	CREATE TABLE radippool ( 
	  `id`                    int(11) unsigned NOT NULL auto_increment,
	  `pool_name`             varchar(30) NOT NULL,
	  `framedipaddress`       varchar(15) NOT NULL default '',
	  `nasipaddress`          varchar(15) NOT NULL default '',
	  `calledstationid`       VARCHAR(30) NOT NULL,
	  `callingstationid`      VARCHAR(30) NOT NULL,
	  `expiry_time`           DATETIME NULL default NULL,
	  `username`              varchar(64) NOT NULL default '',
	  `pool_key`              varchar(30) NOT NULL default '',
	  `nasidentifier`         varchar(64) NOT NULL DEFAULT '',				 
	  `extra_name`            varchar(100) NOT NULL DEFAULT '',
	  `extra_value`           varchar(100) NOT NULL DEFAULT '',
	  `active`                tinyint(1) NOT NULL DEFAULT '1',
	  `permanent_user_id`     int(11) DEFAULT NULL,
	  `created`               datetime NOT NULL,
	  `modified`              datetime NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY radippool_poolname_expire (`pool_name`, `expiry_time`),
	  KEY framedipaddress (`framedipaddress`),
	  KEY radippool_nasip_poolkey_ipaddress (`nasipaddress`, `pool_key`, `framedipaddress`)
	) ENGINE=InnoDB;

end if;

end//

delimiter ;
call add_radippool;








