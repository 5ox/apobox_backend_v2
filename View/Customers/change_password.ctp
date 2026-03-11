<?php echo $this->Form->create('Customer'); ?>

<div class="row">
	<div class="offset-md-2 col-md-8">
		<h3 class="page-header">Change Password</h3>
			<?php echo $this->Form->input('current_password', array('type' => 'password')); ?>
			<?php echo $this->Form->input('new_password', array('type' => 'password')); ?>
			<?php echo $this->Form->input('confirm_new_password', array('type' => 'password')); ?>
	</div>
</div>

<div class="row">
	<div class="offset-md-3 col-md-2">
		<?php echo $this->Html->link('Cancel',
			array(
				'controller' => 'customers',
				'action' => 'account',
				'#' => 'my-info',
			),
			array(
				'class' => 'btn btn-warning',
			)
		); ?>
	</div>
	<div class="col-md-3">
		<?php echo $this->Form->button('Update', array(
			'class' => 'btn btn-primary float-end',
			'type' => 'submit'
		)); ?>
	</div>
</div>
<?php echo $this->Form->end(); ?>
