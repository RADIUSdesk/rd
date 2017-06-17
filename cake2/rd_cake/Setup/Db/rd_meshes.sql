drop procedure if exists meshes_addcolumns;

delimiter //
create procedure meshes_addcolumns()
begin

if not exists (select * from information_schema.columns
    where column_name = 'available_to_siblings' and table_name = 'meshes' and table_schema = 'rd') then
    alter table meshes add column `available_to_siblings` tinyint(1) NOT NULL DEFAULT '0';
end if;


if not exists (select * from information_schema.columns
    where table_name = 'unknown_nodes' and table_schema = 'rd') then
	CREATE TABLE unknown_nodes ( 
	  `id`                    int(11) unsigned NOT NULL auto_increment,
      `mac` varchar(255) NOT NULL,
      `vendor` varchar(255) DEFAULT NULL,
	  `from_ip` varchar(15) NOT NULL default '',
	  `gateway`        tinyint(1) NOT NULL DEFAULT '1',
	  `last_contact`   DATETIME NULL default NULL,
	  `created`        datetime NOT NULL,
	  `modified`       datetime NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB;

end if;


end//

delimiter ;
call meshes_addcolumns;

