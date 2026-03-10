<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Load API routes
 */
include "routes-api.php";

/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
Router::redirect('/', array(
	'controller' => 'customers',
	'action' => 'account'
), array(
	'persist' => true
));

/**
 * ...and connect the rest of 'Pages' controller's URLs.
 */
Router::connect('/pages/*', array(
	'controller' => 'pages',
	'action' => 'display',
));

/**
 * Local version of the Terms of Service (fetched from WordPress site)
 */
Router::connect('/tos', array(
	'controller' => 'pages',
	'action' => 'tos',
));

/**
 * Widget documentation with demos (possibly pulled from widget docs)
 */
Router::connect('/developers/widget', array(
	'controller' => 'pages',
	'action' => 'developers_widget',
));

/**
 * CustomersController routes
 */
Router::connect('/login', array(
	'controller' => 'customers',
	'action' => 'login',
));

Router::connect('/logout', array(
	'controller' => 'customers',
	'action' => 'logout'
));

Router::connect('/customers/account-incomplete', array(
	'controller' => 'customers',
	'action' => 'account_incomplete'
));

Router::connect('/customers/almost-finished', array(
	'controller' => 'customers',
	'action' => 'almost_finished'
));

Router::connect('/customers/change-password', array(
	'controller' => 'customers',
	'action' => 'change_password'
));

Router::connect('/forgot-password', array(
	'controller' => 'customers',
	'action' => 'forgot_password'
));

Router::connect('/reset-password/:uuid', array(
	'controller' => 'customers',
	'action' => 'reset_password'
), array(
	'pass' => array('uuid'),
));

Router::connect('/account', array(
	'controller' => 'customers',
	'action' => 'account'
));

Router::connect('/customers/edit/:partial', array(
	'controller' => 'customers',
	'action' => 'edit_partial',
), array(
	'pass' => array('partial'),
	'partial' => '[A-z\_]+',
));

Router::connect("/customers", array(
	'[method]' => 'POST',
	'controller' => 'customers',
	'action' => 'add',
));

Router::connect('/close-account/:hash', array(
	'controller' => 'customers',
	'action' => 'close_account'
), array(
	'pass' => array('hash'),
	'hash' => '([a-f0-9]{40})',
));

Router::connect('/confirm-close/:customerId/:hash', array(
	'controller' => 'customers',
	'action' => 'confirm_close'
), array(
	'pass' => array('customerId', 'hash'),
	'customerId' => '[0-9]+',
	'hash' => '([a-f0-9]{40})',
));

/**
 * AuthorizedNamesController routes
 */
Router::connect('/authorized_names/add', array(
	'controller' => 'authorized_names',
	'action' => 'add'
));

Router::connect('/authorized_names/:id/edit', array(
	'controller' => 'authorized_names',
	'action' => 'edit'
), array(
	'pass' => array('id'),
));

Router::connect('/authorized_names/:id/delete', array(
	'controller' => 'authorized_names',
	'action' => 'delete'
), array(
	'pass' => array('id'),
));

/**
 * AddressesController routes
 */
Router::connect('/address/add', array(
	'controller' => 'addresses',
	'action' => 'add'
));

Router::connect('/addresses', array(
	'[method]' => 'POST',
	'controller' => 'addresses',
	'action' => 'add'
));

Router::connect('/address/:id/edit', array(
	'controller' => 'addresses',
	'action' => 'edit'
), array(
	'pass' => array('id'),
));

Router::connect('/address/:id/delete', array(
	'controller' => 'addresses',
	'action' => 'delete'
), array(
	'pass' => array('id'),
));

/**
 * CustomPackageRequestsController routes
 */
Router::connect('/requests/add', array(
	'controller' => 'custom_package_requests',
	'action' => 'add'
));

Router::connect('/requests/edit/*', array(
	'controller' => 'custom_package_requests',
	'action' => 'edit'
));

Router::connect('/requests/delete/*', array(
	'controller' => 'custom_package_requests',
	'action' => 'delete'
));

Router::connect('/requests/*', array(
	'controller' => 'custom_package_requests',
	'action' => 'index'
));

/**
 * OrdersController routes
 */
Router::connect("/orders/:id", array(
	'controller' => 'orders',
	'action' => 'view',
), array(
	'pass' => array('id'),
	'id' => '[0-9]+',
));

Router::connect("/orders/:id/pay", array(
	'controller' => 'orders',
	'action' => 'pay_manually',
), array(
	'pass' => array('id')
));

Router::connect("/orders/*", array(
	'controller' => 'orders',
	'action' => 'index',
));

/**
 * Admin login / logout routes
 */
Router::redirect('/admin', array(
	'controller' => 'admins',
	'action' => 'login',
), array(
	'persist' => true
));

Router::connect("/admin/login", array(
	'controller' => 'admins',
	'action' => 'login',
));

Router::connect("/admin/logout", array(
	'controller' => 'admins',
	'action' => 'logout',
));

Router::connect("/admin/login-google", array(
	'controller' => 'admins',
	'action' => 'login_google',
));

/**
 * Wire up all prefixed routes
 */
if ($prefixes = Configure::read('Routing.prefixes')) {
	// We don't need api prefixed routes for these
	$prefixes = array_filter($prefixes, function ($value) {
		return $value != 'api';
	});

	foreach ($prefixes as $prefix) {
		Router::connect("/{$prefix}", array(
			'controller' => 'admins',
			'action' => 'index',
			$prefix => true,
		));

		/**
		 * Prefixed routes for PagesController, currently configured to work
		 * with `manager` prefix only.
		 */
		Router::connect("/{$prefix}/pages/*", array(
			'controller' => 'pages',
			'action' => 'display',
			$prefix => true,
		));

		/**
		 * This redirect is required to allow expired admin sessions to
		 * properly redirect to the admin login page.
		 */
		Router::redirect("/{$prefix}/customers/login", array(
			'controller' => 'admins',
			'action' => 'login',
			$prefix => false,
		));

		Router::connect("/{$prefix}/admins/index/*", array(
			'controller' => 'admins',
			'action' => 'index_list',
			$prefix => true,
		));

		Router::connect("/{$prefix}/admins/add", array(
			'controller' => 'admins',
			'action' => 'add',
			$prefix => true,
		));

		Router::connect("/{$prefix}/admins/edit/*", array(
			'controller' => 'admins',
			'action' => 'edit',
			$prefix => true,
		));

		Router::connect("/{$prefix}/admins/delete/*", array(
			'controller' => 'admins',
			'action' => 'delete',
			$prefix => true,
		));

		Router::connect("/{$prefix}/customers/report/*", array(
			'controller' => 'customers_infos',
			'action' => 'report',
			$prefix => true
		));

		Router::connect("/{$prefix}/customers/demographics/*", array(
			'controller' => 'customers',
			'action' => 'demographics_report',
			$prefix => true
		));

		Router::connect("/{$prefix}/customers/:billingId/view", array(
			'controller' => 'customers',
			'action' => 'view_billing',
			$prefix => true
		), array(
			'pass' => array('billingId'),
			'billingId' => '[A-Z]{2}\d{4}'
		));

		Router::connect("/{$prefix}/customers/view/:id", array(
			'controller' => 'customers',
			'action' => 'view',
			$prefix => true
		), array(
			'pass' => array('id'),
			'id' => '[0-9]+',
		));

		Router::connect("/{$prefix}/customers/:id/recent-orders", array(
			'controller' => 'customers',
			'action' => 'recent',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/customers/:id/edit/payment-info", array(
			'controller' => 'customers',
			'action' => 'edit_payment_info',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/customers/:id/edit/contact-info", array(
			'controller' => 'customers',
			'action' => 'edit_contact_info',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/customers/:customerId/authorized_names/add", array(
			'controller' => 'authorized_names',
			'action' => 'add',
			$prefix => true
		), array(
			'pass' => array('customerId')
		));

		Router::connect("/{$prefix}/authorized_names/:id/edit", array(
			'controller' => 'authorized_names',
			'action' => 'edit',
			$prefix => true,
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/authorized_names/:id/delete", array(
			'controller' => 'authorized_names',
			'action' => 'delete',
			$prefix => true,
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/customers/:id/addresses", array(
			'controller' => 'customers',
			'action' => 'addresses',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/customers/:id/shippingAddresses", array(
			'controller' => 'customers',
			'action' => 'shipping_addresses',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/customers/:id/edit/default-addresses", array(
			'controller' => 'customers',
			'action' => 'edit_default_addresses',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/customers/:customerId/address/add", array(
			'controller' => 'addresses',
			'action' => 'add',
			$prefix => true
		), array(
			'pass' => array('customerId')
		));

		Router::connect("/{$prefix}/customers/:customerId/close-account", array(
			'controller' => 'customers',
			'action' => 'close_account',
			$prefix => true
		), array(
			'pass' => array('customerId'),
			'id' => '[0-9]+',
		));

		Router::connect("/{$prefix}/customers/quick-order/*", array(
			'controller' => 'customers',
			'action' => 'quick_order',
			$prefix => true
		));

		Router::connect("/{$prefix}/customers/*", array(
			'controller' => 'customers',
			'action' => 'search',
			$prefix => true
		));

		Router::connect("/{$prefix}/orders/delete/*", array(
			'controller' => 'orders',
			'action' => 'delete',
			$prefix => true,
		));

		Router::connect("/{$prefix}/orders/statustotals", array(
			'controller' => 'orders',
			'action' => 'statustotals',
			$prefix => true
		));

		Router::connect("/{$prefix}/orders/report/*", array(
			'controller' => 'orders',
			'action' => 'report',
			$prefix => true
		));

		Router::connect("/{$prefix}/orders/add/:customerId", array(
			'controller' => 'orders',
			'action' => 'add',
			$prefix => true
		), array(
			'pass' => array('customerId'),
			'customerId' => '[0-9]+',
		));

		Router::connect("/{$prefix}/orders/:id", array(
			'controller' => 'orders',
			'action' => 'view',
			$prefix => true
		), array(
			'pass' => array('id'),
			'id' => '[0-9]+'
		));

		Router::connect("/{$prefix}/orders/:id/mark-shipped", array(
			'controller' => 'orders',
			'action' => 'mark_as_shipped',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/orders/:id/update-status", array(
			'controller' => 'orders',
			'action' => 'update_status',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/orders/recent/*", array(
			'controller' => 'customers',
			'action' => 'recent',
			$prefix => true
		));

		Router::connect("/{$prefix}/orders/:id/charge", array(
			'controller' => 'orders',
			'action' => 'charge',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/orders/:id/print_label", array(
			'controller' => 'orders',
			'action' => 'print_label',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/orders/:id/print_fedex", array(
			'controller' => 'orders',
			'action' => 'print_fedex',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/orders/:id/print_fedex/:reprint", array(
			'controller' => 'orders',
			'action' => 'print_fedex',
			$prefix => true
		), array(
			'pass' => array('id', 'reprint')
		));

		Router::connect("/{$prefix}/orders/:id/delete_label", array(
			'controller' => 'orders',
			'action' => 'delete_label',
			$prefix => true
		), array(
			'pass' => array('id')
		));

		Router::connect("/{$prefix}/orders/*", array(
			'controller' => 'orders',
			'action' => 'search',
			$prefix => true
		));

		Router::connect("/{$prefix}/customer/:customerId/request/add", array(
			'controller' => 'custom_package_requests',
			'action' => 'add',
			$prefix => true,
		), array(
			'pass' => array('customerId')
		));

		Router::connect("/{$prefix}/reports/index", array(
			'controller' => 'reports',
			'action' => 'index',
			$prefix => true,
		));

		Router::connect("/{$prefix}/requests/edit/*", array(
			'controller' => 'custom_package_requests',
			'action' => 'edit',
			$prefix => true,
		));

		Router::connect("/{$prefix}/requests/delete/*", array(
			'controller' => 'custom_package_requests',
			'action' => 'delete',
			$prefix => true,
		));

		Router::connect("/{$prefix}/requests/*", array(
			'controller' => 'custom_package_requests',
			'action' => 'index',
			$prefix => true,
		));

		Router::connect("/{$prefix}/scan", array(
			'controller' => 'trackings',
			'action' => 'add',
			$prefix => true,
		));

		Router::connect("/{$prefix}/scan/delete/*", array(
			'controller' => 'trackings',
			'action' => 'delete',
			$prefix => true,
		));

		Router::connect("/{$prefix}/scan/edit/*", array(
			'controller' => 'trackings',
			'action' => 'edit',
			$prefix => true,
		));

		Router::connect("/{$prefix}/scans/*", array(
			'controller' => 'trackings',
			'action' => 'search',
			$prefix => true,
		));

		Router::connect("/{$prefix}/logs/view/*", array(
			'controller' => 'logs',
			'action' => 'view',
			$prefix => true,
		));

		Router::connect("/{$prefix}/affiliate-links", array(
			'controller' => 'affiliate_links',
			'action' => 'index',
			$prefix => true,
		));

		Router::connect("/{$prefix}/affiliate-links/add", array(
			'controller' => 'affiliate_links',
			'action' => 'add',
			$prefix => true,
		));

		Router::connect("/{$prefix}/affiliate-links/edit/*", array(
			'controller' => 'affiliate_links',
			'action' => 'edit',
			$prefix => true,
		));

		Router::connect("/{$prefix}/affiliate-links/delete/*", array(
			'controller' => 'affiliate_links',
			'action' => 'delete',
			$prefix => true,
		));
	}
}

/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
//CakePlugin::routes();
