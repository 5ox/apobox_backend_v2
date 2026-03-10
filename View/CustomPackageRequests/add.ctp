<div class="custom_package_requests form">
	<?php echo $this->Form->create('CustomPackageRequest'); ?>
	<h3 class="page-header">Add a Custom Package Request</h3>

	<?php echo $this->element('CustomPackageRequests/form'); ?>

	<div class="row">
		<div class="col-sm-offset-2 col-sm-10">
			<?php echo $this->Form->button('Add Custom Package Request', array(
				'class' => 'btn btn-primary pull-right',
				'type' => 'submit'
			)); ?>
		</div>
	</div>
	<?php echo $this->Form->end(); ?>
</div>
