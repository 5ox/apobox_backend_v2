<?php $this->start('status'); ?>
Order Status: <?php echo ucfirst($status); ?>
<?php $this->end(); ?>

<?php $this->start('package-data'); ?>
<?php echo $this->element('Email/package_data'); ?>
<?php $this->end(); ?>

<?php $type = ($status == 'Shipped' ? 'shipped' : 'update'); ?>
<?php $this->start($type); ?>
<?php echo Router::url(array(
		'controller' => 'orders',
		'action' => 'view',
		'id' => h($orderId),
		'manager' => false,
		'prefix' => false,
	), true
); ?>
<?php $this->end(); ?>

<?php if (!empty($comments)): ?>
	<?php $this->append('comments'); ?>
	<p><?php echo $comments; ?></p>
	<?php $this->end(); ?>
<?php endif; ?>
