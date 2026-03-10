<?php $this->append('action-buttons'); ?>
<?php if (in_array($order['Order']['orders_status'], [1, 2])): ?>
	<?php $label = (!$invoiceCustomer ? 'Manually Charge' : 'Process Invoice'); ?>
		<?= $this->Html->link(
			$label,
			[
				'controller' => 'orders',
				'action' => 'charge',
				'id' => $order['Order']['orders_id'],
				key($level) => current($level),
		],
		['class' => 'btn btn-primary btn-block']
		); ?>
<?php endif; ?>
<?= $this->Html->link('<span>' . $action . ' ' . $mailClass . ' Postage</span>',
	$url,
	[
		'class' => 'btn btn-primary btn-block xml-btn',
		'id' => $mailClass,
		'escape' => false,
	]
); ?>
<?php if ($reprint): ?>
	<?= $this->Html->link('Remove Saved Fedex Label',
		[
			'controller' => 'orders',
			'action' => 'delete_label',
			'id' => $order['Order']['orders_id'],
			key($level) => current($level),
		],
		[
			'class' => 'btn btn-primary btn-block',
			'escape' => false,
		]
	); ?>
<?php endif; ?>
<?= $this->Html->link('Print Label',
	[
		'controller' => 'orders',
		'action' => 'print_label',
		'id' => $order['Order']['orders_id'],
		key($level) => current($level),
	],
	[
		'class' => 'btn btn-primary btn-block zpl-btn',
		'escape' => false,
	]
); ?>
<?php $this->end(); ?>
