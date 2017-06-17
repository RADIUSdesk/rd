DROP TABLE IF EXISTS `user_stats`;

CREATE TABLE `user_stats` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`radacct_id` int(11) NOT NULL,
`username` varchar(64) NOT NULL DEFAULT '',
`realm` varchar(64) DEFAULT '',
`nasipaddress` varchar(15) NOT NULL DEFAULT '',
`nasidentifier` varchar(64) NOT NULL DEFAULT '',
`framedipaddress` varchar(15) NOT NULL DEFAULT '',
`callingstationid` varchar(50) NOT NULL DEFAULT '',
`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
`acctinputoctets` bigint(20) NOT NULL,
`acctoutputoctets` bigint(20) NOT NULL, 
PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

ALTER TABLE user_stats ADD INDEX user_stats_index (radacct_id, username, realm, nasipaddress, nasidentifier, callingstationid);


delimiter $$
CREATE TRIGGER radacct_after_update
AFTER update ON radacct FOR EACH ROW BEGIN
INSERT INTO user_stats 
  SET 
  radacct_id        = OLD.radacctid,
  username          = OLD.username,
  realm             = OLD.realm,  
  nasipaddress      = OLD.nasipaddress,
  nasidentifier     = OLD.nasidentifier,
  framedipaddress   = OLD.framedipaddress,
  callingstationid  = OLD.callingstationid,
  acctinputoctets   = (NEW.acctinputoctets - OLD.acctinputoctets), 
  acctoutputoctets  = (NEW.acctoutputoctets - OLD.acctoutputoctets);
END$$ 
delimiter ;

