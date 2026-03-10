# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.41-0ubuntu0.12.04.1)
# Database: vagrant
# Generation Time: 2015-02-27 16:36:22 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table address_book
# ------------------------------------------------------------

CREATE TABLE `address_book` (
  `address_book_id` int(11) NOT NULL AUTO_INCREMENT,
  `customers_id` int(11) NOT NULL DEFAULT '0',
  `entry_gender` varchar(1) NOT NULL DEFAULT '',
  `entry_company` varchar(32) DEFAULT NULL,
  `entry_firstname` varchar(32) NOT NULL DEFAULT '',
  `entry_lastname` varchar(32) NOT NULL DEFAULT '',
  `entry_street_address` varchar(64) NOT NULL DEFAULT '',
  `entry_suburb` varchar(32) NOT NULL DEFAULT '',
  `entry_postcode` varchar(10) NOT NULL DEFAULT '',
  `entry_city` varchar(32) NOT NULL DEFAULT '',
  `entry_state` varchar(32) NOT NULL DEFAULT '',
  `entry_country_id` int(11) NOT NULL DEFAULT '0',
  `entry_zone_id` int(11) NOT NULL DEFAULT '0',
  `entry_basename` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`address_book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table admins
# ------------------------------------------------------------

CREATE TABLE `admins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `role` varchar(16) NOT NULL DEFAULT '',
  `token` varchar(255) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table authorized_names
# ------------------------------------------------------------

CREATE TABLE `authorized_names` (
  `authorized_names_id` int(11) NOT NULL AUTO_INCREMENT,
  `customers_id` int(11) NOT NULL DEFAULT '0',
  `authorized_firstname` varchar(20) NOT NULL DEFAULT '',
  `authorized_lastname` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`authorized_names_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5395 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table countries
# ------------------------------------------------------------

CREATE TABLE `countries` (
  `countries_id` int(11) NOT NULL AUTO_INCREMENT,
  `countries_name` varchar(64) NOT NULL DEFAULT '',
  `countries_iso_code_2` char(2) NOT NULL DEFAULT '',
  `countries_iso_code_3` char(3) NOT NULL DEFAULT '',
  `address_format_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`countries_id`),
  KEY `IDX_COUNTRIES_NAME` (`countries_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;

INSERT INTO `countries` (`countries_id`, `countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `address_format_id`)
VALUES
	(1,'Afghanistan','AF','AFG',1),
	(2,'Albania','AL','ALB',1),
	(3,'Algeria','DZ','DZA',1),
	(4,'American Samoa','AS','ASM',1),
	(5,'Andorra','AD','AND',1),
	(6,'Angola','AO','AGO',1),
	(7,'Anguilla','AI','AIA',1),
	(8,'Antarctica','AQ','ATA',1),
	(9,'Antigua and Barbuda','AG','ATG',1),
	(10,'Argentina','AR','ARG',1),
	(11,'Armenia','AM','ARM',1),
	(12,'Aruba','AW','ABW',1),
	(13,'Australia','AU','AUS',1),
	(14,'Austria','AT','AUT',5),
	(15,'Azerbaijan','AZ','AZE',1),
	(16,'Bahamas','BS','BHS',1),
	(17,'Bahrain','BH','BHR',1),
	(18,'Bangladesh','BD','BGD',1),
	(19,'Barbados','BB','BRB',1),
	(20,'Belarus','BY','BLR',1),
	(21,'Belgium','BE','BEL',1),
	(22,'Belize','BZ','BLZ',1),
	(23,'Benin','BJ','BEN',1),
	(24,'Bermuda','BM','BMU',1),
	(25,'Bhutan','BT','BTN',1),
	(26,'Bolivia','BO','BOL',1),
	(27,'Bosnia and Herzegowina','BA','BIH',1),
	(28,'Botswana','BW','BWA',1),
	(29,'Bouvet Island','BV','BVT',1),
	(30,'Brazil','BR','BRA',1),
	(31,'British Indian Ocean Territory','IO','IOT',1),
	(32,'Brunei Darussalam','BN','BRN',1),
	(33,'Bulgaria','BG','BGR',1),
	(34,'Burkina Faso','BF','BFA',1),
	(35,'Burundi','BI','BDI',1),
	(36,'Cambodia','KH','KHM',1),
	(37,'Cameroon','CM','CMR',1),
	(38,'Canada','CA','CAN',1),
	(39,'Cape Verde','CV','CPV',1),
	(40,'Cayman Islands','KY','CYM',1),
	(41,'Central African Republic','CF','CAF',1),
	(42,'Chad','TD','TCD',1),
	(43,'Chile','CL','CHL',1),
	(44,'China','CN','CHN',1),
	(45,'Christmas Island','CX','CXR',1),
	(46,'Cocos (Keeling) Islands','CC','CCK',1),
	(47,'Colombia','CO','COL',1),
	(48,'Comoros','KM','COM',1),
	(49,'Congo','CG','COG',1),
	(50,'Cook Islands','CK','COK',1),
	(51,'Costa Rica','CR','CRI',1),
	(52,'Cote D\'Ivoire','CI','CIV',1),
	(53,'Croatia','HR','HRV',1),
	(54,'Cuba','CU','CUB',1),
	(55,'Cyprus','CY','CYP',1),
	(56,'Czech Republic','CZ','CZE',1),
	(57,'Denmark','DK','DNK',1),
	(58,'Djibouti','DJ','DJI',1),
	(59,'Dominica','DM','DMA',1),
	(60,'Dominican Republic','DO','DOM',1),
	(61,'East Timor','TP','TMP',1),
	(62,'Ecuador','EC','ECU',1),
	(63,'Egypt','EG','EGY',1),
	(64,'El Salvador','SV','SLV',1),
	(65,'Equatorial Guinea','GQ','GNQ',1),
	(66,'Eritrea','ER','ERI',1),
	(67,'Estonia','EE','EST',1),
	(68,'Ethiopia','ET','ETH',1),
	(69,'Falkland Islands (Malvinas)','FK','FLK',1),
	(70,'Faroe Islands','FO','FRO',1),
	(71,'Fiji','FJ','FJI',1),
	(72,'Finland','FI','FIN',1),
	(73,'France','FR','FRA',1),
	(74,'France, Metropolitan','FX','FXX',1),
	(75,'French Guiana','GF','GUF',1),
	(76,'French Polynesia','PF','PYF',1),
	(77,'French Southern Territories','TF','ATF',1),
	(78,'Gabon','GA','GAB',1),
	(79,'Gambia','GM','GMB',1),
	(80,'Georgia','GE','GEO',1),
	(81,'Germany','DE','DEU',5),
	(82,'Ghana','GH','GHA',1),
	(83,'Gibraltar','GI','GIB',1),
	(84,'Greece','GR','GRC',1),
	(85,'Greenland','GL','GRL',1),
	(86,'Grenada','GD','GRD',1),
	(87,'Guadeloupe','GP','GLP',1),
	(88,'Guam','GU','GUM',1),
	(89,'Guatemala','GT','GTM',1),
	(90,'Guinea','GN','GIN',1),
	(91,'Guinea-bissau','GW','GNB',1),
	(92,'Guyana','GY','GUY',1),
	(93,'Haiti','HT','HTI',1),
	(94,'Heard and Mc Donald Islands','HM','HMD',1),
	(95,'Honduras','HN','HND',1),
	(96,'Hong Kong','HK','HKG',1),
	(97,'Hungary','HU','HUN',1),
	(98,'Iceland','IS','ISL',1),
	(99,'India','IN','IND',1),
	(100,'Indonesia','ID','IDN',1),
	(101,'Iran (Islamic Republic of)','IR','IRN',1),
	(102,'Iraq','IQ','IRQ',1),
	(103,'Ireland','IE','IRL',1),
	(104,'Israel','IL','ISR',1),
	(105,'Italy','IT','ITA',1),
	(106,'Jamaica','JM','JAM',1),
	(107,'Japan','JP','JPN',1),
	(108,'Jordan','JO','JOR',1),
	(109,'Kazakhstan','KZ','KAZ',1),
	(110,'Kenya','KE','KEN',1),
	(111,'Kiribati','KI','KIR',1),
	(112,'Korea, Democratic People\'s Republic of','KP','PRK',1),
	(113,'Korea, Republic of','KR','KOR',1),
	(114,'Kuwait','KW','KWT',1),
	(115,'Kyrgyzstan','KG','KGZ',1),
	(116,'Lao People\'s Democratic Republic','LA','LAO',1),
	(117,'Latvia','LV','LVA',1),
	(118,'Lebanon','LB','LBN',1),
	(119,'Lesotho','LS','LSO',1),
	(120,'Liberia','LR','LBR',1),
	(121,'Libyan Arab Jamahiriya','LY','LBY',1),
	(122,'Liechtenstein','LI','LIE',1),
	(123,'Lithuania','LT','LTU',1),
	(124,'Luxembourg','LU','LUX',1),
	(125,'Macau','MO','MAC',1),
	(126,'Macedonia, The Former Yugoslav Republic of','MK','MKD',1),
	(127,'Madagascar','MG','MDG',1),
	(128,'Malawi','MW','MWI',1),
	(129,'Malaysia','MY','MYS',1),
	(130,'Maldives','MV','MDV',1),
	(131,'Mali','ML','MLI',1),
	(132,'Malta','MT','MLT',1),
	(133,'Marshall Islands','MH','MHL',1),
	(134,'Martinique','MQ','MTQ',1),
	(135,'Mauritania','MR','MRT',1),
	(136,'Mauritius','MU','MUS',1),
	(137,'Mayotte','YT','MYT',1),
	(138,'Mexico','MX','MEX',1),
	(139,'Micronesia, Federated States of','FM','FSM',1),
	(140,'Moldova, Republic of','MD','MDA',1),
	(141,'Monaco','MC','MCO',1),
	(142,'Mongolia','MN','MNG',1),
	(143,'Montserrat','MS','MSR',1),
	(144,'Morocco','MA','MAR',1),
	(145,'Mozambique','MZ','MOZ',1),
	(146,'Myanmar','MM','MMR',1),
	(147,'Namibia','NA','NAM',1),
	(148,'Nauru','NR','NRU',1),
	(149,'Nepal','NP','NPL',1),
	(150,'Netherlands','NL','NLD',1),
	(151,'Netherlands Antilles','AN','ANT',1),
	(152,'New Caledonia','NC','NCL',1),
	(153,'New Zealand','NZ','NZL',1),
	(154,'Nicaragua','NI','NIC',1),
	(155,'Niger','NE','NER',1),
	(156,'Nigeria','NG','NGA',1),
	(157,'Niue','NU','NIU',1),
	(158,'Norfolk Island','NF','NFK',1),
	(159,'Northern Mariana Islands','MP','MNP',1),
	(160,'Norway','NO','NOR',1),
	(161,'Oman','OM','OMN',1),
	(162,'Pakistan','PK','PAK',1),
	(163,'Palau','PW','PLW',1),
	(164,'Panama','PA','PAN',1),
	(165,'Papua New Guinea','PG','PNG',1),
	(166,'Paraguay','PY','PRY',1),
	(167,'Peru','PE','PER',1),
	(168,'Philippines','PH','PHL',1),
	(169,'Pitcairn','PN','PCN',1),
	(170,'Poland','PL','POL',1),
	(171,'Portugal','PT','PRT',1),
	(172,'Puerto Rico','PR','PRI',1),
	(173,'Qatar','QA','QAT',1),
	(174,'Reunion','RE','REU',1),
	(175,'Romania','RO','ROM',1),
	(176,'Russian Federation','RU','RUS',1),
	(177,'Rwanda','RW','RWA',1),
	(178,'Saint Kitts and Nevis','KN','KNA',1),
	(179,'Saint Lucia','LC','LCA',1),
	(180,'Saint Vincent and the Grenadines','VC','VCT',1),
	(181,'Samoa','WS','WSM',1),
	(182,'San Marino','SM','SMR',1),
	(183,'Sao Tome and Principe','ST','STP',1),
	(184,'Saudi Arabia','SA','SAU',1),
	(185,'Senegal','SN','SEN',1),
	(186,'Seychelles','SC','SYC',1),
	(187,'Sierra Leone','SL','SLE',1),
	(188,'Singapore','SG','SGP',4),
	(189,'Slovakia (Slovak Republic)','SK','SVK',1),
	(190,'Slovenia','SI','SVN',1),
	(191,'Solomon Islands','SB','SLB',1),
	(192,'Somalia','SO','SOM',1),
	(193,'South Africa','ZA','ZAF',1),
	(194,'South Georgia and the South Sandwich Islands','GS','SGS',1),
	(195,'Spain','ES','ESP',3),
	(196,'Sri Lanka','LK','LKA',1),
	(197,'St. Helena','SH','SHN',1),
	(198,'St. Pierre and Miquelon','PM','SPM',1),
	(199,'Sudan','SD','SDN',1),
	(200,'Suriname','SR','SUR',1),
	(201,'Svalbard and Jan Mayen Islands','SJ','SJM',1),
	(202,'Swaziland','SZ','SWZ',1),
	(203,'Sweden','SE','SWE',1),
	(204,'Switzerland','CH','CHE',1),
	(205,'Syrian Arab Republic','SY','SYR',1),
	(206,'Taiwan','TW','TWN',1),
	(207,'Tajikistan','TJ','TJK',1),
	(208,'Tanzania, United Republic of','TZ','TZA',1),
	(209,'Thailand','TH','THA',1),
	(210,'Togo','TG','TGO',1),
	(211,'Tokelau','TK','TKL',1),
	(212,'Tonga','TO','TON',1),
	(213,'Trinidad and Tobago','TT','TTO',1),
	(214,'Tunisia','TN','TUN',1),
	(215,'Turkey','TR','TUR',1),
	(216,'Turkmenistan','TM','TKM',1),
	(217,'Turks and Caicos Islands','TC','TCA',1),
	(218,'Tuvalu','TV','TUV',1),
	(219,'Uganda','UG','UGA',1),
	(220,'Ukraine','UA','UKR',1),
	(221,'United Arab Emirates','AE','ARE',1),
	(222,'United Kingdom','GB','GBR',1),
	(223,'United States','US','USA',2),
	(224,'United States Minor Outlying Islands','UM','UMI',1),
	(225,'Uruguay','UY','URY',1),
	(226,'Uzbekistan','UZ','UZB',1),
	(227,'Vanuatu','VU','VUT',1),
	(228,'Vatican City State (Holy See)','VA','VAT',1),
	(229,'Venezuela','VE','VEN',1),
	(230,'Viet Nam','VN','VNM',1),
	(231,'Virgin Islands (British)','VG','VGB',1),
	(232,'Virgin Islands (U.S.)','VI','VIR',1),
	(233,'Wallis and Futuna Islands','WF','WLF',1),
	(234,'Western Sahara','EH','ESH',1),
	(235,'Yemen','YE','YEM',1),
	(236,'Yugoslavia','YU','YUG',1),
	(237,'Zaire','ZR','ZAR',1),
	(238,'Zambia','ZM','ZMB',1),
	(239,'Zimbabwe','ZW','ZWE',1),
	(250,'USA','US','USA',2);

/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

CREATE TABLE `cron_tasks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `jobtype` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'task / method',
  `created` datetime NOT NULL,
  `notbefore` datetime DEFAULT NULL,
  `fetched` datetime DEFAULT NULL,
  `completed` datetime DEFAULT NULL,
  `failed` int(3) NOT NULL DEFAULT '0',
  `failure_message` text COLLATE utf8_unicode_ci,
  `workerkey` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `interval` int(10) NOT NULL DEFAULT '0' COMMENT 'in minutes',
  `status` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `custom_orders` (
  `custom_orders_id` int(11) NOT NULL AUTO_INCREMENT,
  `customers_id` int(20) NOT NULL DEFAULT '0',
  `tracking_id` varchar(30) NOT NULL DEFAULT '0',
  `billing_id` varchar(20) NOT NULL DEFAULT '0',
  `orders_id` varchar(30) NOT NULL DEFAULT '0',
  `package_status` int(11) NOT NULL DEFAULT '0',
  `package_repack` varchar(4) NOT NULL DEFAULT '',
  `insurance_fee` varchar(10) NOT NULL DEFAULT '',
  `insurance_coverage` varchar(10) NOT NULL DEFAULT '',
  `mail_class` varchar(15) NOT NULL,
  `instructions` text NOT NULL,
  `order_add_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`custom_orders_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8116 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

# Dump of table customers
# ------------------------------------------------------------

CREATE TABLE `customers` (
  `customers_id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_id` varchar(8) NOT NULL DEFAULT '',
  `customers_gender` varchar(1) NOT NULL DEFAULT '',
  `customers_firstname` varchar(32) NOT NULL DEFAULT '',
  `customers_lastname` varchar(32) NOT NULL DEFAULT '',
  `customers_dob` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `customers_email_address` varchar(96) NOT NULL DEFAULT '',
  `customers_default_address_id` int(11) DEFAULT NULL,
  `customers_shipping_address_id` int(11) NOT NULL DEFAULT '0',
  `customers_emergency_address_id` int(11) NOT NULL DEFAULT '0',
  `customers_telephone` varchar(32) NOT NULL DEFAULT '',
  `customers_fax` varchar(32) DEFAULT NULL,
  `customers_password` varchar(40) NOT NULL DEFAULT '',
  `customers_newsletter` varchar(1) DEFAULT NULL,
  `customers_referral_id` varchar(64) NOT NULL DEFAULT '',
  `customers_referral_points` int(11) NOT NULL DEFAULT '0',
  `cc_firstname` varchar(64) NOT NULL DEFAULT '',
  `cc_lastname` varchar(64) NOT NULL DEFAULT '',
  `cc_number` varchar(32) NOT NULL DEFAULT '',
  `cc_number_encrypted` text NOT NULL,
  `cc_expires_month` varchar(2) NOT NULL DEFAULT '',
  `cc_expires_year` varchar(2) NOT NULL DEFAULT '',
  `cc_cvv` text NOT NULL,
  `card_token` varchar(32) NOT NULL DEFAULT '',
  `insurance_amount` decimal(15,2) NOT NULL DEFAULT '50.00',
  `insurance_fee` decimal(15,2) NOT NULL DEFAULT '1.65',
  `backup_email_address` varchar(255) NOT NULL DEFAULT '',
  `customers_referral_referred` varchar(64) NOT NULL DEFAULT '',
  `referral_status` int(1) NOT NULL DEFAULT '0',
  `default_postal_type` varchar(64) NOT NULL DEFAULT 'apobox_direct',
  `billing_type` varchar(15) NOT NULL DEFAULT 'cc',
  `invoicing_authorized` tinyint(1) NOT NULL DEFAULT '1',
  `editable_max_amount` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`customers_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table customers_info
# ------------------------------------------------------------

CREATE TABLE `customers_info` (
  `customers_info_id` int(11) NOT NULL DEFAULT '0',
  `customers_info_date_of_last_logon` datetime DEFAULT NULL,
  `customers_info_number_of_logons` int(5) DEFAULT NULL,
  `customers_info_date_account_created` datetime DEFAULT NULL,
  `customers_info_date_account_last_modified` datetime DEFAULT NULL,
  `customers_info_source_id` int(11) NOT NULL,
  `global_product_notifications` int(1) DEFAULT '0',
  `IP_signup` varchar(15) NOT NULL,
  `IP_lastlogon` varchar(15) NOT NULL,
  `IP_cc_update` varchar(15) NOT NULL,
  `IP_addressbook_update` varchar(15) NOT NULL,
  PRIMARY KEY (`customers_info_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

# Dump of table insurance
# ------------------------------------------------------------

CREATE TABLE `insurance` (
  `insurance_id` int(11) NOT NULL AUTO_INCREMENT,
  `amount_from` decimal(15,2) NOT NULL DEFAULT '0.00',
  `amount_to` decimal(15,2) NOT NULL DEFAULT '0.00',
  `insurance_fee` decimal(15,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`insurance_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED;

LOCK TABLES `insurance` WRITE;
/*!40000 ALTER TABLE `insurance` DISABLE KEYS */;

INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`)
VALUES
(1, 0.01, 50.00, 1.75),
(2, 50.01, 100.00, 2.25),
(3, 100.01, 200.00, 2.75),
(4, 200.01, 300.00, 4.70),
(5, 300.01, 400.00, 5.70),
(6, 400.01, 500.00, 6.70),
(7, 500.01, 600.00, 7.70),
(15, 600.01, 700.00, 8.70),
(16, 700.01, 800.00, 9.70),
(17, 800.01, 900.00, 10.70),
(18, 900.01, 1000.00, 11.70),
(19, 1000.01, 1100.00, 12.70),
(20, 1100.01, 1200.00, 13.70),
(21, 1200.01, 1300.00, 14.70),
(22, 1300.01, 1400.00, 15.70),
(23, 1400.01, 1500.00, 16.70),
(24, 1500.01, 1600.00, 17.70),
(25, 1600.01, 1700.00, 18.70),
(26, 1700.01, 1800.00, 19.70),
(27, 1800.01, 1900.00, 20.70),
(28, 1900.01, 2000.00, 21.70),
(29, 2000.01, 2100.00, 22.70),
(30, 2100.01, 2200.00, 23.70),
(31, 2200.01, 2300.00, 24.70),
(32, 2300.01, 2400.00, 25.70),
(33, 2400.01, 2500.00, 26.70),
(34, 2500.01, 2600.00, 27.70),
(35, 2600.01, 2700.00, 28.70),
(36, 2700.01, 2800.00, 29.70),
(37, 2800.01, 2900.00, 30.70),
(38, 2900.01, 3000.00, 31.70),
(39, 3000.01, 3100.00, 32.70),
(40, 3100.01, 3200.00, 33.70),
(41, 3200.01, 3300.00, 34.70),
(42, 3300.01, 3400.00, 35.70),
(43, 3400.01, 3500.00, 36.70),
(44, 3500.01, 3600.00, 37.70),
(45, 3600.01, 3700.00, 38.70),
(46, 3700.01, 3800.00, 39.70),
(47, 3800.01, 3900.00, 40.70),
(48, 3900.01, 4000.00, 41.70),
(49, 4000.01, 4100.00, 42.70),
(50, 4100.01, 4200.00, 43.70),
(51, 4200.01, 4300.00, 44.70),
(52, 4300.01, 4400.00, 45.70),
(53, 4400.01, 4500.00, 46.70),
(54, 4500.01, 4600.00, 47.70),
(55, 4600.01, 4700.00, 48.70),
(56, 4700.01, 4800.00, 49.70),
(57, 4800.01, 4900.00, 50.70),
(58, 4900.01, 5000.00, 51.70);

/*!40000 ALTER TABLE `insurance` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table orders
# ------------------------------------------------------------

CREATE TABLE `orders` (
  `orders_id` bigint(17) unsigned NOT NULL AUTO_INCREMENT,
  `customers_id` int(11) NOT NULL DEFAULT '0',
  `customers_name` varchar(64) NOT NULL DEFAULT '',
  `customers_company` varchar(32) DEFAULT NULL,
  `customers_street_address` varchar(64) NOT NULL DEFAULT '',
  `customers_suburb` varchar(32) DEFAULT NULL,
  `customers_city` varchar(32) NOT NULL DEFAULT '',
  `customers_postcode` varchar(10) NOT NULL DEFAULT '',
  `customers_state` varchar(32) DEFAULT NULL,
  `customers_country` varchar(32) NOT NULL DEFAULT '',
  `customers_telephone` varchar(32) NOT NULL DEFAULT '',
  `customers_email_address` varchar(96) NOT NULL DEFAULT '',
  `customers_address_format_id` int(5) NOT NULL DEFAULT '0',
  `delivery_name` varchar(64) NOT NULL DEFAULT '',
  `delivery_company` varchar(32) DEFAULT NULL,
  `delivery_street_address` varchar(64) NOT NULL DEFAULT '',
  `delivery_suburb` varchar(32) DEFAULT NULL,
  `delivery_city` varchar(32) NOT NULL DEFAULT '',
  `delivery_postcode` varchar(10) NOT NULL DEFAULT '',
  `delivery_state` varchar(32) DEFAULT NULL,
  `delivery_country` varchar(32) NOT NULL DEFAULT '',
  `delivery_address_format_id` int(5) NOT NULL DEFAULT '0',
  `billing_name` varchar(64) NOT NULL DEFAULT '',
  `billing_company` varchar(32) DEFAULT NULL,
  `billing_street_address` varchar(64) NOT NULL DEFAULT '',
  `billing_suburb` varchar(32) DEFAULT NULL,
  `billing_city` varchar(32) NOT NULL DEFAULT '',
  `billing_postcode` varchar(10) NOT NULL DEFAULT '',
  `billing_state` varchar(32) DEFAULT NULL,
  `billing_country` varchar(32) NOT NULL DEFAULT '',
  `billing_address_format_id` int(5) NOT NULL DEFAULT '0',
  `payment_method` varchar(32) NOT NULL DEFAULT '',
  `cc_type` varchar(20) DEFAULT NULL,
  `cc_owner` varchar(64) DEFAULT NULL,
  `cc_number` varchar(32) DEFAULT NULL,
  `cc_expires` varchar(4) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `last_modified` datetime DEFAULT NULL,
  `date_purchased` datetime DEFAULT NULL,
  `turnaround_sec` varchar(15) DEFAULT NULL,
  `orders_status` int(5) NOT NULL DEFAULT '0',
  `orders_date_finished` datetime DEFAULT NULL,
  `ups_track_num` varchar(40) DEFAULT NULL,
  `usps_track_num` varchar(40) DEFAULT NULL,
  `usps_track_num_in` varchar(40) DEFAULT NULL,
  `fedex_track_num` varchar(40) DEFAULT NULL,
  `fedex_freight_track_num` varchar(40) DEFAULT NULL,
  `dhl_track_num` varchar(40) DEFAULT NULL,
  `currency` char(3) DEFAULT NULL,
  `currency_value` decimal(14,6) DEFAULT NULL,
  `shipping_tax` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `billing_status` tinyint(1) NOT NULL DEFAULT '0',
  `qbi_imported` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `width` varchar(10) DEFAULT NULL,
  `length` varchar(10) DEFAULT NULL,
  `depth` varchar(10) DEFAULT NULL,
  `weight_oz` varchar(10) DEFAULT NULL,
  `mail_class` varchar(15) DEFAULT NULL,
  `package_type` varchar(20) DEFAULT NULL,
  `NonMachinable` varchar(10) DEFAULT NULL,
  `OversizeRate` varchar(10) DEFAULT NULL,
  `BalloonRate` varchar(10) DEFAULT NULL,
  `package_flow` varchar(5) DEFAULT NULL,
  `shipped_from` varchar(40) DEFAULT NULL,
  `insurance_coverage` varchar(10) DEFAULT NULL,
  `warehouse` varchar(15) DEFAULT NULL,
  `postage_id` varchar(25) NOT NULL,
  `trans_id` varchar(25) NOT NULL,
  `moved_to_invoice` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`orders_id`),
  KEY `qbi_imported` (`qbi_imported`),
  KEY `customers_state` (`customers_state`),
  KEY `orders_status` (`orders_status`),
  KEY `customers_postcode` (`customers_postcode`),
  KEY `customers_postcode_2` (`customers_postcode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table orders_status
# ------------------------------------------------------------

CREATE TABLE `orders_status` (
  `orders_status_id` int(11) NOT NULL DEFAULT '0',
  `language_id` int(11) NOT NULL DEFAULT '1',
  `orders_status_name` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`orders_status_id`,`language_id`),
  KEY `idx_orders_status_name` (`orders_status_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

LOCK TABLES `orders_status` WRITE;
/*!40000 ALTER TABLE `orders_status` DISABLE KEYS */;

INSERT INTO `orders_status` (`orders_status_id`, `language_id`, `orders_status_name`)
VALUES
	(1,1,'Warehouse'),
	(2,1,'Awaiting Payment'),
	(3,1,'Shipped'),
	(4,1,'Paid'),
	(5,1,'Returned');

/*!40000 ALTER TABLE `orders_status` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table orders_status_history
# ------------------------------------------------------------

CREATE TABLE `orders_status_history` (
  `orders_status_history_id` int(17) NOT NULL AUTO_INCREMENT,
  `orders_id` bigint(17) unsigned NOT NULL DEFAULT '0',
  `orders_status_id` int(5) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `customer_notified` int(1) DEFAULT '0',
  `comments` text,
  PRIMARY KEY (`orders_status_history_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

# Dump of table orders_total
# ------------------------------------------------------------

CREATE TABLE `orders_total` (
  `orders_total_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orders_id` bigint(17) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `text` varchar(255) NOT NULL DEFAULT '',
  `value` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `class` varchar(32) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`orders_total_id`),
  KEY `idx_orders_total_orders_id` (`orders_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1928535 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

# Dump of table password_requests
# ------------------------------------------------------------

CREATE TABLE `password_requests` (
  `id` char(36) NOT NULL DEFAULT '',
  `customer_id` int(11) DEFAULT NULL COMMENT 'NULL if record does not belong to a customer',
  `admin_id` int(11) DEFAULT NULL COMMENT 'NULL if record does not belong to an admin',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `queued_tasks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `jobtype` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  `group` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL,
  `notbefore` datetime DEFAULT NULL,
  `fetched` datetime DEFAULT NULL,
  `progress` float(3,2) DEFAULT NULL,
  `completed` datetime DEFAULT NULL,
  `failed` int(3) NOT NULL DEFAULT '0',
  `failure_message` text COLLATE utf8_unicode_ci,
  `workerkey` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `tracking` (
	  `tracking_id` varchar(40) NOT NULL DEFAULT '',
	  `warehouse` varchar(30) NOT NULL DEFAULT 'Bancroft',
	  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	  `comments` varchar(200) DEFAULT NULL,
	  `shipped` varchar(5) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`tracking_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

# Dump of table zones
# ------------------------------------------------------------

CREATE TABLE `zones` (
  `zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `zone_country_id` int(11) NOT NULL DEFAULT '0',
  `zone_code` varchar(32) NOT NULL DEFAULT '',
  `zone_name` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`zone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

LOCK TABLES `zones` WRITE;
/*!40000 ALTER TABLE `zones` DISABLE KEYS */;

INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`)
VALUES
	(1,223,'AL','Alabama'),
	(2,223,'AK','Alaska'),
	(3,223,'AS','American Samoa'),
	(4,223,'AZ','Arizona'),
	(5,223,'AR','Arkansas'),
	(7,223,'AA','AA'),
	(9,223,'AE','AE'),
	(11,223,'AP','AP'),
	(12,223,'CA','California'),
	(13,223,'CO','Colorado'),
	(14,223,'CT','Connecticut'),
	(15,223,'DE','Delaware'),
	(16,223,'DC','District of Columbia'),
	(17,223,'FM','Federated States Of Micronesia'),
	(18,223,'FL','Florida'),
	(19,223,'GA','Georgia'),
	(20,223,'GU','Guam'),
	(21,223,'HI','Hawaii'),
	(22,223,'ID','Idaho'),
	(23,223,'IL','Illinois'),
	(24,223,'IN','Indiana'),
	(25,223,'IA','Iowa'),
	(26,223,'KS','Kansas'),
	(27,223,'KY','Kentucky'),
	(28,223,'LA','Louisiana'),
	(29,223,'ME','Maine'),
	(30,223,'MH','Marshall Islands'),
	(31,223,'MD','Maryland'),
	(32,223,'MA','Massachusetts'),
	(33,223,'MI','Michigan'),
	(34,223,'MN','Minnesota'),
	(35,223,'MS','Mississippi'),
	(36,223,'MO','Missouri'),
	(37,223,'MT','Montana'),
	(38,223,'NE','Nebraska'),
	(39,223,'NV','Nevada'),
	(40,223,'NH','New Hampshire'),
	(41,223,'NJ','New Jersey'),
	(42,223,'NM','New Mexico'),
	(43,223,'NY','New York'),
	(44,223,'NC','North Carolina'),
	(45,223,'ND','North Dakota'),
	(46,223,'MP','Northern Mariana Islands'),
	(47,223,'OH','Ohio'),
	(48,223,'OK','Oklahoma'),
	(49,223,'OR','Oregon'),
	(50,223,'PW','Palau'),
	(51,223,'PA','Pennsylvania'),
	(52,223,'PR','Puerto Rico'),
	(53,223,'RI','Rhode Island'),
	(54,223,'SC','South Carolina'),
	(55,223,'SD','South Dakota'),
	(56,223,'TN','Tennessee'),
	(57,223,'TX','Texas'),
	(58,223,'UT','Utah'),
	(59,223,'VT','Vermont'),
	(60,223,'VI','Virgin Islands'),
	(61,223,'VA','Virginia'),
	(62,223,'WA','Washington'),
	(63,223,'WV','West Virginia'),
	(64,223,'WI','Wisconsin'),
	(65,223,'WY','Wyoming'),
	(66,38,'AB','Alberta'),
	(67,38,'BC','British Columbia'),
	(68,38,'MB','Manitoba'),
	(69,38,'NF','Newfoundland'),
	(70,38,'NB','New Brunswick'),
	(71,38,'NS','Nova Scotia'),
	(72,38,'NT','Northwest Territories'),
	(73,38,'NU','Nunavut'),
	(74,38,'ON','Ontario'),
	(75,38,'PE','Prince Edward Island'),
	(76,38,'QC','Quebec'),
	(77,38,'SK','Saskatchewan'),
	(78,38,'YT','Yukon Territory'),
	(79,81,'NDS','Niedersachsen'),
	(80,81,'BAW',''),
	(81,81,'BAY','Bayern'),
	(82,81,'BER','Berlin'),
	(83,81,'BRG','Brandenburg'),
	(84,81,'BRE','Bremen'),
	(85,81,'HAM','Hamburg'),
	(86,81,'HES','Hessen'),
	(87,81,'MEC','Mecklenburg-Vorpommern'),
	(88,81,'NRW','Nordrhein-Westfalen'),
	(89,81,'RHE','Rheinland-Pfalz'),
	(90,81,'SAR','Saarland'),
	(91,81,'SAS','Sachsen'),
	(92,81,'SAC','Sachsen-Anhalt'),
	(93,81,'SCN','Schleswig-Holstein'),
	(94,81,'THE',''),
	(95,14,'WI','Wien'),
	(96,14,'NO',''),
	(97,14,'OO',''),
	(98,14,'SB','Salzburg'),
	(99,14,'KN',''),
	(100,14,'ST','Steiermark'),
	(101,14,'TI','Tirol'),
	(102,14,'BL','Burgenland'),
	(103,14,'VB','Voralberg'),
	(104,204,'AG','Aargau'),
	(105,204,'AI','Appenzell Innerrhoden'),
	(106,204,'AR','Appenzell Ausserrhoden'),
	(107,204,'BE','Bern'),
	(108,204,'BL','Basel-Landschaft'),
	(109,204,'BS','Basel-Stadt'),
	(110,204,'FR','Freiburg'),
	(111,204,'GE','Genf'),
	(112,204,'GL','Glarus'),
	(113,204,'JU',''),
	(114,204,'JU','Jura'),
	(115,204,'LU','Luzern'),
	(116,204,'NE','Neuenburg'),
	(117,204,'NW','Nidwalden'),
	(118,204,'OW','Obwalden'),
	(119,204,'SG','St. Gallen'),
	(120,204,'SH','Schaffhausen'),
	(121,204,'SO','Solothurn'),
	(122,204,'SZ','Schwyz'),
	(123,204,'TG','Thurgau'),
	(124,204,'TI','Tessin'),
	(125,204,'UR','Uri'),
	(126,204,'VD','Waadt'),
	(127,204,'VS','Wallis'),
	(128,204,'ZG','Zug'),
	(129,204,'ZH',''),
	(130,195,'',''),
	(131,195,'Alava','Alava'),
	(132,195,'Albacete','Albacete'),
	(133,195,'Alicante','Alicante'),
	(134,195,'Almeria','Almeria'),
	(135,195,'Asturias','Asturias'),
	(136,195,'Avila','Avila'),
	(137,195,'Badajoz','Badajoz'),
	(138,195,'Baleares','Baleares'),
	(139,195,'Barcelona','Barcelona'),
	(140,195,'Burgos','Burgos'),
	(141,195,'Caceres','Caceres'),
	(142,195,'Cadiz','Cadiz'),
	(143,195,'Cantabria','Cantabria'),
	(144,195,'Castellon','Castellon'),
	(145,195,'Ceuta','Ceuta'),
	(146,195,'Ciudad Real','Ciudad Real'),
	(147,195,'Cordoba','Cordoba'),
	(148,195,'Cuenca','Cuenca'),
	(149,195,'Girona','Girona'),
	(150,195,'Granada','Granada'),
	(151,195,'Guadalajara','Guadalajara'),
	(152,195,'Guipuzcoa','Guipuzcoa'),
	(153,195,'Huelva','Huelva'),
	(154,195,'Huesca','Huesca'),
	(155,195,'Jaen','Jaen'),
	(156,195,'La Rioja','La Rioja'),
	(157,195,'Las Palmas','Las Palmas'),
	(158,195,'Leon','Leon'),
	(159,195,'Lleida','Lleida'),
	(160,195,'Lugo','Lugo'),
	(161,195,'Madrid','Madrid'),
	(162,195,'Malaga','Malaga'),
	(163,195,'Melilla','Melilla'),
	(164,195,'Murcia','Murcia'),
	(165,195,'Navarra','Navarra'),
	(166,195,'Ourense','Ourense'),
	(167,195,'Palencia','Palencia'),
	(168,195,'Pontevedra','Pontevedra'),
	(169,195,'Salamanca','Salamanca'),
	(170,195,'Santa Cruz de Tenerife','Santa Cruz de Tenerife'),
	(171,195,'Segovia','Segovia'),
	(172,195,'Sevilla','Sevilla'),
	(173,195,'Soria','Soria'),
	(174,195,'Tarragona','Tarragona'),
	(175,195,'Teruel','Teruel'),
	(176,195,'Toledo','Toledo'),
	(177,195,'Valencia','Valencia'),
	(178,195,'Valladolid','Valladolid'),
	(179,195,'Vizcaya','Vizcaya'),
	(180,195,'Zamora','Zamora'),
	(181,195,'Zaragoza','Zaragoza'),
	(182,250,'AA','AA'),
	(183,250,'AE','AE'),
	(184,250,'AP','AP');

/*!40000 ALTER TABLE `zones` ENABLE KEYS */;
UNLOCK TABLES;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
