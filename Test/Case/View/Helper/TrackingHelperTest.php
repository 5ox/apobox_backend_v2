<?php
App::uses('Controller', 'Controller');
App::uses('View', 'Core');
App::uses('TrackingHelper', 'View/Helper');

class TrackingHelperTest extends CakeTestCase {
	public $TrackingHelper = null;

	public function setUp() {
		parent::setUp();
		$Controller = new Controller();
		$this->View = $View = new View($Controller);
		$this->TrackingHelper = new TrackingHelper($View);
	}

	public function tearDown() {
		unset($this->TrackingHelper);
		parent::tearDown();
	}

	/**
	 * @dataProvider inboundTrackingLinkProvider
	 */
	public function testInboundTrackingLink($text, $order, $contains) {
		$result = $this->TrackingHelper->inboundTrackingLink($order, $text);

		foreach ($contains as $contain) {
			$this->assertContains($contain, $result);
		}
	}

	public function inboundTrackingLinkProvider() {
		$tests = array(
			array(
				'...456789',
				array('Order' => array(
					'inbound_tracking' => '123456789',
					'inbound_carrier' => 'ups'
				)),
				array(
					'http',
					'>...456789<',
					'ups.com',
					'123456789',
					'target="_blank"',
				)
			),
			array(
				'456789',
				array('Order' => array(
					'inbound_tracking' => '123456789',
					'inbound_carrier' => 'fedex'
				)),
				array(
					'http',
					'>456789<',
					'fedex.com',
					'123456789',
					'target="_blank"',
				)
			),
			array(
				'Fedex',
				array('Order' => array(
					'inbound_tracking' => '123456789',
					'inbound_carrier' => 'fedex_freight'
				)),
				array(
					'http',
					'>Fedex<',
					'fedex.com',
					'123456789',
					'target="_blank"',
				)
			),
			array(
				null,
				array('Order' => array(
					'inbound_tracking' => '123456789',
					'inbound_carrier' => 'fedex_freight'
				)),
				array(
					'http',
					'>123456789<',
					'fedex.com',
					'123456789',
					'target="_blank"',
				)
			),
			array(
				null,
				array('Order' => array(
					'fedex_track_num' => '123456789',
				)),
				array(
					'http',
					'>123456789<',
					'fedex.com',
					'123456789',
				)
			),
		);

		return $tests;
	}

	/**
	 *
	 */
	public function testInboundTrackingLinkFallsBackWithoutInboundCarrier() {
		$this->TrackingHelper = $this->getMockBuilder('TrackingHelper')
			->setConstructorArgs(array($this->View))
			->getMock();
		$this->TrackingHelper->expects($this->once())
			->method('inbound');
		$order = array('Order' => array(
			'inbound_tracking' => '123456789',
			'ups_tracking_num' => '123456789'
		));
		$this->TrackingHelper->inbound('Link', $order);
	}

	/**
	 *
	 */
	public function testInboundTrackingLinkFallsBackWithoutInboundTracking() {
		$this->TrackingHelper = $this->getMockBuilder('TrackingHelper')
			->setConstructorArgs(array($this->View))
			->getMock();
		$this->TrackingHelper->expects($this->once())
			->method('inbound');
		$order = array('Order' => array(
			'inbound_carrier' => 'ups',
			'ups_tracking_num' => '123456789'
		));
		$this->TrackingHelper->inbound('Link', $order);
	}

	/**
	 * @dataProvider inboundProvider
	 */
	public function testInbound($order, $trackNum, $link) {
		$this->assertEquals(
			$this->TrackingHelper->Html->link(
				$trackNum,
				$link
			),
			$this->TrackingHelper->inbound($order)
		);
	}

	public function inboundProvider() {
		$trackNum = '12345';
		$baseOrder = array('Order' => array(
			'dhl_track_num' => '',
			'fedex_freight_track_num' => '',
			'fedex_track_num' => '',
			'ups_track_num' => '',
			'usps_track_num_in' => '',
		));

		$order = $baseOrder;
		$order['Order']['dhl_track_num'] = $trackNum;
		$tests[] = array(
			$order,
			$trackNum,
			'http://track.dhl-usa.com/TrackByNbr.asp?ShipmentNumber=' . $trackNum,
		);


		$order = $baseOrder;
		$order['Order']['fedex_freight_track_num'] = $trackNum;
		$tests[] = array(
			$order,
			$trackNum,
			'http://www.fedex.com/Tracking?action=track&tracknumbers=' . $trackNum,
		);

		$order = $baseOrder;
		$order['Order']['fedex_track_num'] = $trackNum;
		$tests[] = array(
			$order,
			$trackNum,
			'http://www.fedex.com/Tracking?action=track&tracknumbers=' . $trackNum,
		);

		$order = $baseOrder;
		$order['Order']['ups_track_num'] = $trackNum;
		$tests[] = array(
			$order,
			$trackNum,
			'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=' . $trackNum,
		);

		$order = $baseOrder;
		$order['Order']['usps_track_num_in'] = $trackNum;
		$tests[] = array(
			$order,
			$trackNum,
			'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=' . $trackNum,
		);

		return $tests;
	}

	/**
	 * testOutbound
	 *
	 * @return void
	 */
	public function testOutboundUsps() {
		$trackNum = '12345';
		$order = ['Order' => [
			'usps_track_num' => $trackNum,
			'mail_class' => 'PRIORITY',
		]];
		$result = $this->TrackingHelper->outbound($order);
		$this->assertContains(' href="https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=' . $trackNum, $result);
		$this->assertContains('>' . $trackNum . '</', $result);
		$this->assertContains(' target="_blank"', $result, 'Outbound link should open in new window.');
	}

	/**
	 * Confirm that PRIORITY and PARCEL mail classes return USPS tracking urls
	 * and that FEDEX returns a Fedex tracking url. If an unknown mail class is
	 * set USPS is used by default.
	 *
	 * @dataProvider outboundProvider
	 * @return void
	 */
	public function testOutbound($order, $expected, $message = '') {
		$result = $this->TrackingHelper->outbound($order);
		$this->assertContains($expected, $result);
		$this->assertContains('>' . $order['Order']['usps_track_num'] . '</', $result);
		$this->assertContains(' target="_blank"', $result, 'Outbound link should open in new window.');
	}

	public function outboundProvider() {
		$trackNum = '12345';
		return [
			[
				['Order' => [
					'usps_track_num' => $trackNum,
					'mail_class' => 'PRIORITY',
				]],
				'href="https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=' . $trackNum
			],
			[
				['Order' => [
					'usps_track_num' => $trackNum,
					'mail_class' => 'PARCEL',
				]],
				'href="https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=' . $trackNum
			],
			[
				['Order' => [
					'usps_track_num' => $trackNum,
					'mail_class' => 'FEDEX',
				]],
				'http://www.fedex.com/Tracking?action=track&amp;tracknumbers=' . $trackNum
			],
			[
				['Order' => [
					'usps_track_num' => $trackNum,
					'mail_class' => 'FOO',
				]],
				'href="https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=' . $trackNum
			],
		];
	}


	public function testOutboundWithoutUPSTrackNum() {
		$trackNum = '12345';
		$order = array('Order' => array());
		$result = $this->TrackingHelper->outbound($order);
		$this->assertEquals('', $result);
	}

	public function testOutboundWithTextWithoutUPSTrackNum() {
		$text = 'text';
		$trackNum = '12345';
		$order = array('Order' => array());
		$result = $this->TrackingHelper->outbound($text, $order);
		$this->assertEquals('', $result);
	}

	/**
	 * @dataProvider dateShippedProvider
	 */
	public function testDateShipped($input, $expected) {
		$order = array(
			'OrderStatusHistory' => array(
				array('date_added' => $input)
			)
		);
		$this->assertEquals(
			$expected,
			$this->TrackingHelper->dateShipped($order)
		);
	}

	public function dateShippedProvider() {
		$matchingTests = array(
			'2015-01-15 03:23:45',
			'randomness',
		);
		foreach ($matchingTests as $input) {
			$tests[] = array($input, $input);
		}
		$tests[] = array('', 'Not Recorded');
		$tests[] = array(NULL, 'Not Recorded');

		return $tests;
	}

	/**
	 * @dataProvider dateShippedProviderWithFormat
	 */
	public function testDateShippedWithFormat($input, $format, $expected, $message = null) {
		$order = array(
			'OrderStatusHistory' => array(
				array('date_added' => $input)
			)
		);
		$this->assertEquals(
			$expected,
			$this->TrackingHelper->dateShipped($order, $format),
			$message
		);
	}

	public function dateShippedProviderWithFormat() {
		$dateTime = '2015-01-15 03:23:45';
		$tests = array(
			array('', 'customer', 'Not Recorded'),
			array(NULL, 'customer', 'Not Recorded'),
			array($dateTime, 'customer', 'Jan 15th, 2015', 'Common format failed'),
			array($dateTime, 'F \o\f Y', 'January of 2015', 'Custom format failed'),
		);

		return $tests;
	}

	/**
	 * @dataProvider datePurchasedProvider
	 */
	public function testDatePurchased($input, $expected) {
		$order = array(
			'Order' => array(
				'date_purchased' => $input
			)
		);
		$this->assertEquals(
			$expected,
			$this->TrackingHelper->datePurchased($order)
		);
	}

	public function datePurchasedProvider() {
		$matchingTests = array(
			'1/15/15',
		);
		foreach ($matchingTests as $input) {
			$tests[] = array($input, $input);
		}
		$tests[] = array('', 'Not Recorded');
		$tests[] = array(NULL, 'Not Recorded');

		return $tests;
	}

	/**
	 * @dataProvider datePurchasedProviderWithFormat
	 */
	public function testDatePurchasedWithFormat($input, $format, $expected, $message = null) {
		$order = array(
			'Order' => array(
				'date_purchased' => $input
			)
		);
		$this->assertEquals(
			$expected,
			$this->TrackingHelper->datePurchased($order, $format),
			$message
		);
	}

	public function datePurchasedProviderWithFormat() {
		$dateTime = '2015-01-15 03:23:45';
		$tests = array(
			array('', 'customer', 'Not Recorded'),
			array(NULL, 'customer', 'Not Recorded'),
			array($dateTime, 'customer', 'Jan 15th, 2015', 'Common format failed'),
			array($dateTime, 'F \o\f Y', 'January of 2015', 'Custom format failed'),
		);

		return $tests;
	}

	public function testDateShippedNotSet() {
		$order = array();
		$this->assertEquals(
			'Not Recorded',
			$this->TrackingHelper->dateShipped($order),
			'The proper message was not returned'
		);
	}

	public function testFullAddress() {
		$address = array(
			'entry_firstname' => 'Bob',
			'entry_lastname' => 'TheTester',
			'entry_company' => 'ACME, Inc.',
			'entry_street_address' => '',
			'entry_suburb' => '',
			'entry_city' => 'Gotham',
			'entry_postcode' => '99999',
			'entry_basename' => '',
			'Zone' => array('zone_code' => 'NY'),
		);
		$expectation = array(
			'tag' => 'address',
			'attributes' => array('class' => 'lead')
		);
		$result = $this->TrackingHelper->fullAddress($address);

		$this->assertTag($expectation, $result);
	}

	public function testFullAddressWithLongAddress() {
		$address = array(
			'entry_firstname' => 'Bob',
			'entry_lastname' => 'TheTester',
			'entry_company' => 'ACME, Inc.',
			'entry_street_address' => '123 Street',
			'entry_suburb' => 'Apt. Somehwere',
			'entry_city' => 'Gotham',
			'entry_postcode' => '99999',
			'entry_basename' => '',
			'Zone' => array('zone_code' => 'NY'),
		);
		$expectation = array(
			'tag' => 'address',
			'attributes' => array('class' => 'lead')
		);
		$result = $this->TrackingHelper->fullAddress($address);

		$this->assertTag($expectation, $result);
	}

	public function testFullAddressWithEmptyArray() {
		$address = array();
		$expectation = array(
			'tag' => 'address',
			'attributes' => array('class' => 'lead')
		);
		$result = $this->TrackingHelper->fullAddress($address);

		$this->assertTag($expectation, $result);
	}

	public function testPackageDimensions() {
		$order = array('Order' => array(
			'length' => '11.00',
			'width' => '13.00',
			'depth' => '8.00',
		));
		$expectation = '11.00 x 13.00 x 8.00 in.';

		$result = $this->TrackingHelper->packageDimensions($order);

		$this->assertEquals($expectation, $result, 'Package dimensions not formatted as expected.');
	}

	public function testPackageDimensionsAsInts() {
		$order = array('Order' => array(
			'length' => 11,
			'width' => 13,
			'depth' => 8,
		));
		$expectation = '11.00 x 13.00 x 8.00 in.';

		$result = $this->TrackingHelper->packageDimensions($order);

		$this->assertEquals($expectation, $result, 'Dimensions given in intergers not formatted as expected.');
	}

	public function testPackageDimensionsWithoutUnits() {
		$order = array('Order' => array(
			'length' => '11.00',
			'width' => '13.00',
			'depth' => '8.00',
		));
		$units = '';
		$expectation = '11.00 x 13.00 x 8.00';

		$result = $this->TrackingHelper->packageDimensions($order, $units);

		$this->assertEquals($expectation, $result, 'Package dimensions not formatted as expected.');
	}

	public function testPackageDimensionsTrimmed() {
		$order = array('Order' => array(
			'length' => '11.00',
			'width' => '13.00',
			'depth' => '8.00',
		));
		$units = ' ';
		$expectation = '11.00 x 13.00 x 8.00';

		$result = $this->TrackingHelper->packageDimensions($order, $units);

		$this->assertEquals($expectation, $result, 'Package dimensions not formatted as expected.');
	}

	public function testPackageVolume() {
		$order = array('Order' => array(
			'length' => '11.00',
			'width' => '13.00',
			'depth' => '8.00',
		));
		$expectation = '1,144 in<sup>3</sup>';

		$result = $this->TrackingHelper->packageVolume($order);

		$this->assertEquals($expectation, $result, 'Package dimensions not formatted as expected.');
	}

	public function testBalloonRate() {
		$order = array('Order' => array(
			'BalloonRate' => 'TRUE',
		));

		$result = $this->TrackingHelper->balloonRateLabel($order);

		$this->assertStringStartsWith('<span', $result);
		$this->assertStringEndsWith('</span>', $result);
		$this->assertContains('Balloon Rate', $result);
		$this->assertContains('class="label label-warning"', $result);
	}
	public function testBalloonRateFalse() {
		$order = array('Order' => array(
			'BalloonRate' => 'FALSE',
		));

		$result = $this->TrackingHelper->balloonRateLabel($order);

		$this->assertEquals('', $result);
	}

	public function testBalloonRateEmpty() {
		$order = array('Order' => array(
		));

		$result = $this->TrackingHelper->balloonRateLabel($order);

		$this->assertEquals('', $result);
	}

	public function testOversizeRate() {
		$order = array('Order' => array(
			'OversizeRate' => 'TRUE',
		));

		$result = $this->TrackingHelper->oversizeRateLabel($order);

		$this->assertStringStartsWith('<span', $result);
		$this->assertStringEndsWith('</span>', $result);
		$this->assertContains('Oversize', $result);
		$this->assertContains('class="label label-warning"', $result);
	}

	public function testOversizeRateFalse() {
		$order = array('Order' => array(
			'OversizeRate' => 'FALSE',
		));

		$result = $this->TrackingHelper->oversizeRateLabel($order);

		$this->assertEquals('', $result);
	}

	public function testOversizeRateEmpty() {
		$order = array('Order' => array(
		));

		$result = $this->TrackingHelper->oversizeRateLabel($order);

		$this->assertEquals('', $result);
	}

	public function testNonMachinable() {
		$order = array('Order' => array(
			'NonMachinable' => 'TRUE',
		));

		$result = $this->TrackingHelper->nonMachinableLabel($order);

		$this->assertStringStartsWith('<span', $result);
		$this->assertStringEndsWith('</span>', $result);
		$this->assertContains('NonMachinable', $result);
		$this->assertContains('class="label label-warning"', $result);
	}

	public function testNonMachinableFalse() {
		$order = array('Order' => array(
			'NonMachinable' => 'FALSE',
		));

		$result = $this->TrackingHelper->nonMachinableLabel($order);

		$this->assertEquals('', $result);
	}

	public function testNonMachinableEmpty() {
		$order = array('Order' => array(
		));

		$result = $this->TrackingHelper->nonMachinableLabel($order);

		$this->assertEquals('', $result);
	}

	/**
	 * @dataProvider statusLabelProvider
	 */
	public function testStatusLabel($label, $name, $class) {
		$order = array('OrderStatus' => array(
			'orders_status_id' => $label,
			'orders_status_name' => $name,
		));

		$result = $this->TrackingHelper->statusLabel($order);

		$this->assertStringStartsWith('<span ', $result);
		$this->assertStringEndsWith('</span>', $result);
		$this->assertContains('>' . $name . '<', $result);
		$this->assertContains(' class="label label-' . $class . '"', $result);
	}

	public function statusLabelProvider() {
		return array(
			array(1, 'A', 'info'),
			array(2, 'B', 'warning'),
			array(3, 'C', 'success'),
			array(4, 'D', 'success'),
			array(5, 'E', 'danger'),
		);
	}

	public function testStatusLabelWithOutOrdersStatusId() {
		$name = 'Paid';
		$order = array('OrderStatus' => array(
			'orders_status_name' => $name,
		));

		$result = $this->TrackingHelper->statusLabel($order);

		$this->assertEqual($name, $result);
	}

	public function testStatusLabelWithOutOrdersStatusName() {
		$id = 1;
		$order = array('OrderStatus' => array(
			'orders_status_id' => $id,
		));

		$result = $this->TrackingHelper->statusLabel($order);

		$this->assertEqual('', $result);
	}

	public function testStatusLabelWithEmptyOrderStatusName() {
		$order = array('OrderStatus' => array());

		$result = $this->TrackingHelper->statusLabel($order);

		$this->assertEquals('', $result);
	}

	public function testCcExpires() {
		$exprString = '0822';
		$expectation = '08 / 2022';

		$result = $this->TrackingHelper->ccExpires($exprString);

		$this->assertEquals($expectation, $result, 'Expiration date not formatted as expected.');
	}

	public function testCcExpiresNotDefined() {
		$exprString = '';
		$expectation = '';

		$result = $this->TrackingHelper->ccExpires($exprString);

		$this->assertEquals($expectation, $result, 'Result not empty.');
	}

	/**
	 * @dataProvider provideWeight
	 */
	public function testWeight($ounces, $round, $expected) {
		$order = array('Order' => array(
			'weight_oz' => $ounces,
		));
		$result = $this->TrackingHelper->weight($order, $round);

		$this->assertEquals($expected, $result, 'Package weight not formatted as expected.');
	}

	public function provideWeight() {
		return [
			['8', true, '1 lb'],
			['20', true, '2 lb'],
			['9', true, '1 lb'],
			['8', false, '0 lb, 8 oz'],
			['20', false, '1 lb, 4 oz'],
			['9', false, '0 lb, 9 oz'],
			['400', false, '25 lb, 0 oz'],
		];
	}

	public function testWeightWithWeightOzNotDefined() {
		$order = array('Order' => array(
		));
		$expectation = '';

		$result = $this->TrackingHelper->weight($order);

		$this->assertEquals($expectation, $result, 'Package weight not formatted as expected.');
	}

	public function testLastUpdated() {
		$order = array('Order' => array(
			'last_modified' => '-0 minutes'
		));
		$expectation = 'Today';

		$result = $this->TrackingHelper->lastUpdated($order);

		$this->assertEquals($expectation, $result, 'Last updated not formatted as expected.');
	}

	public function testLastUpdatedEmpty() {
		$order = array('Order' => array());
		$expectation = '';

		$result = $this->TrackingHelper->lastUpdated($order);

		$this->assertEquals($expectation, $result, 'Last updated not formatted as expected.');
	}

	public function testLastUpdatedLongerThanOneWeek() {
		$order = array('Order' => array(
			'last_modified' => '-2 weeks'
		));
		$expectation = '2 weeks ago';

		$result = $this->TrackingHelper->lastUpdated($order);

		$this->assertNotEquals($expectation, $result, 'Last updated not formatted as expected.');
	}

	public function testLastUpdatedDateFormat() {
		$order = array('Order' => array(
			'last_modified' => '1999-01-01 00:00:01'
		));
		$expectation = '1/1/99';

		$result = $this->TrackingHelper->lastUpdated($order);

		$this->assertEquals($expectation, $result, 'Last updated not formatted as expected.');
	}

	public function testLastUpdatedYesterday() {
		$order = array('Order' => array(
			'last_modified' => '-1 day'
		));
		$expectation = 'Yesterday';

		$result = $this->TrackingHelper->lastUpdated($order);

		$this->assertEquals($expectation, $result, 'Last updated not formatted as expected.');
	}

	public function testOrderTotal() {
		$order = array('OrderTotal' => array(
			'value' => '112.6000'
		));
		$expectation = '$112.60';

		$result = $this->TrackingHelper->orderTotal($order);

		$this->assertEquals($expectation, $result, 'Order total not formatted as expected.');
	}

	public function testOrderTotalWithHighSpecificityRounding() {
		$order = array('OrderTotal' => array(
			'value' => '112.6999'
		));
		$expectation = '$112.70';

		$result = $this->TrackingHelper->orderTotal($order);

		$this->assertEquals($expectation, $result, 'Order total not formatted as expected.');
	}

	public function testOrderTotalValueNotDefined() {
		$order = array('OrderTotal' => array(
			'title' => 'test'
		));
		$expectation = '';

		$result = $this->TrackingHelper->orderTotal($order);

		$this->assertEquals($expectation, $result, 'Order total not formatted as expected.');
	}

	/**
	 * @dataProvider requestInboundProvider
	 */
	public function testRequestInbound($trackingId, $expected, $message) {
		$request = array('CustomPackageRequest' => array(
			'tracking_id' => $trackingId,
		));
		$result = $this->TrackingHelper->requestInbound($request);
		$this->assertRegExp($expected, $result, $message);
	}

	public function requestInboundProvider() {
		return array(
			array('123', '/^123$/', 'Expected same string back.'),
			array(
				'1212341234',
				'/^<a href.+dhl-usa\.com.+>1212341234<\/a>$/',
				'Expected linked DHL tracking number.'
			),
			array(
				'12341234123412',
				'/^<a href.+fedex\.com.+>12341234123412<\/a>$/',
				'Expected linked FedEx tracking number.'
			),
			array(
				'1ZABCABC1212341234',
				'/^<a href.+ups\.com.+>1ZABCABC1212341234<\/a>$/',
				'Expected linked UPS tracking number.'
			),
			array(
				'1234123412341234123412',
				'/^<a href.+usps\.com.+>1234123412341234123412<\/a>$/',
				'Expected linked USPS tracking number.'
			),
			array(
				'',
				'//',
				'Expected empty string.'
			),
		);
	}

	/**
	 * @dataProvider dateRequestedProvider
	 */
	public function testDateRequested($date, $format, $expected, $message = null) {
		$request = array('CustomPackageRequest' => array(
			'order_add_date' => $date,
		));
		$result = $this->TrackingHelper->dateRequested($request, $format);
		$this->assertEquals($expected, $result, $message);
	}

	public function dateRequestedProvider() {
		return array(
			array('', null, 'Not Recorded'),
			array(NULL, null, 'Not Recorded'),
			array('2015-04-10 00:00:00', 'customer', 'Apr 10th, 2015'),
			array('2015-04-10 00:00:00', null, '2015-04-10 00:00:00'),
			array('2015-04-10 00:00:00', 'Y-m-d', '2015-04-10'),
		);
	}

	/**
	 * @dataProvider requestLabelProvider
	 */
	public function testRequestLabel($request, $expected) {
		$result = $this->TrackingHelper->requestLabel($request);
		$this->assertRegExp($expected, $result);
	}

	public function requestLabelProvider() {
		$defaultRequest = array('CustomPackageRequest' => array(
			'package_status' => 1,
		));
		$defaultExpected = '/^<span.+>Awaiting Package<\/span>$/';
		return array(
			array(
				$defaultRequest,
				$defaultExpected,
			),
			array(
				array(
					'CustomPackageRequest' => array(
						'orders_id' => '1234',
						'package_status' => 1
					),
					'Order' => array(
						'orders_id' => '1234',
						'OrderStatus' => array(
							'orders_status_name' => 'Testing',
							'orders_status_id' => 1,
						)
					)),
				'/^<span.+label-info.+>Testing<\/span> <span>Order# <a href=".+">1234<\/a><\/span>$/',
			),
			array(
				array(
					'CustomPackageRequest' => array(
						'orders_id' => '1234',
						'package_status' => 1
					),
					'Order' => array(
						'orders_id' => '1234',
						'OrderStatus' => array(
							'orders_status_name' => 'Shipped',
							'orders_status_id' => 3,
						)
					)),
				'/^Package: <span.+>Awaiting Package<\/span><br \/>Order: <span.+label-success.+>Shipped<\/span> <span>Order# <a href=".+">1234<\/a><\/span>$/',
			),
			array(
				array(
					'CustomPackageRequest' => array(
						'orders_id' => '1234',
						'package_status' => 1
					),
					'Order' => array(
						'orders_id' => '1234',
					)),
				'/^<span.+label-info.+>Awaiting Package<\/span>$/',
			),
		);
	}

	/**
	 * @dataProvider repackLabelProvider
	 */
	public function testRepackLabel($order, $expected) {
		$result = $this->TrackingHelper->repackLabel($order);
		$this->assertRegExp($expected, $result);
	}
	public function repackLabelProvider() {
		return array(
			array(
				array('CustomPackageRequest' => array('package_repack' => 'yes')),
				'/^<span.+>Repackage<\/span>$/',
			),
			array(
				array('CustomPackageRequest' => array('package_repack' => 'no')),
				'/^$/',
			),
			array(
				array(),
				'/^$/',
			),
		);
	}
	/**
	 * @dataProvider customRequestLabelProvider
	 */
	public function testCustomRequestLabel($order, $customRequests, $expected) {
		$result = $this->TrackingHelper->customRequestLabel($order, $customRequests);
		$this->assertRegExp($expected, $result);
	}
	public function customRequestLabelProvider() {
		return [
			[
				['CustomPackageRequest' => ['custom_orders_id' => 123]],
				[],
				'/^ <span.+>Custom<\/span>$/',
			],
			[
				[],
				[],
				'/^$/',
			],
			[
				['Order' => ['orders_id' => '12345']],
				[['CustomPackageRequest' => ['orders_id' => '12345']]],
				'/^ <span.+>Custom<\/span>$/',
			],
			[
				['Order' => ['orders_id' => '98765']],
				[['CustomPackageRequest' => ['orders_id' => '12345']]],
				'/^$/',
			],
		];
	}

	public function testFormatDatetime() {
		$date = '1999-01-01 00:00:01';
		$format = 'customer';
		$expectation = '12:00am Jan 1st, 1999';

		$result = $this->TrackingHelper->formatDatetime($date, $format);

		$this->assertEquals($expectation, $result, 'Date purchased not formatted as expected.');
	}

	public function testFormatDatetimeWithEmptyDate() {
		$date = '';
		$format = 'customer';
		$expectation = 'Not Recorded';

		$result = $this->TrackingHelper->formatDatetime($date, $format);

		$this->assertEquals($expectation, $result, 'Date purchased not formatted as expected.');
	}

	/**
	 * @dataProvider zipProvider
	 */
	public function testZip($zip, $expected) {
		$result = $this->TrackingHelper->zip($zip);
		$this->assertEquals($expected, $result);
	}

	public function zipProvider() {
		return array(
			array(
				'12345',
				'12345',
			),
			array(
				'123456789',
				'12345-6789',
			),
			array(
				'12345-6789',
				'12345-6789',
			),
			array(
				'1234',
				'1234',
			),
			array(
				'12-34',
				'12-34',
			),
			array(
				'123-456789',
				'123-456789',
			),
		);
	}

	/**
	 * @dataProvider orderChargesProvider
	 */
	public function testOrderCharges($orderCharges, $expected) {
		$result = $this->TrackingHelper->orderCharges($orderCharges);
		$this->assertRegExp($expected, $result);
	}

	public function orderChargesProvider() {
		return [
			[
				['charge' => [
					'OrderTotal' => [
						'title' => 'foo',
						'text' => 'bar',
						'class' => '',
					],
				]],
				'/foo.*bar/',
			],
			[
				['charge' => [
					'OrderTotal' => [
						'title' => 'Total',
						'text' => '$20.00',
						'class' => '',
					],
				]],
				'/Total.*\$20\.00/',
			],
			[
				['charge' => [
					'OrderTotal' => [
						'title' => 'Total',
						'text' => '$10.00',
						'class' => 'ot_total',
					],
				]],
				'/Total.*\$10\.00/',
			],
		];
	}

	/**
	 * dataProvider for testYesNo method
	 *
	 * @return array
	 */
	public function provideYesNoArgs() {
		return array(
			array(
				true,
				'Yes',
				'',
			),
			array(
				false,
				'No',
				'',
			),
			array(
				null,
				'No',
				'',
			),
		);
	}

	/**
	 * testYesNo
	 *
	 * tests the yesNo method
	 *
	 * @dataProvider provideYesNoArgs
	 * @return void
	 */
	public function testYesNo($input, $expected, $msg = '') {
		$this->assertEquals(
			$expected,
			$this->TrackingHelper->yesNo($input),
			$msg
		);
	}

	/**
	 * dataProvider for checkmark method
	 *
	 * @return array
	 */
	public function provideCheckmark() {
		return array(
			array(
				true,
				'<span class="label label-success"><i class="fa fa-check"></span>',
				'Should be success and fa-check',
			),
			array(
				false,
				'<span class="label label-danger"><i class="fa fa-times"></span>',
				'Should be danger and fa-times',
			),
			array(
				null,
				'<span class="label label-danger"><i class="fa fa-times"></span>',
				'Should be danger and fa-times',
			),
		);
	}

	/**
	 * Confirm the expected icon wrapped in the expected class is returned based
	 * on boolean input.
	 *
	 * @dataProvider provideCheckmark
	 * @return void
	 */
	public function testCheckmark($input, $expected, $msg = '') {
		$this->assertEquals(
			$expected,
			$this->TrackingHelper->checkmark($input),
			$msg
		);
	}

	public function testInsuranceCoverageWithAmount() {
		$order = array('CustomPackageRequest' => array(
			'insurance_coverage' => '12.973'
		));
		$expectation = '$12.97';
		$result = $this->TrackingHelper->insuranceCoverage($order);
		$this->assertEquals($expectation, $result, 'insurance_coverage not formatted as expected.');

	}

	public function testInsuranceCoverageWithoutAmount() {
		$order = array();
		$expectation = '<span class="small">Default</span>';
		$result = $this->TrackingHelper->insuranceCoverage($order);
		$this->assertEquals($expectation, $result, 'insurance_coverage not formatted as expected.');
	}

	/**
	 * Confirm the TrackingHelper constructor can set configure variable tracking
	 * urls as class properties.
	 *
	 * @return void
	 */
	public function testConstructorSetsTrackingUrls() {
		$uspsTrackingUrl = 'usps tracking url';
		$fedexTrackingUrl = 'fedex tracking url';
		Configure::write('ShippingApis.Usps.trackingUrl', $uspsTrackingUrl);
		Configure::write('ShippingApis.Fedex.trackingUrl', $fedexTrackingUrl);

		unset($this->TrackingHelper);

		$Controller = new Controller();
		$this->View = $View = new View($Controller);
		$this->TrackingHelper = new TrackingHelper($View);

		$this->assertSame($uspsTrackingUrl . '%s', $this->TrackingHelper->carriers['usps']['url']);
		$this->assertSame($fedexTrackingUrl . '%s', $this->TrackingHelper->carriers['fedex']['url']);
		$this->assertSame($fedexTrackingUrl . '%s', $this->TrackingHelper->carriers['fedex_freight']['url']);
	}

	/**
	 * Confirm the apoBoxAddress() method will return the expected address string
	 * when supplied with customer name and billing id.
	 *
	 * @return void
	 */
	public function testApoBoxAddress() {
		$customer = ['Customer' => [
			'customers_firstname' => 'Foo',
			'customers_lastname' => 'Bar',
			'billing_id' => 'Baz',
		]];
		$expected = 'Foo Bar<br>Attn: Baz<br>1911 Western Ave<br>Plymouth, IN 46563';

		$result = $this->TrackingHelper->apoBoxAddress($customer);
		$this->assertSame($expected, $result, 'should match the expected string');
	}

	/**
	 * Confirm the deliveryAddress method will return the expected address string.
	 *
	 * @return void
	 */
	public function testDeliveryAddress() {
		$order = ['Order' => [
			'delivery_name' => 'Foo',
			'delivery_company' => 'Bar',
			'delivery_street_address' => '9',
			'delivery_suburb' => 'Suburb',
			'delivery_city' => 'City',
			'delivery_state' => 'CA',
			'delivery_postcode' => '12345',
			'delivery_country' => 'US',
		]];
		$expected = 'Foo<br>Bar<br>9<br>Suburb<br>City, CA 12345<br>US';

		$result = $this->TrackingHelper->deliveryAddress($order);
		$this->assertSame($expected, $result, 'should match the expected string');
	}

	/**
	 * Confirm the billingAddress method will return the expected address string.
	 *
	 * @return void
	 */
	public function testBillingAddress() {
		$order = ['Order' => [
			'billing_name' => 'Bar',
			'billing_company' => 'Foo',
			'billing_street_address' => '9',
			'billing_suburb' => 'Suburb',
			'billing_city' => 'City',
			'billing_state' => 'CA',
			'billing_postcode' => '12345',
			'billing_country' => 'US',
		]];
		$expected = 'Foo<br>Bar<br>9<br>Suburb<br>City, CA 12345<br>US';

		$result = $this->TrackingHelper->billingAddress($order);
		$this->assertSame($expected, $result, 'should match the expected string');
	}

	/**
	 * Confirm the customerAddress method will return the expected address string.
	 *
	 * @return void
	 */
	public function testCustomerAddress() {
		$order = ['Order' => [
			'customers_company' => 'Bar',
			'customers_name' => 'Foo',
			'customers_street_address' => '9',
			'customers_suburb' => 'Suburb',
			'customers_city' => 'City',
			'customers_state' => 'CA',
			'customers_postcode' => '12345',
			'customers_country' => 'US',
		]];
		$expected = 'Bar<br>Foo<br>9<br>Suburb<br>City, CA 12345<br>US';

		$result = $this->TrackingHelper->customerAddress($order);
		$this->assertSame($expected, $result, 'should match the expected string');
	}

	/**
	 * Confirm the deliveryCityState method will return the expected string.
	 *
	 * @return void
	 */
	public function testDeliveryCityState() {
		$order = ['Order' => [
			'delivery_city' => 'City',
			'delivery_state' => 'CA',
		]];
		$expected = 'City, CA';

		$result = $this->TrackingHelper->deliveryCityState($order);
		$this->assertSame($expected, $result, 'should match the expected string');
	}

	/**
	 * Confirm the paymentInfo method will return the expected string.
	 *
	 * @return void
	 */
	public function testPaymentInfo() {
		$order = ['Order' => [
			'cc_owner' => 'Foo',
			'cc_type' => '',
			'cc_number' => 'XXXXXXXXXXXX1234',
			'cc_expires' => '0920',
		]];
		$expected = 'Name: Foo<br>XXXXXXXXXXXX1234<br>Expires: 09 / 2020';

		$result = $this->TrackingHelper->paymentInfo($order);
		$this->assertSame($expected, $result, 'should match the expected string');
	}

	/**
	 * Confirm the various order* methods can format currency as expected.
	 *
	 * @return void
	 * @dataProvider provideOrderTypes
	 */
	public function testOrderTypes($order, $type, $expected, $msg = '') {
		$result = $this->TrackingHelper->{'order' . $type}($order);
		$this->assertSame($expected, $result, $msg);
	}

	public function provideOrderTypes() {
		return [
			[
				['OrderShipping' => ['value' => 1.11]],
				'Shipping',
				'$1.11',
				'should match the expected currency value',
			],
			[
				[],
				'Shipping',
				'',
				'should match an empty string',
			],
			[
				['OrderInsurance' => ['value' => 2.222]],
				'Insurance',
				'$2.22',
				'should match the expected currency value',
			],
			[
				[],
				'Insurance',
				'',
				'should match an empty string',
			],
			[
				['OrderStorage' => ['value' => 33.33]],
				'Storage',
				'$33.33',
				'should match the expected currency value',
			],
			[
				[],
				'Storage',
				'',
				'should match an empty string',
			],
			[
				['OrderSubtotal' => ['value' => 444.444]],
				'Subtotal',
				'$444.44',
				'should match the expected currency value',
			],
			[
				[],
				'Subtotal',
				'',
				'should match an empty string',
			],
		];
	}

	/**
	 * Confirm requestEdit() returns the expected formatted html link.
	 *
	 * @return void
	 */
	public function testRequestEdit() {
		$request = ['CustomPackageRequest' => ['custom_orders_id' => 123]];
		$expected = '<a href="/requests/edit/123" class="btn btn-xs btn-primary">Edit</a>';

		$result = $this->TrackingHelper->requestEdit($request);

		$this->assertSame($expected, $result);
	}
}
