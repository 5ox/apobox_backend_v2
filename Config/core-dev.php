<?php
/**
 * This is an environment-specific core configuration file.
 *
 * It contains vagrant-specific overrides for the common config settings
 * in `Config/core.php`. Only items that must truly be different from the
 * master core config should be added here.
 *
 */

$config = [
	'debug' => 2,

	'App' => [
		'fullBaseUrl' => 'http://localhost:9280',
	],

	'Cookie' => [
		'domain' => 'localhost',
	],

	/**
	 * Vagrant DB configuration. These settings match those in
	 * `puphpet/config.yaml` for the MySQL server that is set up in the
	 * Vagrant VM. Must at least define a `default` connection.
	 */
	'Database' => [
		'default' => [
			'datasource' => 'Database/Mysql',
			'persistent' => false,
			'host' => 'mysql',
			'login' => 'docker',
			'password' => 'docker',
			'database' => 'docker',
		],
		'test' => [
			'datasource' => 'Database/Mysql',
			'persistent' => false,
			'host' => 'mysql',
			'login' => 'docker',
			'password' => 'docker',
			'database' => 'docker_test',
		],
		'memory' => [
			'datasource' => 'Database/Sqlite',
			'database' => ':memory:', // Or something independently reviewable, like: 'tmp/treetest.sqlite3',
		],
	],

	'Email' => [
		'Transports' => [
			'default' => [
				'transport' => 'Smtp',
				'host' => 'mail',
				'port' => 1025,
				'tls' => false,
				'username' => null,
				'password' => null,
				'log' => false,
			],
		],
	],

	'Javascript' => [
		'autoBuild' => true,
	],

	'Memcached' => [
		'servers' => [
			'one' => 'memcached:11211',
		],
	],

	'Session' => [
		'defaults' => 'php',
		'cookie' => 'apobox',
		'timeout' => '1200',
		'handler' => null,
	],

	'PayPal' => [
		'clientId' => 'AYP18RA4xeYH5_aSF_KM9X0fsP9cTVKrR-2m0n3JAkdoA0tK4jf9wWyyl_2W',
		'clientSecret' => 'EDc0rBCwo_WnZM7lIchMFC8itfkGzt9daL1-83tI0q6OLJq2DIEJjyVuS2iR',
		'mode' => 'sandbox',
	],
	'Security' => [
		'admin' => [
			'ips' => false,
		],
	],

	'ShippingApis' => [
		'Usps' => [
			'userId' => '541LOADS1006',
		],
		'Fedex' => [
			'auth' => [
				'apiKey' => 'a3S9k5oLMdsAQxtV',
				'apiPassword' => 'p0smLxBIxmx3z0or7SLkApBLD',
				'apiAccount' => '510087984',
				'apiMeter' => '100278968',
			],
		],
	],

	'OAuth2' => [
		'legacyLogin' => true,
		'Google' => [
			'clientId' => '138445473199-u47jnh0urngit1nlm1meerdm60vg3el8.apps.googleusercontent.com',
			'clientSecret' => '4IJKiOqcoR_RGPXFhEhb9xbQ',
			'redirectUri' => 'http://localhost:9280/admin/login-google',
		],
	],
];
