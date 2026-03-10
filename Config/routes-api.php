<?php
// Defins a key => value store of "route prefix" => "method prefix"
// "Route prefix MUST NOT begin or end with a `/`
// "Method prefix" MUST be compatible with PHP methond name restrictions.
$prefixes = array(
	'api' => 'api',
);

Router::mapResources(array(
    '{controller_name}',
));

/**
 * Wire up all prefixed routes
 */
if (!empty($prefixes)) {
	foreach ($prefixes as $route => $prefix) {
		/**
		 * Customers controller routes
		 */
		Router::connect("/{$route}/customers/:id/notify", array(
			'[method]' => 'POST',
			'controller' => 'customers',
			'action' => 'notify',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$route}/customers/:id", array(
			'[method]' => 'GET',
			'controller' => 'customers',
			'action' => 'view',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$route}/customers", array(
			'[method]' => 'POST',
			'controller' => 'customers',
			'action' => 'add',
			$prefix => true
		));

		Router::connect("/{$route}/customers/signin", array(
			'[method]' => 'POST',
			'controller' => 'customers',
			'action' => 'login',
			$prefix => true
		));

		/**
		 * Addresses controller routes
		 */
		Router::connect("/{$route}/addresses", array(
			'[method]' => 'POST',
			'controller' => 'addresses',
			'action' => 'add',
			$prefix => true
		));

		/**
		 * Payment route from widget
		 */
		Router::connect("/{$route}/payments", array(
			'[method]' => 'POST',
			'controller' => 'customers',
			'action' => 'charge',
			$prefix => true
		));

		/**
		 * Orders controller routes
		 */
		Router::connect("/{$route}/orders/changestatus", array(
			'[method]' => 'PATCH',
			'controller' => 'orders',
			'action' => 'changestatus',
			$prefix => true
		));

		Router::connect("/{$route}/orders/:id/add", array(
			'[method]' => 'POST',
			'controller' => 'orders',
			'action' => 'add',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$route}/orders/:id/charge", array(
			'[method]' => 'PATCH',
			'controller' => 'orders',
			'action' => 'charge',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$route}/orders/:id", array(
			'[method]' => 'GET',
			'controller' => 'orders',
			'action' => 'view',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		/**
		 * API Base routes
		 */
		Router::connect("/{$route}", array(
			'controller' => 'apis',
			'action' => 'index',
			$prefix => true,
		));

		Router::connect("/{$route}/**", array(
			'controller' => 'apis',
			'action' => 'not_implemented',
			$prefix => true
		));

	}
}

unset($prefixes);
