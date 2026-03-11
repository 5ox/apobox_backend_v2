<div class="admins form">
	<div class="actions float-end">
		<?php echo $this->Html->link(__('List Admins'),
			array(
				'action' => 'index_list'
			),
			array(
				'class' => 'btn btn-primary'
			)
		); ?>
	</div>
	<h2><?php echo __('Edit Admin'); ?></h2>
	<p><small>Enter your password only if you want to change it.</small></p>
	<?php echo $this->Form->create('Admin'); ?>
		<fieldset>
			<?php echo $this->Form->input('id'); ?>
			<?php echo $this->element('forms/inputs/admin_fields'); ?>
		</fieldset>
		<?php echo $this->Form->button('Update', array(
			'type' => 'submit',
			'class' => 'btn btn-primary center-block',
		)); ?>
	<?php echo $this->Form->end(); ?>
</div>
