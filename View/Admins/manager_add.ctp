<div class="admins form">
	<div class="actions pull-right">
		<?php echo $this->Html->link(__('List Admins'),
			array(
				'action' => 'index_list'
			),
			array(
				'class' => 'btn btn-primary'
			)
		); ?>
	</div>
	<h2><?php echo __('Add Admin'); ?></h2>
	<?php echo $this->Form->create('Admin'); ?>
		<fieldset>
			<?php echo $this->element('forms/inputs/admin_fields'); ?>
		</fieldset>
		<?php echo $this->Form->button('Save', array(
			'type' => 'submit',
			'class' => 'btn btn-primary center-block',
		)); ?>
	<?php echo $this->Form->end(); ?>
</div>
