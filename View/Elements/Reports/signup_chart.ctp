<?php
$dates = Hash::extract($signupChartData, '{n}.date');
$volumes = [
		[
			'name' => 'Fees',
			'data' => array_map('intval', Hash::extract($signupChartData, '{n}.total')),
		],
];
?>

<div id="signups-volume" style="width:100%; height:400px;"
	data-dates="<?php echo htmlspecialchars(json_encode($dates)); ?>"
	data-volumes="<?php echo htmlspecialchars(json_encode($volumes)); ?>"
></div>
