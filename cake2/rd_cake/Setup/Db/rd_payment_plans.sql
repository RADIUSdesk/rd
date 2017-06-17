DROP TABLE IF EXISTS `fin_payment_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_payment_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `profile_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT '',
  `type` enum('voucher','user') DEFAULT 'user',
  `currency_code` enum('USD','ZAR','GBP','EUR') DEFAULT 'ZAR',
  `value` numeric(15,2) DEFAULT 0,
  `tax`	numeric(15,2) DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `fin_payment_plan_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_payment_plan_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fin_payment_plan_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
