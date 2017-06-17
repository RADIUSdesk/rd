DROP TABLE IF EXISTS `fin_premium_sms_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_premium_sms_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id`     int(11) DEFAULT NULL,
  `voucher_id`  int(11) DEFAULT NULL,
  `top_up_id`   int(11) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `fin_premium_sms_transaction_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_premium_sms_transaction_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fin_premium_sms_transaction_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
