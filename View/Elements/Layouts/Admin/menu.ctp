<?php echo $this->Html->link('All Customers', array(
	'controller' => 'customers',
	'action' => 'search',
)); ?>
<br>
<?php echo $this->Html->link('All Orders', array(
	'controller' => 'orders',
	'action' => 'search',
)); ?>
&nbsp;|
<?php foreach ($orderStatuses as $orderStatus): ?>
	<?php echo $this->Html->link($orderStatus['OrderStatus']['orders_status_name'],
		array(
			'controller' => 'orders',
			'action' => 'search',
			'?' => array(
				'showStatus' => $orderStatus['OrderStatus']['orders_status_id']
			)
		),
		array(
			'class' => 'label label-default'
		)
	); ?>
<?php endforeach; ?>

<br />
<?php echo $this->Html->link('Custom Package Requests', array(
	'controller' => 'custom_package_requests',
	'action' => 'index',
)); ?>
<br />
<?php echo $this->Html->link('Add Scan', array(
	'controller' => 'trackings',
	'action' => 'add',
)); ?>
&nbsp;|
<?php echo $this->Html->link('View Scans', array(
	'controller' => 'trackings',
	'action' => 'search',
)); ?>
<?php if (!empty($isManager)): ?>
<br />
<?php echo $this->Html->link('Manage Admins', array(
	'controller' => 'admins',
	'action' => 'index_list',
)); ?>
<?php endif; ?>
