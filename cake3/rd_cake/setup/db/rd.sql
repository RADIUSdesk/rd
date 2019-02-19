-- MySQL dump 10.13  Distrib 5.7.25, for Linux (x86_64)
--
-- Host: localhost    Database: rd
-- ------------------------------------------------------
-- Server version	5.7.25-0ubuntu0.18.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `acl_phinxlog`
--

DROP TABLE IF EXISTS `acl_phinxlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_phinxlog`
--

LOCK TABLES `acl_phinxlog` WRITE;
/*!40000 ALTER TABLE `acl_phinxlog` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_phinxlog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acos`
--

DROP TABLE IF EXISTS `acos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=450 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acos`
--

LOCK TABLES `acos` WRITE;
/*!40000 ALTER TABLE `acos` DISABLE KEYS */;
INSERT INTO `acos` VALUES (29,NULL,NULL,NULL,'Access Providers','A container with rights available to Access Providers - DO NOT DELETE!!',1,626),(30,NULL,NULL,NULL,'Permanent Users','A container with rights for Permanent Users - DO NOT DELETE!!',627,632),(31,29,NULL,NULL,'Controllers','A container with the various controllers and their actions which can be used by the Access Providers',2,615),(32,29,NULL,NULL,'Other Rights','A list of other rights which can be configured for an Access Provider',616,625),(33,30,NULL,NULL,'Controllers','A container with the various controllers and their actions which can be used by the Permanent Users',628,629),(34,30,NULL,NULL,'Other Rights','A list of other rights which can be configured for a Permanent User',630,631),(42,32,NULL,NULL,'View users or vouchers not created self','',617,618),(43,31,NULL,NULL,'Vouchers','',3,32),(44,43,NULL,NULL,'index','',4,5),(45,31,NULL,NULL,'PermanentUsers','',33,78),(46,45,NULL,NULL,'index','',34,35),(58,31,NULL,NULL,'AccessProviders','Access Providers can only do these actions on any access provider that is a child of the Access Provider',79,102),(59,58,NULL,NULL,'index','Without this right, the Access Providers option will not be shown in the Access Provider\'s menu',80,81),(60,58,NULL,NULL,'add','Without this right an Access Provider will not be able to create Access Provider children',82,83),(61,58,NULL,NULL,'edit','',84,85),(62,58,NULL,NULL,'delete','',86,87),(63,32,NULL,NULL,'Can Change Rights','This is a key option to allow an Access Provider the ability to change the rights of any of his Access Provider children',619,620),(64,32,NULL,NULL,'Can disable activity recording','Can disable Activity Recording on Access Provider children',621,622),(65,58,NULL,NULL,'changePassword','',88,89),(67,31,NULL,NULL,'Realms','',103,128),(68,67,NULL,NULL,'index','',104,105),(69,67,NULL,NULL,'add','',106,107),(70,67,NULL,NULL,'edit','',108,109),(71,67,NULL,NULL,'delete','',110,111),(102,31,NULL,NULL,'Nas','Nas Devices - These rights are also considering the hierarchy of the Access Provider',129,184),(103,102,NULL,NULL,'index','Without this right there will be no NAS Devices in the AP\'s menu',130,131),(104,102,NULL,NULL,'add','',132,133),(105,102,NULL,NULL,'edit','',134,135),(106,102,NULL,NULL,'delete','',136,137),(107,31,NULL,NULL,'Tags','Tags for NAS Devices',185,204),(108,107,NULL,NULL,'index','Without this right, there will be no NAS Device tags in the AP\'s menu',186,187),(109,107,NULL,NULL,'add','',188,189),(110,107,NULL,NULL,'edit','',190,191),(111,107,NULL,NULL,'delete','',192,193),(112,102,NULL,NULL,'manage_tags','Attach or remove tags to NAS devices',138,139),(113,107,NULL,NULL,'exportCsv','Exporting the display from the grid to CSV',194,195),(114,107,NULL,NULL,'indexForFilter','A list for of tags to display on the filter field on the Access Provider grid',196,197),(115,107,NULL,NULL,'noteIndex','List notes',198,199),(116,107,NULL,NULL,'noteAdd','',200,201),(117,107,NULL,NULL,'noteDel','Remove a note of a NAS Tag',202,203),(118,102,NULL,NULL,'export_csv','Exporting the display of the grid to CSV',140,141),(119,102,NULL,NULL,'note_index','List notes',142,143),(120,102,NULL,NULL,'note_add','',144,145),(121,102,NULL,NULL,'note_del','',146,147),(122,67,NULL,NULL,'exportCsv','',112,113),(123,67,NULL,NULL,'indexForFilter','',114,115),(124,67,NULL,NULL,'noteIndex','',116,117),(125,67,NULL,NULL,'noteAdd','',118,119),(126,67,NULL,NULL,'noteDel','',120,121),(127,58,NULL,NULL,'exportCsv','',90,91),(128,58,NULL,NULL,'noteIndex','',92,93),(129,58,NULL,NULL,'noteAdd','',94,95),(130,58,NULL,NULL,'noteDel','',96,97),(132,31,NULL,NULL,'AcosRights','Controller to manage the Rights Tree',205,210),(133,132,NULL,NULL,'indexAp','List the rights of a specific AP',206,207),(134,132,NULL,NULL,'editAp','Modify the rights of a specific AP by another AP',208,209),(137,31,NULL,NULL,'Devices','Devices belonging to PermanentUsers',211,248),(138,137,NULL,NULL,'index','',212,213),(149,43,NULL,NULL,'add','',6,7),(150,43,NULL,NULL,'delete','',8,9),(151,31,NULL,NULL,'Desktop','',249,256),(152,151,NULL,NULL,'desktop_shortcuts','',250,251),(153,151,NULL,NULL,'change_password','',252,253),(154,151,NULL,NULL,'save_wallpaper_selection','',254,255),(156,43,NULL,NULL,'viewBasicInfo','',10,11),(157,43,NULL,NULL,'editBasicInfo','',12,13),(158,43,NULL,NULL,'privateAttrIndex','',14,15),(159,43,NULL,NULL,'privateAttrAdd','',16,17),(160,43,NULL,NULL,'privateAttrEdit','',18,19),(161,43,NULL,NULL,'privateAttrDelete','',20,21),(162,43,NULL,NULL,'changePassword','',22,23),(163,43,NULL,NULL,'exportCsv','',24,25),(164,43,NULL,NULL,'exportPdf','',26,27),(165,67,NULL,NULL,'indexAp','',122,123),(166,31,NULL,NULL,'Profiles','',257,274),(167,166,NULL,NULL,'index','',258,259),(168,166,NULL,NULL,'indexAp','Dropdown list based on selected Access Provider owner',260,261),(169,166,NULL,NULL,'add','',262,263),(170,166,NULL,NULL,'manageComponents','',264,265),(171,166,NULL,NULL,'delete','',266,267),(173,166,NULL,NULL,'noteIndex','',268,269),(174,166,NULL,NULL,'noteAdd','',270,271),(175,166,NULL,NULL,'noteDel','',272,273),(176,31,NULL,NULL,'Radaccts','',275,286),(177,176,NULL,NULL,'export_csv','',276,277),(178,176,NULL,NULL,'index','',278,279),(179,176,NULL,NULL,'delete','',280,281),(180,176,NULL,NULL,'kick_active','',282,283),(181,176,NULL,NULL,'close_open','',284,285),(182,43,NULL,NULL,'delete_accounting_data','',28,29),(184,45,NULL,NULL,'add','',36,37),(185,45,NULL,NULL,'delete','',38,39),(186,45,NULL,NULL,'viewBasicInfo','',40,41),(187,45,NULL,NULL,'editBasicInfo','',42,43),(188,45,NULL,NULL,'viewPersonalInfo','',44,45),(189,45,NULL,NULL,'editPersonalInfo','',46,47),(190,45,NULL,NULL,'privateAttrIndex','',48,49),(191,45,NULL,NULL,'privateAttrAdd','',50,51),(192,45,NULL,NULL,'privateAttrEdit','',52,53),(193,45,NULL,NULL,'privateAttrDelete','',54,55),(194,45,NULL,NULL,'changePassword','',56,57),(195,45,NULL,NULL,'enableDisable','',58,59),(196,45,NULL,NULL,'exportCsv','',60,61),(197,45,NULL,NULL,'noteIndex','',62,63),(198,137,NULL,NULL,'add','',214,215),(199,137,NULL,NULL,'delete','',216,217),(200,137,NULL,NULL,'viewBasicInfo','',218,219),(201,137,NULL,NULL,'editBasicInfo','',220,221),(202,137,NULL,NULL,'privateAttrIndex','',222,223),(203,137,NULL,NULL,'privateAttrAdd','',224,225),(204,137,NULL,NULL,'privateAttrEdit','',226,227),(205,137,NULL,NULL,'privateAttrDelete','',228,229),(206,137,NULL,NULL,'enableDisable','',230,231),(207,137,NULL,NULL,'exportCsv','',232,233),(208,137,NULL,NULL,'noteIndex','',234,235),(209,31,NULL,NULL,'FreeRadius','',287,292),(210,209,NULL,NULL,'test_radius','',288,289),(211,209,NULL,NULL,'index','Displays the stats of the FreeRADIUS server',290,291),(212,31,NULL,NULL,'Radpostauths','',293,302),(213,212,NULL,NULL,'index','',294,295),(214,212,NULL,NULL,'add','',296,297),(215,212,NULL,NULL,'delete','',298,299),(221,212,NULL,NULL,'export_csv','',300,301),(223,67,NULL,NULL,'updateNaRealm','',124,125),(224,102,NULL,NULL,'add_direct','',148,149),(225,102,NULL,NULL,'add_open_vpn','',150,151),(226,102,NULL,NULL,'add_dynamic','',152,153),(227,102,NULL,NULL,'add_pptp','',154,155),(228,102,NULL,NULL,'view_openvpn','',156,157),(229,102,NULL,NULL,'edit_openvpn','',158,159),(230,102,NULL,NULL,'view_pptp','',160,161),(231,102,NULL,NULL,'edit_pptp','',162,163),(232,102,NULL,NULL,'view_dynamic','',164,165),(233,102,NULL,NULL,'edit_dynamic','',166,167),(234,102,NULL,NULL,'view_nas','',168,169),(235,102,NULL,NULL,'edit_nas','',170,171),(236,102,NULL,NULL,'view_photo','',172,173),(237,102,NULL,NULL,'upload_photo','',174,175),(238,102,NULL,NULL,'view_map_pref','',176,177),(239,102,NULL,NULL,'edit_map_pref','',178,179),(240,102,NULL,NULL,'delete_map','',180,181),(241,102,NULL,NULL,'edit_map','',182,183),(243,67,NULL,NULL,'view','',126,127),(246,45,NULL,NULL,'restrictListOfDevices','',64,65),(247,45,NULL,NULL,'edit_tracking','',66,67),(248,45,NULL,NULL,'view_tracking','',68,69),(249,45,NULL,NULL,'noteAdd','',70,71),(250,45,NULL,NULL,'noteDel','',72,73),(251,137,NULL,NULL,'noteAdd','',236,237),(252,137,NULL,NULL,'noteDel','',238,239),(253,137,NULL,NULL,'view_tracking','',240,241),(254,137,NULL,NULL,'edit_tracking','',242,243),(258,31,NULL,NULL,'ProfileComponents','',303,318),(259,258,NULL,NULL,'index','',304,305),(260,258,NULL,NULL,'add','',306,307),(261,258,NULL,NULL,'edit','',308,309),(262,258,NULL,NULL,'delete','',310,311),(263,258,NULL,NULL,'note_index','',312,313),(264,258,NULL,NULL,'note_add','',314,315),(265,258,NULL,NULL,'note_del','',316,317),(267,31,NULL,NULL,'NaStates','',319,324),(268,267,NULL,NULL,'index','',320,321),(269,267,NULL,NULL,'delete','',322,323),(271,58,NULL,NULL,'view','',98,99),(272,58,NULL,NULL,'enableDisable','',100,101),(275,31,NULL,NULL,'DynamicDetails','',325,380),(276,275,NULL,NULL,'exportCsv','',326,327),(277,275,NULL,NULL,'index','',328,329),(278,275,NULL,NULL,'add','',330,331),(279,275,NULL,NULL,'edit','',332,333),(280,275,NULL,NULL,'delete','',334,335),(281,275,NULL,NULL,'view','',336,337),(282,275,NULL,NULL,'uploadLogo','',338,339),(283,275,NULL,NULL,'indexPhoto','',340,341),(284,275,NULL,NULL,'uploadPhoto','',342,343),(285,275,NULL,NULL,'deletePhoto','',344,345),(286,275,NULL,NULL,'editPhoto','',346,347),(287,275,NULL,NULL,'indexPage','',348,349),(288,275,NULL,NULL,'addPage','',350,351),(289,275,NULL,NULL,'editPage','',352,353),(290,275,NULL,NULL,'deletePage','',354,355),(291,275,NULL,NULL,'indexPair','',356,357),(292,275,NULL,NULL,'addPair','',358,359),(293,275,NULL,NULL,'editPair','',360,361),(294,275,NULL,NULL,'deletePair','',362,363),(295,275,NULL,NULL,'noteIndex','',364,365),(296,275,NULL,NULL,'noteAdd','',366,367),(297,275,NULL,NULL,'noteDel','',368,369),(299,45,NULL,NULL,'autoMacOnOff','',74,75),(300,32,NULL,NULL,'Password Manager Only','Enabling this option will allow the Access Provider ONLY access to the Password Manager applet',623,624),(301,45,NULL,NULL,'viewPassword','',76,77),(302,31,NULL,NULL,'Actions','',381,388),(303,302,NULL,NULL,'index','',382,383),(304,302,NULL,NULL,'add','',384,385),(305,302,NULL,NULL,'delete','',386,387),(309,275,NULL,NULL,'editSettings','',370,371),(310,275,NULL,NULL,'editClickToConnect','',372,373),(311,31,NULL,NULL,'Meshes','MESHdesk main controller',389,458),(312,311,NULL,NULL,'index','',390,391),(313,311,NULL,NULL,'add','',392,393),(314,311,NULL,NULL,'delete','',394,395),(315,311,NULL,NULL,'note_index','',396,397),(316,311,NULL,NULL,'note_add','',398,399),(317,311,NULL,NULL,'note_del','',400,401),(318,311,NULL,NULL,'mesh_entries_index','',402,403),(319,311,NULL,NULL,'mesh_entry_add','',404,405),(320,311,NULL,NULL,'mesh_entry_edit','',406,407),(321,311,NULL,NULL,'mesh_entry_view','',408,409),(322,311,NULL,NULL,'mesh_entry_delete','',410,411),(323,311,NULL,NULL,'mesh_settings_view','',412,413),(324,311,NULL,NULL,'mesh_settings_edit','',414,415),(325,311,NULL,NULL,'mesh_exits_index','',416,417),(326,311,NULL,NULL,'mesh_exit_add','',418,419),(327,311,NULL,NULL,'mesh_exit_edit','',420,421),(328,311,NULL,NULL,'mesh_exit_view','',422,423),(329,311,NULL,NULL,'mesh_exit_delete','',424,425),(330,311,NULL,NULL,'mesh_nodes_index','',426,427),(332,311,NULL,NULL,'mesh_node_add','',428,429),(333,311,NULL,NULL,'mesh_node_edit','',430,431),(334,311,NULL,NULL,'mesh_node_view','',432,433),(335,311,NULL,NULL,'mesh_node_delete','',434,435),(336,311,NULL,NULL,'mesh_entry_points','',436,437),(337,311,NULL,NULL,'node_common_settings_view','',438,439),(338,311,NULL,NULL,'node_common_settings_edit','',440,441),(339,311,NULL,NULL,'static_entry_options','',442,443),(340,311,NULL,NULL,'static_exit_options','',444,445),(341,311,NULL,NULL,'map_pref_view','',446,447),(342,311,NULL,NULL,'map_pref_edit','',448,449),(343,311,NULL,NULL,'map_node_save','',450,451),(344,311,NULL,NULL,'map_node_delete','',452,453),(345,311,NULL,NULL,'nodes_avail_for_map','',454,455),(346,31,NULL,NULL,'NodeActions','',459,466),(347,346,NULL,NULL,'index','',460,461),(348,346,NULL,NULL,'add','',462,463),(349,346,NULL,NULL,'delete','',464,465),(350,31,NULL,NULL,'Ssids','Optional option for Permanent Users to limit their connections',467,478),(351,350,NULL,NULL,'index','',468,469),(352,350,NULL,NULL,'indexAp','List might changed based on the Access Provider specified',470,471),(353,350,NULL,NULL,'add','',472,473),(354,350,NULL,NULL,'delete','',474,475),(355,350,NULL,NULL,'edit','',476,477),(356,31,NULL,NULL,'LicensedDevices','Add-on - non standard',479,488),(357,356,NULL,NULL,'index','',480,481),(358,356,NULL,NULL,'add','',482,483),(359,356,NULL,NULL,'delete','',484,485),(360,356,NULL,NULL,'edit','',486,487),(361,31,NULL,NULL,'NodeLists','Additional convenient add-on to MESHdesk',489,492),(362,361,NULL,NULL,'index','',490,491),(363,31,NULL,NULL,'DynamicClients','Part of FreeRADIUS version 3.x',493,522),(364,363,NULL,NULL,'index','',494,495),(365,363,NULL,NULL,'clients_avail_for_map','',496,497),(366,363,NULL,NULL,'add','',498,499),(367,363,NULL,NULL,'delete','',500,501),(368,363,NULL,NULL,'edit','',502,503),(369,363,NULL,NULL,'view','',504,505),(370,363,NULL,NULL,'view_photo','',506,507),(371,363,NULL,NULL,'note_index','',508,509),(372,363,NULL,NULL,'note_add','',510,511),(373,363,NULL,NULL,'note_del','',512,513),(374,363,NULL,NULL,'view_map_pref','',514,515),(375,363,NULL,NULL,'edit_map_pref','',516,517),(376,363,NULL,NULL,'delete_map','',518,519),(377,363,NULL,NULL,'edit_map','',520,521),(378,31,NULL,NULL,'DynamicClientStates','',523,528),(379,378,NULL,NULL,'index','',524,525),(380,378,NULL,NULL,'delete','',526,527),(381,31,NULL,NULL,'UnknownDynamicClients','',529,536),(382,381,NULL,NULL,'index','',530,531),(383,381,NULL,NULL,'edit','',532,533),(384,381,NULL,NULL,'delete','',534,535),(385,31,NULL,NULL,'ApProfiles','Access Point Profiles',537,590),(386,385,NULL,NULL,'index','',538,539),(387,385,NULL,NULL,'add','',540,541),(388,385,NULL,NULL,'delete','',542,543),(389,385,NULL,NULL,'note_index','',544,545),(390,385,NULL,NULL,'note_add','',546,547),(391,385,NULL,NULL,'note_del','',548,549),(392,385,NULL,NULL,'ap_profile_entries_index','',550,551),(393,385,NULL,NULL,'ap_profile_entry_add','',552,553),(394,385,NULL,NULL,'ap_profile_entry_edit','',554,555),(395,385,NULL,NULL,'ap_profile_entry_view','',556,557),(396,385,NULL,NULL,'ap_profile_entry_delete','',558,559),(397,385,NULL,NULL,'ap_profile_exits_index','',560,561),(398,385,NULL,NULL,'ap_profile_exit_add','',562,563),(399,385,NULL,NULL,'ap_profile_exit_edit','',564,565),(400,385,NULL,NULL,'ap_profile_exit_view','',566,567),(401,385,NULL,NULL,'ap_profile_exit_delete','',568,569),(402,385,NULL,NULL,'ap_profile_entry_points','List available Entry Points',570,571),(403,385,NULL,NULL,'ap_common_settings_view','',572,573),(404,385,NULL,NULL,'ap_common_settings_edit','',574,575),(405,385,NULL,NULL,'advanced_settings_for_model','',576,577),(406,385,NULL,NULL,'ap_profile_ap_index','',578,579),(407,385,NULL,NULL,'ap_profile_ap_add','',580,581),(408,385,NULL,NULL,'ap_profile_ap_delete','',582,583),(409,385,NULL,NULL,'ap_profile_ap_edit','',584,585),(410,385,NULL,NULL,'ap_profile_ap_view','',586,587),(411,31,NULL,NULL,'Aps','',591,594),(412,411,NULL,NULL,'index','',592,593),(413,385,NULL,NULL,'ap_profile_exit_add_defaults','',588,589),(414,311,NULL,NULL,'mesh_exit_add_defaults','',456,457),(435,275,NULL,NULL,'viewSocialLogin','',374,375),(436,275,NULL,NULL,'editSocialLogin','',376,377),(437,43,NULL,NULL,'emailVoucherDetails','',30,31),(438,31,NULL,NULL,'GlobalDomains','Add-on',595,598),(439,438,NULL,NULL,'index','',596,597),(440,275,NULL,NULL,'shufflePhoto','New addition allow rearranging ',378,379),(441,NULL,'Realms',36,NULL,NULL,677,678),(442,31,NULL,NULL,'TopUps',NULL,599,610),(443,442,NULL,NULL,'exportCsv',NULL,600,601),(444,442,NULL,NULL,'index',NULL,602,603),(445,442,NULL,NULL,'add',NULL,604,605),(446,442,NULL,NULL,'edit',NULL,606,607),(447,442,NULL,NULL,'delete',NULL,608,609),(448,31,NULL,NULL,'TopUpTransactions',NULL,611,614),(449,448,NULL,NULL,'index',NULL,612,613);
/*!40000 ALTER TABLE `acos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `actions`
--

DROP TABLE IF EXISTS `actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `na_id` int(10) NOT NULL,
  `action` enum('execute') DEFAULT 'execute',
  `command` varchar(500) DEFAULT '',
  `status` enum('awaiting','fetched','replied') DEFAULT 'awaiting',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actions`
--

LOCK TABLES `actions` WRITE;
/*!40000 ALTER TABLE `actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_actions`
--

DROP TABLE IF EXISTS `ap_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_actions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ap_id` int(10) NOT NULL,
  `action` enum('execute') DEFAULT 'execute',
  `command` varchar(500) DEFAULT '',
  `status` enum('awaiting','fetched','replied') DEFAULT 'awaiting',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_actions`
--

LOCK TABLES `ap_actions` WRITE;
/*!40000 ALTER TABLE `ap_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ap_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_loads`
--

DROP TABLE IF EXISTS `ap_loads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_loads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ap_id` int(11) DEFAULT NULL,
  `mem_total` int(11) DEFAULT NULL,
  `mem_free` int(11) DEFAULT NULL,
  `uptime` varchar(255) DEFAULT NULL,
  `system_time` varchar(255) NOT NULL,
  `load_1` float(2,2) NOT NULL,
  `load_2` float(2,2) NOT NULL,
  `load_3` float(2,2) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_loads`
--

LOCK TABLES `ap_loads` WRITE;
/*!40000 ALTER TABLE `ap_loads` DISABLE KEYS */;
/*!40000 ALTER TABLE `ap_loads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_profile_entries`
--

DROP TABLE IF EXISTS `ap_profile_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_profile_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ap_profile_id` int(11) DEFAULT NULL,
  `name` varchar(128) NOT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `isolate` tinyint(1) NOT NULL DEFAULT '0',
  `encryption` enum('none','wep','psk','psk2','wpa','wpa2') DEFAULT 'none',
  `special_key` varchar(100) NOT NULL DEFAULT '',
  `auth_server` varchar(255) NOT NULL DEFAULT '',
  `auth_secret` varchar(255) NOT NULL DEFAULT '',
  `dynamic_vlan` tinyint(1) NOT NULL DEFAULT '0',
  `frequency_band` enum('both','two','five') DEFAULT 'both',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `chk_maxassoc` tinyint(1) NOT NULL DEFAULT '0',
  `maxassoc` int(6) DEFAULT '100',
  `macfilter` enum('disable','allow','deny') DEFAULT 'disable',
  `permanent_user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_profile_entries`
--

LOCK TABLES `ap_profile_entries` WRITE;
/*!40000 ALTER TABLE `ap_profile_entries` DISABLE KEYS */;
INSERT INTO `ap_profile_entries` VALUES (17,14,'Hotel California',0,0,'none','','','',0,'both','2016-04-30 11:01:14','2016-04-30 11:35:11',0,100,'disable',0),(18,14,'Test',0,0,'none','','','',0,'both','2016-05-10 05:16:57','2016-05-10 05:16:57',0,100,'disable',0);
/*!40000 ALTER TABLE `ap_profile_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_profile_exit_ap_profile_entries`
--

DROP TABLE IF EXISTS `ap_profile_exit_ap_profile_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_profile_exit_ap_profile_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ap_profile_exit_id` int(11) NOT NULL,
  `ap_profile_entry_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_profile_exit_ap_profile_entries`
--

LOCK TABLES `ap_profile_exit_ap_profile_entries` WRITE;
/*!40000 ALTER TABLE `ap_profile_exit_ap_profile_entries` DISABLE KEYS */;
INSERT INTO `ap_profile_exit_ap_profile_entries` VALUES (76,40,17,'2016-09-18 05:00:15','2016-09-18 05:00:15'),(79,23,18,'2017-02-24 21:13:54','2017-02-24 21:13:54');
/*!40000 ALTER TABLE `ap_profile_exit_ap_profile_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_profile_exit_captive_portals`
--

DROP TABLE IF EXISTS `ap_profile_exit_captive_portals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_profile_exit_captive_portals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ap_profile_exit_id` int(11) NOT NULL,
  `radius_1` varchar(128) NOT NULL,
  `radius_2` varchar(128) NOT NULL DEFAULT '',
  `radius_secret` varchar(128) NOT NULL,
  `radius_nasid` varchar(128) NOT NULL,
  `uam_url` varchar(255) NOT NULL,
  `uam_secret` varchar(255) NOT NULL,
  `walled_garden` varchar(255) NOT NULL,
  `swap_octets` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `mac_auth` tinyint(1) NOT NULL DEFAULT '0',
  `proxy_enable` tinyint(1) NOT NULL DEFAULT '0',
  `proxy_ip` varchar(128) NOT NULL DEFAULT '',
  `proxy_port` int(11) NOT NULL DEFAULT '3128',
  `proxy_auth_username` varchar(128) NOT NULL DEFAULT '',
  `proxy_auth_password` varchar(128) NOT NULL DEFAULT '',
  `coova_optional` varchar(255) NOT NULL DEFAULT '',
  `dns_manual` tinyint(1) NOT NULL DEFAULT '0',
  `dns1` varchar(128) NOT NULL DEFAULT '',
  `dns2` varchar(128) NOT NULL DEFAULT '',
  `uamanydns` tinyint(1) NOT NULL DEFAULT '0',
  `dnsparanoia` tinyint(1) NOT NULL DEFAULT '0',
  `dnsdesk` tinyint(1) NOT NULL DEFAULT '0',
  `ap_profile_exit_upstream_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_profile_exit_captive_portals`
--

LOCK TABLES `ap_profile_exit_captive_portals` WRITE;
/*!40000 ALTER TABLE `ap_profile_exit_captive_portals` DISABLE KEYS */;
INSERT INTO `ap_profile_exit_captive_portals` VALUES (5,23,'198.27.111.78','','testing123','','http://198.27.111.78/cake3/rd_cake/dynamic-details/chilli-browser-detect/','greatsecret','',0,'2016-05-10 05:23:30','2017-02-24 21:13:54',1,0,'',3128,'','','ssid=radiusdesk',0,'4.4.4.4','8.8.8.8',0,0,0,NULL);
/*!40000 ALTER TABLE `ap_profile_exit_captive_portals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_profile_exits`
--

DROP TABLE IF EXISTS `ap_profile_exits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_profile_exits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ap_profile_id` int(11) DEFAULT NULL,
  `type` enum('bridge','tagged_bridge','nat','captive_portal','openvpn_bridge','tagged_bridge_l3') DEFAULT NULL,
  `vlan` int(4) DEFAULT NULL,
  `auto_dynamic_client` tinyint(1) NOT NULL DEFAULT '0',
  `realm_list` varchar(128) NOT NULL DEFAULT '',
  `auto_login_page` tinyint(1) NOT NULL DEFAULT '0',
  `dynamic_detail_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `openvpn_server_id` int(11) DEFAULT NULL,
  `proto` enum('static','dhcp','dhcpv6') DEFAULT 'dhcp',
  `ipaddr` varchar(50) NOT NULL DEFAULT '',
  `netmask` varchar(50) NOT NULL DEFAULT '',
  `gateway` varchar(50) NOT NULL DEFAULT '',
  `dns_1` varchar(50) NOT NULL DEFAULT '',
  `dns_2` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_profile_exits`
--

LOCK TABLES `ap_profile_exits` WRITE;
/*!40000 ALTER TABLE `ap_profile_exits` DISABLE KEYS */;
INSERT INTO `ap_profile_exits` VALUES (23,14,'captive_portal',NULL,1,'35',1,3,'2016-05-10 05:23:30','2017-02-24 21:13:54',NULL,'dhcp','','','','',''),(40,14,'openvpn_bridge',NULL,0,'',0,NULL,'2016-09-18 05:00:15','2016-09-18 05:00:15',2,'dhcp','','','','','');
/*!40000 ALTER TABLE `ap_profile_exits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_profile_notes`
--

DROP TABLE IF EXISTS `ap_profile_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_profile_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ap_profile_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_profile_notes`
--

LOCK TABLES `ap_profile_notes` WRITE;
/*!40000 ALTER TABLE `ap_profile_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `ap_profile_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_profile_settings`
--

DROP TABLE IF EXISTS `ap_profile_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_profile_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ap_profile_id` int(11) DEFAULT NULL,
  `password` varchar(128) NOT NULL,
  `heartbeat_interval` int(5) NOT NULL DEFAULT '60',
  `heartbeat_dead_after` int(5) NOT NULL DEFAULT '600',
  `password_hash` varchar(100) NOT NULL DEFAULT '',
  `tz_name` varchar(128) NOT NULL DEFAULT 'America/New York',
  `tz_value` varchar(128) NOT NULL DEFAULT 'EST5EDT,M3.2.0,M11.1.0',
  `country` varchar(5) NOT NULL DEFAULT 'US',
  `gw_dhcp_timeout` int(5) NOT NULL DEFAULT '120',
  `gw_use_previous` tinyint(1) NOT NULL DEFAULT '1',
  `gw_auto_reboot` tinyint(1) NOT NULL DEFAULT '1',
  `gw_auto_reboot_time` int(5) NOT NULL DEFAULT '600',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_profile_settings`
--

LOCK TABLES `ap_profile_settings` WRITE;
/*!40000 ALTER TABLE `ap_profile_settings` DISABLE KEYS */;
INSERT INTO `ap_profile_settings` VALUES (5,14,'admin',60,300,'','Africa/Johannesburg','SAST-2','ZA',120,1,0,600,'2016-04-30 11:02:17','2016-04-30 11:02:17');
/*!40000 ALTER TABLE `ap_profile_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_profile_specifics`
--

DROP TABLE IF EXISTS `ap_profile_specifics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_profile_specifics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ap_profile_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_profile_specifics`
--

LOCK TABLES `ap_profile_specifics` WRITE;
/*!40000 ALTER TABLE `ap_profile_specifics` DISABLE KEYS */;
/*!40000 ALTER TABLE `ap_profile_specifics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_profiles`
--

DROP TABLE IF EXISTS `ap_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_profiles`
--

LOCK TABLES `ap_profiles` WRITE;
/*!40000 ALTER TABLE `ap_profiles` DISABLE KEYS */;
INSERT INTO `ap_profiles` VALUES (14,'Hotel California',44,'2016-04-30 11:00:51','2016-04-30 11:00:51',1);
/*!40000 ALTER TABLE `ap_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_stations`
--

DROP TABLE IF EXISTS `ap_stations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_stations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ap_id` int(11) DEFAULT NULL,
  `ap_profile_entry_id` int(11) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `mac` varchar(17) NOT NULL,
  `tx_bytes` bigint(20) NOT NULL,
  `rx_bytes` bigint(20) NOT NULL,
  `tx_packets` int(11) NOT NULL,
  `rx_packets` int(11) NOT NULL,
  `tx_bitrate` int(11) NOT NULL,
  `rx_bitrate` int(11) NOT NULL,
  `tx_extra_info` varchar(255) NOT NULL,
  `rx_extra_info` varchar(255) NOT NULL,
  `authenticated` enum('yes','no') DEFAULT 'no',
  `authorized` enum('yes','no') DEFAULT 'no',
  `tdls_peer` varchar(255) NOT NULL,
  `preamble` enum('long','short') DEFAULT 'long',
  `tx_failed` int(11) NOT NULL,
  `inactive_time` int(11) NOT NULL,
  `WMM_WME` enum('yes','no') DEFAULT 'no',
  `tx_retries` int(11) NOT NULL,
  `MFP` enum('yes','no') DEFAULT 'no',
  `signal` int(11) NOT NULL,
  `signal_avg` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_stations`
--

LOCK TABLES `ap_stations` WRITE;
/*!40000 ALTER TABLE `ap_stations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ap_stations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_systems`
--

DROP TABLE IF EXISTS `ap_systems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_systems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ap_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_systems`
--

LOCK TABLES `ap_systems` WRITE;
/*!40000 ALTER TABLE `ap_systems` DISABLE KEYS */;
/*!40000 ALTER TABLE `ap_systems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ap_wifi_settings`
--

DROP TABLE IF EXISTS `ap_wifi_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ap_wifi_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ap_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ap_wifi_settings`
--

LOCK TABLES `ap_wifi_settings` WRITE;
/*!40000 ALTER TABLE `ap_wifi_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ap_wifi_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aps`
--

DROP TABLE IF EXISTS `aps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ap_profile_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `mac` varchar(255) NOT NULL,
  `hardware` varchar(255) DEFAULT NULL,
  `last_contact_from_ip` varchar(255) DEFAULT NULL,
  `last_contact` datetime DEFAULT NULL,
  `on_public_maps` tinyint(1) NOT NULL DEFAULT '0',
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `photo_file_name` varchar(128) NOT NULL DEFAULT 'logo.jpg',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aps`
--

LOCK TABLES `aps` WRITE;
/*!40000 ALTER TABLE `aps` DISABLE KEYS */;
/*!40000 ALTER TABLE `aps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aros`
--

DROP TABLE IF EXISTS `aros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aros` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3269 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aros`
--

LOCK TABLES `aros` WRITE;
/*!40000 ALTER TABLE `aros` DISABLE KEYS */;
INSERT INTO `aros` VALUES (3115,NULL,'Groups',8,NULL,1,4),(3116,NULL,'Groups',9,NULL,5,34),(3117,NULL,'Groups',10,NULL,35,210),(3118,3115,'Users',44,NULL,2,3),(3268,3116,'Users',182,NULL,14,15);
/*!40000 ALTER TABLE `aros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aros_acos`
--

DROP TABLE IF EXISTS `aros_acos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aros_acos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aro_id` int(10) NOT NULL,
  `aco_id` int(10) NOT NULL,
  `_create` varchar(2) NOT NULL DEFAULT '0',
  `_read` varchar(2) NOT NULL DEFAULT '0',
  `_update` varchar(2) NOT NULL DEFAULT '0',
  `_delete` varchar(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ARO_ACO_KEY` (`aro_id`,`aco_id`)
) ENGINE=InnoDB AUTO_INCREMENT=410 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aros_acos`
--

LOCK TABLES `aros_acos` WRITE;
/*!40000 ALTER TABLE `aros_acos` DISABLE KEYS */;
INSERT INTO `aros_acos` VALUES (16,3116,44,'1','1','1','1'),(17,3116,46,'1','1','1','1'),(18,3116,59,'1','1','1','1'),(19,3116,60,'1','1','1','1'),(20,3116,62,'1','1','1','1'),(21,3116,42,'-1','-1','-1','-1'),(22,3116,61,'1','1','1','1'),(23,3116,63,'-1','-1','-1','-1'),(24,3116,64,'1','1','1','1'),(25,3116,65,'1','1','1','1'),(61,3116,68,'1','1','1','1'),(62,3116,69,'1','1','1','1'),(63,3116,70,'1','1','1','1'),(64,3116,71,'1','1','1','1'),(75,3116,103,'1','1','1','1'),(76,3116,104,'1','1','1','1'),(77,3116,105,'1','1','1','1'),(78,3116,106,'1','1','1','1'),(79,3116,108,'1','1','1','1'),(80,3116,109,'1','1','1','1'),(81,3116,110,'1','1','1','1'),(82,3116,111,'1','1','1','1'),(83,3116,112,'1','1','1','1'),(86,3116,117,'1','1','1','1'),(87,3116,116,'1','1','1','1'),(88,3116,115,'1','1','1','1'),(89,3116,114,'1','1','1','1'),(90,3116,113,'1','1','1','1'),(91,3116,118,'1','1','1','1'),(92,3116,119,'1','1','1','1'),(93,3116,120,'1','1','1','1'),(94,3116,121,'1','1','1','1'),(95,3116,122,'1','1','1','1'),(96,3116,123,'1','1','1','1'),(97,3116,124,'1','1','1','1'),(98,3116,125,'1','1','1','1'),(99,3116,126,'1','1','1','1'),(100,3116,127,'1','1','1','1'),(101,3116,128,'1','1','1','1'),(102,3116,129,'1','1','1','1'),(103,3116,130,'1','1','1','1'),(108,3116,133,'1','1','1','1'),(109,3116,134,'1','1','1','1'),(112,3116,138,'1','1','1','1'),(113,3116,149,'1','1','1','1'),(114,3116,150,'1','1','1','1'),(115,3116,152,'1','1','1','1'),(116,3255,46,'1','1','1','1'),(117,3255,138,'1','1','1','1'),(118,3255,44,'1','1','1','1'),(119,3254,46,'1','1','1','1'),(120,3116,153,'1','1','1','1'),(121,3116,154,'1','1','1','1'),(122,3254,155,'1','1','1','1'),(123,3116,163,'1','1','1','1'),(124,3116,162,'1','1','1','1'),(125,3116,161,'1','1','1','1'),(126,3116,160,'1','1','1','1'),(127,3116,159,'1','1','1','1'),(128,3116,158,'1','1','1','1'),(129,3116,157,'1','1','1','1'),(130,3116,156,'1','1','1','1'),(131,3116,164,'1','1','1','1'),(132,3116,165,'1','1','1','1'),(133,3255,32,'1','1','-1','-1'),(134,3255,148,'-1','-1','-1','-1'),(135,3255,146,'-1','-1','-1','-1'),(136,3254,148,'1','1','1','1'),(137,3254,146,'1','1','1','1'),(138,3116,167,'1','1','1','1'),(139,3116,168,'1','1','1','1'),(140,3116,175,'1','1','1','1'),(141,3116,174,'1','1','1','1'),(142,3116,173,'1','1','1','1'),(144,3116,170,'1','1','1','1'),(145,3116,169,'1','1','1','1'),(146,3116,171,'1','1','1','1'),(147,3116,181,'1','1','1','1'),(148,3116,180,'1','1','1','1'),(149,3116,179,'1','1','1','1'),(150,3116,178,'1','1','1','1'),(151,3116,177,'1','1','1','1'),(152,3116,182,'1','1','1','1'),(153,3116,184,'1','1','1','1'),(154,3116,185,'1','1','1','1'),(155,3116,186,'1','1','1','1'),(156,3116,187,'1','1','1','1'),(157,3116,188,'1','1','1','1'),(158,3116,189,'1','1','1','1'),(159,3116,190,'1','1','1','1'),(160,3116,191,'1','1','1','1'),(161,3116,192,'1','1','1','1'),(162,3116,193,'1','1','1','1'),(163,3116,194,'1','1','1','1'),(164,3116,195,'1','1','1','1'),(165,3116,197,'1','1','1','1'),(166,3116,196,'1','1','1','1'),(167,3116,206,'1','1','1','1'),(168,3116,205,'1','1','1','1'),(169,3116,204,'1','1','1','1'),(170,3116,203,'1','1','1','1'),(171,3116,202,'1','1','1','1'),(172,3116,201,'1','1','1','1'),(173,3116,200,'1','1','1','1'),(174,3116,199,'1','1','1','1'),(175,3116,198,'1','1','1','1'),(176,3116,207,'1','1','1','1'),(177,3116,208,'1','1','1','1'),(178,3255,155,'1','1','1','1'),(179,3254,195,'1','1','1','1'),(180,3116,210,'1','1','1','1'),(181,3116,211,'1','1','1','1'),(183,3116,213,'1','1','1','1'),(184,3116,221,'1','1','1','1'),(185,3116,223,'1','1','1','1'),(186,3116,241,'1','1','1','1'),(187,3116,240,'1','1','1','1'),(188,3116,239,'1','1','1','1'),(189,3116,238,'1','1','1','1'),(190,3116,237,'1','1','1','1'),(191,3116,236,'1','1','1','1'),(192,3116,235,'1','1','1','1'),(193,3116,234,'1','1','1','1'),(194,3116,233,'1','1','1','1'),(195,3116,232,'1','1','1','1'),(196,3116,231,'1','1','1','1'),(197,3116,230,'1','1','1','1'),(198,3116,229,'1','1','1','1'),(199,3116,228,'1','1','1','1'),(200,3116,227,'1','1','1','1'),(201,3116,226,'1','1','1','1'),(202,3116,225,'1','1','1','1'),(203,3116,224,'1','1','1','1'),(204,3116,243,'1','1','1','1'),(205,3268,245,'-1','-1','-1','-1'),(206,3116,248,'1','1','1','1'),(207,3116,247,'1','1','1','1'),(208,3116,246,'1','1','1','1'),(209,3116,215,'1','1','1','1'),(210,3116,214,'1','1','1','1'),(211,3116,249,'1','1','1','1'),(212,3116,250,'1','1','1','1'),(215,3116,254,'1','1','1','1'),(216,3116,253,'1','1','1','1'),(217,3116,259,'1','1','1','1'),(218,3116,260,'1','1','1','1'),(219,3116,261,'1','1','1','1'),(220,3116,263,'1','1','1','1'),(221,3116,262,'1','1','1','1'),(222,3116,264,'1','1','1','1'),(223,3116,265,'1','1','1','1'),(224,3116,268,'1','1','1','1'),(225,3116,269,'1','1','1','1'),(226,3116,272,'1','1','1','1'),(227,3116,271,'1','1','1','1'),(229,3116,276,'1','1','1','1'),(230,3116,297,'1','1','1','1'),(231,3116,296,'1','1','1','1'),(232,3116,295,'1','1','1','1'),(233,3116,294,'1','1','1','1'),(234,3116,293,'1','1','1','1'),(235,3116,292,'1','1','1','1'),(236,3116,291,'1','1','1','1'),(237,3116,290,'1','1','1','1'),(238,3116,289,'1','1','1','1'),(239,3116,288,'1','1','1','1'),(240,3116,287,'1','1','1','1'),(241,3116,286,'1','1','1','1'),(242,3116,285,'1','1','1','1'),(243,3116,284,'1','1','1','1'),(244,3116,283,'1','1','1','1'),(245,3116,282,'1','1','1','1'),(246,3116,281,'1','1','1','1'),(247,3116,280,'1','1','1','1'),(248,3116,279,'1','1','1','1'),(249,3116,278,'1','1','1','1'),(250,3116,277,'1','1','1','1'),(251,3116,299,'1','1','1','1'),(252,3116,300,'-1','-1','-1','-1'),(253,3268,300,'-1','-1','-1','-1'),(254,3268,42,'1','1','1','1'),(255,3116,301,'1','1','1','1'),(256,3116,303,'1','1','1','1'),(257,3116,304,'1','1','1','1'),(258,3116,305,'1','1','1','1'),(259,3116,309,'1','1','1','1'),(260,3116,310,'1','1','1','1'),(261,3116,312,'1','1','1','1'),(262,3116,313,'1','1','1','1'),(263,3116,314,'1','1','1','1'),(264,3116,315,'1','1','1','1'),(265,3116,316,'1','1','1','1'),(266,3116,317,'1','1','1','1'),(267,3116,318,'1','1','1','1'),(268,3116,319,'1','1','1','1'),(269,3116,320,'1','1','1','1'),(270,3116,321,'1','1','1','1'),(271,3116,322,'1','1','1','1'),(272,3116,323,'1','1','1','1'),(273,3116,324,'1','1','1','1'),(274,3116,325,'1','1','1','1'),(275,3116,326,'1','1','1','1'),(276,3116,327,'1','1','1','1'),(277,3116,328,'1','1','1','1'),(278,3116,329,'1','1','1','1'),(279,3116,330,'1','1','1','1'),(280,3116,332,'1','1','1','1'),(281,3116,333,'1','1','1','1'),(282,3116,334,'1','1','1','1'),(283,3116,335,'1','1','1','1'),(284,3116,336,'1','1','1','1'),(285,3116,337,'1','1','1','1'),(286,3116,338,'1','1','1','1'),(287,3116,339,'1','1','1','1'),(288,3116,340,'1','1','1','1'),(289,3116,341,'1','1','1','1'),(290,3116,342,'1','1','1','1'),(291,3116,343,'1','1','1','1'),(292,3116,344,'1','1','1','1'),(293,3116,345,'1','1','1','1'),(294,3116,347,'1','1','1','1'),(295,3116,348,'1','1','1','1'),(296,3116,349,'1','1','1','1'),(297,3116,355,'1','1','1','1'),(298,3116,354,'1','1','1','1'),(299,3116,353,'1','1','1','1'),(300,3116,352,'1','1','1','1'),(301,3116,351,'1','1','1','1'),(302,3116,357,'1','1','1','1'),(303,3116,358,'1','1','1','1'),(304,3116,359,'1','1','1','1'),(305,3116,362,'1','1','1','1'),(306,3116,360,'1','1','1','1'),(308,3268,44,'1','1','1','1'),(309,3268,43,'1','1','1','1'),(310,3268,149,'1','1','1','1'),(311,3268,150,'1','1','1','1'),(312,3268,156,'1','1','1','1'),(313,3268,63,'-1','-1','-1','-1'),(314,3268,64,'1','1','1','1'),(315,3276,44,'1','1','1','1'),(316,3268,46,'1','1','1','1'),(317,3116,384,'1','1','1','1'),(318,3116,383,'1','1','1','1'),(319,3116,382,'1','1','1','1'),(320,3116,379,'1','1','1','1'),(321,3116,380,'1','1','1','1'),(322,3116,364,'1','1','1','1'),(323,3116,365,'1','1','1','1'),(324,3116,366,'1','1','1','1'),(325,3116,367,'1','1','1','1'),(326,3116,368,'1','1','1','1'),(327,3116,369,'1','1','1','1'),(328,3116,370,'1','1','1','1'),(329,3116,371,'1','1','1','1'),(330,3116,372,'1','1','1','1'),(331,3116,373,'1','1','1','1'),(332,3116,374,'1','1','1','1'),(333,3116,375,'1','1','1','1'),(334,3116,376,'1','1','1','1'),(335,3116,377,'1','1','1','1'),(336,3116,386,'1','1','1','1'),(337,3116,387,'1','1','1','1'),(338,3116,388,'1','1','1','1'),(339,3116,389,'1','1','1','1'),(340,3116,390,'1','1','1','1'),(341,3116,391,'1','1','1','1'),(342,3116,392,'1','1','1','1'),(343,3116,393,'1','1','1','1'),(344,3116,394,'1','1','1','1'),(345,3116,395,'1','1','1','1'),(346,3116,396,'1','1','1','1'),(347,3116,397,'1','1','1','1'),(348,3116,410,'1','1','1','1'),(349,3116,409,'1','1','1','1'),(350,3116,408,'1','1','1','1'),(351,3116,407,'1','1','1','1'),(352,3116,406,'1','1','1','1'),(353,3116,405,'1','1','1','1'),(354,3116,404,'1','1','1','1'),(355,3116,403,'1','1','1','1'),(356,3116,402,'1','1','1','1'),(357,3116,401,'1','1','1','1'),(358,3116,400,'1','1','1','1'),(359,3116,399,'1','1','1','1'),(360,3116,398,'1','1','1','1'),(361,3116,412,'1','1','1','1'),(362,3116,414,'1','1','1','1'),(363,3116,413,'1','1','1','1'),(364,3284,42,'1','1','1','1'),(365,3284,415,'1','1','1','1'),(366,3285,42,'1','1','1','1'),(367,3285,417,'1','1','1','1'),(378,3280,42,'1','1','1','1'),(379,3280,167,'-1','-1','-1','-1'),(380,3280,259,'-1','-1','-1','-1'),(381,3280,425,'1','1','1','1'),(386,3292,42,'1','1','1','1'),(387,3292,167,'-1','-1','-1','-1'),(388,3292,259,'-1','-1','-1','-1'),(389,3292,429,'1','1','1','1'),(390,3283,42,'1','1','1','1'),(391,3283,167,'-1','-1','-1','-1'),(392,3283,259,'-1','-1','-1','-1'),(393,3283,431,'1','1','1','1'),(398,3116,436,'1','1','1','1'),(399,3116,435,'1','1','1','1'),(400,3116,437,'1','1','1','1'),(401,3116,439,'1','1','1','1'),(402,3116,440,'1','1','1','1'),(403,3268,441,'1','1','1','1'),(404,3116,443,'1','1','1','1'),(405,3116,444,'1','1','1','1'),(406,3116,445,'1','1','1','1'),(407,3116,446,'1','1','1','1'),(408,3116,447,'1','1','1','1'),(409,3116,449,'1','1','1','1');
/*!40000 ALTER TABLE `aros_acos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checks`
--

DROP TABLE IF EXISTS `checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `checks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `value` varchar(40) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checks`
--

LOCK TABLES `checks` WRITE;
/*!40000 ALTER TABLE `checks` DISABLE KEYS */;
INSERT INTO `checks` VALUES (2,'radius_restart','1','2013-09-01 20:41:20','2016-03-09 10:00:06');
/*!40000 ALTER TABLE `checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `iso_code` varchar(2) DEFAULT NULL,
  `icon_file` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES (4,'United Kingdom','GB','/cake2/rd_cake/webroot/img/flags/GB.png','2012-10-05 04:55:12','2012-11-23 21:15:38'),(5,'South Africa','ZA','/cake2/rd_cake/webroot/img/flags/ZA.png','2012-10-07 04:30:48','2012-10-07 04:30:48'),(18,'Iran','IR','/cake2/rd_cake/webroot/img/flags/IR.png','2013-01-01 15:27:17','2013-01-01 15:27:17'),(19,'Portugal','PT','/cake2/rd_cake/webroot/img/flags/PT.png','2014-02-11 14:33:37','2014-02-11 14:33:37'),(20,'Spain','ES','/cake2/rd_cake/webroot/img/flags/ES.png','2014-02-20 22:23:55','2014-02-20 22:23:55'),(21,'Nicaragua','NI','/cake2/rd_cake/webroot/img/flags/NI.png','2014-02-21 15:20:32','2014-02-21 15:20:32'),(22,'Russia','RU','/cake2/rd_cake/webroot/img/flags/RU.png','2014-02-24 09:20:42','2014-02-24 09:20:42');
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_collectors`
--

DROP TABLE IF EXISTS `data_collectors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_collectors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dynamic_detail_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `mac` varchar(36) NOT NULL,
  `cp_mac` varchar(36) DEFAULT NULL,
  `public_ip` varchar(36) DEFAULT NULL,
  `nasid` varchar(255) DEFAULT NULL,
  `ssid` varchar(255) DEFAULT NULL,
  `is_mobile` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_collectors`
--

LOCK TABLES `data_collectors` WRITE;
/*!40000 ALTER TABLE `data_collectors` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_collectors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_notes`
--

DROP TABLE IF EXISTS `device_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_notes`
--

LOCK TABLES `device_notes` WRITE;
/*!40000 ALTER TABLE `device_notes` DISABLE KEYS */;
INSERT INTO `device_notes` VALUES (2,1,90,'2017-05-06 09:39:49','2017-05-06 09:39:49');
/*!40000 ALTER TABLE `device_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `description` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `last_accept_time` datetime DEFAULT NULL,
  `last_reject_time` datetime DEFAULT NULL,
  `last_accept_nas` varchar(128) DEFAULT NULL,
  `last_reject_nas` varchar(128) DEFAULT NULL,
  `last_reject_message` varchar(255) DEFAULT NULL,
  `permanent_user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `perc_time_used` int(6) DEFAULT NULL,
  `perc_data_used` int(6) DEFAULT NULL,
  `data_used` bigint(20) DEFAULT NULL,
  `data_cap` bigint(20) DEFAULT NULL,
  `time_used` int(12) DEFAULT NULL,
  `time_cap` int(12) DEFAULT NULL,
  `time_cap_type` enum('hard','soft') DEFAULT 'soft',
  `data_cap_type` enum('hard','soft') DEFAULT 'soft',
  `realm` varchar(100) NOT NULL DEFAULT '',
  `realm_id` int(11) DEFAULT NULL,
  `profile` varchar(100) NOT NULL DEFAULT '',
  `profile_id` int(11) DEFAULT NULL,
  `from_date` datetime DEFAULT NULL,
  `to_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devices`
--

LOCK TABLES `devices` WRITE;
/*!40000 ALTER TABLE `devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynamic_client_notes`
--

DROP TABLE IF EXISTS `dynamic_client_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamic_client_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dynamic_client_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynamic_client_notes`
--

LOCK TABLES `dynamic_client_notes` WRITE;
/*!40000 ALTER TABLE `dynamic_client_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `dynamic_client_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynamic_client_realms`
--

DROP TABLE IF EXISTS `dynamic_client_realms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamic_client_realms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dynamic_client_id` int(11) NOT NULL,
  `realm_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynamic_client_realms`
--

LOCK TABLES `dynamic_client_realms` WRITE;
/*!40000 ALTER TABLE `dynamic_client_realms` DISABLE KEYS */;
/*!40000 ALTER TABLE `dynamic_client_realms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynamic_client_states`
--

DROP TABLE IF EXISTS `dynamic_client_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamic_client_states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dynamic_client_id` char(36) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynamic_client_states`
--

LOCK TABLES `dynamic_client_states` WRITE;
/*!40000 ALTER TABLE `dynamic_client_states` DISABLE KEYS */;
/*!40000 ALTER TABLE `dynamic_client_states` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynamic_clients`
--

DROP TABLE IF EXISTS `dynamic_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamic_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `nasidentifier` varchar(128) NOT NULL DEFAULT '',
  `calledstationid` varchar(128) NOT NULL DEFAULT '',
  `last_contact` datetime DEFAULT NULL,
  `last_contact_ip` varchar(128) NOT NULL DEFAULT '',
  `timezone` varchar(255) NOT NULL DEFAULT '',
  `monitor` enum('off','heartbeat','socket') DEFAULT 'off',
  `session_auto_close` tinyint(1) NOT NULL DEFAULT '0',
  `session_dead_time` int(5) NOT NULL DEFAULT '3600',
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `on_public_maps` tinyint(1) NOT NULL DEFAULT '0',
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `photo_file_name` varchar(128) NOT NULL DEFAULT 'logo.jpg',
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `data_limit_active` tinyint(1) NOT NULL DEFAULT '0',
  `data_limit_amount` int(11) NOT NULL DEFAULT '1',
  `data_limit_unit` enum('kb','mb','gb','tb') DEFAULT 'mb',
  `data_limit_reset_on` int(3) NOT NULL DEFAULT '1',
  `data_limit_reset_hour` int(3) NOT NULL DEFAULT '0',
  `data_limit_reset_minute` int(3) NOT NULL DEFAULT '0',
  `data_used` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynamic_clients`
--

LOCK TABLES `dynamic_clients` WRITE;
/*!40000 ALTER TABLE `dynamic_clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `dynamic_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynamic_detail_notes`
--

DROP TABLE IF EXISTS `dynamic_detail_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamic_detail_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dynamic_detail_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynamic_detail_notes`
--

LOCK TABLES `dynamic_detail_notes` WRITE;
/*!40000 ALTER TABLE `dynamic_detail_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `dynamic_detail_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynamic_detail_social_logins`
--

DROP TABLE IF EXISTS `dynamic_detail_social_logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamic_detail_social_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dynamic_detail_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `realm_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `enable` tinyint(1) NOT NULL DEFAULT '0',
  `record_info` tinyint(1) NOT NULL DEFAULT '0',
  `special_key` varchar(100) NOT NULL DEFAULT '',
  `secret` varchar(100) NOT NULL DEFAULT '',
  `type` enum('voucher','user') DEFAULT 'voucher',
  `extra_name` varchar(100) NOT NULL DEFAULT '',
  `extra_value` varchar(100) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynamic_detail_social_logins`
--

LOCK TABLES `dynamic_detail_social_logins` WRITE;
/*!40000 ALTER TABLE `dynamic_detail_social_logins` DISABLE KEYS */;
/*!40000 ALTER TABLE `dynamic_detail_social_logins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynamic_details`
--

DROP TABLE IF EXISTS `dynamic_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamic_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
  `icon_file_name` varchar(128) NOT NULL DEFAULT 'logo.jpg',
  `phone` varchar(14) NOT NULL DEFAULT '',
  `fax` varchar(14) NOT NULL DEFAULT '',
  `cell` varchar(14) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `url` varchar(128) NOT NULL DEFAULT '',
  `street_no` char(10) NOT NULL DEFAULT '',
  `street` char(50) NOT NULL DEFAULT '',
  `town_suburb` char(50) NOT NULL DEFAULT '',
  `city` char(50) NOT NULL DEFAULT '',
  `country` char(50) NOT NULL DEFAULT '',
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `t_c_check` tinyint(1) NOT NULL DEFAULT '0',
  `t_c_url` char(50) NOT NULL DEFAULT '',
  `redirect_check` tinyint(1) NOT NULL DEFAULT '0',
  `redirect_url` char(200) NOT NULL DEFAULT '',
  `slideshow_check` tinyint(1) NOT NULL DEFAULT '0',
  `seconds_per_slide` int(3) NOT NULL DEFAULT '30',
  `connect_check` tinyint(1) NOT NULL DEFAULT '0',
  `connect_username` char(50) NOT NULL DEFAULT '',
  `connect_suffix` char(50) NOT NULL DEFAULT 'nasid',
  `connect_delay` int(3) NOT NULL DEFAULT '0',
  `connect_only` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `user_login_check` tinyint(1) NOT NULL DEFAULT '1',
  `voucher_login_check` tinyint(1) NOT NULL DEFAULT '0',
  `auto_suffix_check` tinyint(1) NOT NULL DEFAULT '0',
  `auto_suffix` char(200) NOT NULL DEFAULT '',
  `usage_show_check` tinyint(1) NOT NULL DEFAULT '1',
  `usage_refresh_interval` int(3) NOT NULL DEFAULT '120',
  `theme` char(200) NOT NULL DEFAULT 'Default',
  `register_users` tinyint(1) NOT NULL DEFAULT '0',
  `lost_password` tinyint(1) NOT NULL DEFAULT '0',
  `social_enable` tinyint(1) NOT NULL DEFAULT '0',
  `social_temp_permanent_user_id` int(11) DEFAULT NULL,
  `coova_desktop_url` varchar(255) NOT NULL DEFAULT '',
  `coova_mobile_url` varchar(255) NOT NULL DEFAULT '',
  `mikrotik_desktop_url` varchar(255) NOT NULL DEFAULT '',
  `mikrotik_mobile_url` varchar(255) NOT NULL DEFAULT '',
  `default_language` varchar(255) NOT NULL DEFAULT '',
  `realm_id` int(11) DEFAULT NULL,
  `profile_id` int(11) DEFAULT NULL,
  `reg_auto_suffix_check` tinyint(1) NOT NULL DEFAULT '0',
  `reg_auto_suffix` char(200) NOT NULL DEFAULT '',
  `reg_mac_check` tinyint(1) NOT NULL DEFAULT '0',
  `reg_auto_add` tinyint(1) NOT NULL DEFAULT '0',
  `reg_email` tinyint(1) NOT NULL DEFAULT '0',
  `slideshow_enforce_watching` tinyint(1) NOT NULL DEFAULT '1',
  `slideshow_enforce_seconds` int(4) NOT NULL DEFAULT '10',
  `available_languages` varchar(255) NOT NULL DEFAULT '',
  `ctc_require_email` tinyint(1) NOT NULL DEFAULT '0',
  `ctc_resupply_email_interval` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynamic_details`
--

LOCK TABLES `dynamic_details` WRITE;
/*!40000 ALTER TABLE `dynamic_details` DISABLE KEYS */;
INSERT INTO `dynamic_details` VALUES (3,'SA Coast - Struisbaai',1,'1484077003.png','27128037032','27128037033','27128037034','bredasdorp@discovercapeagulhas.co.za','http://www.discovercapeagulhas.co.za/','1','Longstreet','Bredasdorp','Bredasdorp','South Africa',0,0,44,1,'http://www.radiusdesk.com',0,'http://www.radiusdesk.com',1,30,1,'click_to_connect','ssid',0,0,'2013-05-23 09:57:09','2017-05-21 14:21:28',1,1,1,'mysite',1,120,'Default',0,1,0,187,'/rd_login/cc/d/index.html','/rd_login/cc/m/index.html','/rd_login/mt/d/index.html','/rd_login/mt/m/index.html','en_GB',NULL,NULL,0,'',0,0,0,1,30,'en_GB,fr_FR,en_GB',0,0);
/*!40000 ALTER TABLE `dynamic_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynamic_pages`
--

DROP TABLE IF EXISTS `dynamic_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamic_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dynamic_detail_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynamic_pages`
--

LOCK TABLES `dynamic_pages` WRITE;
/*!40000 ALTER TABLE `dynamic_pages` DISABLE KEYS */;
INSERT INTO `dynamic_pages` VALUES (7,3,'Welcome to Struisbaai','<font color=\"0000FF\"><font size=\"3\">You are in a High Speed Internet Zone!<br></font></font><ul><li>Thanks to the vibrant community, you can now enjoy being connected 24/7 @ speeds of up to 10Mb/s</li><li>Ideal for watching HD movies over the Internet</li><li>Budget connectivity is also available <br></li></ul><p><br></p>','2013-05-23 10:30:58','2013-05-28 21:45:59');
/*!40000 ALTER TABLE `dynamic_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynamic_pairs`
--

DROP TABLE IF EXISTS `dynamic_pairs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamic_pairs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `value` varchar(64) NOT NULL DEFAULT '',
  `priority` int(11) NOT NULL DEFAULT '1',
  `dynamic_detail_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynamic_pairs`
--

LOCK TABLES `dynamic_pairs` WRITE;
/*!40000 ALTER TABLE `dynamic_pairs` DISABLE KEYS */;
INSERT INTO `dynamic_pairs` VALUES (5,'ssid','Struisbaai',1,3,NULL,'2013-05-23 10:32:48','2013-05-28 22:02:38'),(6,'nasid','RADIUSdesk-1',1,3,NULL,'2013-08-21 19:49:38','2013-08-21 19:49:38'),(9,'nasid','lion_cp1',1,3,NULL,'2014-08-11 12:36:28','2014-08-11 12:36:28'),(10,'nasid','lion_cp2',1,3,NULL,'2014-08-11 12:36:40','2014-08-11 12:36:40'),(11,'nasid','lion_cp3',1,3,NULL,'2014-08-11 12:36:54','2014-08-11 12:36:54'),(12,'nasid','cheetah_cp1',1,3,NULL,'2014-08-11 12:37:15','2014-08-11 12:37:15');
/*!40000 ALTER TABLE `dynamic_pairs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynamic_photos`
--

DROP TABLE IF EXISTS `dynamic_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamic_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dynamic_detail_id` int(11) NOT NULL,
  `title` varchar(128) NOT NULL DEFAULT '',
  `description` varchar(250) NOT NULL DEFAULT '',
  `url` varchar(250) NOT NULL DEFAULT '',
  `file_name` varchar(128) NOT NULL DEFAULT 'logo.jpg',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `fit` enum('stretch_to_fit','horizontal','vertical','original','dynamic') DEFAULT 'stretch_to_fit',
  `background_color` varchar(7) NOT NULL DEFAULT 'ffffff',
  `slide_duration` int(4) NOT NULL DEFAULT '10',
  `include_title` tinyint(1) NOT NULL DEFAULT '1',
  `include_description` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynamic_photos`
--

LOCK TABLES `dynamic_photos` WRITE;
/*!40000 ALTER TABLE `dynamic_photos` DISABLE KEYS */;
INSERT INTO `dynamic_photos` VALUES (107,3,'Rocks rocks rocks','Nature\'s own obstacle course','','1369746199.jpg','2013-05-28 15:03:19','2017-02-15 10:08:58',1,'dynamic','24ee0f',10,1,1),(108,3,'Sounds of the sea','Where land and water meet','','1369746423.jpg','2013-05-28 15:07:03','2017-02-13 11:53:17',1,'vertical','dde5d9',3,1,1),(109,3,'Fresh fish daily','The best yellowtail in South Africa','','1369745821.jpg','2013-05-28 14:57:01','2017-02-15 08:09:49',1,'horizontal','ffffff',10,0,1),(110,3,'Animals Welcome','Nice long beaches to go for a walk','http://radiusdesk.com','1369745727.jpg','2013-05-28 14:55:27','2014-05-21 22:18:40',1,'stretch_to_fit','ffffff',10,1,1),(111,3,'Whiskey on the rocks?','.... or your favourite softdrink','','1369745902.jpg','2013-05-28 14:58:22','2017-02-11 23:35:12',1,'vertical','cd92d6',10,1,1),(112,3,'Castles in the sand','Lots of sand for the kids to play in','','1369746009.jpg','2013-05-28 15:00:09','2013-05-28 15:00:30',1,'stretch_to_fit','ffffff',10,1,1),(113,3,'And a road of my own','With the city and the rat race behind me','','1369746348.jpg','2013-05-28 15:05:48','2017-02-11 23:33:48',1,'vertical','ffffff',10,1,1);
/*!40000 ALTER TABLE `dynamic_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_messages`
--

DROP TABLE IF EXISTS `email_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `title` varchar(64) NOT NULL DEFAULT '',
  `message` varchar(255) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_messages`
--

LOCK TABLES `email_messages` WRITE;
/*!40000 ALTER TABLE `email_messages` DISABLE KEYS */;
INSERT INTO `email_messages` VALUES (3,'April','Goed Self','Wasssssssaaaap!','2016-03-14 05:43:51','2016-03-14 05:43:51');
/*!40000 ALTER TABLE `email_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (8,'Administrators','2012-12-10 13:13:09','2012-12-10 13:13:09'),(9,'Access Providers','2012-12-10 13:13:19','2012-12-10 13:13:19'),(10,'Permanent Users','2012-12-10 13:13:28','2012-12-10 13:13:28');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `iso_code` varchar(2) DEFAULT NULL,
  `rtl` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (4,'English','en',0,'2012-10-05 04:55:28','2012-10-06 07:58:26');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `licensed_devices`
--

DROP TABLE IF EXISTS `licensed_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `licensed_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
  `master_key` tinyint(1) NOT NULL DEFAULT '1',
  `provider_key` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  `extra_name` varchar(100) NOT NULL DEFAULT '',
  `extra_value` varchar(100) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `licensed_devices`
--

LOCK TABLES `licensed_devices` WRITE;
/*!40000 ALTER TABLE `licensed_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `licensed_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `limits`
--

DROP TABLE IF EXISTS `limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `alias` varchar(100) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `count` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `limits`
--

LOCK TABLES `limits` WRITE;
/*!40000 ALTER TABLE `limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mac_usages`
--

DROP TABLE IF EXISTS `mac_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mac_usages` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `mac` varchar(17) NOT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `data_used` bigint(20) DEFAULT NULL,
  `data_cap` bigint(20) DEFAULT NULL,
  `time_used` int(12) DEFAULT NULL,
  `time_cap` int(12) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mac_usages`
--

LOCK TABLES `mac_usages` WRITE;
/*!40000 ALTER TABLE `mac_usages` DISABLE KEYS */;
INSERT INTO `mac_usages` VALUES (1,'aa-aa-aa-aa-aa-aa','click_to_connect@Struisbaai',20,5000000,NULL,NULL,'2014-09-02 15:25:07','2014-09-02 15:25:07');
/*!40000 ALTER TABLE `mac_usages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesh_entries`
--

DROP TABLE IF EXISTS `mesh_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesh_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesh_id` int(11) DEFAULT NULL,
  `name` varchar(128) NOT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `isolate` tinyint(1) NOT NULL DEFAULT '0',
  `apply_to_all` tinyint(1) NOT NULL DEFAULT '0',
  `encryption` enum('none','wep','psk','psk2','wpa','wpa2') DEFAULT 'none',
  `special_key` varchar(100) NOT NULL DEFAULT '',
  `auth_server` varchar(255) NOT NULL DEFAULT '',
  `auth_secret` varchar(255) NOT NULL DEFAULT '',
  `dynamic_vlan` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `chk_maxassoc` tinyint(1) NOT NULL DEFAULT '0',
  `maxassoc` int(6) DEFAULT '100',
  `macfilter` enum('disable','allow','deny') DEFAULT 'disable',
  `permanent_user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesh_entries`
--

LOCK TABLES `mesh_entries` WRITE;
/*!40000 ALTER TABLE `mesh_entries` DISABLE KEYS */;
INSERT INTO `mesh_entries` VALUES (50,35,'Meerkat Wifi',0,0,1,'none','','','',0,'2014-07-26 04:21:24','2014-07-26 04:21:24',0,100,'disable',0),(52,40,'Cheetah guest',0,1,1,'none','','','',0,'2014-08-11 12:14:59','2016-04-14 14:32:21',0,100,'disable',0),(53,40,'Cheetah wireless',0,0,1,'psk2','cheetahwireless','','',0,'2014-08-11 12:16:14','2016-04-14 14:32:27',0,100,'disable',0),(54,41,'Lion Coffee',0,1,1,'none','','','',0,'2014-08-11 12:23:03','2014-09-08 05:48:43',0,100,'disable',0),(55,41,'Lion Lager Pub',0,1,1,'none','','','',0,'2014-08-11 12:23:53','2014-08-11 12:23:53',0,100,'disable',0),(56,41,'Lion Sushi',0,1,1,'none','','','',0,'2014-08-11 12:25:03','2014-08-11 12:25:03',0,100,'disable',0),(57,41,'Lion wireless',0,0,1,'wpa2','','206.221.176.235','testing123',0,'2014-08-11 12:26:21','2014-08-11 12:26:21',0,100,'disable',0);
/*!40000 ALTER TABLE `mesh_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesh_exit_captive_portals`
--

DROP TABLE IF EXISTS `mesh_exit_captive_portals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesh_exit_captive_portals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesh_exit_id` int(11) NOT NULL,
  `radius_1` varchar(128) NOT NULL,
  `radius_2` varchar(128) NOT NULL DEFAULT '',
  `radius_secret` varchar(128) NOT NULL,
  `radius_nasid` varchar(128) NOT NULL,
  `uam_url` varchar(255) NOT NULL,
  `uam_secret` varchar(255) NOT NULL,
  `walled_garden` varchar(255) NOT NULL,
  `swap_octets` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `mac_auth` tinyint(1) NOT NULL DEFAULT '0',
  `proxy_enable` tinyint(1) NOT NULL DEFAULT '0',
  `proxy_ip` varchar(128) NOT NULL DEFAULT '',
  `proxy_port` int(11) NOT NULL DEFAULT '3128',
  `proxy_auth_username` varchar(128) NOT NULL DEFAULT '',
  `proxy_auth_password` varchar(128) NOT NULL DEFAULT '',
  `coova_optional` varchar(255) NOT NULL DEFAULT '',
  `dns_manual` tinyint(1) NOT NULL DEFAULT '0',
  `dns1` varchar(128) NOT NULL DEFAULT '',
  `dns2` varchar(128) NOT NULL DEFAULT '',
  `uamanydns` tinyint(1) NOT NULL DEFAULT '0',
  `dnsparanoia` tinyint(1) NOT NULL DEFAULT '0',
  `dnsdesk` tinyint(1) NOT NULL DEFAULT '0',
  `mesh_exit_upstream_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesh_exit_captive_portals`
--

LOCK TABLES `mesh_exit_captive_portals` WRITE;
/*!40000 ALTER TABLE `mesh_exit_captive_portals` DISABLE KEYS */;
INSERT INTO `mesh_exit_captive_portals` VALUES (1,33,'198.27.111.78','','testing123','cheetah_cp1','http://198.27.111.78/cake3/rd_cake/dynamic-details/chilli-browser-detect/','greatsecret','www.radiusdesk.com',0,'2014-08-11 12:21:02','2017-02-24 20:56:38',0,0,'192.168.10.10',3128,'admin','admin','',0,'4.4.4.4','8.8.8.8',0,0,0,NULL);
/*!40000 ALTER TABLE `mesh_exit_captive_portals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesh_exit_mesh_entries`
--

DROP TABLE IF EXISTS `mesh_exit_mesh_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesh_exit_mesh_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesh_exit_id` int(11) NOT NULL,
  `mesh_entry_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesh_exit_mesh_entries`
--

LOCK TABLES `mesh_exit_mesh_entries` WRITE;
/*!40000 ALTER TABLE `mesh_exit_mesh_entries` DISABLE KEYS */;
INSERT INTO `mesh_exit_mesh_entries` VALUES (65,35,57,'2014-08-11 12:28:41','2014-08-11 12:28:41'),(96,32,53,'2016-04-24 15:33:04','2016-04-24 15:33:04'),(102,30,50,'2016-04-30 11:56:06','2016-04-30 11:56:06'),(132,59,54,'2016-09-19 03:34:27','2016-09-19 03:34:27'),(133,60,55,'2016-09-19 03:34:43','2016-09-19 03:34:43'),(135,33,52,'2017-02-24 20:56:38','2017-02-24 20:56:38');
/*!40000 ALTER TABLE `mesh_exit_mesh_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesh_exits`
--

DROP TABLE IF EXISTS `mesh_exits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesh_exits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesh_id` int(11) DEFAULT NULL,
  `name` varchar(128) NOT NULL,
  `type` enum('bridge','tagged_bridge','nat','captive_portal','openvpn_bridge','tagged_bridge_l3') DEFAULT NULL,
  `auto_detect` tinyint(1) NOT NULL DEFAULT '0',
  `vlan` int(4) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `openvpn_server_id` int(11) DEFAULT NULL,
  `proto` enum('static','dhcp','dhcpv6') DEFAULT 'dhcp',
  `ipaddr` varchar(50) NOT NULL DEFAULT '',
  `netmask` varchar(50) NOT NULL DEFAULT '',
  `gateway` varchar(50) NOT NULL DEFAULT '',
  `dns_1` varchar(50) NOT NULL DEFAULT '',
  `dns_2` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesh_exits`
--

LOCK TABLES `mesh_exits` WRITE;
/*!40000 ALTER TABLE `mesh_exits` DISABLE KEYS */;
INSERT INTO `mesh_exits` VALUES (30,35,'br-one','bridge',1,NULL,'2014-07-26 04:21:57','2016-04-30 11:56:06',NULL,'dhcp','','','','',''),(32,40,'cheetah_ebr1','bridge',1,NULL,'2014-08-11 12:16:52','2016-04-24 15:33:04',NULL,'dhcp','','','','',''),(33,40,'cheetah_cp1','captive_portal',1,NULL,'2014-08-11 12:21:02','2017-02-24 20:56:38',NULL,'dhcp','','','','',''),(35,41,'lion_ebr1','bridge',1,NULL,'2014-08-11 12:28:41','2014-08-11 12:28:41',NULL,'dhcp','','','','',''),(59,41,'','openvpn_bridge',1,NULL,'2016-09-19 03:34:27','2016-09-19 03:34:27',1,'dhcp','','','','',''),(60,41,'','openvpn_bridge',1,NULL,'2016-09-19 03:34:43','2016-09-19 03:34:43',2,'dhcp','','','','','');
/*!40000 ALTER TABLE `mesh_exits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesh_notes`
--

DROP TABLE IF EXISTS `mesh_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesh_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesh_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesh_notes`
--

LOCK TABLES `mesh_notes` WRITE;
/*!40000 ALTER TABLE `mesh_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `mesh_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesh_settings`
--

DROP TABLE IF EXISTS `mesh_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesh_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesh_id` int(11) DEFAULT NULL,
  `aggregated_ogms` tinyint(1) NOT NULL DEFAULT '1',
  `ap_isolation` tinyint(1) NOT NULL DEFAULT '0',
  `bonding` tinyint(1) NOT NULL DEFAULT '0',
  `bridge_loop_avoidance` tinyint(1) NOT NULL DEFAULT '0',
  `fragmentation` tinyint(1) NOT NULL DEFAULT '1',
  `distributed_arp_table` tinyint(1) NOT NULL DEFAULT '1',
  `orig_interval` int(10) NOT NULL DEFAULT '1000',
  `gw_sel_class` int(10) NOT NULL DEFAULT '20',
  `connectivity` enum('IBSS','mesh_point') DEFAULT 'mesh_point',
  `encryption` tinyint(1) NOT NULL DEFAULT '0',
  `encryption_key` varchar(63) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesh_settings`
--

LOCK TABLES `mesh_settings` WRITE;
/*!40000 ALTER TABLE `mesh_settings` DISABLE KEYS */;
INSERT INTO `mesh_settings` VALUES (6,35,1,0,0,0,1,1,1000,20,'mesh_point',0,'','2016-04-28 14:50:20','2016-04-28 14:50:20');
/*!40000 ALTER TABLE `mesh_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesh_specifics`
--

DROP TABLE IF EXISTS `mesh_specifics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesh_specifics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesh_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesh_specifics`
--

LOCK TABLES `mesh_specifics` WRITE;
/*!40000 ALTER TABLE `mesh_specifics` DISABLE KEYS */;
/*!40000 ALTER TABLE `mesh_specifics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meshes`
--

DROP TABLE IF EXISTS `meshes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meshes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `ssid` varchar(32) NOT NULL,
  `bssid` varchar(32) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meshes`
--

LOCK TABLES `meshes` WRITE;
/*!40000 ALTER TABLE `meshes` DISABLE KEYS */;
INSERT INTO `meshes` VALUES (35,'Meerkat','02_CA_FE_CA_00_01','02:CA:FE:CA:00:01',44,'2014-07-26 04:20:46','2014-07-26 04:20:46',0),(40,'Cheetah','02_CA_FE_CA_00_02','02:CA:FE:CA:00:02',44,'2014-08-11 12:09:29','2014-08-11 12:09:29',0),(41,'Lion','02_CA_FE_CA_00_03','02:CA:FE:CA:00:03',44,'2014-08-11 12:09:42','2014-08-11 12:09:42',0);
/*!40000 ALTER TABLE `meshes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_notes`
--

DROP TABLE IF EXISTS `na_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `na_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_notes`
--

LOCK TABLES `na_notes` WRITE;
/*!40000 ALTER TABLE `na_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `na_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_realms`
--

DROP TABLE IF EXISTS `na_realms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_realms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `na_id` int(11) NOT NULL,
  `realm_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_realms`
--

LOCK TABLES `na_realms` WRITE;
/*!40000 ALTER TABLE `na_realms` DISABLE KEYS */;
INSERT INTO `na_realms` VALUES (1,58,33,'2013-08-24 19:11:47','2013-08-24 19:11:47');
/*!40000 ALTER TABLE `na_realms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_states`
--

DROP TABLE IF EXISTS `na_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `na_id` char(36) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_states`
--

LOCK TABLES `na_states` WRITE;
/*!40000 ALTER TABLE `na_states` DISABLE KEYS */;
/*!40000 ALTER TABLE `na_states` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `na_tags`
--

DROP TABLE IF EXISTS `na_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `na_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `na_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `na_tags`
--

LOCK TABLES `na_tags` WRITE;
/*!40000 ALTER TABLE `na_tags` DISABLE KEYS */;
INSERT INTO `na_tags` VALUES (1,59,1,'2016-12-29 15:32:25','2016-12-29 15:32:25');
/*!40000 ALTER TABLE `na_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nas`
--

DROP TABLE IF EXISTS `nas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nas` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nasname` varchar(128) NOT NULL,
  `shortname` varchar(32) DEFAULT NULL,
  `nasidentifier` varchar(64) NOT NULL DEFAULT '',
  `type` varchar(30) DEFAULT 'other',
  `ports` int(5) DEFAULT NULL,
  `secret` varchar(60) NOT NULL DEFAULT 'secret',
  `server` varchar(64) DEFAULT NULL,
  `community` varchar(50) DEFAULT NULL,
  `description` varchar(200) DEFAULT 'RADIUS Client',
  `connection_type` enum('direct','openvpn','pptp','dynamic') DEFAULT 'direct',
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
  `record_auth` tinyint(1) NOT NULL DEFAULT '0',
  `ignore_acct` tinyint(1) NOT NULL DEFAULT '0',
  `dynamic_attribute` varchar(50) NOT NULL DEFAULT '',
  `dynamic_value` varchar(50) NOT NULL DEFAULT '',
  `monitor` enum('off','ping','heartbeat') DEFAULT 'off',
  `ping_interval` int(5) NOT NULL DEFAULT '600',
  `heartbeat_dead_after` int(5) NOT NULL DEFAULT '600',
  `last_contact` datetime DEFAULT NULL,
  `session_auto_close` tinyint(1) NOT NULL DEFAULT '0',
  `session_dead_time` int(5) NOT NULL DEFAULT '3600',
  `on_public_maps` tinyint(1) NOT NULL DEFAULT '0',
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `photo_file_name` varchar(128) NOT NULL DEFAULT 'logo.jpg',
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nasname` (`nasname`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nas`
--

LOCK TABLES `nas` WRITE;
/*!40000 ALTER TABLE `nas` DISABLE KEYS */;
INSERT INTO `nas` VALUES (59,'127.0.0.1','localhost','localhost','CoovaChilli',3799,'testing123','','','RADIUS Client','direct',0,0,0,'','','off',600,600,NULL,1,3600,0,-25.7382573400939,28.3021675344951,'logo.jpg',44,'2013-08-24 22:02:18','2015-07-10 23:02:26');
/*!40000 ALTER TABLE `nas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new_accountings`
--

DROP TABLE IF EXISTS `new_accountings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `new_accountings` (
  `mac` varchar(17) NOT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`mac`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new_accountings`
--

LOCK TABLES `new_accountings` WRITE;
/*!40000 ALTER TABLE `new_accountings` DISABLE KEYS */;
/*!40000 ALTER TABLE `new_accountings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_actions`
--

DROP TABLE IF EXISTS `node_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_actions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `node_id` int(10) NOT NULL,
  `action` enum('execute') DEFAULT 'execute',
  `command` varchar(500) DEFAULT '',
  `status` enum('awaiting','fetched','replied') DEFAULT 'awaiting',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_actions`
--

LOCK TABLES `node_actions` WRITE;
/*!40000 ALTER TABLE `node_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_ibss_connections`
--

DROP TABLE IF EXISTS `node_ibss_connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_ibss_connections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) DEFAULT NULL,
  `station_node_id` int(11) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `mac` varchar(17) NOT NULL,
  `tx_bytes` bigint(20) NOT NULL,
  `rx_bytes` bigint(20) NOT NULL,
  `tx_packets` int(11) NOT NULL,
  `rx_packets` int(11) NOT NULL,
  `tx_bitrate` int(11) NOT NULL,
  `rx_bitrate` int(11) NOT NULL,
  `tx_extra_info` varchar(255) NOT NULL,
  `rx_extra_info` varchar(255) NOT NULL,
  `authenticated` enum('yes','no') DEFAULT 'no',
  `authorized` enum('yes','no') DEFAULT 'no',
  `tdls_peer` varchar(255) NOT NULL,
  `preamble` enum('long','short') DEFAULT 'long',
  `tx_failed` int(11) NOT NULL,
  `inactive_time` int(11) NOT NULL,
  `WMM_WME` enum('yes','no') DEFAULT 'no',
  `tx_retries` int(11) NOT NULL,
  `MFP` enum('yes','no') DEFAULT 'no',
  `signal` int(11) NOT NULL,
  `signal_avg` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_ibss_connections`
--

LOCK TABLES `node_ibss_connections` WRITE;
/*!40000 ALTER TABLE `node_ibss_connections` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_ibss_connections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_loads`
--

DROP TABLE IF EXISTS `node_loads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_loads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) DEFAULT NULL,
  `mem_total` int(11) DEFAULT NULL,
  `mem_free` int(11) DEFAULT NULL,
  `uptime` varchar(255) DEFAULT NULL,
  `system_time` varchar(255) NOT NULL,
  `load_1` float(2,2) NOT NULL,
  `load_2` float(2,2) NOT NULL,
  `load_3` float(2,2) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_loads`
--

LOCK TABLES `node_loads` WRITE;
/*!40000 ALTER TABLE `node_loads` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_loads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_mesh_entries`
--

DROP TABLE IF EXISTS `node_mesh_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_mesh_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) NOT NULL,
  `mesh_entry_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_mesh_entries`
--

LOCK TABLES `node_mesh_entries` WRITE;
/*!40000 ALTER TABLE `node_mesh_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_mesh_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_mesh_exits`
--

DROP TABLE IF EXISTS `node_mesh_exits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_mesh_exits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) NOT NULL,
  `mesh_exit_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_mesh_exits`
--

LOCK TABLES `node_mesh_exits` WRITE;
/*!40000 ALTER TABLE `node_mesh_exits` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_mesh_exits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_mp_settings`
--

DROP TABLE IF EXISTS `node_mp_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_mp_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `value` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_mp_settings`
--

LOCK TABLES `node_mp_settings` WRITE;
/*!40000 ALTER TABLE `node_mp_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_mp_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_neighbors`
--

DROP TABLE IF EXISTS `node_neighbors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_neighbors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) DEFAULT NULL,
  `gateway` enum('yes','no') DEFAULT 'no',
  `neighbor_id` int(11) DEFAULT NULL,
  `metric` decimal(6,4) NOT NULL,
  `hwmode` char(5) DEFAULT '11g',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_neighbors`
--

LOCK TABLES `node_neighbors` WRITE;
/*!40000 ALTER TABLE `node_neighbors` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_neighbors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_settings`
--

DROP TABLE IF EXISTS `node_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesh_id` int(11) DEFAULT NULL,
  `password` varchar(128) NOT NULL,
  `power` int(3) NOT NULL DEFAULT '100',
  `all_power` tinyint(1) NOT NULL DEFAULT '1',
  `two_chan` int(3) NOT NULL DEFAULT '6',
  `five_chan` int(3) NOT NULL DEFAULT '44',
  `heartbeat_interval` int(5) NOT NULL DEFAULT '60',
  `heartbeat_dead_after` int(5) NOT NULL DEFAULT '600',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `password_hash` varchar(100) NOT NULL DEFAULT '',
  `eth_br_chk` tinyint(1) NOT NULL DEFAULT '0',
  `eth_br_with` int(11) NOT NULL DEFAULT '0',
  `eth_br_for_all` tinyint(1) NOT NULL DEFAULT '1',
  `tz_name` varchar(128) NOT NULL DEFAULT 'America/New York',
  `tz_value` varchar(128) NOT NULL DEFAULT 'EST5EDT,M3.2.0,M11.1.0',
  `country` varchar(5) NOT NULL DEFAULT 'US',
  `gw_dhcp_timeout` int(5) NOT NULL DEFAULT '120',
  `gw_use_previous` tinyint(1) NOT NULL DEFAULT '1',
  `gw_auto_reboot` tinyint(1) NOT NULL DEFAULT '1',
  `gw_auto_reboot_time` int(5) NOT NULL DEFAULT '600',
  `client_key` varchar(255) NOT NULL DEFAULT 'radiusdesk',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_settings`
--

LOCK TABLES `node_settings` WRITE;
/*!40000 ALTER TABLE `node_settings` DISABLE KEYS */;
INSERT INTO `node_settings` VALUES (15,41,'admin',100,1,1,44,60,300,'2014-08-11 12:33:19','2014-08-11 13:44:43','',0,0,1,'America/New York','EST5EDT,M3.2.0,M11.1.0','US',120,1,1,600,'radiusdesk'),(16,35,'admin',100,0,6,44,60,300,'2014-09-15 12:55:31','2015-09-10 07:58:33','',0,30,0,'America/New York','EST5EDT,M3.2.0,M11.1.0','US',120,1,1,600,'radiusdesk'),(18,40,'admin',100,0,11,161,60,300,'2015-05-08 09:53:23','2016-04-14 14:34:18','$1$480Su0cr$NZDXGrVydken24oH2t9jr.',0,0,0,'Africa/Johannesburg','SAST-2','ZA',120,1,1,600,'radiusdesk');
/*!40000 ALTER TABLE `node_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_stations`
--

DROP TABLE IF EXISTS `node_stations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_stations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) DEFAULT NULL,
  `mesh_entry_id` int(11) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `mac` varchar(17) NOT NULL,
  `tx_bytes` bigint(20) NOT NULL,
  `rx_bytes` bigint(20) NOT NULL,
  `tx_packets` int(11) NOT NULL,
  `rx_packets` int(11) NOT NULL,
  `tx_bitrate` int(11) NOT NULL,
  `rx_bitrate` int(11) NOT NULL,
  `tx_extra_info` varchar(255) NOT NULL,
  `rx_extra_info` varchar(255) NOT NULL,
  `authenticated` enum('yes','no') DEFAULT 'no',
  `authorized` enum('yes','no') DEFAULT 'no',
  `tdls_peer` varchar(255) NOT NULL,
  `preamble` enum('long','short') DEFAULT 'long',
  `tx_failed` int(11) NOT NULL,
  `inactive_time` int(11) NOT NULL,
  `WMM_WME` enum('yes','no') DEFAULT 'no',
  `tx_retries` int(11) NOT NULL,
  `MFP` enum('yes','no') DEFAULT 'no',
  `signal` int(11) NOT NULL,
  `signal_avg` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_stations`
--

LOCK TABLES `node_stations` WRITE;
/*!40000 ALTER TABLE `node_stations` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_stations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_systems`
--

DROP TABLE IF EXISTS `node_systems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_systems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_systems`
--

LOCK TABLES `node_systems` WRITE;
/*!40000 ALTER TABLE `node_systems` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_systems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_wifi_settings`
--

DROP TABLE IF EXISTS `node_wifi_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_wifi_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_wifi_settings`
--

LOCK TABLES `node_wifi_settings` WRITE;
/*!40000 ALTER TABLE `node_wifi_settings` DISABLE KEYS */;
INSERT INTO `node_wifi_settings` VALUES (1,1,'device_type','standard','2017-02-17 00:09:06','2017-02-17 00:09:06'),(2,1,'radio0_htmode','HT20','2017-02-17 00:09:06','2017-02-17 00:09:06'),(3,1,'radio0_disable_b','radio0_disable_b','2017-02-17 00:09:06','2017-02-17 00:09:06'),(4,1,'radio0_diversity','radio0_diversity','2017-02-17 00:09:06','2017-02-17 00:09:06'),(5,1,'radio0_ldpc','radio0_ldpc','2017-02-17 00:09:06','2017-02-17 00:09:06'),(6,1,'radio0_txpower','21','2017-02-17 00:09:06','2017-02-17 00:09:06'),(7,1,'radio0_beacon_int','100','2017-02-17 00:09:06','2017-02-17 00:09:06'),(8,1,'radio0_distance','300','2017-02-17 00:09:06','2017-02-17 00:09:06'),(9,1,'radio0_ht_capab','SHORT-GI-40','2017-02-17 00:09:06','2017-02-17 00:09:06'),(10,1,'radio0_ht_capab','RX-STBC1','2017-02-17 00:09:06','2017-02-17 00:09:06'),(11,1,'radio0_ht_capab','TX-STBC','2017-02-17 00:09:06','2017-02-17 00:09:06'),(12,1,'radio0_ht_capab','DSSS_CCK-40','2017-02-17 00:09:06','2017-02-17 00:09:06');
/*!40000 ALTER TABLE `node_wifi_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nodes`
--

DROP TABLE IF EXISTS `nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesh_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `mac` varchar(255) NOT NULL,
  `hardware` varchar(255) DEFAULT NULL,
  `power` int(3) NOT NULL DEFAULT '100',
  `ip` varchar(255) DEFAULT NULL,
  `last_contact` datetime DEFAULT NULL,
  `on_public_maps` tinyint(1) NOT NULL DEFAULT '0',
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `photo_file_name` varchar(128) NOT NULL DEFAULT 'logo.jpg',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `radio0_enable` tinyint(1) NOT NULL DEFAULT '1',
  `radio0_mesh` tinyint(1) NOT NULL DEFAULT '1',
  `radio0_entry` tinyint(1) NOT NULL DEFAULT '1',
  `radio0_band` tinyint(3) NOT NULL DEFAULT '24',
  `radio0_two_chan` int(4) NOT NULL DEFAULT '1',
  `radio0_five_chan` int(4) NOT NULL DEFAULT '44',
  `radio1_enable` tinyint(1) NOT NULL DEFAULT '1',
  `radio1_mesh` tinyint(1) NOT NULL DEFAULT '1',
  `radio1_entry` tinyint(1) NOT NULL DEFAULT '1',
  `radio1_band` tinyint(3) NOT NULL DEFAULT '5',
  `radio1_two_chan` int(4) NOT NULL DEFAULT '1',
  `radio1_five_chan` int(4) NOT NULL DEFAULT '44',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nodes`
--

LOCK TABLES `nodes` WRITE;
/*!40000 ALTER TABLE `nodes` DISABLE KEYS */;
INSERT INTO `nodes` VALUES (1,40,'removeMe','','78-A3-51-0B-BC-CA','tl841n',100,'10.5.5.1','2017-02-17 00:14:03',0,NULL,NULL,'logo.jpg','2017-02-17 00:09:06','2017-02-17 00:14:03',1,1,1,24,1,44,1,1,1,5,1,44);
/*!40000 ALTER TABLE `nodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note` text NOT NULL,
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
INSERT INTO `notes` VALUES (76,'Sample data for RADIUSdesk',1,44,'2013-05-25 12:38:42','2013-05-25 12:38:42'),(77,'This is a note',1,182,'2014-01-07 22:12:23','2014-01-07 22:12:23'),(78,'Up the price a bit',1,44,'2015-02-01 18:34:51','2015-02-01 18:34:51'),(87,'Multi\nLine\nNote',0,44,'2017-01-02 06:17:25','2017-01-02 06:17:25'),(90,'Device Note',1,44,'2017-05-06 09:39:49','2017-05-06 09:39:49');
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `openvpn_clients`
--

DROP TABLE IF EXISTS `openvpn_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `openvpn_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `subnet` int(3) DEFAULT NULL,
  `peer1` int(3) DEFAULT NULL,
  `peer2` int(3) DEFAULT NULL,
  `na_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `openvpn_clients`
--

LOCK TABLES `openvpn_clients` WRITE;
/*!40000 ALTER TABLE `openvpn_clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `openvpn_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `openvpn_server_clients`
--

DROP TABLE IF EXISTS `openvpn_server_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `openvpn_server_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesh_ap_profile` enum('mesh','ap_profile') DEFAULT 'mesh',
  `openvpn_server_id` int(11) DEFAULT NULL,
  `mesh_id` int(11) DEFAULT NULL,
  `mesh_exit_id` int(11) DEFAULT NULL,
  `ap_profile_id` int(11) DEFAULT NULL,
  `ap_profile_exit_id` int(11) DEFAULT NULL,
  `ap_id` int(11) DEFAULT NULL,
  `ip_address` varchar(40) NOT NULL,
  `last_contact_to_server` datetime DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `openvpn_server_clients`
--

LOCK TABLES `openvpn_server_clients` WRITE;
/*!40000 ALTER TABLE `openvpn_server_clients` DISABLE KEYS */;
INSERT INTO `openvpn_server_clients` VALUES (19,'mesh',1,41,59,NULL,NULL,NULL,'10.8.0.129',NULL,0,'2016-09-19 03:34:27','2016-09-19 03:34:27'),(20,'mesh',2,41,60,NULL,NULL,NULL,'10.8.0.129',NULL,0,'2016-09-19 03:34:43','2016-09-19 03:34:43');
/*!40000 ALTER TABLE `openvpn_server_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `openvpn_servers`
--

DROP TABLE IF EXISTS `openvpn_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `openvpn_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  `local_remote` enum('local','remote') DEFAULT 'local',
  `protocol` enum('udp','tcp') DEFAULT 'udp',
  `ip_address` varchar(40) NOT NULL,
  `port` int(6) NOT NULL,
  `vpn_gateway_address` varchar(40) NOT NULL,
  `vpn_bridge_start_address` varchar(40) NOT NULL,
  `vpn_mask` varchar(40) NOT NULL,
  `config_preset` varchar(100) NOT NULL DEFAULT 'default',
  `ca_crt` text NOT NULL,
  `extra_name` varchar(100) NOT NULL DEFAULT '',
  `extra_value` varchar(100) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `openvpn_servers`
--

LOCK TABLES `openvpn_servers` WRITE;
/*!40000 ALTER TABLE `openvpn_servers` DISABLE KEYS */;
INSERT INTO `openvpn_servers` VALUES (1,'USA-1','Tunnel to West Coast',1,44,'local','udp','198.27.111.76',1194,'10.8.0.1','10.8.0.129','255.255.255.0','default','-----BEGIN CERTIFICATE-----\nMIIE+jCCA+KgAwIBAgIJAIZVNkfIiREVMA0GCSqGSIb3DQEBCwUAMIGuMQswCQYD\nVQQGEwJaQTEQMA4GA1UECBMHR2F1dGVuZzERMA8GA1UEBxMITWV5ZXJ0b24xETAP\nBgNVBAoTCExpbm92YXRlMRUwEwYDVQQLEwxDb21wdXRlckxhYnMxFDASBgNVBAMT\nC0xpbm92YXRlIENBMREwDwYDVQQpEwhMaW5vdmF0ZTEnMCUGCSqGSIb3DQEJARYY\nZGlya3ZhbmRlcndhbHRAZ21haWwuY29tMB4XDTE2MDkxMjA4MTQwMVoXDTI2MDkx\nMDA4MTQwMVowga4xCzAJBgNVBAYTAlpBMRAwDgYDVQQIEwdHYXV0ZW5nMREwDwYD\nVQQHEwhNZXllcnRvbjERMA8GA1UEChMITGlub3ZhdGUxFTATBgNVBAsTDENvbXB1\ndGVyTGFiczEUMBIGA1UEAxMLTGlub3ZhdGUgQ0ExETAPBgNVBCkTCExpbm92YXRl\nMScwJQYJKoZIhvcNAQkBFhhkaXJrdmFuZGVyd2FsdEBnbWFpbC5jb20wggEiMA0G\nCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDDwCqsTqiQOWqC+nAw04GC4wDOvCWM\nMkzjGM1A7W/BJe3vt8gxFg7ffcXjJWrROQvJacv4vodNgL0lNrzltEyhTwkHhkqx\nCHQZMGPBclg0izP5Lz/6cyOd0zv5I9RQGDnBLQPq+baXVfBPudaFi8kBYPlRiFRY\nrDt2N76b13mqMHEdeANhDfwAl5T5ftmd2wKlfQo0wltFkDGmiiwStSdz5e3nDI6D\nyRuopS/hq2gGJWutlw9ucaDIYJf4X5OzvyRrEx9M5bj2MZf4QaDQphW9NMrO8TbN\n7mbh1bS0aJ9b/SSK4vegtqlGLpCx1SME00HuC1osiraHbIPZ0/8L9y4HAgMBAAGj\nggEXMIIBEzAdBgNVHQ4EFgQUYa19kSBWE/C1fEr2tI9j3Zq7238wgeMGA1UdIwSB\n2zCB2IAUYa19kSBWE/C1fEr2tI9j3Zq723+hgbSkgbEwga4xCzAJBgNVBAYTAlpB\nMRAwDgYDVQQIEwdHYXV0ZW5nMREwDwYDVQQHEwhNZXllcnRvbjERMA8GA1UEChMI\nTGlub3ZhdGUxFTATBgNVBAsTDENvbXB1dGVyTGFiczEUMBIGA1UEAxMLTGlub3Zh\ndGUgQ0ExETAPBgNVBCkTCExpbm92YXRlMScwJQYJKoZIhvcNAQkBFhhkaXJrdmFu\nZGVyd2FsdEBnbWFpbC5jb22CCQCGVTZHyIkRFTAMBgNVHRMEBTADAQH/MA0GCSqG\nSIb3DQEBCwUAA4IBAQCk3PW1kz26Qg1SkXYjK1plp3dBeQjZ2mkJ+3MZn5wau4+u\nEinJ8OxGdUoiQMliniecOhkuavibrz4vEnIGi0K5OGzA8msLLWb9glHDUSjRXwlV\nTWRgEtL8vmEjcz57vN556zwe/4rNOLLTPjcvexG41PuCw7OQGRV3+Gw2YGREvNn6\nKLjcEqBsT2ju4NJNRAyXu50t4Ugvvi7QJtL3YFniSE87ojsJ06heuDXM58LJf5jz\nPA8p+LCh6V9esHNa3AkHp0M+tHdmlrR0qtfVB8oBk8yuCJQGhlefC80RZFAnhEQN\nwuU0JY1bWFc579IdU/bBIWaxvy7ZGSXpKscbGCpu\n-----END CERTIFICATE-----\n','','','2016-09-15 22:25:46','2016-09-15 23:28:56'),(2,'USA-2','Tunnel to East Coast',1,44,'remote','udp','198.27.111.77',1194,'10.8.0.1','10.8.0.129','255.255.255.0','default','-----BEGIN CERTIFICATE-----\nMIIE+jCCA+KgAwIBAgIJAIZVNkfIiREVMA0GCSqGSIb3DQEBCwUAMIGuMQswCQYD\nVQQGEwJaQTEQMA4GA1UECBMHR2F1dGVuZzERMA8GA1UEBxMITWV5ZXJ0b24xETAP\nBgNVBAoTCExpbm92YXRlMRUwEwYDVQQLEwxDb21wdXRlckxhYnMxFDASBgNVBAMT\nC0xpbm92YXRlIENBMREwDwYDVQQpEwhMaW5vdmF0ZTEnMCUGCSqGSIb3DQEJARYY\nZGlya3ZhbmRlcndhbHRAZ21haWwuY29tMB4XDTE2MDkxMjA4MTQwMVoXDTI2MDkx\nMDA4MTQwMVowga4xCzAJBgNVBAYTAlpBMRAwDgYDVQQIEwdHYXV0ZW5nMREwDwYD\nVQQHEwhNZXllcnRvbjERMA8GA1UEChMITGlub3ZhdGUxFTATBgNVBAsTDENvbXB1\ndGVyTGFiczEUMBIGA1UEAxMLTGlub3ZhdGUgQ0ExETAPBgNVBCkTCExpbm92YXRl\nMScwJQYJKoZIhvcNAQkBFhhkaXJrdmFuZGVyd2FsdEBnbWFpbC5jb20wggEiMA0G\nCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDDwCqsTqiQOWqC+nAw04GC4wDOvCWM\nMkzjGM1A7W/BJe3vt8gxFg7ffcXjJWrROQvJacv4vodNgL0lNrzltEyhTwkHhkqx\nCHQZMGPBclg0izP5Lz/6cyOd0zv5I9RQGDnBLQPq+baXVfBPudaFi8kBYPlRiFRY\nrDt2N76b13mqMHEdeANhDfwAl5T5ftmd2wKlfQo0wltFkDGmiiwStSdz5e3nDI6D\nyRuopS/hq2gGJWutlw9ucaDIYJf4X5OzvyRrEx9M5bj2MZf4QaDQphW9NMrO8TbN\n7mbh1bS0aJ9b/SSK4vegtqlGLpCx1SME00HuC1osiraHbIPZ0/8L9y4HAgMBAAGj\nggEXMIIBEzAdBgNVHQ4EFgQUYa19kSBWE/C1fEr2tI9j3Zq7238wgeMGA1UdIwSB\n2zCB2IAUYa19kSBWE/C1fEr2tI9j3Zq723+hgbSkgbEwga4xCzAJBgNVBAYTAlpB\nMRAwDgYDVQQIEwdHYXV0ZW5nMREwDwYDVQQHEwhNZXllcnRvbjERMA8GA1UEChMI\nTGlub3ZhdGUxFTATBgNVBAsTDENvbXB1dGVyTGFiczEUMBIGA1UEAxMLTGlub3Zh\ndGUgQ0ExETAPBgNVBCkTCExpbm92YXRlMScwJQYJKoZIhvcNAQkBFhhkaXJrdmFu\nZGVyd2FsdEBnbWFpbC5jb22CCQCGVTZHyIkRFTAMBgNVHRMEBTADAQH/MA0GCSqG\nSIb3DQEBCwUAA4IBAQCk3PW1kz26Qg1SkXYjK1plp3dBeQjZ2mkJ+3MZn5wau4+u\nEinJ8OxGdUoiQMliniecOhkuavibrz4vEnIGi0K5OGzA8msLLWb9glHDUSjRXwlV\nTWRgEtL8vmEjcz57vN556zwe/4rNOLLTPjcvexG41PuCw7OQGRV3+Gw2YGREvNn6\nKLjcEqBsT2ju4NJNRAyXu50t4Ugvvi7QJtL3YFniSE87ojsJ06heuDXM58LJf5jz\nPA8p+LCh6V9esHNa3AkHp0M+tHdmlrR0qtfVB8oBk8yuCJQGhlefC80RZFAnhEQN\nwuU0JY1bWFc579IdU/bBIWaxvy7ZGSXpKscbGCpu\n-----END CERTIFICATE-----\n','','','2016-09-16 07:42:38','2016-09-16 07:46:30');
/*!40000 ALTER TABLE `openvpn_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permanent_user_notes`
--

DROP TABLE IF EXISTS `permanent_user_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permanent_user_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permanent_user_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permanent_user_notes`
--

LOCK TABLES `permanent_user_notes` WRITE;
/*!40000 ALTER TABLE `permanent_user_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `permanent_user_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permanent_user_notifications`
--

DROP TABLE IF EXISTS `permanent_user_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permanent_user_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permanent_user_id` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `method` enum('whatsapp','email','sms') DEFAULT 'email',
  `type` enum('daily','usage') DEFAULT 'daily',
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `start` int(3) DEFAULT '80',
  `increment` int(3) DEFAULT '10',
  `last_value` int(3) DEFAULT NULL,
  `last_notification` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permanent_user_notifications`
--

LOCK TABLES `permanent_user_notifications` WRITE;
/*!40000 ALTER TABLE `permanent_user_notifications` DISABLE KEYS */;
INSERT INTO `permanent_user_notifications` VALUES (2,187,1,'email','daily','dirkvanderwalt@gmail.com','',80,10,NULL,NULL,'2015-07-19 19:35:19','2015-07-20 09:26:23');
/*!40000 ALTER TABLE `permanent_user_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permanent_user_settings`
--

DROP TABLE IF EXISTS `permanent_user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permanent_user_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permanent_user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permanent_user_settings`
--

LOCK TABLES `permanent_user_settings` WRITE;
/*!40000 ALTER TABLE `permanent_user_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `permanent_user_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permanent_users`
--

DROP TABLE IF EXISTS `permanent_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=250 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permanent_users`
--

LOCK TABLES `permanent_users` WRITE;
/*!40000 ALTER TABLE `permanent_users` DISABLE KEYS */;
INSERT INTO `permanent_users` VALUES (248,'dvdwalt@meshdesk','5db12f09b204bb56b5dac06877550d3c064e4e1a','1afb04b4-0d81-4091-881f-1db84296bfcf','','','','','','sql',1,'2017-07-15 17:43:56',NULL,'127.0.0.1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'hard','soft','MESHdesk',36,'1G-1Day',15,NULL,NULL,0,1,'','','',4,4,44,'2017-05-06 19:57:06','2017-05-21 14:47:31');
/*!40000 ALTER TABLE `permanent_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pptp_clients`
--

DROP TABLE IF EXISTS `pptp_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pptp_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `na_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pptp_clients`
--

LOCK TABLES `pptp_clients` WRITE;
/*!40000 ALTER TABLE `pptp_clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `pptp_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile_component_notes`
--

DROP TABLE IF EXISTS `profile_component_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_component_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_component_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile_component_notes`
--

LOCK TABLES `profile_component_notes` WRITE;
/*!40000 ALTER TABLE `profile_component_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `profile_component_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile_components`
--

DROP TABLE IF EXISTS `profile_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_components` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile_components`
--

LOCK TABLES `profile_components` WRITE;
/*!40000 ALTER TABLE `profile_components` DISABLE KEYS */;
INSERT INTO `profile_components` VALUES (46,'250M',1,44,'2013-08-24 21:20:20','2013-08-24 21:20:20'),(48,'500M',1,44,'2013-08-24 21:21:09','2013-08-24 21:21:09'),(49,'1G',1,44,'2013-08-24 21:21:23','2013-08-24 21:21:23'),(53,'1Hour',1,44,'2013-08-24 21:24:08','2013-08-24 21:24:08'),(56,'5M-every-hour',1,44,'2014-05-27 19:34:26','2014-05-27 19:34:26'),(61,'2G',1,44,'2016-06-27 07:21:44','2016-06-27 07:21:44'),(62,'5G',1,44,'2016-06-27 07:52:29','2016-06-27 07:52:29'),(63,'1Week',1,44,'2016-06-27 09:03:36','2016-06-27 09:03:36'),(64,'1Day',1,44,'2016-06-27 09:06:35','2016-06-27 09:06:35'),(65,'1Month',1,44,'2016-06-27 09:15:16','2016-06-27 09:15:16'),(66,'BW-1Mbs',1,44,'2016-06-27 09:44:24','2016-06-27 09:44:24'),(67,'BW-384Kbs',1,44,'2016-06-27 09:49:44','2016-06-27 09:49:44'),(68,'BW-512Kbs',1,44,'2016-06-27 09:51:38','2016-06-27 09:51:38'),(69,'BW-2Mbs',1,44,'2016-06-27 09:53:22','2016-06-27 09:53:22'),(70,'BW-4Mbs',1,44,'2016-06-27 09:55:00','2016-06-27 09:55:00'),(71,'1Hour per MAC daily',1,44,'2016-06-27 12:52:06','2016-06-27 12:52:06'),(72,'500M per MAC daily',1,44,'2016-06-27 13:05:57','2016-06-27 13:05:57');
/*!40000 ALTER TABLE `profile_components` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile_notes`
--

DROP TABLE IF EXISTS `profile_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile_notes`
--

LOCK TABLES `profile_notes` WRITE;
/*!40000 ALTER TABLE `profile_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `profile_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profiles`
--

DROP TABLE IF EXISTS `profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profiles`
--

LOCK TABLES `profiles` WRITE;
/*!40000 ALTER TABLE `profiles` DISABLE KEYS */;
INSERT INTO `profiles` VALUES (13,'unc-1hour',1,44,'2016-06-27 09:35:56','2016-06-27 09:35:56'),(15,'1G-1Day',1,44,'2016-06-27 09:40:04','2016-06-27 09:40:04'),(17,'1G-1Day-BW-1Mbs',1,44,'2016-06-27 09:59:00','2016-06-27 09:59:24'),(18,'5M-every hour',1,44,'2016-06-27 10:01:02','2016-06-27 10:01:02'),(19,'1 Hour click to connect',1,44,'2016-06-27 13:13:59','2016-06-27 13:13:59'),(20,'500M click to connect',1,44,'2016-06-27 13:14:34','2016-06-27 13:14:34');
/*!40000 ALTER TABLE `profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radacct`
--

DROP TABLE IF EXISTS `radacct`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radacct` (
  `radacctid` bigint(21) NOT NULL AUTO_INCREMENT,
  `acctsessionid` varchar(64) NOT NULL DEFAULT '',
  `acctuniqueid` varchar(32) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `realm` varchar(64) DEFAULT '',
  `nasipaddress` varchar(15) NOT NULL DEFAULT '',
  `nasidentifier` varchar(64) NOT NULL DEFAULT '',
  `nasportid` varchar(15) DEFAULT NULL,
  `nasporttype` varchar(32) DEFAULT NULL,
  `acctstarttime` datetime DEFAULT NULL,
  `acctupdatetime` datetime DEFAULT NULL,
  `acctstoptime` datetime DEFAULT NULL,
  `acctinterval` int(12) DEFAULT NULL,
  `acctsessiontime` int(12) unsigned DEFAULT NULL,
  `acctauthentic` varchar(32) DEFAULT NULL,
  `connectinfo_start` varchar(50) DEFAULT NULL,
  `connectinfo_stop` varchar(50) DEFAULT NULL,
  `acctinputoctets` bigint(20) DEFAULT NULL,
  `acctoutputoctets` bigint(20) DEFAULT NULL,
  `calledstationid` varchar(50) NOT NULL DEFAULT '',
  `callingstationid` varchar(50) NOT NULL DEFAULT '',
  `acctterminatecause` varchar(32) NOT NULL DEFAULT '',
  `servicetype` varchar(32) DEFAULT NULL,
  `framedprotocol` varchar(32) DEFAULT NULL,
  `framedipaddress` varchar(15) NOT NULL DEFAULT '',
  `acctstartdelay` int(12) DEFAULT NULL,
  `acctstopdelay` int(12) DEFAULT NULL,
  `xascendsessionsvrkey` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`radacctid`),
  UNIQUE KEY `acctuniqueid` (`acctuniqueid`),
  KEY `username` (`username`),
  KEY `framedipaddress` (`framedipaddress`),
  KEY `acctsessionid` (`acctsessionid`),
  KEY `acctsessiontime` (`acctsessiontime`),
  KEY `acctstarttime` (`acctstarttime`),
  KEY `acctinterval` (`acctinterval`),
  KEY `acctstoptime` (`acctstoptime`),
  KEY `nasipaddress` (`nasipaddress`),
  KEY `nasidentifier` (`nasidentifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radacct`
--

LOCK TABLES `radacct` WRITE;
/*!40000 ALTER TABLE `radacct` DISABLE KEYS */;
/*!40000 ALTER TABLE `radacct` ENABLE KEYS */;
UNLOCK TABLES;
ALTER DATABASE `rd` CHARACTER SET utf8 COLLATE utf8_swedish_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER radacct_after_update
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `rd` CHARACTER SET utf8 COLLATE utf8_general_ci ;

--
-- Table structure for table `radcheck`
--

DROP TABLE IF EXISTS `radcheck`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radcheck` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(64) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT '==',
  `value` varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `username` (`username`(32)),
  KEY `FK_radcheck_ref_vouchers` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=10382 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radcheck`
--

LOCK TABLES `radcheck` WRITE;
/*!40000 ALTER TABLE `radcheck` DISABLE KEYS */;
INSERT INTO `radcheck` VALUES (10336,'dvdwalt@meshdesk','User-Profile',':=','1G-1Day'),(10337,'dvdwalt@meshdesk','Rd-Realm',':=','MESHdesk'),(10338,'dvdwalt@meshdesk','Rd-Cap-Type-Time',':=','hard'),(10339,'dvdwalt@meshdesk','Rd-Account-Disabled',':=','0'),(10340,'dvdwalt@meshdesk','Cleartext-Password',':=','dvdwalt'),(10341,'dvdwalt@meshdesk','Rd-User-Type',':=','user');
/*!40000 ALTER TABLE `radcheck` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radgroupcheck`
--

DROP TABLE IF EXISTS `radgroupcheck`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radgroupcheck` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(64) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT '==',
  `value` varchar(253) NOT NULL DEFAULT '',
  `comment` varchar(253) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupname` (`groupname`(32))
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radgroupcheck`
--

LOCK TABLES `radgroupcheck` WRITE;
/*!40000 ALTER TABLE `radgroupcheck` DISABLE KEYS */;
INSERT INTO `radgroupcheck` VALUES (43,'500M','Rd-Reset-Type-Data',':=','never','','2013-08-24 21:34:24','2013-08-24 21:35:25'),(44,'500M','Rd-Cap-Type-Data',':=','hard','','2013-08-24 21:34:34','2013-08-24 21:35:17'),(45,'500M','Rd-Total-Data',':=','500000000','','2013-08-24 21:34:53','2013-08-24 21:35:11'),(49,'250M','Rd-Reset-Type-Data',':=','never','','2013-08-24 21:38:07','2013-08-24 21:38:48'),(51,'250M','Rd-Total-Data',':=','250000000','','2013-08-24 21:38:21','2013-08-24 21:38:33'),(53,'1Hour','Rd-Reset-Type-Time',':=','never','','2013-08-24 21:39:32','2013-08-24 21:39:53'),(62,'1Hour','Rd-Cap-Type-Time',':=','hard','','2013-08-24 21:44:42','2013-08-24 21:44:49'),(63,'5M-every-hour','Rd-Reset-Interval-Data',':=','3600','','2014-05-27 19:35:39','2014-05-27 19:36:09'),(64,'5M-every-hour','Rd-Cap-Type-Data',':=','hard','','2014-05-27 19:37:15','2014-05-27 19:37:20'),(65,'5M-every-hour','Rd-Total-Data',':=','5000000','','2014-05-27 19:37:31','2014-05-27 19:37:38'),(67,'5M-every-hour','Rd-Reset-Type-Data',':=','dynamic','','2014-05-27 19:39:11','2014-05-27 19:39:21'),(68,'5M-every-hour','Rd-Mac-Counter-Data',':=','1','','2014-05-27 19:39:48','2014-05-27 19:39:53'),(69,'1G','Rd-Cap-Type-Data',':=','hard','','2014-09-02 16:09:08','2014-09-02 16:09:18'),(85,'1G','Rd-Total-Data',':=','1000000000','','2016-06-27 07:23:15','2016-06-27 07:23:44'),(87,'2G','Rd-Reset-Type-Data',':=','never','','2016-06-27 07:46:58','2016-06-27 07:48:10'),(88,'2G','Rd-Cap-Type-Data',':=','hard','','2016-06-27 07:47:08','2016-06-27 07:48:17'),(89,'2G','Rd-Total-Data',':=','2000000000','','2016-06-27 07:47:33','2016-06-27 07:48:41'),(91,'5G','Rd-Cap-Type-Data',':=','hard','','2016-06-27 07:53:12','2016-06-27 07:53:17'),(92,'5G','Rd-Reset-Type-Data',':=','never','','2016-06-27 07:53:24','2016-06-27 07:54:06'),(93,'5G','Rd-Total-Data',':=','5000000000','','2016-06-27 07:54:24','2016-06-27 07:54:32'),(95,'1Week','Rd-Total-Time',':=','604800','','2016-06-27 09:05:03','2016-06-27 09:05:15'),(96,'1Week','Rd-Reset-Type-Time',':=','never','','2016-06-27 09:05:35','2016-06-27 09:05:44'),(97,'1Week','Rd-Cap-Type-Time',':=','hard','','2016-06-27 09:05:54','2016-06-27 09:06:01'),(99,'1Day','Rd-Cap-Type-Time',':=','hard','','2016-06-27 09:07:15','2016-06-27 09:11:53'),(100,'1Day','Rd-Reset-Type-Time',':=','never','','2016-06-27 09:07:31','2016-06-27 09:12:00'),(101,'1Day','Rd-Total-Time',':=','86400','','2016-06-27 09:12:05','2016-06-27 09:12:10'),(102,'1Month','Rd-Total-Time',':=','2628029','','2016-06-27 09:15:32','2016-06-27 09:16:42'),(104,'1Month','Rd-Cap-Type-Time',':=','hard','','2016-06-27 09:17:38','2016-06-27 09:17:54'),(105,'1Month','Rd-Reset-Type-Time',':=','never','','2016-06-27 09:17:47','2016-06-27 09:18:01'),(122,'1Hour per MAC daily','Rd-Total-Time',':=','3600','','2016-06-27 12:57:57','2016-06-27 13:02:14'),(123,'1Hour','Rd-Total-Time',':=','3600','','2016-06-27 12:58:42','2016-06-27 12:59:25'),(124,'250M','Rd-Cap-Type-Data',':=','hard','','2016-06-27 13:00:33','2016-06-27 13:00:40'),(125,'1Hour per MAC daily','Rd-Reset-Type-Time',':=','daily','','2016-06-27 13:02:57','2016-06-27 13:03:06'),(126,'1Hour per MAC daily','Rd-Mac-Counter-Time',':=','1','','2016-06-27 13:03:59','2016-06-27 13:04:04'),(127,'1Hour per MAC daily','Rd-Cap-Type-Time',':=','hard','','2016-06-27 13:04:20','2016-06-27 13:04:26'),(129,'500M per MAC daily','Rd-Cap-Type-Data',':=','hard','','2016-06-27 13:06:37','2016-06-27 13:07:32'),(131,'500M per MAC daily','Rd-Mac-Counter-Data',':=','1','','2016-06-27 13:07:06','2016-06-27 13:07:44'),(132,'500M per MAC daily','Rd-Total-Data',':=','500000000','','2016-06-27 13:07:23','2016-06-27 13:11:26'),(133,'500M per MAC daily','Rd-Reset-Type-Data',':=','daily','','2016-06-27 13:11:05','2016-06-27 13:11:16'),(134,'1G','Rd-Reset-Type-Data',':=','daily','','2017-07-15 17:43:45','2017-07-15 17:43:50');
/*!40000 ALTER TABLE `radgroupcheck` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radgroupreply`
--

DROP TABLE IF EXISTS `radgroupreply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radgroupreply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(64) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT '=',
  `value` varchar(253) NOT NULL DEFAULT '',
  `comment` varchar(253) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupname` (`groupname`(32))
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radgroupreply`
--

LOCK TABLES `radgroupreply` WRITE;
/*!40000 ALTER TABLE `radgroupreply` DISABLE KEYS */;
INSERT INTO `radgroupreply` VALUES (5,'500M','Fall-Through',':=','Yes','','2013-08-24 21:27:46','2013-08-24 21:27:46'),(9,'250M','Fall-Through',':=','Yes','','2013-08-24 21:28:41','2013-08-24 21:28:41'),(10,'1Hour','Fall-Through',':=','Yes','','2013-08-24 21:29:07','2013-08-24 21:29:07'),(12,'1G','Fall-Through',':=','Yes','','2013-08-24 21:29:28','2013-08-24 21:29:28'),(18,'','','=','','','2015-07-11 14:11:55','2015-07-11 14:11:55'),(28,'2G','Fall-Through',':=','Yes','','2016-06-27 07:47:44','2016-06-28 22:10:39'),(29,'5G','Fall-Through',':=','Yes','','2016-06-27 07:52:46','2016-06-28 22:10:26'),(30,'1Week','Fall-Through',':=','Yes','','2016-06-27 09:04:36','2016-06-28 22:11:09'),(31,'1Day','Fall-Through',':=','Yes','','2016-06-27 09:06:52','2016-06-28 22:11:30'),(32,'1Month','Fall-Through',':=','Yes','','2016-06-27 09:17:21','2016-06-28 22:11:14'),(33,'BW-1Mbs','Fall-Through',':=','Yes','','2016-06-27 09:44:45','2016-06-28 22:09:42'),(34,'BW-1Mbs','WISPr-Bandwidth-Max-Down',':=','1000000','','2016-06-27 09:48:41','2016-06-27 09:48:41'),(35,'BW-1Mbs','WISPr-Bandwidth-Max-Up',':=','1000000','','2016-06-27 09:48:46','2016-06-27 09:48:46'),(36,'BW-384Kbs','Fall-Through',':=','Yes','','2016-06-27 09:50:08','2016-06-28 22:09:30'),(37,'BW-384Kbs','WISPr-Bandwidth-Max-Up',':=','384000','','2016-06-27 09:50:43','2016-06-27 09:51:09'),(38,'BW-384Kbs','WISPr-Bandwidth-Max-Down',':=','384000','','2016-06-27 09:51:15','2016-06-27 09:51:15'),(39,'BW-512Kbs','Fall-Through',':=','Yes','','2016-06-27 09:51:51','2016-06-28 22:08:54'),(40,'BW-512Kbs','WISPr-Bandwidth-Max-Up',':=','512000','','2016-06-27 09:52:22','2016-06-27 09:52:34'),(41,'BW-512Kbs','WISPr-Bandwidth-Max-Down',':=','512000','','2016-06-27 09:52:27','2016-06-27 09:52:46'),(42,'BW-2Mbs','Fall-Through',':=','Yes','','2016-06-27 09:53:53','2016-06-28 22:09:36'),(43,'BW-2Mbs','WISPr-Bandwidth-Max-Down',':=','2000000','','2016-06-27 09:53:57','2016-06-27 09:54:19'),(44,'BW-2Mbs','WISPr-Bandwidth-Max-Up',':=','2000000','','2016-06-27 09:54:02','2016-06-27 09:54:30'),(45,'BW-4Mbs','Fall-Through',':=','Yes','','2016-06-27 09:55:38','2016-06-28 22:09:13'),(46,'BW-4Mbs','WISPr-Bandwidth-Max-Down',':=','4000000','','2016-06-27 09:55:42','2016-06-27 09:55:59'),(47,'BW-4Mbs','WISPr-Bandwidth-Max-Up',':=','4000000','','2016-06-27 09:55:46','2016-06-27 09:56:06'),(48,'5M-every-hour','Fall-Through',':=','Yes','','2016-06-27 12:55:34','2016-06-27 12:55:34'),(49,'1Hour per MAC daily','Fall-Through',':=','Yes','','2016-06-27 12:56:33','2016-06-28 22:11:19'),(50,'500M per MAC daily','Fall-Through',':=','Yes','','2016-06-27 13:06:14','2016-06-28 22:10:31');
/*!40000 ALTER TABLE `radgroupreply` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radippool`
--

DROP TABLE IF EXISTS `radippool`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radippool` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pool_name` varchar(30) NOT NULL,
  `framedipaddress` varchar(15) NOT NULL DEFAULT '',
  `nasipaddress` varchar(15) NOT NULL DEFAULT '',
  `calledstationid` varchar(30) NOT NULL,
  `callingstationid` varchar(30) NOT NULL,
  `expiry_time` datetime DEFAULT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `pool_key` varchar(30) NOT NULL DEFAULT '',
  `nasidentifier` varchar(64) NOT NULL DEFAULT '',
  `extra_name` varchar(100) NOT NULL DEFAULT '',
  `extra_value` varchar(100) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `permanent_user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `radippool_poolname_expire` (`pool_name`,`expiry_time`),
  KEY `framedipaddress` (`framedipaddress`),
  KEY `radippool_nasip_poolkey_ipaddress` (`nasipaddress`,`pool_key`,`framedipaddress`)
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radippool`
--

LOCK TABLES `radippool` WRITE;
/*!40000 ALTER TABLE `radippool` DISABLE KEYS */;
INSERT INTO `radippool` VALUES (164,'Test','192.168.1.1','','','',NULL,'dvdwalt','','','','',1,187,'2015-04-14 04:33:45','2015-04-14 09:01:03'),(165,'Test','192.168.1.2','','','',NULL,'','','','','',1,NULL,'2015-04-14 04:33:45','2015-04-14 08:12:23'),(166,'Test','192.168.1.3','','','',NULL,'','','','','',1,NULL,'2015-04-14 04:33:45','2015-04-14 04:33:45'),(167,'Test','192.168.1.4','','','',NULL,'','','','','',1,NULL,'2015-04-14 04:33:45','2015-04-14 04:33:45'),(168,'Test','192.168.1.5','','','',NULL,'','','','','',1,NULL,'2015-04-14 04:33:45','2015-04-14 04:33:45');
/*!40000 ALTER TABLE `radippool` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radpostauth`
--

DROP TABLE IF EXISTS `radpostauth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radpostauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `realm` varchar(64) DEFAULT NULL,
  `pass` varchar(64) NOT NULL DEFAULT '',
  `reply` varchar(32) NOT NULL DEFAULT '',
  `nasname` varchar(128) NOT NULL DEFAULT '',
  `authdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radpostauth`
--

LOCK TABLES `radpostauth` WRITE;
/*!40000 ALTER TABLE `radpostauth` DISABLE KEYS */;
INSERT INTO `radpostauth` VALUES (2,'dvdwalt@meshdesk',NULL,'dvdwalt','Access-Accept','','2017-05-06 22:29:02'),(3,'dvdwalt@meshdesk',NULL,'dvdwalt','Access-Reject','','2017-07-15 15:39:06'),(4,'dvdwalt@meshdesk',NULL,'dvdwalt','Access-Reject','','2017-07-15 15:39:27'),(5,'dvdwalt@meshdesk',NULL,'dvdwalt','Access-Accept','','2017-07-15 15:43:24'),(6,'dvdwalt@meshdesk',NULL,'dvdwalt','Access-Accept','','2017-07-15 15:43:25'),(7,'dvdwalt@meshdesk',NULL,'dvdwalt','Access-Accept','','2017-07-15 15:43:53'),(8,'dvdwalt@meshdesk',NULL,'dvdwalt','Access-Accept','','2017-07-15 15:43:54'),(9,'dvdwalt@meshdesk',NULL,'dvdwalt','Access-Accept','','2017-07-15 15:43:56');
/*!40000 ALTER TABLE `radpostauth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radreply`
--

DROP TABLE IF EXISTS `radreply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radreply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(64) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT '=',
  `value` varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `username` (`username`(32)),
  KEY `FK_radreply_ref_vouchers` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radreply`
--

LOCK TABLES `radreply` WRITE;
/*!40000 ALTER TABLE `radreply` DISABLE KEYS */;
/*!40000 ALTER TABLE `radreply` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radusergroup`
--

DROP TABLE IF EXISTS `radusergroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radusergroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `priority` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `username` (`username`(32))
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radusergroup`
--

LOCK TABLES `radusergroup` WRITE;
/*!40000 ALTER TABLE `radusergroup` DISABLE KEYS */;
INSERT INTO `radusergroup` VALUES (26,'unc-1hour','1Hour',5),(30,'1G-1Day','1G',5),(31,'1G-1Day','1Day',3),(35,'1G-1Day-BW-1Mbs','1G',1),(36,'1G-1Day-BW-1Mbs','1Day',5),(37,'1G-1Day-BW-1Mbs','BW-1Mbs',3),(38,'5M-every hour','5M-every-hour',5),(39,'1 Hour click to connect','1Hour per MAC daily',5),(40,'500M click to connect','500M per MAC daily',5);
/*!40000 ALTER TABLE `radusergroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `realm_notes`
--

DROP TABLE IF EXISTS `realm_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `realm_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `realm_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `realm_notes`
--

LOCK TABLES `realm_notes` WRITE;
/*!40000 ALTER TABLE `realm_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `realm_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `realms`
--

DROP TABLE IF EXISTS `realms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `realms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
  `icon_file_name` varchar(128) NOT NULL DEFAULT 'logo.jpg',
  `phone` varchar(14) NOT NULL DEFAULT '',
  `fax` varchar(14) NOT NULL DEFAULT '',
  `cell` varchar(14) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `url` varchar(128) NOT NULL DEFAULT '',
  `street_no` char(10) NOT NULL DEFAULT '',
  `street` char(50) NOT NULL DEFAULT '',
  `town_suburb` char(50) NOT NULL DEFAULT '',
  `city` char(50) NOT NULL DEFAULT '',
  `country` char(50) NOT NULL DEFAULT '',
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `twitter` varchar(255) NOT NULL DEFAULT '',
  `facebook` varchar(255) NOT NULL DEFAULT '',
  `youtube` varchar(255) NOT NULL DEFAULT '',
  `google_plus` varchar(255) NOT NULL DEFAULT '',
  `linkedin` varchar(255) NOT NULL DEFAULT '',
  `t_c_title` varchar(255) NOT NULL DEFAULT '',
  `t_c_content` text NOT NULL,
  `suffix` char(200) NOT NULL DEFAULT '',
  `suffix_permanent_users` tinyint(1) NOT NULL DEFAULT '0',
  `suffix_vouchers` tinyint(1) NOT NULL DEFAULT '0',
  `suffix_devices` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `realms`
--

LOCK TABLES `realms` WRITE;
/*!40000 ALTER TABLE `realms` DISABLE KEYS */;
INSERT INTO `realms` VALUES (36,'MESHdesk',1,'logo.jpg','','','','','','','','','','',0,0,44,'2017-03-23 11:57:12','2017-05-06 19:44:24','','','','','','','','meshdesk',1,0,1);
/*!40000 ALTER TABLE `realms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_login_user_realms`
--

DROP TABLE IF EXISTS `social_login_user_realms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `social_login_user_realms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `social_login_user_id` int(11) DEFAULT NULL,
  `realm_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_login_user_realms`
--

LOCK TABLES `social_login_user_realms` WRITE;
/*!40000 ALTER TABLE `social_login_user_realms` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_login_user_realms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_login_users`
--

DROP TABLE IF EXISTS `social_login_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `social_login_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider` enum('Facebook','Google','Twitter') DEFAULT 'Facebook',
  `uid` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `last_name` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `image` varchar(100) NOT NULL DEFAULT '',
  `locale` varchar(5) NOT NULL DEFAULT '',
  `timezone` tinyint(1) NOT NULL DEFAULT '0',
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female') DEFAULT 'male',
  `last_connect_time` datetime DEFAULT NULL,
  `extra_name` varchar(100) NOT NULL DEFAULT '',
  `extra_value` varchar(100) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_login_users`
--

LOCK TABLES `social_login_users` WRITE;
/*!40000 ALTER TABLE `social_login_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_login_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ssids`
--

DROP TABLE IF EXISTS `ssids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ssids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  `extra_name` varchar(100) NOT NULL DEFAULT '',
  `extra_value` varchar(100) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ssids`
--

LOCK TABLES `ssids` WRITE;
/*!40000 ALTER TABLE `ssids` DISABLE KEYS */;
INSERT INTO `ssids` VALUES (2,'Test1',0,44,'test extra name1','test extra value1','2015-04-16 21:40:48','2016-12-31 11:39:44'),(3,'Test2',0,182,'','','2015-04-17 08:57:21','2015-04-17 08:58:12'),(4,'Test3',1,182,'Another','Name','2015-04-17 08:57:44','2016-12-31 11:19:54');
/*!40000 ALTER TABLE `ssids` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag_notes`
--

DROP TABLE IF EXISTS `tag_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag_notes`
--

LOCK TABLES `tag_notes` WRITE;
/*!40000 ALTER TABLE `tag_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `tag_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES (1,'test',1,44,'2015-07-11 08:31:45','2015-07-11 08:31:56');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `template_attributes`
--

DROP TABLE IF EXISTS `template_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) DEFAULT NULL,
  `attribute` varchar(128) NOT NULL,
  `type` enum('Check','Reply') DEFAULT 'Check',
  `tooltip` varchar(200) NOT NULL,
  `unit` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `template_attributes`
--

LOCK TABLES `template_attributes` WRITE;
/*!40000 ALTER TABLE `template_attributes` DISABLE KEYS */;
INSERT INTO `template_attributes` VALUES (2,19,'koos','Check','Gooi hom','text_string','2013-02-09 10:50:35','2013-02-09 12:15:04'),(5,19,'koos','Reply','Hy werk lek','reply','2013-02-09 10:50:44','2013-02-09 16:26:08'),(6,19,'koos','Check','Skipm dit sal bemost wees','text_string','2013-02-09 10:50:45','2013-02-09 12:03:54'),(7,19,'Rd-Tag-A','Check','==Not Defined==','text_string','2013-02-09 16:55:18','2013-02-09 16:55:18'),(8,19,'Rd-Tag-B','Check','==Not Defined==','text_string','2013-02-09 16:55:26','2013-02-09 16:55:26'),(9,19,'Rd-Tag-C','Check','==Not Defined==','text_string','2013-02-09 16:55:32','2013-02-09 16:55:32');
/*!40000 ALTER TABLE `template_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `template_notes`
--

DROP TABLE IF EXISTS `template_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `template_notes`
--

LOCK TABLES `template_notes` WRITE;
/*!40000 ALTER TABLE `template_notes` DISABLE KEYS */;
INSERT INTO `template_notes` VALUES (20,18,46,'2013-02-08 06:07:59','2013-02-08 06:07:59'),(21,18,47,'2013-02-08 06:08:47','2013-02-08 06:08:47');
/*!40000 ALTER TABLE `template_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `templates`
--

DROP TABLE IF EXISTS `templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `available_to_siblings` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `templates`
--

LOCK TABLES `templates` WRITE;
/*!40000 ALTER TABLE `templates` DISABLE KEYS */;
INSERT INTO `templates` VALUES (19,'Lekker',0,58,'2013-02-08 10:22:52','2013-02-08 10:22:52'),(20,'Op die oor',0,44,'2013-02-08 12:55:44','2013-02-08 12:55:44');
/*!40000 ALTER TABLE `templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `top_up_transactions`
--

DROP TABLE IF EXISTS `top_up_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `top_up_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `permanent_user_id` int(11) DEFAULT NULL,
  `permanent_user` varchar(255) DEFAULT NULL,
  `top_up_id` int(11) DEFAULT NULL,
  `type` enum('data','time','days_to_use') DEFAULT 'data',
  `action` enum('create','update','delete') DEFAULT 'create',
  `radius_attribute` varchar(30) NOT NULL DEFAULT '',
  `old_value` varchar(30) DEFAULT NULL,
  `new_value` varchar(30) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `top_up_transactions`
--

LOCK TABLES `top_up_transactions` WRITE;
/*!40000 ALTER TABLE `top_up_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `top_up_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `top_ups`
--

DROP TABLE IF EXISTS `top_ups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `top_ups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `permanent_user_id` int(11) DEFAULT NULL,
  `data` bigint(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `days_to_use` int(11) DEFAULT NULL,
  `comment` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `type` enum('data','time','days_to_use') DEFAULT 'data',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `top_ups`
--

LOCK TABLES `top_ups` WRITE;
/*!40000 ALTER TABLE `top_ups` DISABLE KEYS */;
/*!40000 ALTER TABLE `top_ups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unknown_aps`
--

DROP TABLE IF EXISTS `unknown_aps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unknown_aps` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mac` varchar(255) NOT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `last_contact_from_ip` varchar(255) DEFAULT NULL,
  `last_contact` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `new_server` varchar(255) NOT NULL DEFAULT '',
  `new_server_status` enum('awaiting','fetched','replied') DEFAULT 'awaiting',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unknown_aps`
--

LOCK TABLES `unknown_aps` WRITE;
/*!40000 ALTER TABLE `unknown_aps` DISABLE KEYS */;
INSERT INTO `unknown_aps` VALUES (2,'78-A3-51-0B-BC-CA','Shenzhen # SHENZHEN ZHIBOTONG ELECTRONICS CO.,LTD','192.168.99.158','2016-09-20 15:43:54','2016-09-20 15:43:54','2016-09-20 15:43:54','','awaiting');
/*!40000 ALTER TABLE `unknown_aps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unknown_dynamic_clients`
--

DROP TABLE IF EXISTS `unknown_dynamic_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unknown_dynamic_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasidentifier` varchar(128) NOT NULL DEFAULT '',
  `calledstationid` varchar(128) NOT NULL DEFAULT '',
  `last_contact` datetime DEFAULT NULL,
  `last_contact_ip` varchar(128) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nasidentifier` (`nasidentifier`),
  UNIQUE KEY `calledstationid` (`calledstationid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unknown_dynamic_clients`
--

LOCK TABLES `unknown_dynamic_clients` WRITE;
/*!40000 ALTER TABLE `unknown_dynamic_clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `unknown_dynamic_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unknown_nodes`
--

DROP TABLE IF EXISTS `unknown_nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unknown_nodes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mac` varchar(255) NOT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `from_ip` varchar(15) NOT NULL DEFAULT '',
  `gateway` tinyint(1) NOT NULL DEFAULT '1',
  `last_contact` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `new_server` varchar(255) NOT NULL DEFAULT '',
  `new_server_status` enum('awaiting','fetched','replied') DEFAULT 'awaiting',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unknown_nodes`
--

LOCK TABLES `unknown_nodes` WRITE;
/*!40000 ALTER TABLE `unknown_nodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `unknown_nodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_notes`
--

DROP TABLE IF EXISTS `user_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_notes`
--

LOCK TABLES `user_notes` WRITE;
/*!40000 ALTER TABLE `user_notes` DISABLE KEYS */;
INSERT INTO `user_notes` VALUES (1,182,77,'2014-01-07 22:12:23','2014-01-07 22:12:23'),(10,182,87,'2017-01-02 06:17:25','2017-01-02 06:17:25');
/*!40000 ALTER TABLE `user_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_settings`
--

DROP TABLE IF EXISTS `user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_settings`
--

LOCK TABLES `user_settings` WRITE;
/*!40000 ALTER TABLE `user_settings` DISABLE KEYS */;
INSERT INTO `user_settings` VALUES (52,44,'map_zoom','18','2013-04-05 11:30:19','2015-07-10 03:33:42'),(53,44,'map_type','HYBRID','2013-04-05 11:30:19','2015-07-10 03:33:42'),(54,44,'map_lat','-25.737590494704','2013-04-05 11:30:19','2015-07-10 03:33:42'),(55,44,'map_lng','28.30269861188','2013-04-05 11:30:19','2015-07-10 03:33:42'),(56,44,'wallpaper','8.jpg','2013-04-06 13:51:50','2016-11-01 14:43:20'),(57,182,'map_zoom','18','2013-08-30 07:01:35','2013-08-30 07:01:35'),(58,182,'map_type','ROADMAP','2013-08-30 07:01:35','2013-08-30 07:01:35'),(59,182,'map_lat','42.33821464661343','2013-08-30 07:01:35','2013-08-30 07:01:35'),(60,182,'map_lng','-71.09557402167296','2013-08-30 07:01:35','2013-08-30 07:01:35'),(61,182,'wallpaper','1.jpg','2013-09-06 17:59:42','2016-05-04 04:59:04'),(62,44,'dynamic_client_map_zoom','18','2016-03-19 04:40:21','2016-03-19 04:40:21'),(63,44,'dynamic_client_map_type','ROADMAP','2016-03-19 04:40:21','2016-03-19 04:40:21'),(64,44,'dynamic_client_map_lat','42.33725929507717','2016-03-19 04:40:21','2016-03-19 04:40:21'),(65,44,'dynamic_client_map_lng','-71.09232318434691','2016-03-19 04:40:21','2016-03-19 04:40:21'),(86,44,'realm_id','36','2017-05-02 10:32:38','2017-05-02 10:32:38'),(87,44,'show_data_usage','0','2017-05-02 10:32:38','2017-05-02 10:32:38'),(88,44,'show_recent_failures','0','2017-05-02 10:32:38','2017-05-02 10:32:38'),(89,44,'compact_view','1','2017-05-02 10:32:38','2017-05-02 10:32:38');
/*!40000 ALTER TABLE `user_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_ssids`
--

DROP TABLE IF EXISTS `user_ssids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_ssids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `ssidname` varchar(64) NOT NULL DEFAULT '',
  `priority` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `username` (`username`(32))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_ssids`
--

LOCK TABLES `user_ssids` WRITE;
/*!40000 ALTER TABLE `user_ssids` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_ssids` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_stats`
--

DROP TABLE IF EXISTS `user_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  PRIMARY KEY (`id`),
  KEY `user_stats_index` (`radacct_id`,`username`,`realm`,`nasipaddress`,`nasidentifier`,`callingstationid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_stats`
--

LOCK TABLES `user_stats` WRITE;
/*!40000 ALTER TABLE `user_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(50) NOT NULL,
  `token` char(36) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `monitor` tinyint(1) NOT NULL DEFAULT '0',
  `country_id` int(11) DEFAULT NULL,
  `group_id` int(11) NOT NULL,
  `language_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `lft` int(11) DEFAULT NULL,
  `rght` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (44,'root','9b2b0416194bfdd0db089b9c09fad3163eae5383','b4c6ac81-8c7c-4802-b50a-0a6380555b50','root','','','','',1,0,4,8,4,NULL,1,4,'2012-12-10 13:14:13','2017-02-24 19:25:48'),(182,'admin_college','b0451947e4b0ee5b5ee981afe174e6630d72ff58','521dc362-81a4-4a34-8a0b-052f03662c24','','','','','',1,0,4,9,4,44,2,3,'2013-08-28 11:31:14','2017-05-21 14:48:15');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vouchers`
--

DROP TABLE IF EXISTS `vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `batch` varchar(128) NOT NULL DEFAULT '',
  `status` enum('new','used','depleted','expired') DEFAULT 'new',
  `perc_time_used` int(6) DEFAULT NULL,
  `perc_data_used` int(6) DEFAULT NULL,
  `last_accept_time` datetime DEFAULT NULL,
  `last_reject_time` datetime DEFAULT NULL,
  `last_accept_nas` varchar(128) DEFAULT NULL,
  `last_reject_nas` varchar(128) DEFAULT NULL,
  `last_reject_message` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `extra_name` varchar(100) NOT NULL DEFAULT '',
  `extra_value` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(30) NOT NULL DEFAULT '',
  `realm` varchar(50) NOT NULL DEFAULT '',
  `realm_id` int(11) DEFAULT NULL,
  `profile` varchar(50) NOT NULL DEFAULT '',
  `profile_id` int(11) DEFAULT NULL,
  `expire` varchar(10) NOT NULL DEFAULT '',
  `time_valid` varchar(10) NOT NULL DEFAULT '',
  `data_used` bigint(20) DEFAULT NULL,
  `data_cap` bigint(20) DEFAULT NULL,
  `time_used` int(12) DEFAULT NULL,
  `time_cap` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ak_vouchers` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vouchers`
--

LOCK TABLES `vouchers` WRITE;
/*!40000 ALTER TABLE `vouchers` DISABLE KEYS */;
INSERT INTO `vouchers` VALUES (1,'hardbikes','','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,44,'2017-05-04 00:46:10','2017-05-04 00:46:10','','','hardbikes','MESHdesk',36,'1G-1Day',15,'','',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `vouchers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-01-28 10:05:29
