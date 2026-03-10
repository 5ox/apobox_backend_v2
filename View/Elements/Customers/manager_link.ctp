<?php
$action = !empty($action) ? $action : 'view';
$link = !empty($link) ? $link : [
	'controller' => 'customers',
	'action' => $action,
	'id' => $customer['Customer']['customers_id'],
];
?>
<?php if (!empty($isManager)): ?>
	<small>
		<?php echo $this->Html->link($text, $link); ?>
	</small>
<?php endif; ?>
