DROP TABLE IF EXISTS `fin_authorize_net_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_authorize_net_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id`     int(11) DEFAULT NULL,
  `voucher_id`  int(11) DEFAULT NULL,
  `voucher_name`char(50) NOT NULL DEFAULT '',
  `top_up_id`   int(11) DEFAULT NULL,
  `description` char(50) NOT NULL DEFAULT '', 
  `x_response_code` int(2) DEFAULT NULL,
  `x_response_subcode` int(4) DEFAULT NULL,
  `x_response_reason_code` int(4) DEFAULT NULL,
  `x_response_reason_text` char(200) NOT NULL DEFAULT '',
  `x_auth_code` char(50) NOT NULL DEFAULT '',
  `x_avs_code` char(50) NOT NULL DEFAULT '',
  `x_trans_id` char(50) NOT NULL DEFAULT '',
  `x_method` char(5) NOT NULL DEFAULT '',
  `x_card_type` char(50) NOT NULL DEFAULT '',
  `x_account_number` char(50) NOT NULL DEFAULT '',
  `x_first_name` char(50) NOT NULL DEFAULT '',
  `x_last_name` char(50) NOT NULL DEFAULT '',
  `x_company` char(50) NOT NULL DEFAULT '', 
  `x_address` char(50) NOT NULL DEFAULT '',
  `x_city` char(50) NOT NULL DEFAULT '',
  `x_state` char(50) NOT NULL DEFAULT '', 
  `x_zip` char(50) NOT NULL DEFAULT '',
  `x_country` char(50) NOT NULL DEFAULT '',  
  `x_phone` char(50) NOT NULL DEFAULT '', 
  `x_fax` char(50) NOT NULL DEFAULT '',
  `x_email` char(50) NOT NULL DEFAULT '',
  `x_amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00', 
  `x_catalog_link_id` char(50) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `fin_authorize_net_transaction_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_authorize_net_transaction_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fin_authorize_net_transaction_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

