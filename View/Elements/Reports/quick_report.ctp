<?php echo $this->Html->link($title, [
	'controller' => $controller,
	'action' => 'report',
	'?' => [
		'interval' => $interval,
		'from_date' => date('Y-m-d H:i:s', $from),
		'to_date' => date('Y-m-d H:i:s', $to),
	],
]); ?>
