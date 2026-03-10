<?php
date_default_timezone_set('UTC');
$config = [
	'debug' => 2,

	'env' => 'ci',

	'App' => [
		'fullBaseUrl' => 'https://apobox.dev',
	],

	'Database' => [
		'default' => [
			'datasource' => 'Database/Mysql',
			'persistent' => false,
			'host' => '127.0.0.1',
			'login' => 'root',
			'password' => 'root',
			'database' => 'ci_test',
			'prefix' => '',
		],
		'test' => [
			'datasource' => 'Database/Mysql',
			'persistent' => false,
			'host' => '127.0.0.1',
			'login' => 'root',
			'password' => 'root',
			'database' => 'ci_test',
			'prefix' => '',
		],
		'memory' => [
			'datasource' => 'Database/Sqlite',
			'database' => ':memory:', // Or something independently reviewable, like: 'tmp/treetest.sqlite3',
		],
	],

	'Email' => [
		'delivery' => 'debug',

		// Overriding email transport to ensure delivery isn't attempted with prod credentials.
		'Transports' => [
			'default' => [
				'transport' => 'Smtp',
				'host' => 'localhost',
				'port' => 25,
				'tls' => false,
				'username' => null,
				'password' => null,
				'log' => true,
			],
		],
	],

	'Security' => [
		'admin' => [
			'ips' => false,
		],
	],

	'Session' => [
		'defaults' => 'php',
		'cookie' => 'apobox',
		'timeout' => '1200',
		'handler' => null,
	],
];
