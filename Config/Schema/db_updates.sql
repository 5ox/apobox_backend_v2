-- Put all new queries at the bottom of this file in chronological order.

-- chronon 2016-01-13
ALTER TABLE `customers_info` ADD `customers_info_date_account_closed` DATETIME  NULL  DEFAULT NULL  AFTER `customers_info_date_account_created`;

-- chronon 2015-10-21
ALTER TABLE `customers` ADD `created` DATETIME  NOT NULL  DEFAULT '0000-00-00 00:00:00'  AFTER `editable_max_amount`;

-- chronon 2016-01-13
ALTER TABLE `customers` ADD `is_active` BOOL  NOT NULL  DEFAULT '1'   AFTER `created`;

-- alecho 2015-06-23
DELETE FROM `insurance` WHERE `insurance_id` = 14 LIMIT 1;
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '600.01', '700.00', '8.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '700.01', '800.00', '9.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '800.01', '900.00', '10.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '900.01', '1000.00', '11.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '1000.01', '1100.00', '12.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '1100.01', '1200.00', '13.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '1200.01', '1300.00', '14.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '1300.01', '1400.00', '15.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '1400.01', '1500.00', '16.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '1500.01', '1600.00', '17.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '1600.01', '1700.00', '18.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '1700.01', '1800.00', '19.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '1800.01', '1900.00', '20.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '1900.01', '2000.00', '21.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '2000.01', '2100.00', '22.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '2100.01', '2200.00', '23.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '2200.01', '2300.00', '24.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '2300.01', '2400.00', '25.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '2400.01', '2500.00', '26.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '2500.01', '2600.00', '27.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '2600.01', '2700.00', '28.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '2700.01', '2800.00', '29.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '2800.01', '2900.00', '30.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '2900.01', '3000.00', '31.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '3000.01', '3100.00', '32.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '3100.01', '3200.00', '33.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '3200.01', '3300.00', '34.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '3300.01', '3400.00', '35.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '3400.01', '3500.00', '36.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '3500.01', '3600.00', '37.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '3600.01', '3700.00', '38.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '3700.01', '3800.00', '39.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '3800.01', '3900.00', '40.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '3900.01', '4000.00', '41.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '4000.01', '4100.00', '42.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '4100.01', '4200.00', '43.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '4200.01', '4300.00', '44.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '4300.01', '4400.00', '45.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '4400.01', '4500.00', '46.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '4500.01', '4600.00', '47.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '4600.01', '4700.00', '48.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '4700.01', '4800.00', '49.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '4800.01', '4900.00', '50.70');
INSERT INTO `insurance` (`insurance_id`, `amount_from`, `amount_to`, `insurance_fee`) VALUES (NULL, '4900.01', '5000.00', '51.70');

-- chronon 2015-09-02
ALTER TABLE `orders` CHANGE `billing_status` `billing_status` INT(5)  NOT NULL  DEFAULT '0';

-- ricog 2015-10-15
ALTER TABLE `address_book` CHANGE `entry_country_id` `entry_country_id` INT(11)  NOT NULL;
ALTER TABLE `address_book` CHANGE `entry_zone_id` `entry_zone_id` INT(11)  NOT NULL;

-- chronon 2016-01-01
CREATE TABLE `customer_reminders` (
  `customer_reminder_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customers_id` int(11) NOT NULL,
  `orders_id` bigint(17) NOT NULL,
  `reminder_type` varchar(50) NOT NULL DEFAULT '',
  `reminder_count` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`customer_reminder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ricog 2016-03-15
ALTER TABLE `tracking` ADD `created` DATETIME  NULL  AFTER `shipped`;
ALTER TABLE `tracking` ADD `modified` DATETIME  NULL  AFTER `created`;
UPDATE `tracking` SET `created` = `timestamp`;
UPDATE `tracking` SET `modified` = `created`;
UPDATE `tracking` SET `timestamp` = `created`;

-- ricog 2016-03-22
UPDATE `insurance` SET `insurance_fee` = '2.20' WHERE `insurance_id` = '1';
UPDATE `insurance` SET `insurance_fee` = '2.75' WHERE `insurance_id` = '2';
UPDATE `insurance` SET `insurance_fee` = '3.50' WHERE `insurance_id` = '3';
UPDATE `insurance` SET `insurance_fee` = '4.60' WHERE `insurance_id` = '4';
UPDATE `insurance` SET `insurance_fee` = '5.80' WHERE `insurance_id` = '5';
UPDATE `insurance` SET `insurance_fee` = '7.00' WHERE `insurance_id` = '6';
UPDATE `insurance` SET `insurance_fee` = '9.45' WHERE `insurance_id` = '7';
UPDATE `insurance` SET `insurance_fee` = '10.75' WHERE `insurance_id` = '15';
UPDATE `insurance` SET `insurance_fee` = '12.05' WHERE `insurance_id` = '16';
UPDATE `insurance` SET `insurance_fee` = '13.35' WHERE `insurance_id` = '17';
UPDATE `insurance` SET `insurance_fee` = '14.65' WHERE `insurance_id` = '18';
UPDATE `insurance` SET `insurance_fee` = '15.95' WHERE `insurance_id` = '19';
UPDATE `insurance` SET `insurance_fee` = '17.25' WHERE `insurance_id` = '20';
UPDATE `insurance` SET `insurance_fee` = '18.55' WHERE `insurance_id` = '21';
UPDATE `insurance` SET `insurance_fee` = '19.85' WHERE `insurance_id` = '22';
UPDATE `insurance` SET `insurance_fee` = '21.15' WHERE `insurance_id` = '23';
UPDATE `insurance` SET `insurance_fee` = '22.45' WHERE `insurance_id` = '24';
UPDATE `insurance` SET `insurance_fee` = '23.75' WHERE `insurance_id` = '25';
UPDATE `insurance` SET `insurance_fee` = '25.05' WHERE `insurance_id` = '26';
UPDATE `insurance` SET `insurance_fee` = '26.35' WHERE `insurance_id` = '27';
UPDATE `insurance` SET `insurance_fee` = '27.65' WHERE `insurance_id` = '28';
UPDATE `insurance` SET `insurance_fee` = '28.95' WHERE `insurance_id` = '29';
UPDATE `insurance` SET `insurance_fee` = '30.25' WHERE `insurance_id` = '30';
UPDATE `insurance` SET `insurance_fee` = '31.55' WHERE `insurance_id` = '31';
UPDATE `insurance` SET `insurance_fee` = '32.85' WHERE `insurance_id` = '32';
UPDATE `insurance` SET `insurance_fee` = '34.15' WHERE `insurance_id` = '33';
UPDATE `insurance` SET `insurance_fee` = '35.45' WHERE `insurance_id` = '34';
UPDATE `insurance` SET `insurance_fee` = '36.75' WHERE `insurance_id` = '35';
UPDATE `insurance` SET `insurance_fee` = '38.05' WHERE `insurance_id` = '36';
UPDATE `insurance` SET `insurance_fee` = '39.35' WHERE `insurance_id` = '37';
UPDATE `insurance` SET `insurance_fee` = '40.65' WHERE `insurance_id` = '38';
UPDATE `insurance` SET `insurance_fee` = '41.95' WHERE `insurance_id` = '39';
UPDATE `insurance` SET `insurance_fee` = '43.25' WHERE `insurance_id` = '40';
UPDATE `insurance` SET `insurance_fee` = '44.55' WHERE `insurance_id` = '41';
UPDATE `insurance` SET `insurance_fee` = '45.85' WHERE `insurance_id` = '42';
UPDATE `insurance` SET `insurance_fee` = '47.15' WHERE `insurance_id` = '43';
UPDATE `insurance` SET `insurance_fee` = '48.45' WHERE `insurance_id` = '44';
UPDATE `insurance` SET `insurance_fee` = '49.75' WHERE `insurance_id` = '45';
UPDATE `insurance` SET `insurance_fee` = '51.05' WHERE `insurance_id` = '46';
UPDATE `insurance` SET `insurance_fee` = '52.35' WHERE `insurance_id` = '47';
UPDATE `insurance` SET `insurance_fee` = '53.65' WHERE `insurance_id` = '48';
UPDATE `insurance` SET `insurance_fee` = '54.95' WHERE `insurance_id` = '49';
UPDATE `insurance` SET `insurance_fee` = '56.25' WHERE `insurance_id` = '50';
UPDATE `insurance` SET `insurance_fee` = '57.55' WHERE `insurance_id` = '51';
UPDATE `insurance` SET `insurance_fee` = '58.85' WHERE `insurance_id` = '52';
UPDATE `insurance` SET `insurance_fee` = '60.15' WHERE `insurance_id` = '53';
UPDATE `insurance` SET `insurance_fee` = '61.45' WHERE `insurance_id` = '54';
UPDATE `insurance` SET `insurance_fee` = '62.75' WHERE `insurance_id` = '55';
UPDATE `insurance` SET `insurance_fee` = '64.05' WHERE `insurance_id` = '56';
UPDATE `insurance` SET `insurance_fee` = '65.35' WHERE `insurance_id` = '57';
UPDATE `insurance` SET `insurance_fee` = '66.65' WHERE `insurance_id` = '58';

-- chronon 2016-05-10
CREATE TABLE `orders_data` (
  `orders_data_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `orders_id` bigint(17) unsigned NOT NULL,
  `data_type` varchar(30) NOT NULL DEFAULT '',
  `data` text NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`orders_data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- chronon 2016-06-14
CREATE TABLE `search_indices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `association_key` varchar(36) NOT NULL,
  `model` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `data` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `association_key` (`association_key`,`model`),
  FULLTEXT KEY `data` (`data`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- chronon 2016-04-28
ALTER TABLE `custom_orders` CHANGE `insurance_fee` `insurance_fee` VARCHAR(10)  CHARACTER SET latin1  COLLATE latin1_swedish_ci  NOT NULL  DEFAULT ''  COMMENT 'Deprecated, legacy system use only';

-- ricog 2017-01-16
-- DB optimizations for slow queries
ALTER TABLE `orders` ADD INDEX `customers_id` (`customers_id`);
ALTER TABLE `orders_status_history` ADD INDEX `orders_id` (`orders_id`);
ALTER TABLE `custom_orders` CHANGE `orders_id` `orders_id` BIGINT(17)  NOT NULL  DEFAULT '0';

-- ricog 2017-01-20
-- Make invoicing_authorized a boolean field
ALTER TABLE `customers` CHANGE `invoicing_authorized` `invoicing_authorized` TINYINT(1)  NOT NULL  DEFAULT '0';
UPDATE customers SET invoicing_authorized = 0 WHERE invoicing_authorized = 1;
UPDATE customers SET invoicing_authorized = 1 WHERE invoicing_authorized = 2;

-- chronon 2017-04-04
-- Add zone data for Dominican Republic - https://en.wikipedia.org/wiki/ISO_3166-2:DO#Provinces_and_district
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '02', 'Azua');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '03', 'Baoruco');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '04', 'Barahona');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '05', 'Dajabón');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '06', 'Duarte');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '07', 'Elías Piña');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '08', 'El Seibo');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '09', 'Espaillat');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '10', 'Independencia');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '11', 'La Altagracia');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '12', 'La Romana');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '13', 'La Vega');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '14', 'María Trinidad Sánchez');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '15', 'Monte Cristi');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '16', 'Pedernales');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '17', 'Peravia');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '18', 'Puerto Plata');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '19', 'Hermanas Mirabal');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '20', 'Samaná');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '21', 'San Cristóbal');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '22', 'San Juan');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '23', 'San Pedro de Macorís');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '24', 'Sánchez Ramírez');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '25', 'Santiago');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '26', 'Santiago Rodríguez');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '27', 'Valverde');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '28', 'Monseñor Nouel');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '29', 'Monte Plata');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '30', 'Hato Mayor');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '31', 'San José de Ocoa');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '60', '32', 'Santo Domingo');

-- ricog 2017-04-27
-- Add zone data for Belgium - https://en.wikipedia.org/wiki/Belgium#Provinces
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '21', 'Antwerpen', 'Antwerpen');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '21', 'Oost-Vlaanderen', 'Oost-Vlaanderen');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '21', 'Vlaams-Brabant', 'Vlaams-Brabant');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '21', 'Hainaut', 'Hainaut');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '21', 'Li&eacute;ge', 'Li&eacute;ge');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '21', 'Limburg', 'Limburg');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '21', 'Luxembourg', 'Luxembourg');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '21', 'Namur', 'Namur');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '21', 'Brabant wallon', 'Brabant wallon');
INSERT INTO `zones` (`zone_id`, `zone_country_id`, `zone_code`, `zone_name`) VALUES (NULL, '21', 'West-Vlaanderen', 'West-Vlaanderen');

-- ricog 2017-05-04
-- Convert APOBox tables to Innodb
ALTER TABLE `address_book` ENGINE = InnoDB;
ALTER TABLE `authorized_names` ENGINE = InnoDB;
ALTER TABLE `countries` ENGINE = InnoDB;
ALTER TABLE `custom_orders` ENGINE = InnoDB;
ALTER TABLE `customers` ENGINE = InnoDB;
ALTER TABLE `customers_info` ENGINE = InnoDB;
ALTER TABLE `insurance` ENGINE = InnoDB;
ALTER TABLE `orders` ENGINE = InnoDB;
ALTER TABLE `orders_status` ENGINE = InnoDB;
ALTER TABLE `orders_status_history` ENGINE = InnoDB;
ALTER TABLE `orders_total` ENGINE = InnoDB;
ALTER TABLE `search_indices` ENGINE = InnoDB;
ALTER TABLE `tracking` ENGINE = InnoDB;
ALTER TABLE `zones` ENGINE = InnoDB;

-- ricog 2017-06-16
INSERT INTO `zones` (`zone_country_id`, `zone_code`, `zone_name`) VALUES
(167, 'AM', 'Amazonas'),
(167, 'AN', 'Ancash'),
(167, 'AP', 'Apurimac'),
(167, 'AR', 'Arequipa'),
(167, 'AY', 'Ayacucho'),
(167, 'CJ', 'Cajamarca'),
(167, 'CL', 'Callao'),
(167, 'CU', 'Cusco'),
(167, 'HV', 'Huancavelica'),
(167, 'HO', 'Huanuco'),
(167, 'IC', 'Ica'),
(167, 'JU', 'Junin'),
(167, 'LD', 'La Libertad'),
(167, 'LY', 'Lambayeque'),
(167, 'LI', 'Lima'),
(167, 'LO', 'Loreto'),
(167, 'MD', 'Madre de Dios'),
(167, 'MO', 'Moquegua'),
(167, 'PA', 'Pasco'),
(167, 'PI', 'Piura'),
(167, 'PU', 'Puno'),
(167, 'SM', 'San Martin'),
(167, 'TA', 'Tacna'),
(167, 'TU', 'Tumbes'),
(167, 'UC', 'Ucayali');

-- ricog 2017-07-20
INSERT INTO `zones` (`zone_country_id`, `zone_code`, `zone_name`) VALUES
(107, 'AI', 'Aichi'),
(107, 'AK', 'Akita'),
(107, 'AO', 'Aomori'),
(107, 'CH', 'Chiba'),
(107, 'EH', 'Ehime'),
(107, 'FK', 'Fukui'),
(107, 'FU', 'Fukuoka'),
(107, 'FS', 'Fukushima'),
(107, 'GI', 'Gifu'),
(107, 'GU', 'Gunma'),
(107, 'HI', 'Hiroshima'),
(107, 'HO', 'Hokkaido'),
(107, 'HY', 'Hyogo'),
(107, 'IB', 'Ibaraki'),
(107, 'IS', 'Ishikawa'),
(107, 'IW', 'Iwate'),
(107, 'KA', 'Kagawa'),
(107, 'KG', 'Kagoshima'),
(107, 'KN', 'Kanagawa'),
(107, 'KO', 'Kochi'),
(107, 'KU', 'Kumamoto'),
(107, 'KY', 'Kyoto'),
(107, 'MI', 'Mie'),
(107, 'MY', 'Miyagi'),
(107, 'MZ', 'Miyazaki'),
(107, 'NA', 'Nagano'),
(107, 'NG', 'Nagasaki'),
(107, 'NR', 'Nara'),
(107, 'NI', 'Niigata'),
(107, 'OI', 'Oita'),
(107, 'OK', 'Okayama'),
(107, 'ON', 'Okinawa'),
(107, 'OS', 'Osaka'),
(107, 'SA', 'Saga'),
(107, 'SI', 'Saitama'),
(107, 'SH', 'Shiga'),
(107, 'SM', 'Shimane'),
(107, 'SZ', 'Shizuoka'),
(107, 'TO', 'Tochigi'),
(107, 'TS', 'Tokushima'),
(107, 'TK', 'Tokyo'),
(107, 'TT', 'Tottori'),
(107, 'TY', 'Toyama'),
(107, 'WA', 'Wakayama'),
(107, 'YA', 'Yamagata'),
(107, 'YM', 'Yamaguchi'),
(107, 'YN', 'Yamanashi');

-- kris 2017-10-13
CREATE TABLE `affiliate_links` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL DEFAULT '',
    `url` varchar(255) NOT NULL DEFAULT '',
    `enabled` tinyint(1) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- rick 2018-07-25
-- Add Philippines zones
INSERT INTO `zones` (`zone_country_id`, `zone_code`, `zone_name`) VALUES
(168, 'ABR', 'Abra'),
(168, 'AGN', 'Agusan del Norte'),
(168, 'AGS', 'Agusan del Sur'),
(168, 'AKL', 'Aklan'),
(168, 'ALB', 'Albay'),
(168, 'ANT', 'Antique'),
(168, 'APA', 'Apayao'),
(168, 'AUR', 'Aurora'),
(168, 'BAS', 'Basilan'),
(168, 'BAN', 'Bataan'),
(168, 'BTN', 'Batanes'),
(168, 'BTG', 'Batangas'),
(168, 'BEN', 'Benguet'),
(168, 'BIL', 'Biliran'),
(168, 'BOH', 'Bohol'),
(168, 'BUK', 'Bukidnon'),
(168, 'BUL', 'Bulacan'),
(168, 'CAG', 'Cagayan'),
(168, 'CAN', 'Camarines Norte'),
(168, 'CAS', 'Camarines Sur'),
(168, 'CAM', 'Camiguin'),
(168, 'CAP', 'Capiz'),
(168, 'CAT', 'Catanduanes'),
(168, 'CAV', 'Cavite'),
(168, 'CEB', 'Cebu'),
(168, 'COM', 'Compostela Valley'),
(168, 'NCO', 'Cotabato'),
(168, 'DAV', 'Davao del Norte'),
(168, 'DAS', 'Davao del Sur'),
(168, 'DVO', 'Davao Occidental'),
(168, 'DAO', 'Davao Oriental'),
(168, 'DIN', 'Dinagat Islands'),
(168, 'EAS', 'Eastern Samar'),
(168, 'GUI', 'Guimaras'),
(168, 'IFU', 'Ifugao'),
(168, 'ILN', 'Ilocos Norte'),
(168, 'ILS', 'Ilocos Sur'),
(168, 'ILI', 'Iloilo'),
(168, 'ISA', 'Isabela'),
(168, 'KAL', 'Kalinga'),
(168, 'LUN', 'La Union'),
(168, 'LAG', 'Laguna'),
(168, 'LAN', 'Lanao del Norte'),
(168, 'LAS', 'Lanao del Sur'),
(168, 'LEY', 'Leyte'),
(168, 'MAG', 'Maguindanao'),
(168, 'MAD', 'Marinduque'),
(168, 'MAS', 'Masbate'),
(168, 'MSC', 'Misamis Occidental'),
(168, 'MSR', 'Misamis Oriental'),
(168, 'MOU', 'Mountain Province'),
(168, 'NEC', 'Negros Occidental'),
(168, 'NER', 'Negros Oriental'),
(168, 'NSA', 'Northern Samar'),
(168, 'NUE', 'Nueva Ecija'),
(168, 'NUV', 'Nueva Vizcaya'),
(168, 'MDC', 'Occidental Mindoro'),
(168, 'MDR', 'Oriental Mindoro'),
(168, 'PLW', 'Palawan'),
(168, 'PAM', 'Pampanga'),
(168, 'PAN', 'Pangasinan'),
(168, 'QUE', 'Quezon'),
(168, 'QUI', 'Quirino'),
(168, 'RIZ', 'Rizal'),
(168, 'ROM', 'Romblon'),
(168, 'WSA', 'Samar'),
(168, 'SAR', 'Sarangani'),
(168, 'SIG', 'Siquijor'),
(168, 'SOR', 'Sorsogon'),
(168, 'SCO', 'South Cotabato'),
(168, 'SLE', 'Southern Leyte'),
(168, 'SUK', 'Sultan Kudarat'),
(168, 'SLU', 'Sulu'),
(168, 'SUN', 'Surigao del Norte'),
(168, 'SUR', 'Surigao del Sur'),
(168, 'TAR', 'Tarlac'),
(168, 'TAW', 'Tawi-Tawi'),
(168, 'ZMB', 'Zambales'),
(168, 'ZAN', 'Zamboanga del Norte'),
(168, 'ZAS', 'Zamboanga del Sur'),
(168, 'ZSI', 'Zamboanga Sibugay'),
(168, '00', 'Metro Manila');


-- rick 2019-05-03
-- Add Denmark zones
INSERT INTO `zones` (`zone_country_id`, `zone_code`, `zone_name`) VALUES
(57, '84', 'Hovedstaden'),
(57, '82', 'Midtjylland'),
(57, '81', 'Nordjylland'),
(57, '85', 'Sjælland'),
(57, '83', 'Syddanmark');

-- rick 2019-05-03
-- Set zones to UTF8
ALTER TABLE zones CONVERT TO CHARACTER SET utf8;

-- rick 2019-10-08
ALTER TABLE `orders` ADD `amazon_track_num` VARCHAR(40) NULL DEFAULT NULL AFTER `orders_date_finished`;

-- rick 2023-03-03
ALTER TABLE `orders` ADD `customs_description` VARCHAR(255) NULL DEFAULT NULL AFTER `package_type`;

-- rick 2025-02-05
ALTER TABLE `orders` ADD `creator_id` INT(11) NULL;
