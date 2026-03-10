<?php
/**
 * This is the core configuration file.
 *
 * **ALL** configuration settings should be placed here, and overriden in
 * environment-sepcific files such as `core-vagrant.php`.
 *
 */

Configure::write('debug', 0);

Configure::write('Error', array(
	'handler' => 'ErrorHandler::handleError',
	'level' => E_ALL & ~E_DEPRECATED,
	'trace' => true
));

Configure::write('Exception', [
	'handler' => 'ErrorHandler::handleException',
	'renderer' => 'SerializersErrors.SerializerExceptionRenderer',
	'log' => true,
	'skipLog' => [
		'NotFoundException',
		'ForbiddenException',
		'MissingControllerException',
		'MissingActionException',
		'UnauthorizedException',
		'BadRequestException',
		'BaseSerializerException', // explicitly logged
	],
]);

//Configure::write('App', array(
	//'baseUrl' => env('SCRIPT_NAME'),
	//'fullBaseUrl' => 'http://example.com',
	//'imageBaseUrl' => 'img/',
	//'cssBaseUrl' => 'css/',
	//'jsBaseUrl' => 'js/',
//));
Configure::write('App.fullBaseUrl', 'https://account.apobox.com');
Configure::write('App.encoding', 'UTF-8');

Configure::write('Routing.prefixes', array('employee', 'manager', 'api'));

//Configure::write('Cache', array(
	//'disable' => true,
	//'check' => true,
	//'viewPrefix' => 'prefix',
//));

Configure::write('Cookie', array(
	'domain' => '.apobox.com',
	// This is the default secret from node. This should match the session.secret
	// config in nodewhen implemented.
	'secret' => 'g#s9dfkj2jsi46%kskja#agjkjhkdfjleLAw#sBmzZxZskgjtwertQWE@',
));

Configure::write('Memcached', array(
	'servers' => array('localhost:11211'),
));

Configure::write('Security', array(
	'salt' => 'AfUU1yvNHbTANPvkT1VA5c8Dy0e2ggWBTZrgb99ZYLsXeUpb31',
	'cipherSeed' => '646812638894945238981913738083',
	'creditCardKey' => 'testkey',
));

Configure::write('Security.admin', array(
	'ips' => array('192.168.1.33'),
));
Configure::write('Asset', array(
	'timestamp' => true,
	//'filter.css' => 'css.php',
	//'filter.js' => 'custom_javascript_output_filter.php',
));

Configure::write('Acl', array(
	'classname' => 'DbAcl',
	'database' => 'default',
));

//date_default_timezone_set('UTC');
//Configure::write('Config.timezone', 'Europe/Paris');

/**
 * Configure the cache used for general framework caching. Path information,
 * object listings, and translation cache files are stored with this configuration.
 */
$engine = 'File';
$duration = (Configure::read('debug') > 0 ? '+10 seconds' : '+999 days');
$prefix = 'apobox_';
Cache::config('_cake_core_', array(
	'engine' => $engine,
	'prefix' => $prefix . 'cake_core_',
	'path' => CACHE . 'persistent' . DS,
	'serialize' => ($engine === 'File'),
	'duration' => $duration,
));

/**
 * Configure the cache for model and datasource caches. This cache configuration
 * is used to store schema descriptions, and table listings in connections.
 */
Cache::config('_cake_model_', array(
	'engine' => $engine,
	'prefix' => $prefix . 'cake_model_',
	'path' => CACHE . 'models' . DS,
	'serialize' => ($engine === 'File'),
	'duration' => $duration
));



/**
 * Application-specific configurations.
 */

Configure::write(array(
	'FeeByWeight' => [
		0 => 10.95,
		17 => 12.95, // Fee if weight in ounces greater than or equal to key
	],
	'Warehouse' => array(
		'code' => 'IN'
	),
	'Tracking' => array(
		'prefix' => 'S:',
	),
));
/**
 * Default DB configuration. Should be suitable for production use when
 * no APP_ENV is set. Must at least define a `default` connection.
 */
Configure::write('Database', array(
	'default' => array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => '@TODO: Enter production DB host.',
		'login' => '@TODO: Enter production DB login.',
		'password' => '@TODO: Enter production DB password.',
		'database' => '@TODO: Enter production DB database.',
		//'prefix' => '',
		//'encoding' => 'utf8',
	),
));

/**
 * Default Site Configuration
 *
 * Any time you'd be tempted to type one of these strings directly into
 * a file, call this Configure var instead.
 */
Configure::write('Defaults', array(
	'short_name' => 'APO Box', //@TODO: Set the app's short name.
	'long_name' => 'APO Box', //@TODO: Set the app's long name.
	'domain' => 'apobox.com', //@TODO: Set the app's default domain name (used for email addresses and suitable for generated docs, like PDFs.)
	'meta_description' => 'This is a fresh baked Loadsys CakePHP site skeleton.', //@TODO: Set the app's default meta description.
	'meta_keywords' => 'loadsys, cakephp, rapid web development', //@TODO: Set the app's default meta keywords.
));

/**
 * Anonymous helper function for constructing consistent email addresses.
 */
$email = function($localAddress, $displayName = false) {
	$displayName = ($displayName ?: Configure::read('Defaults.short_name'));
	$address = sprintf('%s@%s', $localAddress, Configure::read('Defaults.domain'));
	return array($address => $displayName);
};

/**
 * Email Configuration
 *
 * Either pass the result straight to AppEmail, or unpack a config key in
 * your code like so:
 *
 * `list($name, $email) = Configure::read('Email.whatever');`
 */
Configure::write('Email', array(
	'Address' => array(
		'noreply' => $email('no-reply'),
		'support' => $email('support'),
		'contact' => $email('info'),
	),
	'Subjects' => array(
		'customer' => 'APO Box Message',
		'order' => 'APO Box Shipping Package %s: ', // Prefixes other order statuses
	),
	'Transports' => array(
		'default' => array(
			'transport' => 'Smtp',
			'host' => 'ssl://smtp.gmail.com',
			'port' => 465,
			//'tls' => true,
			'timeout' => 30,
			'username' => 'admin@apobox.com',
			'password' => '@TODO: Enter email app password.',
			'log' => false,

			'from' => $email('no-reply'),
			'charset' => 'utf-8',
			'headerCharset' => 'utf-8',
			'emailFormat' => 'html',

			//'sender' => null,
			//'to' => null,
			//'cc' => null,
			//'bcc' => null,
			//'replyTo' => null,
			//'readReceipt' => null,
			//'returnPath' => null,
			//'messageId' => true,
			//'subject' => null,
			//'message' => null,
			//'headers' => null,
			//'viewRender' => null,
			//'template' => false,
			//'layout' => false,
			//'viewVars' => null,
			//'attachments' => null,
			//'client' => null,
		),
	),
));

/**
 * Javascript Settings:
 * `autoBuild`: enable JSPM auto building (currently for development only)
 */
Configure::write('Javascript', [
	'autoBuild' => false,
]);

/**
 * PayPal
 */
Configure::write('PayPal', array(
	'clientId' => '@TODO: Enter PayPal clientId.',
	'clientSecret' => '@TODO: Enter PayPal clientSecret.',
	'mode' => 'live',
	// PayPal supported countries: https://developer.paypal.com/docs/classic/api/country_codes/
	'allowedBillingCountries' => [
		'AL',
		'DZ',
		'AD',
		'AO',
		'AI',
		'AG',
		'AR',
		'AM',
		'AW',
		'AU',
		'AT',
		'AZ',
		'BS',
		'BH',
		'BB',
		'BY',
		'BE',
		'BZ',
		'BJ',
		'BM',
		'BT',
		'BO',
		'BA',
		'BW',
		'BR',
		'VG',
		'BN',
		'BG',
		'BF',
		'BI',
		'KH',
		'CM',
		'CA',
		'CV',
		'KY',
		'TD',
		'CL',
		'C2',
		'CO',
		'KM',
		'CG',
		'CD',
		'CK',
		'CR',
		'CI',
		'HR',
		'CY',
		'CZ',
		'DK',
		'DJ',
		'DM',
		'DO',
		'EC',
		'EG',
		'SV',
		'ER',
		'EE',
		'ET',
		'FK',
		'FO',
		'FJ',
		'FI',
		'FR',
		'GF',
		'PF',
		'GA',
		'GM',
		'GE',
		'DE',
		'GI',
		'GR',
		'GL',
		'GD',
		'GP',
		'GT',
		'GN',
		'GW',
		'GY',
		'HN',
		'HK',
		'HU',
		'IS',
		'IN',
		'ID',
		'IE',
		'IL',
		'IT',
		'JM',
		'JP',
		'JO',
		'KZ',
		'KE',
		'KI',
		'KW',
		'KG',
		'LA',
		'LV',
		'LS',
		'LI',
		'LT',
		'LU',
		'MK',
		'MG',
		'MW',
		'MY',
		'MV',
		'ML',
		'MT',
		'MH',
		'MQ',
		'MR',
		'MU',
		'YT',
		'MX',
		'FM',
		'MD',
		'MC',
		'MN',
		'ME',
		'MS',
		'MA',
		'MZ',
		'NA',
		'NR',
		'NP',
		'NL',
		'NC',
		'NZ',
		'NI',
		'NE',
		'NG',
		'NU',
		'NF',
		'NO',
		'OM',
		'PW',
		'PA',
		'PG',
		'PY',
		'PE',
		'PH',
		'PN',
		'PL',
		'PT',
		'QA',
		'RE',
		'RO',
		'RU',
		'RW',
		'WS',
		'SM',
		'ST',
		'SA',
		'SN',
		'RS',
		'SC',
		'SL',
		'SG',
		'SK',
		'SI',
		'SB',
		'SO',
		'ZA',
		'KR',
		'ES',
		'LK',
		'SH',
		'KN',
		'LC',
		'PM',
		'VC',
		'SR',
		'SJ',
		'SZ',
		'SE',
		'CH',
		'TW',
		'TJ',
		'TZ',
		'TH',
		'TG',
		'TO',
		'TT',
		'TN',
		'TM',
		'TC',
		'TV',
		'UG',
		'UA',
		'AE',
		'GB',
		'US',
		'UY',
		'VU',
		'VA',
		'VE',
		'VN',
		'WF',
		'YE',
		'ZM',
		'ZW',
	],
));

Configure::write('Search.date', array(
	'default' => '-60 days',
	'options' => array(
		'-24 hours' => 'past 24 hours',
		'-7 days' => 'Past week',
		'-30 days' => 'Past 30 days',
		'-60 days' => 'Past 60 days',
		'-90 days' => 'Past 90 days',
		'-120 days' => 'Past 120 days',
		'-1 year' => 'Past year',
		'0' => 'All',
	)
));

/**
 * SSL
 */
Configure::write('SSL', array(
	'enabled' => false,
));

/**
 * CDN Configuration
 */
Configure::write('CDN', array(
	'enabled' => false,
));

/**
 * Google service settings. Leave any ID fields empty to disable the
 * associated Javascript.
 */
Configure::write('Google', array(
	'SiteSearch' => array(
		'engine_id' => '', // Production engine. Leave empty to disable.
	),
	'Analytics' => array(
		'tracking_id' => '', // Leave empty to disable.
		'domain' => '',
	),
));

/**
 * Social networking accounts.
 *
 * Powers Elements/Layouts/social_meta_tags.ctp. Leave any [username]
 * fields empty to disable related OpenGraph meta tags.
 *
 * Also powers Elements/Layouts/social_icons.ctp. Leave [link] empty to
 * disable an icon. Place icons such as webroot/img/social-icons/Twitter.png,
 * or specify [image] keys for each service below. [width] and [height]
 * also optionally available (default 48x20 in the ctp.)
 */
Configure::write('SocialNetworks', array(
	'Twitter' => array(
		'link' => 'https://twitter.com/',
		'username' => '',
		// 'image' => '',
		// 'width' => '',
		// 'height' => '',
	),
	'Facebook' => array(
		'link' => 'https://www.facebook.com/',
		'username' => '',
		'profile_id' => false,
	),
	'YouTube' => array(
		'link' => 'http://www.youtube.com/user/',
		'username' => '',
	),
));

Configure::write('States.billing', array(
	''   => 'State',
	'AE' => 'Armed Forces Africa \ Canada \ Europe \ Middle East',
	'AA' => 'Armed Forces America (Except Canada)',
	'AP' => 'Armed Forces Pacific',
	'AL' => 'Alabama',
	'AK' => 'Alaska',
	'AS' => 'American Samoa',
	'AZ' => 'Arizona',
	'AR' => 'Arkansas',
	'CA' => 'California',
	'CO' => 'Colorado',
	'CT' => 'Connecticut',
	'DE' => 'Delaware',
	'DC' => 'District of Columbia',
	'FM' => 'Federated States of Micronesia',
	'FL' => 'Florida',
	'GA' => 'Georgia',
	'GU' => 'Guam GU',
	'HI' => 'Hawaii',
	'ID' => 'Idaho',
	'IL' => 'Illinois',
	'IN' => 'Indiana',
	'IA' => 'Iowa',
	'KS' => 'Kansas',
	'KY' => 'Kentucky',
	'LA' => 'Louisiana',
	'ME' => 'Maine',
	'MH' => 'Marshall Islands',
	'MD' => 'Maryland',
	'MA' => 'Massachusetts',
	'MI' => 'Michigan',
	'MN' => 'Minnesota',
	'MS' => 'Mississippi',
	'MO' => 'Missouri',
	'MT' => 'Montana',
	'NE' => 'Nebraska',
	'NV' => 'Nevada',
	'NH' => 'New Hampshire',
	'NJ' => 'New Jersey',
	'NM' => 'New Mexico',
	'NY' => 'New York',
	'NC' => 'North carolina',
	'ND' => 'North Dakota',
	'MP' => 'Northern Mariana Islands',
	'OH' => 'Ohio',
	'OK' => 'Oklahoma',
	'OR' => 'Oregon',
	'PW' => 'Palau',
	'PA' => 'Pennsylvania',
	'PR' => 'Puerto Rico',
	'RI' => 'Rhode Island',
	'SC' => 'South Carolina',
	'SD' => 'South Dakota',
	'TN' => 'Tennessee',
	'TX' => 'Texas',
	'UT' => 'Utah',
	'VT' => 'Vermont',
	'VI' => 'Virgin islands',
	'VA' => 'Virginia',
	'WA' => 'Washington',
	'WV' => 'West Virginia',
	'WI' => 'Wisconsin',
	'WY' => 'Wyoming'
));

Configure::write('Form.months', array(
	'01'  => 'January (01)',
	'02'  => 'Febuary (02)',
	'03'  => 'March (03)',
	'04'  => 'April (04)',
	'05'  => 'May (05)',
	'06'  => 'June (06)',
	'07'  => 'July (07)',
	'08'  => 'August (08)',
	'09'  => 'September (09)',
	'10' => 'October (10)',
	'11' => 'November (11)',
	'12' => 'December (12)',
));

$yearsInFuture = strtotime('+8 years');
Configure::write('Form.years', array_combine(
	range(date('y'), date('y', $yearsInFuture)),
	range(date('Y'), date('Y', $yearsInFuture))
));

Configure::write('Tooltip', array(
	'defaultAddress' => 'This is the address that is on file for your credit card. You can add your APO Box address to be an authorized shipping address on your credit card by calling the number on the back of your credit card.',
	'shippingAddress' => 'This is the address that we will forward your packages to. This must be an APO/FPO/DPO address',
	'emergencyAddress' => 'This is your backup shipping address. It is only used when a box is over the size or weight limit for your APO/FPO/DPO. It will also be used if your package is returned to us. This address must be in the US and cannot be another APO/FPO/DPO address.',
));

Configure::write('PostalClasses', array(
	'priority_mail' => 'Priority Mail',
	'parcel_post' => 'Parcel Select',
));

/**
 * Options for Credit Card logic
 *
 * invalid_before
 * --------------
 * A an ammount of time before a credit card expiration dat
 * that credit cards, while still valid, are considered invalid.
 *
 * Format follow PHP's DateInterval object's contrsuctor without
 * the leading 'P'.
 *
 * Y	Years
 * M	Months
 * D	Days
 * W	Weeks. These get converted into days, so can not be combined with D.
 *
 * For example, To invalidate a card 1 week before it expires, set this
 * to '1W'.
 *
 * Don't set this value or set it to an `empty()` value to treat cards as valid
 * up to the second they expire.
 */
Configure::write('CreditCard', array(
	'invalid_before' => '3D',
));

/**
 * Order configuration settings:
 * `feeRates`: Array of fee rates
 * `paymentReminders`: Number of times to send an awaiting payment email
 * to a customer.
 */
Configure::write('Orders', array(
	'feeRates' => array(
		'battery' => '5.00',
		'return' => '10.00',
		'misaddressed' => '5.00',
		'shipToUS' => '10.00',
	),
	'paymentReminders' => 3,
	'defaultCustomsDescription' => 'Household & Personal Goods',
	'minimumLabelValue' => '1.00',
));

/**
 * Customer configuration settings:
 * `signupReminders`: Number of times to send a partial signup email
 * `purgePartials`: Number of weeks before partial signups are deleted
 * `expiredCardReminders.numberToSend`: Number of times to send expired card reminders
 * `expiredCardReminders.maxMonths`: Maximum range to look for expired cards (for notify)
 * `expiredCardReminders.sendDelaySeconds`: Delay between email messages
 * `expiredCardReminders.sendMaxPerDay`: The most emails to send per run
 * `sources`: List for almost_finished, and stored in `CustomersInfo.source_id`
 */
Configure::write('Customers', [
	'signupReminders' => 2,
	'purgePartials' => 4,
	'expiredCardReminders' => [
		'numberToSend' => 1,
		'maxMonths' => 6,
		'sendDelaySeconds' => 2,
		'sendMaxPerRun' => 200,
	],
	'sources' => [
		0 => 'How did you hear about us?',
		1 => 'Google',
		2 => 'Yahoo',
		3 => 'Apple',
		4 => 'Stars and Stripes',
		5 => 'Amazon',
		6 => 'Army Times',
		7 => 'Target',
		8 => 'Friend',
		9 => 'APO FPO Post Office',
		9999 => 'Other',
	],
]);

/**
 * All configuration options related to shipping rate API queries
 */
Configure::write('ShippingApis', [
	'originZip' => '46563',

	/**
	 * Set which postal rate API to query. Valid options are:
	 * * `Usps`
	 */
	'Rates' => [
		'backend' => 'Usps',
	],

	/**
	 * Zone codes for a valid APO address
	 */
	'apoZones' => [
		'AA',
		'AE',
		'AP',
	],

	/**
	 * Credentials and options for USPS API rate query. The `rateClasses` array is
	 * what @CLASSID to show (as value). The key is not used and is for reference to
	 * the rate class only.
	 */
	'Usps' => [
		'userId' => '541LOADS1006', //@TODO: Change this to an APO Box owned userId
		'rateClasses' => [
			'Priority Mail Express 2-Day' => '3',
			'Priority Mail Express 2-Day Hold For Pickup' => '2',
			'Priority Mail 2-Day' => '1',
			'Standard Post' => '4',
			'Ground Advantage' => '1058',
			// 'Media Mail Parcel' => '6',
			// 'Library Mail Parcel' => '7',
		],
		'trackingUrl' => 'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=',
	],

	'Endicia' => [
		'accountNumber' => '948121',
		'customsSigner' => 'Melinda Hauptmann',
	],

	'Fedex' => [
		'auth' => [
			'apiKey' => 'DHca9acd5NXqEoqv',
			'apiPassword' => 'HuaSMpgT5cVbyUIOyl1n8DUxA',
			'apiAccount' => '372583720',
			'apiMeter' => '109351944',
		],
		'shipper' => [
			'Contact' => [
				'PersonName' => 'APO Box',
				'CompanyName' => 'APO Box',
				'PhoneNumber' => '8004096013'
			],
			'Address' => [
				'StreetLines' => [
					'1911 Western Avenue'
				],
				'City' => 'Plymouth',
				'StateOrProvinceCode' => 'IN',
				'PostalCode' => '46563',
				'CountryCode' => 'US'
			],
		],
		'label' => [
			'type' => 'ZPLII', // valid values DPL, EPL2, PDF, ZPLII and PNG
			'purge' => 4, // number of weeks before saved label data is deleted
		],
		'validCountries' => [
			// countries valid for FedEx shipments
			'United States',
		],
		'trackingUrl' => 'http://www.fedex.com/Tracking?action=track&tracknumbers=',
	],
]);

/**
 * Zebra / ZPL label printer configuration
 */
Configure::write('ZebraLabel', [
	/**
	 * The method to print by. Options are 'network' to send to the network or
	 * 'raw' to return the raw ZPL data.
	 */
	'method' => 'raw',
	/**
	 * If network printing, the IP address of the printer
	 */
	'client' => null,
	/**
	 * Set to true to enable automatic label printing on charge error
	 */
	'auto' => false,
]);

/**
 * OAuth2 login credentials and configuration settings
 */
Configure::write('OAuth2', [
	/**
	 * Set to true to log the email address of unauthorized login attempts
	 */
	'logFailedAttemps' => true,
	/**
	 * Set to true to enable username/password admin login
	 */
	'legacyLogin' => false,
	/**
	 * Google OAuth2 credentials
	 * see: https://console.developers.google.com
	 */
	'Google' => [
		'clientId' => '241464603789-i510ocku0rhik3mvsl29qoo6lel1ulv8.apps.googleusercontent.com',
		'clientSecret' => 'Y69yoZ-L1dtW0ckDpNfOx8IG',
		'redirectUri' => 'https://account.apobox.com/admin/login-google',
	],
]);

/**
 * Load environment-specific overrides.
 *
 * File such as `Config/core-production.php` can be created to match the
 * `APP_ENV` environment variable and must contain a $config = array(...);
 * definition in them to override any values defined here in `core.php` or in
 * `bootstrap.php`. Any configuration changes that are environment-specific
 * should be made in the appropriate file. See also: `Config/database.php` for
 * allowed APP_ENV values.
 */
$env = getenv('APP_ENV');
if (is_readable(dirname(__FILE__) . "/core-{$env}.php")) {
	Configure::load("core-{$env}");
}

/**
 * Load developer-specific overrides. (Allows a developer to customize their
 * local config as needed for testing by placing their definitions in an
 * (untracked) `Config/core-local.php` file.)
 */
if (is_readable(dirname(__FILE__) . "/core-local.php")) {
	Configure::load("core-local");
}

/**
 * Session configuration must happen after all environment configurations
 * are applied.
 */
if (!in_array($env, ['dev', 'circle'])) {
	Cache::config('session', array(
		'engine' => 'Memcached',
		'servers' => Configure::read('Memcached.servers'),
		'prefix' => '',
	));
	Configure::write('Session', array(
		'defaults' => 'cache',
		'handler' => array(
			'config' => 'session',
			'engine' => 'NodeSession',
		),
		'cookie' => 'apobox',
		'ini' => array(
			'session.cookie_domain' => Configure::read('Cookie.domain'),
			'session.cookie_path' => '/; SameSite=None',
		),
		'checkAgent' => false,
	));
}

unset($email); // Clean up helper function.
