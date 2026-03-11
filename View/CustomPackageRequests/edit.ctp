<div class="addresses form">
	<?= $this->Form->create('CustomPackageRequest'); ?>
	<h3 class="page-header">Edit Custom Package Request</h3>
	<?= $this->element('Orders/customer', ['customer' => $this->request->data]); ?>
	<?= $this->Form->hidden('custom_orders_id'); ?>

	<?= $this->element('CustomPackageRequests/form'); ?>

	<div class="row">
		<div class="col-md-3 float-end">
			<?= $this->Form->button('Save', [
				'class' => 'btn btn-primary',
				'type' => 'submit'
			]); ?>
			<?= $this->Form->end(); ?>
		</div>
		<div class="offset-md-2 col-md-7">
			<?php if (!empty($isManager)): ?>
				<?= $this->Form->postLink(
					'Delete',
					[
						'controller' => 'custom_package_requests',
						'action' => 'delete',
						$this->request->data['CustomPackageRequest']['custom_orders_id'],
					],
					['class' => 'btn btn-danger', 'method' => 'delete'],
					'Are you sure you wish to delete this request?'
				); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
