## Endicia API Plugin

**NOTE:** This plugin is currently NOT used anywhere in the APOBOX app.

This plugin contains the following:

* `Lib/EwsLabelService.php` - A library that converts PHP array input into XML, makes requests to the Endicia LabelService API, and converts response XML back into PHP arrays. Currently supported methods allow for querying rates and fetching and saving postage labels.
* `Example` contains an example controller and app lib to interface with the EwsLabelService lib.

Example required configuration in `Config/core.php`:

```
/**
 * All configuration options related to shipping rate API queries
 */
Configure::write('ShippingApis', array(
	/**
	 * Set which postal rate API to query. Valid options are:
	 * * `Endicia`
	 */
	'Rates' => array(
		'backend' => 'Endicia',
	),

	/**
	 * Credentials and options for Endicia API rate query. The `rateClasses` array is
	 * what `MailClass` to show (as value).
	 */
	'Endicia' => array(
		'credentials' => array(
			'requesterId' => '@TODO: add me',
			'accountId' => '@TODO: add me',
			'password' => '@TODO: add me',
		),
		'rateClasses' => array(
			'Priority',
			'PriorityExpress',
			'ParcelSelect',
			'First',
			'StandardPost',
			// 'CriticalMail',
			// 'MediaMail',
			// 'LibraryMail',
		),
	),
));
```
