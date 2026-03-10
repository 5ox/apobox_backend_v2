<?php
App::uses('AppHelper', 'View/Helper');

/**
 * Class: TrackingHelper
 *
 * @see AppHelper
 */
class TrackingHelper extends AppHelper {

	/**
	 * helpers
	 *
	 * @var array
	 */
	public $helpers = array(
		'Html',
		'Time',
		'Number',
	);

	/**
	 * carriers
	 *
	 * @var array
	 */
	public $carriers = array(
		'amazon' => array(
			'field' => 'amazon_track_num',
			'pattern' => '/^TBA[0-9]{12}$/',
			'url' => null,
		),
		'dhl' => array(
			'field' => 'dhl_track_num',
			'pattern' => '/^[0-9]{2}[0-9]{4}[0-9]{4}$/',
			'url' => 'http://track.dhl-usa.com/TrackByNbr.asp?ShipmentNumber=%s',
		),
		'fedex_freight' => array(
			'field' => 'fedex_freight_track_num',
			'pattern' => '/^no_match$/',
			'url' => null // config var 'ShippingApis.Fedex.trackingUrl'
		),
		'fedex' => array(
			'field' => 'fedex_track_num',
			'pattern' => '/^([0-9]{20})?([0-9]{4}[0-9]{4}[0-9]{4}[0-9]{2})$/',
			'url' => null // config var 'ShippingApis.Fedex.trackingUrl'
		),
		'ups' => array(
			'field' => 'ups_track_num',
			'pattern' => '/^1Z[A-Z0-9]{3}[A-Z0-9]{3}[0-9]{2}[0-9]{4}[0-9]{4}$/i',
			'url' => 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=%s',
		),
		'usps' => array(
			'field' => 'usps_track_num_in',
			'pattern' => '/^[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{2}$/',
			'url' => null // config var 'ShippingApis.Usps.trackingUrl'
		),
	);

	/**
	 * The constructor to set USPS and Fedex tracking urls which are configure
	 * variables in `ShippingApis`.
	 *
	 * @param View $View The View this helper is being attached to.
	 * @param array $settings Optional settings
	 * @return void
	 */
	public function __construct(View $View, array $settings = []) {
		parent::__construct($View, $settings);
		$this->carriers['usps']['url'] = Configure::read('ShippingApis.Usps.trackingUrl') . '%s';
		$this->carriers['fedex']['url'] = Configure::read('ShippingApis.Fedex.trackingUrl') . '%s';
		$this->carriers['fedex_freight']['url'] = Configure::read('ShippingApis.Fedex.trackingUrl') . '%s';
	}

	/**
	 * Takes a link string and an order array and returns the correct inbound tracking link
	 * to the carrier's tracking page with a new tab window target.
	 *
	 * @param array $order An order data array
	 * @param mixed $text The tracking number text
	 * @return string
	 */
	public function inboundTrackingLink($order, $text = null) {
		if (empty($order['Order']['inbound_tracking']) || empty($order['Order']['inbound_carrier'])) {
			return $this->inbound($order);
		}
		if (empty($text)) {
			$text = $order['Order']['inbound_tracking'];
		}

		$url = $this->carriers[$order['Order']['inbound_carrier']]['url'];
		if (empty($url)) {
			return $text;
		} else {
			return $this->Html->link($text, sprintf($url, $order['Order']['inbound_tracking']), array('target' => '_blank'));
		}
	}

	/**
	 * Takes an order array and returns the correct inbound tracking code
	 * linked to the carrier's tracking page.
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function inbound($order) {
		$links = array();
		foreach ($this->carriers as $carrier) {
			if (!empty($order['Order'][$carrier['field']])) {
				if (empty($carrier['url'])) {
					$links[] = $order['Order'][$carrier['field']];
				} else {
					$links[] = $this->Html->link(
						$order['Order'][$carrier['field']],
						sprintf($carrier['url'], $order['Order'][$carrier['field']])
					);
				}
			}
		}
		return implode(', ', $links);
	}

	/**
	 * Takes a request array and returns the inbound tracking code linked to
	 * the carrier's tracking page.
	 *
	 * @param array $request A CustomPackageRequest data array
	 * @return string
	 */
	public function requestInbound($request) {
		if (empty($request['CustomPackageRequest']['tracking_id'])) {
			return '';
		}

		$trackingId = $request['CustomPackageRequest']['tracking_id'];
		foreach ($this->carriers as $carrier) {
			if ($this->matchCarrier($trackingId, $carrier)) {
				return $this->Html->link(
					$trackingId,
					sprintf($carrier['url'], $trackingId)
				);
			}
		}
		return $trackingId;
	}

	/**
	 * matchCarrier
	 *
	 * @param string $trackingId The tracking id
	 * @param array $carrier The carrier
	 * @return bool
	 */
	public function matchCarrier($trackingId, $carrier) {
		return preg_match($carrier['pattern'], $trackingId);
	}

	/**
	 * Takes an order array and returns a tracking code linked to the carrier's
	 * tracking page.
	 *
	 * @param mixed $text The tracking number
	 * @param mixed $order An order data array
	 * @return string
	 */
	public function outbound($text = null, $order = null) {
		if (is_array($text)) {
			$passedOrder = $order;
			$order = $text;
			$text = $passedOrder;
			if ($text == null) {
				if (empty($order['Order']['usps_track_num'])) {
					return '';
				}
				$text = $order['Order']['usps_track_num'];
			}
		}
		if (empty($order['Order']['usps_track_num'])) {
			return '';
		}

		$carrier = ($order['Order']['mail_class'] == 'FEDEX') ? 'fedex' : 'usps';
		return $this->Html->link(
			$text,
			sprintf($this->carriers[$carrier]['url'], $order['Order']['usps_track_num']),
			['target' => '_blank']
		);
	}

	/**
	 * Returns the date an order was shipped
	 *
	 * @param array $order An order data array
	 * @param mixed $format The date format to use
	 * @return string
	 */
	public function dateShipped($order, $format = null) {
		if (empty($order['OrderStatusHistory'][0]['date_added'])) {
			return 'Not Recorded';
		}
		$date = $order['OrderStatusHistory'][0]['date_added'];

		if ($format != null) {
			$date = $this->formatDate($date, $format);
		}

		return $date;
	}

	/**
	 * Returns the date an order was purchased
	 *
	 * @param array $order An order data array
	 * @param mixed $format The date format to use
	 * @return string
	 */
	public function datePurchased($order, $format = null) {
		if (empty($order['Order']['date_purchased'])) {
			return 'Not Recorded';
		}
		$date = $order['Order']['date_purchased'];
		if ($format != null) {
			return $this->formatDate($date, $format);
		}

		return $this->formatDate($date);
	}

	/**
	 * Returns the date a request was created
	 *
	 * @param mixed $request An order data array
	 * @param mixed $format The date format to use
	 * @return string
	 */
	public function dateRequested($request, $format = null) {
		if (empty($request['CustomPackageRequest']['order_add_date'])) {
			return 'Not Recorded';
		}
		$date = $request['CustomPackageRequest']['order_add_date'];

		if ($format != null) {
			$date = $this->formatDate($date, $format);
		}

		return $date;
	}

	/**
	 * formatDate
	 *
	 * @param string $date The date to format
	 * @param string $format The format to use
	 * @return string
	 */
	public function formatDate($date, $format = 'n/j/y') {
		$common = array(
			'customer' => 'M jS, Y',
		);
		if (!empty($common[$format])) {
			$format = $common[$format];
		}

		return date_format(date_create($date), $format);
	}

	/**
	 * formatDatetime
	 *
	 * @param mixed $date The date to format
	 * @param mixed $format The format to use
	 * @return string
	 */
	public function formatDatetime($date, $format) {
		if (empty($date)) {
			return 'Not Recorded';
		}
		$common = array(
			'customer' => 'g:ia M jS, Y',
		);
		if (!empty($common[$format])) {
			$format = $common[$format];
		}

		return date_format(date_create($date), $format);
	}

	/**
	 * deliveryAddress
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function deliveryAddress($order) {
		$out = '';
		$order = $order['Order'];

		if (!empty($order['delivery_name'])) {
			$out .= $order['delivery_name'] . '<br>';
		}
		if (!empty($order['delivery_company'])) {
			$out .= $order['delivery_company'] . '<br>';
		}
		if (!empty($order['delivery_street_address'])) {
			$out .= $order['delivery_street_address'] . '<br>';
		}
		if (!empty($order['delivery_suburb'])) {
			$out .= $order['delivery_suburb'] . '<br>';
		}
		if (!empty($order['delivery_city'])) {
			$out .= $order['delivery_city'] . ', ';
		}
		if (!empty($order['delivery_state'])) {
			$out .= $order['delivery_state'] . ' ';
		}
		if (!empty($order['delivery_postcode'])) {
			$out .= $this->zip($order['delivery_postcode']) . '<br>';
		}
		if (!empty($order['delivery_country'])) {
			$out .= $order['delivery_country'];
		}

		return $out;
	}

	/**
	 * billingAddress
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function billingAddress($order) {
		$out = '';
		$order = $order['Order'];

		if (!empty($order['billing_company'])) {
			$out .= $order['billing_company'] . '<br>';
		}
		if (!empty($order['billing_name'])) {
			$out .= $order['billing_name'] . '<br>';
		}
		if (!empty($order['billing_street_address'])) {
			$out .= $order['billing_street_address'] . '<br>';
		}
		if (!empty($order['billing_suburb'])) {
			$out .= $order['billing_suburb'] . '<br>';
		}
		if (!empty($order['billing_city'])) {
			$out .= $order['billing_city'] . ', ';
		}
		if (!empty($order['billing_state'])) {
			$out .= $order['billing_state'] . ' ';
		}
		if (!empty($order['billing_postcode'])) {
			$out .= $this->zip($order['billing_postcode']) . '<br>';
		}
		if (!empty($order['billing_country'])) {
			$out .= $order['billing_country'];
		}

		return $out;
	}

	/**
	 * customerAddress
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function customerAddress($order) {
		$out = '';
		$order = $order['Order'];

		if (!empty($order['customers_company'])) {
			$out .= $order['customers_company'] . '<br>';
		}
		if (!empty($order['customers_name'])) {
			$out .= $order['customers_name'] . '<br>';
		}
		if (!empty($order['customers_street_address'])) {
			$out .= $order['customers_street_address'] . '<br>';
		}
		if (!empty($order['customers_suburb'])) {
			$out .= $order['customers_suburb'] . '<br>';
		}
		if (!empty($order['customers_city'])) {
			$out .= $order['customers_city'] . ', ';
		}
		if (!empty($order['customers_state'])) {
			$out .= $order['customers_state'] . ' ';
		}
		if (!empty($order['customers_postcode'])) {
			$out .= $this->zip($order['customers_postcode']) . '<br>';
		}
		if (!empty($order['customers_country'])) {
			$out .= $this->zip($order['customers_country']);
		}

		return $out;
	}

	/**
	 * deliveryCityState
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function deliveryCityState($order) {
		$out = '';
		$order = $order['Order'];

		if (!empty($order['delivery_city'])) {
			$out .= $order['delivery_city'] . ', ';
		}
		if (!empty($order['delivery_state'])) {
			$out .= $order['delivery_state'];
		}

		return $out;
	}

	/**
	 * Takes an Address record and formats a full HTML address element
	 *
	 * @param array $address An address array
	 * @param array $options Optional options
	 * @return string
	 */
	public function fullAddress($address, $options = array()) {
		$defaults = array('class' => 'lead', 'wrap' => 'address');
		$options = Hash::merge($defaults, $options);
		$emptyAddress = array(
			'entry_firstname' => '',
			'entry_lastname' => '',
			'entry_company' => '',
			'entry_street_address' => '',
			'entry_suburb' => '',
			'entry_city' => '',
			'entry_postcode' => '',
			'entry_basename' => '',
			'Zone' => array('zone_code' => ''),
		);
		$address = Hash::merge($emptyAddress, $address);
		$tag = $options['wrap'];
		unset($options['wrap']);
		$out = '';

		$out .= h($address['entry_company']);
		if (!empty($address['entry_company'])) {
			$out .= '<br>';
		}
		$out .= h($address['entry_firstname']) . ' ';
		$out .= h($address['entry_lastname']);
		if (!empty($address['entry_firstname']) || !empty($address['entry_lastname'])) {
			$out .= '<br>';
		}
		$out .= h($address['entry_street_address']);
		if (!empty($address['entry_street_address'])) {
			$out .= '<br>';
		}
		$out .= h($address['entry_suburb']);
		if (!empty($address['entry_suburb'])) {
			$out .= '<br>';
		}
		$out .= h($address['entry_city']);
		if (!empty($address['entry_city'])) {
			$out .= ', ';
		}
		$out .= h($address['Zone']['zone_code']);
		if (!empty($address['Zone']['zone_code'])) {
			$out .= ',&nbsp;';
		}
		$out .= h($this->zip($address['entry_postcode']));
		if (!empty($address['entry_postcode'])) {
			$out .= '<br>';
		}
		$out .= h($address['entry_basename']);

		if ($tag) {
			$out = $this->Html->tag($tag, $out, $options);
		}

		return $out;
	}

	/**
	 * packageDimensions
	 *
	 * @param array $order An order data array
	 * @param string $units The units to display
	 * @return string
	 */
	public function packageDimensions($order, $units = ' in.') {
		$out = sprintf('%.2f x %.2f x %.2f', $order['Order']['length'], $order['Order']['width'], $order['Order']['depth']) . $units;
		return trim($out);
	}

	/**
	 * packageVolume
	 *
	 * @param array $order An order data array
	 * @param string $units The units to display
	 * @return string
	 */
	public function packageVolume($order, $units = ' in<sup>3</sup>') {
		$out = number_format($order['Order']['length'] * $order['Order']['width'] * $order['Order']['depth']) . $units;
		return trim($out);
	}

	/**
	 * paymentInfo
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function paymentInfo($order) {
		$out = '';
		$out .= (!empty($order['Order']['cc_owner']) ? 'Name: ' . $order['Order']['cc_owner'] . '<br>' : '');
		$out .= (!empty($order['Order']['cc_type']) ? $order['Order']['cc_type'] . ': ' : '');
		$out .= (!empty($order['Order']['cc_number']) ? $order['Order']['cc_number'] . '<br>' : '');
		$out .= (!empty($order['Order']['cc_expires']) ? 'Expires: ' . $this->ccExpires($order['Order']['cc_expires']) : '');
		return $out;
	}

	/**
	 * lastUpdated
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function lastUpdated($order) {
		if (empty($order['Order']['last_modified'])) {
			return '';
		}
		$date = $order['Order']['last_modified'];
		if ($this->Time->isToday($date)) {
			return 'Today';
		}
		if ($this->Time->wasYesterday($date)) {
			return 'Yesterday';
		}

		return $this->formatDate($date);
	}

	/**
	 * statusLabel
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function statusLabel($order) {
		if (empty($order['OrderStatus']['orders_status_name'])) {
			return '';
		}
		if (empty($order['OrderStatus']['orders_status_id'])) {
			return $order['OrderStatus']['orders_status_name'];
		}
		$id = $order['OrderStatus']['orders_status_id'];
		$labelClass = array(
			1 => 'info',
			2 => 'warning',
			3 => 'success',
			4 => 'success',
			5 => 'danger',
		);
		$class = 'label label-' . $labelClass[$id];

		return $this->Html->tag('span', $order['OrderStatus']['orders_status_name'], array('class' => $class));
	}

	/**
	 * requestLabel
	 *
	 * @param array $request A CustomPackageRequest data array
	 * @return string
	 */
	public function requestLabel($request = array()) {
		$packageLabel = $orderLabel = '';
		$orderStatus = null;
		$packageStatus = $request['CustomPackageRequest']['package_status'];

		$text = $packageStatus == 3 ? 'Shipped' : 'Awaiting Package';
		$class = 'label label-' . ($packageStatus == 3 ? 'success' : 'info');
		$packageLabel = $this->Html->tag('span', $text, array('class' => $class));

		if ($this->_requestOrderExists($request)) {
			$orderStatus = isset($request['Order']['OrderStatus']) ? $request['Order']['OrderStatus']['orders_status_id'] : array();
			$orderLabel = $this->statusLabel($request['Order']);
			$orderLabel .= ' <span>Order# ' . $this->orderLink($request) . '</span>';
		}

		if (!empty($orderStatus)) {
			if ($packageStatus != $orderStatus) {
				return 'Package: ' . $packageLabel . '<br />' . 'Order: ' . $orderLabel;
			}

			return $orderLabel;
		}

		return $packageLabel;
	}

	/**
	 * nonMachinableLabel
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function nonMachinableLabel($order) {
		if (empty($order['Order']['NonMachinable']) || $order['Order']['NonMachinable'] == 'FALSE') {
			return '';
		}

		return $this->Html->tag('span', 'NonMachinable', array('class' => 'label label-warning'));
	}

	/**
	 * oversizeRateLabel
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function oversizeRateLabel($order) {
		if (empty($order['Order']['OversizeRate']) || $order['Order']['OversizeRate'] == 'FALSE') {
			return '';
		}

		return $this->Html->tag('span', 'Oversize', array('class' => 'label label-warning'));
	}

	/**
	 * balloonRateLabel
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function balloonRateLabel($order) {
		if (empty($order['Order']['BalloonRate']) || $order['Order']['BalloonRate'] == 'FALSE') {
			return '';
		}

		return $this->Html->tag('span', 'Balloon Rate', array('class' => 'label label-warning'));
	}

	/**
	 * Return a label if the order has a custom repack set to yes.
	 *
	 * @param array $order An order data array
	 * @return string empty or an html span with label
	 */
	public function repackLabel($order) {
		if (empty($order['CustomPackageRequest']) || $order['CustomPackageRequest']['package_repack'] !== 'yes') {
			return '';
		}
		return $this->Html->tag('span', 'Repackage', ['class' => 'label label-warning']);
	}

	/**
	 * Return a label if the order has a Custom Package Request.
	 *
	 * @param array $order An order data array
	 * @param array $customRequests An optional custom package request data array
	 * @return string empty or an html span with label
	 */
	public function customRequestLabel($order, $customRequests = []) {
		$label = ' ' . $this->Html->tag('span', 'Custom', ['class' => 'label label-info']);
		if (empty($customRequests)) {
			if (empty($order['CustomPackageRequest']['custom_orders_id'])) {
				return '';
			}
			return $label;
		} else {
			if (!empty(Hash::extract($customRequests, '{n}.CustomPackageRequest[orders_id=' . $order['Order']['orders_id'] . '].orders_id'))) {
				return $label;
			}
		}
		return '';
	}

	/**
	 * Convert 4 digit string to common expiration date format
	 *
	 * @param string $exprDate The expiration date
	 * @return string
	 */
	public function ccExpires($exprDate) {
		if (strlen($exprDate) != 4) {
			return '';
		}

		return substr($exprDate, 0, 2) . ' / 20' . substr($exprDate, 2, 2);
	}

	/**
	 * Turns the weight in ounces to a ceiling of they apropriate pound amount
	 *
	 * @param array $order An order data array
	 * @param bool $round Whether to round the weight
	 * @return string
	 */
	public function weight($order, $round = false) {
		if (empty($order['Order']['weight_oz'])) {
			return '';
		}
		if ($round) {
			$lb = ceil($order['Order']['weight_oz'] / 16);

			return sprintf('%d lb', $lb);
		}

		$lb = floor($order['Order']['weight_oz'] / 16);
		$oz = $order['Order']['weight_oz'] % 16;

		return sprintf('%d lb, %d oz', $lb, $oz);
	}

	/**
	 * orderTotal
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function orderTotal($order) {
		if (empty($order['OrderTotal']['value'])) {
			return '';
		}

		return $this->Number->currency($order['OrderTotal']['value']);
	}

	/**
	 * orderShipping
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function orderShipping($order) {
		if (empty($order['OrderShipping']['value'])) {
			return '';
		}

		return $this->Number->currency($order['OrderShipping']['value']);
	}

	/**
	 * orderInsurance
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function orderInsurance($order) {
		if (empty($order['OrderInsurance']['value'])) {
			return '';
		}

		return $this->Number->currency($order['OrderInsurance']['value']);
	}

	/**
	 * orderStorage
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function orderStorage($order) {
		if (empty($order['OrderStorage']['value'])) {
			return '';
		}

		return $this->Number->currency($order['OrderStorage']['value']);
	}

	/**
	 * orderSubtotal
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function orderSubtotal($order) {
		if (empty($order['OrderSubtotal']['value'])) {
			return '';
		}

		return $this->Number->currency($order['OrderSubtotal']['value']);
	}

	/**
	 * insuranceCoverage
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function insuranceCoverage($order) {
		if (empty($order['CustomPackageRequest']['insurance_coverage'])) {
			return '<span class="small">Default</span>';
		}

		return $this->Number->currency($order['CustomPackageRequest']['insurance_coverage']);
	}

	/**
	 * orderLink
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function orderLink($order) {
		$orderId = $order['Order']['orders_id'];
		return $this->Html->link($orderId, array(
			'controller' => 'orders',
			'action' => 'view',
			'id' => $orderId,
		));
	}

	/**
	 * requestEdit
	 *
	 * @param array $request A CustomPackageRequest data array
	 * @param mixed $customerId The customerId
	 * @return string
	 */
	public function requestEdit($request, $customerId = null) {
		return $this->Html->link(
			'Edit',
			array(
				'controller' => 'custom_package_requests',
				'action' => 'edit',
				$request['CustomPackageRequest']['custom_orders_id'],
				$customerId,
			),
			array('class' => 'btn btn-xs btn-primary')
		);
	}

	/**
	 * _requestOrderExists
	 *
	 * @param array $request A CustomPackageRequest data array
	 * @return string
	 */
	protected function _requestOrderExists($request) {
		return !empty($request['CustomPackageRequest']['orders_id']);
	}

	/**
	 * zip
	 *
	 * @param string $zip The zip code
	 * @return string
	 */
	public function zip($zip) {
		if (strlen($zip) == 9 && strpos($zip, '-') === false) {
			return substr_replace($zip, '-', 5, 0);
		}

		return $zip;
	}

	/**
	 * orderCharges
	 *
	 * @param array $orderCharges An OrderTotal data array
	 * @return string
	 */
	public function orderCharges($orderCharges) {
		$rowFormat = '<tr><td>%s</td><td>%s</td></tr>';
		$skipRows = ['ot_subtotal', 'ot_total'];
		$out = '<table class="order-charges">';
		$total = '';
		foreach ($orderCharges as $charge) {
			$row = sprintf($rowFormat, $charge['OrderTotal']['title'], $charge['OrderTotal']['text']);
			if ($charge['OrderTotal']['class'] == 'ot_total') {
				$total = $row;
			}
			if (in_array($charge['OrderTotal']['class'], $skipRows)) {
				continue;
			}
			$out .= $row;
		}
		$out .= '<tr><td class="line" colspan="2"></td></tr>';
		$out .= $total;

		return $out . '</table>';
	}

	/**
	 * yesNo
	 *
	 * returns Yes or No on a boolean input
	 *
	 * @param bool $boolean Boolean to return a value upon, defaults to false
	 * @return string String either Yes or No
	 */
	public function yesNo($boolean = false) {
		return (($boolean) ? __('Yes') : __('No'));
	}

	/**
	 * checkmark
	 *
	 * @param bool $boolean True or False to indicate which check to display
	 * @return string A font awesome "i" tag with checkmark (true) or "X" (false)
	 * wrapped in a bootstrap label (success for true, danger for false)
	 */
	public function checkmark($boolean = false) {
		$class = (($boolean) ? 'fa-check' : 'fa-times');
		$color = (($boolean) ? 'success' : 'danger');
		$mark = $this->Html->tag('i', null, ['class' => "fa $class"]);
		return $this->Html->tag('span', $mark, ['class' => "label label-$color"]);
	}

	/**
	 * apoBoxAddress
	 *
	 * @param array $order An order data array
	 * @return string
	 */
	public function apoBoxAddress($order) {
		$out = '';
		$customer = $order['Customer'];

		$out .= $customer['customers_firstname'] . ' ';
		$out .= $customer['customers_lastname'] . '<br>';
		$out .= 'Attn: ';
		$out .= $customer['billing_id'] . '<br>';
		$out .= '1911 Western Ave<br>';
		$out .= 'Plymouth, IN 46563';

		return $out;
	}
}
