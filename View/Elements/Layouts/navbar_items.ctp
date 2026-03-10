<?php if (!empty($u)): ?>
	<?php $this->append('navbar-collapsing-list-items'); ?>
		<li>
			<?php echo $this->Html->link(
				'My account',
				['controller' => 'customers', 'action' => 'account']
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'My Orders',
				['controller' => 'orders', 'action' => 'index']
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Custom Package Request',
				array(
					'controller' => 'custom_package_requests',
					'action' => 'add',
				)); ?>
		</li>
	<?php $this->end(); ?>
<?php endif; ?>
<?php $this->append('navbar-pull-right-list-items'); ?>
	<li>
		<?php echo $this->Html->link(
			'Customer Support',
			'https://apobox.zendesk.com/hc/en-us'
		); ?>
	</li>
	<?php if (!empty($u)): ?>
		<li>
			<?php echo $this->Html->link(__('Logout'), array(
				'controller' => 'customers',
				'action' => 'logout',
				'user' => false,
			));?>
		</li>
	<?php else: ?>
		<li>
			<?php echo $this->Html->link(__('Login'), array(
				'controller' => 'customers',
				'action' => 'login',
			));?>
		</li>
	<?php endif; ?>
<?php $this->end(); ?>
