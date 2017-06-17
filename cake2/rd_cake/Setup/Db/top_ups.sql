DROP TABLE IF EXISTS `top_ups`;
CREATE TABLE `top_ups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id`     int(11) DEFAULT NULL,
  `permanent_user_id`  int(11) DEFAULT NULL,
  `data`  bigint(11) DEFAULT NULL,
  `time`  int(11) DEFAULT NULL,
  `days_to_use`  int(11) DEFAULT NULL,
  `comment` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

