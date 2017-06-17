CREATE TABLE `permanent_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(50) NOT NULL,
  `token` char(36) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `auth_type` varchar(128) NOT NULL DEFAULT 'sql',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `last_accept_time` datetime DEFAULT NULL,
  `last_reject_time` datetime DEFAULT NULL,
  `last_accept_nas` varchar(128) DEFAULT NULL,
  `last_reject_nas` varchar(128) DEFAULT NULL,
  `last_reject_message` varchar(255) DEFAULT NULL,
  `perc_time_used` int(6) DEFAULT NULL,
  `perc_data_used` int(6) DEFAULT NULL,
  `data_used` bigint(20) DEFAULT NULL,
  `data_cap` bigint(20) DEFAULT NULL,
  `time_used` int(12) DEFAULT NULL,
  `time_cap` int(12) DEFAULT NULL,
  `time_cap_type` enum('hard','soft') DEFAULT 'soft',
  `data_cap_type` enum('hard','soft') DEFAULT 'soft',
  `realm` varchar(50) NOT NULL DEFAULT '',
  `realm_id` int(11) DEFAULT NULL,
  `profile` varchar(50) NOT NULL DEFAULT '',
  `profile_id` int(11) DEFAULT NULL,
  `from_date` datetime DEFAULT NULL,
  `to_date` datetime DEFAULT NULL,
  `track_auth` tinyint(1) NOT NULL DEFAULT '0',
  `track_acct` tinyint(1) NOT NULL DEFAULT '1',
  `static_ip` varchar(50) NOT NULL DEFAULT '',
  `extra_name` varchar(100) NOT NULL DEFAULT '',
  `extra_value` varchar(100) NOT NULL DEFAULT '',
  `country_id` int(11) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=198 DEFAULT CHARSET=utf8;


CREATE TABLE `permanent_user_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permanent_user_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;


CREATE TABLE `permanent_user_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permanent_user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;

