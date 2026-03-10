<?php
/**
 * ZoneFixture
 *
 */
class ZoneFixture extends CakeTestFixture {

	/**
	 * Fields
	 *
	 * @var	array
	 */
	public $fields = array(
		'zone_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'zone_country_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'zone_code' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'zone_name' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'zone_id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'MyISAM')
	);

	/**
	 * Records
	 *
	 * @var	array
	 */
	public $records = array (
    array (
      'zone_id' => 1,
      'zone_country_id' => 223,
      'zone_code' => 'AL',
      'zone_name' => 'Alabama',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'AK',
      'zone_name' => 'Alaska',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'AS',
      'zone_name' => 'American Samoa',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'AZ',
      'zone_name' => 'Arizona',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'AR',
      'zone_name' => 'Arkansas',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'AA',
      'zone_name' => 'AA',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'AE',
      'zone_name' => 'AE',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'AP',
      'zone_name' => 'AP',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'CA',
      'zone_name' => 'California',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'CO',
      'zone_name' => 'Colorado',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'CT',
      'zone_name' => 'Connecticut',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'DE',
      'zone_name' => 'Delaware',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'DC',
      'zone_name' => 'District of Columbia',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'FM',
      'zone_name' => 'Federated States Of Micronesia',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'FL',
      'zone_name' => 'Florida',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'GA',
      'zone_name' => 'Georgia',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'GU',
      'zone_name' => 'Guam',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'HI',
      'zone_name' => 'Hawaii',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'ID',
      'zone_name' => 'Idaho',
    ),
    array (
      'zone_id' => 23,
      'zone_country_id' => 223,
      'zone_code' => 'IL',
      'zone_name' => 'Illinois',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'IN',
      'zone_name' => 'Indiana',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'IA',
      'zone_name' => 'Iowa',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'KS',
      'zone_name' => 'Kansas',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'KY',
      'zone_name' => 'Kentucky',
    ),
    array (
      'zone_id' => 28,
      'zone_country_id' => 223,
      'zone_code' => 'LA',
      'zone_name' => 'Louisiana',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'ME',
      'zone_name' => 'Maine',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'MH',
      'zone_name' => 'Marshall Islands',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'MD',
      'zone_name' => 'Maryland',
    ),
    array (
      'zone_id' => 32,
      'zone_country_id' => 223,
      'zone_code' => 'MA',
      'zone_name' => 'Massachusetts',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'MI',
      'zone_name' => 'Michigan',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'MN',
      'zone_name' => 'Minnesota',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'MS',
      'zone_name' => 'Mississippi',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'MO',
      'zone_name' => 'Missouri',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'MT',
      'zone_name' => 'Montana',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'NE',
      'zone_name' => 'Nebraska',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'NV',
      'zone_name' => 'Nevada',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'NH',
      'zone_name' => 'New Hampshire',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'NJ',
      'zone_name' => 'New Jersey',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'NM',
      'zone_name' => 'New Mexico',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'NY',
      'zone_name' => 'New York',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'NC',
      'zone_name' => 'North Carolina',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'ND',
      'zone_name' => 'North Dakota',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'MP',
      'zone_name' => 'Northern Mariana Islands',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'OH',
      'zone_name' => 'Ohio',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'OK',
      'zone_name' => 'Oklahoma',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'OR',
      'zone_name' => 'Oregon',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'PW',
      'zone_name' => 'Palau',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'PA',
      'zone_name' => 'Pennsylvania',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'PR',
      'zone_name' => 'Puerto Rico',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'RI',
      'zone_name' => 'Rhode Island',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'SC',
      'zone_name' => 'South Carolina',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'SD',
      'zone_name' => 'South Dakota',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'TN',
      'zone_name' => 'Tennessee',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'TX',
      'zone_name' => 'Texas',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'UT',
      'zone_name' => 'Utah',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'VT',
      'zone_name' => 'Vermont',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'VI',
      'zone_name' => 'Virgin Islands',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'VA',
      'zone_name' => 'Virginia',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'WA',
      'zone_name' => 'Washington',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'WV',
      'zone_name' => 'West Virginia',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'WI',
      'zone_name' => 'Wisconsin',
    ),
    array (
      'zone_country_id' => 223,
      'zone_code' => 'WY',
      'zone_name' => 'Wyoming',
    ),
    array (
      'zone_country_id' => 38,
      'zone_code' => 'AB',
      'zone_name' => 'Alberta',
    ),
    array (
      'zone_country_id' => 38,
      'zone_code' => 'BC',
      'zone_name' => 'British Columbia',
    ),
    array (
      'zone_country_id' => 38,
      'zone_code' => 'MB',
      'zone_name' => 'Manitoba',
    ),
    array (
      'zone_country_id' => 38,
      'zone_code' => 'NF',
      'zone_name' => 'Newfoundland',
    ),
    array (
      'zone_country_id' => 38,
      'zone_code' => 'NB',
      'zone_name' => 'New Brunswick',
    ),
    array (
      'zone_country_id' => 38,
      'zone_code' => 'NS',
      'zone_name' => 'Nova Scotia',
    ),
    array (
      'zone_country_id' => 38,
      'zone_code' => 'NT',
      'zone_name' => 'Northwest Territories',
    ),
    array (
      'zone_country_id' => 38,
      'zone_code' => 'NU',
      'zone_name' => 'Nunavut',
    ),
    array (
      'zone_country_id' => 38,
      'zone_code' => 'ON',
      'zone_name' => 'Ontario',
    ),
    array (
      'zone_country_id' => 38,
      'zone_code' => 'PE',
      'zone_name' => 'Prince Edward Island',
    ),
    array (
      'zone_country_id' => 38,
      'zone_code' => 'QC',
      'zone_name' => 'Quebec',
    ),
    array (
      'zone_country_id' => 38,
      'zone_code' => 'SK',
      'zone_name' => 'Saskatchewan',
    ),
    array (
      'zone_country_id' => 38,
      'zone_code' => 'YT',
      'zone_name' => 'Yukon Territory',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'NDS',
      'zone_name' => 'Niedersachsen',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'BAW',
      'zone_name' => '',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'BAY',
      'zone_name' => 'Bayern',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'BER',
      'zone_name' => 'Berlin',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'BRG',
      'zone_name' => 'Brandenburg',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'BRE',
      'zone_name' => 'Bremen',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'HAM',
      'zone_name' => 'Hamburg',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'HES',
      'zone_name' => 'Hessen',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'MEC',
      'zone_name' => 'Mecklenburg-Vorpommern',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'NRW',
      'zone_name' => 'Nordrhein-Westfalen',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'RHE',
      'zone_name' => 'Rheinland-Pfalz',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'SAR',
      'zone_name' => 'Saarland',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'SAS',
      'zone_name' => 'Sachsen',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'SAC',
      'zone_name' => 'Sachsen-Anhalt',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'SCN',
      'zone_name' => 'Schleswig-Holstein',
    ),
    array (
      'zone_country_id' => 81,
      'zone_code' => 'THE',
      'zone_name' => '',
    ),
    array (
      'zone_country_id' => 14,
      'zone_code' => 'WI',
      'zone_name' => 'Wien',
    ),
    array (
      'zone_country_id' => 14,
      'zone_code' => 'NO',
      'zone_name' => '',
    ),
    array (
      'zone_country_id' => 14,
      'zone_code' => 'OO',
      'zone_name' => '',
    ),
    array (
      'zone_country_id' => 14,
      'zone_code' => 'SB',
      'zone_name' => 'Salzburg',
    ),
    array (
      'zone_country_id' => 14,
      'zone_code' => 'KN',
      'zone_name' => '',
    ),
    array (
      'zone_country_id' => 14,
      'zone_code' => 'ST',
      'zone_name' => 'Steiermark',
    ),
    array (
      'zone_country_id' => 14,
      'zone_code' => 'TI',
      'zone_name' => 'Tirol',
    ),
    array (
      'zone_country_id' => 14,
      'zone_code' => 'BL',
      'zone_name' => 'Burgenland',
    ),
    array (
      'zone_country_id' => 14,
      'zone_code' => 'VB',
      'zone_name' => 'Voralberg',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'AG',
      'zone_name' => 'Aargau',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'AI',
      'zone_name' => 'Appenzell Innerrhoden',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'AR',
      'zone_name' => 'Appenzell Ausserrhoden',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'BE',
      'zone_name' => 'Bern',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'BL',
      'zone_name' => 'Basel-Landschaft',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'BS',
      'zone_name' => 'Basel-Stadt',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'FR',
      'zone_name' => 'Freiburg',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'GE',
      'zone_name' => 'Genf',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'GL',
      'zone_name' => 'Glarus',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'JU',
      'zone_name' => '',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'JU',
      'zone_name' => 'Jura',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'LU',
      'zone_name' => 'Luzern',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'NE',
      'zone_name' => 'Neuenburg',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'NW',
      'zone_name' => 'Nidwalden',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'OW',
      'zone_name' => 'Obwalden',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'SG',
      'zone_name' => 'St. Gallen',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'SH',
      'zone_name' => 'Schaffhausen',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'SO',
      'zone_name' => 'Solothurn',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'SZ',
      'zone_name' => 'Schwyz',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'TG',
      'zone_name' => 'Thurgau',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'TI',
      'zone_name' => 'Tessin',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'UR',
      'zone_name' => 'Uri',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'VD',
      'zone_name' => 'Waadt',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'VS',
      'zone_name' => 'Wallis',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'ZG',
      'zone_name' => 'Zug',
    ),
    array (
      'zone_country_id' => 204,
      'zone_code' => 'ZH',
      'zone_name' => '',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => '',
      'zone_name' => '',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Alava',
      'zone_name' => 'Alava',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Albacete',
      'zone_name' => 'Albacete',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Alicante',
      'zone_name' => 'Alicante',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Almeria',
      'zone_name' => 'Almeria',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Asturias',
      'zone_name' => 'Asturias',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Avila',
      'zone_name' => 'Avila',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Badajoz',
      'zone_name' => 'Badajoz',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Baleares',
      'zone_name' => 'Baleares',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Barcelona',
      'zone_name' => 'Barcelona',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Burgos',
      'zone_name' => 'Burgos',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Caceres',
      'zone_name' => 'Caceres',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Cadiz',
      'zone_name' => 'Cadiz',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Cantabria',
      'zone_name' => 'Cantabria',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Castellon',
      'zone_name' => 'Castellon',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Ceuta',
      'zone_name' => 'Ceuta',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Ciudad Real',
      'zone_name' => 'Ciudad Real',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Cordoba',
      'zone_name' => 'Cordoba',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Cuenca',
      'zone_name' => 'Cuenca',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Girona',
      'zone_name' => 'Girona',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Granada',
      'zone_name' => 'Granada',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Guadalajara',
      'zone_name' => 'Guadalajara',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Guipuzcoa',
      'zone_name' => 'Guipuzcoa',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Huelva',
      'zone_name' => 'Huelva',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Huesca',
      'zone_name' => 'Huesca',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Jaen',
      'zone_name' => 'Jaen',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'La Rioja',
      'zone_name' => 'La Rioja',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Las Palmas',
      'zone_name' => 'Las Palmas',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Leon',
      'zone_name' => 'Leon',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Lleida',
      'zone_name' => 'Lleida',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Lugo',
      'zone_name' => 'Lugo',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Madrid',
      'zone_name' => 'Madrid',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Malaga',
      'zone_name' => 'Malaga',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Melilla',
      'zone_name' => 'Melilla',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Murcia',
      'zone_name' => 'Murcia',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Navarra',
      'zone_name' => 'Navarra',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Ourense',
      'zone_name' => 'Ourense',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Palencia',
      'zone_name' => 'Palencia',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Pontevedra',
      'zone_name' => 'Pontevedra',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Salamanca',
      'zone_name' => 'Salamanca',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Santa Cruz de Tenerife',
      'zone_name' => 'Santa Cruz de Tenerife',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Segovia',
      'zone_name' => 'Segovia',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Sevilla',
      'zone_name' => 'Sevilla',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Soria',
      'zone_name' => 'Soria',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Tarragona',
      'zone_name' => 'Tarragona',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Teruel',
      'zone_name' => 'Teruel',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Toledo',
      'zone_name' => 'Toledo',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Valencia',
      'zone_name' => 'Valencia',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Valladolid',
      'zone_name' => 'Valladolid',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Vizcaya',
      'zone_name' => 'Vizcaya',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Zamora',
      'zone_name' => 'Zamora',
    ),
    array (
      'zone_country_id' => 195,
      'zone_code' => 'Zaragoza',
      'zone_name' => 'Zaragoza',
    ),
    array (
      'zone_country_id' => 250,
      'zone_code' => 'AA',
      'zone_name' => 'AA',
    ),
    array (
      'zone_country_id' => 250,
      'zone_code' => 'AE',
      'zone_name' => 'AE',
    ),
    array (
      'zone_country_id' => 250,
      'zone_code' => 'AP',
      'zone_name' => 'AP',
    ),
	);

}
