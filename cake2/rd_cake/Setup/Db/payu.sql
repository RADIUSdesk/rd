DROP TABLE IF EXISTS `fin_pay_u_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_pay_u_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id`     int(11) DEFAULT NULL,
  `voucher_id`  int(11) DEFAULT NULL,
  `top_up_id`   int(11) DEFAULT NULL,
  `merchantReference` varchar(64) NOT NULL,
  `payUReference` varchar(64) NOT NULL,
  `TransactionType`  enum('RESERVE','FINALISE','PAYMENT','EFFECT_STAGING','CREDIT','RESERVE_CANCEL','REGISTER_LINK') DEFAULT 'PAYMENT',
  `TransactionState` enum('NEW','PROCESSING','SUCCESSFUL','FAILED','TIMEOUT') DEFAULT 'NEW',
  `ResultCode` int(11) DEFAULT NULL,
  `ResultMessage` varchar(255) DEFAULT NULL,
  `DisplayMessage` varchar(255) DEFAULT NULL,
  `merchUserId` varchar(255)DEFAULT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `lastName`  varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `regionalId` varchar(255) DEFAULT NULL,
  `amountInCents` int(11) NOT NULL,
  `currencyCode` varchar(255) DEFAULT 'ZAR',
  `description` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `fin_pay_u_transaction_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_pay_u_transaction_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fin_pay_u_transaction_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
