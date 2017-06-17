DROP TABLE IF EXISTS `node_systems`;
CREATE TABLE `node_systems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id`int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `node_loads`;
CREATE TABLE `node_loads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id`int(11) DEFAULT NULL,
  `mem_total` int(11) DEFAULT NULL,
  `mem_free`  int(11) DEFAULT NULL,
  `uptime`  varchar(255) DEFAULT NULL,
  `system_time` varchar(255) NOT NULL,
  `load_1`  FLOAT(2,2) NOT NULL,
  `load_2`  FLOAT(2,2) NOT NULL,
  `load_3`  FLOAT(2,2) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `node_stations`;
CREATE TABLE `node_stations` (
  `id`                  int(11) NOT NULL AUTO_INCREMENT,
  `node_id`             int(11) DEFAULT NULL,
  `mesh_entry_id`       int(11) DEFAULT NULL,
  `vendor`              varchar(255) DEFAULT NULL,
  `mac`                 varchar(17) NOT NULL,
  `tx_bytes`            bigint(20) NOT NULL,
  `rx_bytes`            bigint(20) NOT NULL,
  `tx_packets`          int(11) NOT NULL,
  `rx_packets`          int(11) NOT NULL,
  `tx_bitrate`          int(11) NOT NULL,
  `rx_bitrate`          int(11) NOT NULL,
  `tx_extra_info`       varchar(255) NOT NULL,
  `rx_extra_info`       varchar(255) NOT NULL,
  `authenticated`       enum('yes','no') DEFAULT 'no',
  `authorized`          enum('yes','no') DEFAULT 'no',
  `tdls_peer`           varchar(255) NOT NULL,
  `preamble`            enum('long','short') DEFAULT 'long',
  `tx_failed`           int(11) NOT NULL,
  `inactive_time`       int(11) NOT NULL,
  `WMM_WME`             enum('yes','no') DEFAULT 'no',
  `tx_retries`          int(11) NOT NULL,
  `MFP`                 enum('yes','no') DEFAULT 'no',
  `signal`              int(11)   NOT NULL,
  `signal_avg`          int(11)   NOT NULL,
  `created`             datetime NOT NULL,
  `modified`            datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `node_ibss_connections`;
CREATE TABLE `node_ibss_connections` (
  `id`                  int(11) NOT NULL AUTO_INCREMENT,
  `node_id`             int(11) DEFAULT NULL,
  `station_node_id`     int(11) DEFAULT NULL,
  `vendor`              varchar(255) DEFAULT NULL,
  `mac`                 varchar(17) NOT NULL,
  `tx_bytes`            bigint(20) NOT NULL,
  `rx_bytes`            bigint(20) NOT NULL,
  `tx_packets`          int(11) NOT NULL,
  `rx_packets`          int(11) NOT NULL,
  `tx_bitrate`          int(11) NOT NULL,
  `rx_bitrate`          int(11) NOT NULL,
  `tx_extra_info`       varchar(255) NOT NULL,
  `rx_extra_info`       varchar(255) NOT NULL,
  `authenticated`       enum('yes','no') DEFAULT 'no',
  `authorized`          enum('yes','no') DEFAULT 'no',
  `tdls_peer`           varchar(255) NOT NULL,
  `preamble`            enum('long','short') DEFAULT 'long',
  `tx_failed`           int(11) NOT NULL,
  `inactive_time`       int(11) NOT NULL,
  `WMM_WME`             enum('yes','no') DEFAULT 'no',
  `tx_retries`          int(11) NOT NULL,
  `MFP`                 enum('yes','no') DEFAULT 'no',
  `signal`              int(11)   NOT NULL,
  `signal_avg`          int(11)   NOT NULL,
  `created`             datetime NOT NULL,
  `modified`            datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


