DROP TABLE IF EXISTS `fin_paypal_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_paypal_transactions` (
  `id`          int(11) NOT NULL AUTO_INCREMENT,
  `user_id`     int(11) DEFAULT NULL,
  `voucher_id`  int(11) DEFAULT NULL,
  `top_up_id`   int(11) DEFAULT NULL,
  `business`    varchar(255) NOT NULL,
  `txn_id`      varchar(20) NOT NULL,
  `option_name1` varchar(255) DEFAULT NULL,
  `option_selection1` varchar(255) DEFAULT NULL,
  `item_name`   varchar(255) DEFAULT NULL,
  `item_number` varchar(255) DEFAULT NULL,
  `first_name`  varchar(255) DEFAULT NULL,
  `last_name`   varchar(255) DEFAULT NULL,
  `payer_email` varchar(255) DEFAULT NULL,
  `payer_id`    varchar(255) DEFAULT NULL,
  `payer_status`varchar(255) DEFAULT NULL,
  `payment_gross` DECIMAL(10,2) DEFAULT NULL,
  `mc_gross`    DECIMAL(10,2) NOT NULL,
  `mc_fee`      DECIMAL(10,2) NOT NULL,
  `mc_currency` varchar(255) DEFAULT 'GBP',
  `payment_date` varchar(255) NOT NULL,
  `payment_status` varchar(255) NOT NULL,
  `created`     datetime NOT NULL,
  `modified`    datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `device_notes`
--

DROP TABLE IF EXISTS `fin_paypal_transaction_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_paypal_transaction_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fin_paypal_transaction_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


