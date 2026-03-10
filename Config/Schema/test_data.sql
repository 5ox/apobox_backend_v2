# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.43-0ubuntu0.12.04.1)
# Database: vagrant
# Generation Time: 2015-07-30 14:30:31 +0000
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

LOCK TABLES `address_book` WRITE;
/*!40000 ALTER TABLE `address_book` DISABLE KEYS */;

INSERT INTO `address_book` (`address_book_id`, `customers_id`, `entry_gender`, `entry_company`, `entry_firstname`, `entry_lastname`, `entry_street_address`, `entry_suburb`, `entry_postcode`, `entry_city`, `entry_state`, `entry_country_id`, `entry_zone_id`, `entry_basename`)
VALUES
	(107,56,'','Test Co','Bill','Tester','456 E Tulip Ln','My Suburb','12345','APO','',223,7,''),
	(108,56,'','Co Co','Bill','Tester','123 Test Rd','The Suburb','62454','Robinson','',223,23,''),
	(110,56,'','Anoco','Bill','Tester','999 Military Rd','Your Suburb','12345','APO','',223,7,'');

/*!40000 ALTER TABLE `address_book` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table admins
# ------------------------------------------------------------

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;

INSERT INTO `admins` (`id`, `email`, `password`, `role`, `token`, `created`, `modified`)
VALUES
	(1,'test@loadsys.com','$2a$10$IaTJsplihejAVmLQsJQ/OuW66W8uODxqXzjBASXKtT7R/5MMqpoqu','manager','','2015-02-27 19:13:07','2015-06-10 15:14:17'),
	(2,'api2','$2a$10$8HNvHTOXM1klvb//sXnaiu3n7Rm7PPXWAEU/FEyNqlfI9UEDsP/W2','api','B34R3R70K3N','2015-06-08 17:22:49','2015-06-08 17:22:49');

/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table authorized_names
# ------------------------------------------------------------

LOCK TABLES `authorized_names` WRITE;
/*!40000 ALTER TABLE `authorized_names` DISABLE KEYS */;

INSERT INTO `authorized_names` (`authorized_names_id`, `customers_id`, `authorized_firstname`, `authorized_lastname`)
VALUES
	(5395,56,'Leeroy','Jenkins');

/*!40000 ALTER TABLE `authorized_names` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table custom_orders
# ------------------------------------------------------------

LOCK TABLES `custom_orders` WRITE;
/*!40000 ALTER TABLE `custom_orders` DISABLE KEYS */;

INSERT INTO `custom_orders` (`custom_orders_id`, `customers_id`, `tracking_id`, `billing_id`, `orders_id`, `package_status`, `package_repack`, `insurance_fee`, `insurance_coverage`, `mail_class`, `instructions`, `order_add_date`)
VALUES
	(8131,56,'1Z20','BT4615','0',1,'yes','','4000','priority','Something here','2015-04-14 11:28:12'),
	(8132,56,'1Z20','BT4615','0',1,'yes','','4000','priority','Something here','2015-04-14 11:28:12'),
	(8133,56,'1Z20','BT4615','0',1,'yes','','4000','priority','Something here','2015-04-14 11:28:12'),
	(8134,56,'1Z20','BT4615','0',1,'yes','','4000','priority','Something here','2015-04-14 11:28:12'),
	(8135,56,'1Z20','BT4615','0',1,'yes','','4000','priority','Something here','2015-04-14 11:28:12'),
	(8136,56,'1Z20','BT4615','0',1,'yes','','4000','priority','Something here','2015-04-14 11:28:12'),
	(8137,56,'1Z20','BT4615','0',1,'yes','','4000','priority','Something here','2015-04-14 11:28:12'),
	(8138,56,'1Z20','BT4615','0',1,'yes','','4000','priority','Something here','2015-04-14 11:28:12'),
	(8139,56,'1Z20','BT4615','0',1,'yes','','4000','priority','Something here','2015-04-14 11:28:12'),
	(8140,56,'1Z20','BT4615','0',1,'yes','','4000','priority','Something here','2015-04-14 11:28:12');

/*!40000 ALTER TABLE `custom_orders` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table customers
# ------------------------------------------------------------

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;

INSERT INTO `customers` (`customers_id`, `billing_id`, `customers_gender`, `customers_firstname`, `customers_lastname`, `customers_dob`, `customers_email_address`, `customers_default_address_id`, `customers_shipping_address_id`, `customers_emergency_address_id`, `customers_telephone`, `customers_fax`, `customers_password`, `customers_newsletter`, `customers_referral_id`, `customers_referral_points`, `cc_firstname`, `cc_lastname`, `cc_number`, `cc_number_encrypted`, `cc_expires_month`, `cc_expires_year`, `cc_cvv`, `card_token`, `insurance_amount`, `insurance_fee`, `backup_email_address`, `customers_referral_referred`, `referral_status`, `default_postal_type`, `billing_type`, `invoicing_authorized`, `editable_max_amount`, `created`)
VALUES
	(56,'BT4615','','Bill','Tester','0000-00-00 00:00:00','test@loadsys.com',108,110,107,'5558675309',NULL,'add18d635fceab999dedaa16aa35dc6a:5f',NULL,'',0,'Test','User','XXXXXXXXXXXX9403','','08','15','','CARD-3EN83727Y4712232LKWKGANY',250.00,4.60,'','',0,'parcel_post','cc','1',NULL, '2016-08-02 13:07:07');

/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table orders
# ------------------------------------------------------------

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;

INSERT INTO `orders` (`orders_id`, `customers_id`, `customers_name`, `customers_company`, `customers_street_address`, `customers_suburb`, `customers_city`, `customers_postcode`, `customers_state`, `customers_country`, `customers_telephone`, `customers_email_address`, `customers_address_format_id`, `delivery_name`, `delivery_company`, `delivery_street_address`, `delivery_suburb`, `delivery_city`, `delivery_postcode`, `delivery_state`, `delivery_country`, `delivery_address_format_id`, `billing_name`, `billing_company`, `billing_street_address`, `billing_suburb`, `billing_city`, `billing_postcode`, `billing_state`, `billing_country`, `billing_address_format_id`, `payment_method`, `cc_type`, `cc_owner`, `cc_number`, `cc_expires`, `comments`, `last_modified`, `date_purchased`, `turnaround_sec`, `orders_status`, `orders_date_finished`, `ups_track_num`, `usps_track_num`, `usps_track_num_in`, `fedex_track_num`, `fedex_freight_track_num`, `dhl_track_num`, `currency`, `currency_value`, `shipping_tax`, `billing_status`, `qbi_imported`, `width`, `length`, `depth`, `weight_oz`, `mail_class`, `package_type`, `NonMachinable`, `OversizeRate`, `BalloonRate`, `package_flow`, `shipped_from`, `insurance_coverage`, `warehouse`, `postage_id`, `trans_id`, `moved_to_invoice`)
VALUES
	(1150116120499,56,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States','5558675309','rick+bt@loadsys.com',2,'Bill Tester','Anoco','999 Military Rd','Your Suburb','APO','12345','AA','United States',2,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States',2,'Payments Pro',NULL,NULL,NULL,NULL,'','2015-07-16 13:29:09','2015-07-16 13:29:09',NULL,1,NULL,'','','','','','','USD',1.000000,0.0000,1,0,'','','','','PRIORITY','RECTPARCEL',NULL,NULL,NULL,'0','','250.00','IN','','',''),
	(1150116120500,56,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States','5558675309','rick+bt@loadsys.com',2,'Bill Tester','Anoco','999 Military Rd','Your Suburb','APO','12345','AA','United States',2,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States',2,'Payments Pro',NULL,NULL,NULL,NULL,'','2015-07-16 14:38:39','2015-07-16 14:38:39',NULL,1,NULL,'','','','','','','USD',1.000000,0.0000,1,0,'','','','','PRIORITY','RECTPARCEL',NULL,NULL,NULL,'0','','250.00','IN','','',''),
	(1150116120501,56,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States','5558675309','rick+bt@loadsys.com',2,'Bill Tester','Anoco','999 Military Rd','Your Suburb','APO','12345','AA','United States',2,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States',2,'Payments Pro',NULL,NULL,NULL,NULL,'','2015-07-16 14:40:10','2015-07-16 14:40:10',NULL,1,NULL,'','','','','','','USD',1.000000,0.0000,1,0,'','','','','PRIORITY','RECTPARCEL',NULL,NULL,NULL,'0','','250.00','IN','','',''),
	(1150116120502,56,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States','5558675309','rick+bt@loadsys.com',2,'Bill Tester','Anoco','999 Military Rd','Your Suburb','APO','12345','AA','United States',2,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States',2,'Payments Pro',NULL,NULL,NULL,NULL,'','2015-07-16 14:41:13','2015-07-16 14:41:13',NULL,1,NULL,'','','','','','','USD',1.000000,0.0000,1,0,'','','','','PRIORITY','RECTPARCEL',NULL,NULL,NULL,'0','','250.00','IN','','',''),
	(1150116120503,56,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States','5558675309','rick+bt@loadsys.com',2,'Bill Tester','Anoco','999 Military Rd','Your Suburb','APO','12345','AA','United States',2,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States',2,'Payments Pro',NULL,NULL,NULL,NULL,'','2015-07-16 14:41:44','2015-07-16 14:41:44',NULL,1,NULL,'','','','','','','USD',1.000000,0.0000,1,0,'','','','','PRIORITY','RECTPARCEL',NULL,NULL,NULL,'0','','250.00','IN','','',''),
	(1150116120504,56,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States','5558675309','rick+bt@loadsys.com',2,'Bill Tester','Anoco','999 Military Rd','Your Suburb','APO','12345','AA','United States',2,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States',2,'Payments Pro',NULL,NULL,NULL,NULL,'','2015-07-16 16:52:38','2015-07-16 15:04:13',NULL,3,NULL,'','123123','','','','','USD',1.000000,0.0000,1,0,'','','','','PRIORITY','RECTPARCEL',NULL,NULL,NULL,'0','','250.00','IN','','',''),
	(1150116120505,56,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States','5558675309','rick+bt@loadsys.com',2,'Bill Tester','Anoco','999 Military Rd','Your Suburb','APO','12345','AA','United States',2,'Bill Tester','Co Co','123 Test Rd','The Suburb','Robinson','62454','IL','United States',2,'Payments Pro',NULL,NULL,NULL,NULL,'','2015-07-22 18:58:18','2015-07-22 18:43:41',NULL,1,NULL,'','','','','','','USD',1.000000,0.0000,1,0,'','','','','PRIORITY','RECTPARCEL',NULL,NULL,NULL,'0','','250.00','IN','','','');

/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table orders_status_history
# ------------------------------------------------------------

LOCK TABLES `orders_status_history` WRITE;
/*!40000 ALTER TABLE `orders_status_history` DISABLE KEYS */;

INSERT INTO `orders_status_history` (`orders_status_history_id`, `orders_id`, `orders_status_id`, `date_added`, `customer_notified`, `comments`)
VALUES
	(111,1150116120499,1,'2015-07-16 13:29:09',0,NULL),
	(112,1150116120500,1,'2015-07-16 14:38:39',0,NULL),
	(113,1150116120501,1,'2015-07-16 14:40:10',0,NULL),
	(114,1150116120502,1,'2015-07-16 14:41:13',0,NULL),
	(115,1150116120503,1,'2015-07-16 14:41:44',0,NULL),
	(116,1150116120504,1,'2015-07-16 15:04:13',0,NULL),
	(117,1150116120455,1,'2015-07-16 15:15:17',0,NULL),
	(118,1150116120455,2,'2015-07-16 15:22:03',0,NULL),
	(119,1150116120455,1,'2015-07-16 15:22:19',0,NULL),
	(120,1150116120455,2,'2015-07-16 15:22:39',0,NULL),
	(121,1150116120504,2,'2015-07-16 15:23:00',0,NULL),
	(122,1150116120504,1,'2015-07-16 15:35:06',0,NULL),
	(123,1150116120455,1,'2015-07-16 15:36:06',0,NULL),
	(124,1150116120455,2,'2015-07-16 15:37:02',0,NULL),
	(125,1150116120473,3,'2015-07-16 16:14:33',0,'asdasd'),
	(126,1150116120473,3,'2015-07-16 16:14:49',0,'adasd'),
	(127,1150116120473,3,'2015-07-16 16:14:53',0,'asdasd'),
	(128,1150116120473,3,'2015-07-16 16:25:03',0,'asasd'),
	(129,1150116120504,3,'2015-07-16 16:52:16',0,'1231231'),
	(130,1150116120504,3,'2015-07-16 16:52:23',0,'121231233131'),
	(131,1150116120504,4,'2015-07-16 16:52:30',0,'1121'),
	(132,1150116120504,3,'2015-07-16 16:52:38',0,'121231'),
	(133,1150116120505,1,'2015-07-22 18:43:41',0,NULL),
	(134,1150116120505,2,'2015-07-22 18:43:46',0,NULL),
	(135,1150116120505,1,'2015-07-22 18:58:18',0,NULL);

/*!40000 ALTER TABLE `orders_status_history` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table orders_total
# ------------------------------------------------------------

LOCK TABLES `orders_total` WRITE;
/*!40000 ALTER TABLE `orders_total` DISABLE KEYS */;

INSERT INTO `orders_total` (`orders_total_id`, `orders_id`, `title`, `text`, `value`, `class`, `sort_order`)
VALUES
	(2917959,1150116120499,'Postage :','$0.00',0.0000,'ot_shipping',1),
	(2917960,1150116120499,'Storage Fees:','$0.00',0.0000,'ot_custom',2),
	(2917961,1150116120499,'Insurance :','$4.70',4.7000,'ot_insurance',3),
	(2917962,1150116120499,'APO Box Fee :','$9.95',9.9500,'ot_fee',4),
	(2917963,1150116120499,'Subtotal :','$0.00',14.6500,'ot_subtotal',5),
	(2917964,1150116120499,'Total :','<b>$0.00</b>',14.6500,'ot_total',6),
	(2917965,1150116120500,'Postage :','$0.00',0.0000,'ot_shipping',1),
	(2917966,1150116120500,'Storage Fees:','$0.00',0.0000,'ot_custom',2),
	(2917967,1150116120500,'Insurance :','$4.70',4.7000,'ot_insurance',3),
	(2917968,1150116120500,'APO Box Fee :','$9.95',9.9500,'ot_fee',4),
	(2917969,1150116120500,'Subtotal :','$0.00',14.6500,'ot_subtotal',5),
	(2917970,1150116120500,'Total :','<b>$0.00</b>',14.6500,'ot_total',6),
	(2917971,1150116120501,'Postage :','$0.00',0.0000,'ot_shipping',1),
	(2917972,1150116120501,'Storage Fees:','$0.00',0.0000,'ot_custom',2),
	(2917973,1150116120501,'Insurance :','$4.70',4.7000,'ot_insurance',3),
	(2917974,1150116120501,'APO Box Fee :','$9.95',9.9500,'ot_fee',4),
	(2917975,1150116120501,'Subtotal :','$0.00',14.6500,'ot_subtotal',5),
	(2917976,1150116120501,'Total :','<b>$0.00</b>',14.6500,'ot_total',6),
	(2917977,1150116120502,'Postage :','$0.00',0.0000,'ot_shipping',1),
	(2917978,1150116120502,'Storage Fees:','$0.00',0.0000,'ot_custom',2),
	(2917979,1150116120502,'Insurance :','$4.70',4.7000,'ot_insurance',3),
	(2917980,1150116120502,'APO Box Fee :','$9.95',9.9500,'ot_fee',4),
	(2917981,1150116120502,'Subtotal :','$0.00',14.6500,'ot_subtotal',5),
	(2917982,1150116120502,'Total :','<b>$0.00</b>',14.6500,'ot_total',6),
	(2917983,1150116120503,'Postage :','$0.00',0.0000,'ot_shipping',1),
	(2917984,1150116120503,'Storage Fees:','$0.00',0.0000,'ot_custom',2),
	(2917985,1150116120503,'Insurance :','$4.70',4.7000,'ot_insurance',3),
	(2917986,1150116120503,'APO Box Fee :','$9.95',9.9500,'ot_fee',4),
	(2917987,1150116120503,'Subtotal :','$14.65',14.6500,'ot_subtotal',5),
	(2917988,1150116120503,'Total :','<b>$14.65</b>',14.6500,'ot_total',6),
	(2917989,1150116120504,'Postage :','$0.00',0.0000,'ot_shipping',1),
	(2917990,1150116120504,'Storage Fees:','$0.00',0.0000,'ot_custom',2),
	(2917991,1150116120504,'Insurance :','$4.70',4.7000,'ot_insurance',3),
	(2917992,1150116120504,'APO Box Fee :','$9.95',9.9500,'ot_fee',4),
	(2917993,1150116120504,'Subtotal :','$14.65',14.6500,'ot_subtotal',5),
	(2917994,1150116120504,'Total :','<b>$14.65</b>',14.6500,'ot_total',6),
	(2917995,1150116120505,'Postage :','$0.00',0.0000,'ot_shipping',1),
	(2917996,1150116120505,'Storage Fees:','$0.00',0.0000,'ot_custom',2),
	(2917997,1150116120505,'Insurance :','$4.70',4.7000,'ot_insurance',3),
	(2917998,1150116120505,'APO Box Fee :','$9.95',9.9500,'ot_fee',4),
	(2917999,1150116120505,'Subtotal :','$14.65',14.6500,'ot_subtotal',5),
	(2918000,1150116120505,'Total :','<b>$14.65</b>',14.6500,'ot_total',6);

/*!40000 ALTER TABLE `orders_total` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tracking
# ------------------------------------------------------------

LOCK TABLES `tracking` WRITE;
/*!40000 ALTER TABLE `tracking` DISABLE KEYS */;

INSERT INTO `tracking` (`tracking_id`, `warehouse`, `timestamp`, `comments`, `shipped`)
VALUES
	('12345','IN','2015-06-17 12:34:28','','0'),
	('12346','IN','2015-06-17 12:35:57','Returning package.','0'),
	('1234567','IN','2015-06-17 12:38:04','','0'),
	('123456781','IN','2015-06-17 12:38:23','dadav','0');

/*!40000 ALTER TABLE `tracking` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table customers_info
# ------------------------------------------------------------

LOCK TABLES `customers_info` WRITE;
/*!40000 ALTER TABLE `customers_info` DISABLE KEYS */;

INSERT INTO `customers_info` (`customers_info_id`, `customers_info_date_of_last_logon`, `customers_info_number_of_logons`, `customers_info_date_account_created`, `customers_info_date_account_closed`, `customers_info_date_account_last_modified`, `customers_info_source_id`, `global_product_notifications`, `IP_signup`, `IP_lastlogon`, `IP_cc_update`, `IP_addressbook_update`)
VALUES
	(56,NULL,NULL,'2016-08-02 13:07:07',NULL,NULL,0,0,'','','','');

/*!40000 ALTER TABLE `customers_info` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table search_indices
# ------------------------------------------------------------

LOCK TABLES `search_indices` WRITE;
/*!40000 ALTER TABLE `search_indices` DISABLE KEYS */;

INSERT INTO `search_indices` (`id`, `association_key`, `model`, `data`, `created`, `modified`)
VALUES
	(1,'56','Customer','BT4615. Bill. Tester. test@loadsys.com. Leeroy. Jenkins','2016-08-02 20:32:15','2016-08-02 20:32:15');

/*!40000 ALTER TABLE `search_indices` ENABLE KEYS */;
UNLOCK TABLES;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
