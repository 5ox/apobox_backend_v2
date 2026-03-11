<div class="col-sm-4 offset-sm-8">
	<?php if ($customer['Customer']['is_active']): ?>
		<?php if (!$partialSignup): ?>
			<?php echo $this->Html->link(
				'New Order',
				array(
					'controller' => 'orders',
					'action' => 'add',
					'customerId' => $customer['Customer']['customers_id'],
				),
				array(
					'class' => 'btn btn-primary btn-block',
				)
			); ?>
			<?php echo $this->Html->link(
				'New Custom Package Request',
				array(
					'controller' => 'custom_package_requests',
					'action' => 'add',
					'customerId' => $customer['Customer']['customers_id'],
				),
				array(
					'class' => 'btn btn-primary btn-block',
				)
			); ?>
		<?php endif; ?>
		<?php if (!empty($isManager)): ?>
			<?php echo $this->Html->link(
				'Close Account',
				array(
					'controller' => 'customers',
					'action' => 'close_account',
					'customerId' => $customer['Customer']['customers_id'],
				),
				array(
					'class' => 'btn btn-danger btn-block',
					'confirm' => "Are you sure you want to close this customer's account?",
				)
			); ?>
		<?php endif; ?>
	<?php endif; ?>
</div>
