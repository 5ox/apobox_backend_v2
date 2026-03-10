<?php if (!empty($isManager) && $this->request->action === 'manager_add'): ?>
	<?php echo $this->Form->input('customers_id', array(
		'type' => 'hidden',
	)); ?>
<?php endif; ?>
