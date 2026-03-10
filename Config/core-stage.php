<?php
/**
 * This is an environment-specific core configuration file.
 *
 * It contains vagrant-specific overrides for the common config settings
 * in `Config/core.php`. Only items that must truly be different from the
 * master core config should be added here.
 *
 */

$config = array(
	'debug' => 2,

	'App' => [
		'fullBaseUrl' => 'https://c6.propic.com',
	],

	'Cookie' => array(
		'domain' => '.propic.com',
	),

	'Database' => array(
		'default' => array(
			'datasource' => 'Database/Mysql',
			'persistent' => false,
			'host' => 'db1.propic.com',
			'login' => 'propic_admin',
			'password' => 'propicL1ght',
			'database' => 'apobox2',
		),
	),

	'Email' => array(
		'Transports' => array(
			'default' => array(
				'log' => true,
			),
		),
	),

	'PayPal' => array(
		'clientId' => 'AYP18RA4xeYH5_aSF_KM9X0fsP9cTVKrR-2m0n3JAkdoA0tK4jf9wWyyl_2W',
		'clientSecret' => 'EDc0rBCwo_WnZM7lIchMFC8itfkGzt9daL1-83tI0q6OLJq2DIEJjyVuS2iR',
		'mode' => 'sandbox',
	),

	'Security.admin' => array(
		'ips' => null,
	),
);

