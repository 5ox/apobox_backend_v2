<?php
$dates = Hash::extract($salesChartData, '{n}.date_purchased');
$volumes = [
		[
			'name' => 'Fees',
			'type' => 'column',
			'yAxis' => 0,
			'tooltip' => [
				'pointFormat' =>  '{series.name}: ${point.y}<br/>Total: ${point.stackTotal:,.0f}'
			],
			'data' => array_map('intval', Hash::extract($salesChartData, '{n}.ot_fee')),
		],
		[
			'name' => 'Insurance',
			'type' => 'column',
			'yAxis' => 0,
			'tooltip' => [
				'pointFormat' =>  '{series.name}: ${point.y}<br/>Total: ${point.stackTotal:,.0f}'
			],
			'data' => array_map('intval', Hash::extract($salesChartData, '{n}.ot_insurance')),
		],
		[
			'name' => 'Shipping',
			'type' => 'column',
			'yAxis' => 0,
			'tooltip' => [
				'pointFormat' =>  '{series.name}: ${point.y}<br/>Total: ${point.total:,.0f}'
			],
			'data' => array_map('intval', Hash::extract($salesChartData, '{n}.ot_shipping')),
		],
		[
			'name' => 'Orders',
			'type' => 'spline',
			'yAxis' => 1,
			'data' => array_map('intval', Hash::extract($salesChartData, '{n}.total')),
		],
];
?>

<div id="sales-volume" style="width:100%; height:400px;"
	data-dates="<?php echo htmlspecialchars(json_encode($dates)); ?>"
	data-volumes="<?php echo htmlspecialchars(json_encode($volumes)); ?>"
></div>
