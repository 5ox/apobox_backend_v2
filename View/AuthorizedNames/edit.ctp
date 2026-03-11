<div class="authorizedNames form">
	<?php echo $this->Form->create('AuthorizedName', array(
		'class' => '',
		'inputDefaults' => array(
			'div' => array('class' => 'mb-3'),
			'label' => array('class' => 'visually-hidden'),
			'class' => 'form-control',
			'between' => false,
			'before' => false,
			'after' => false,
		)
	)); ?>
	<?php echo $this->Form->input('authorized_names_id'); ?>
	<h3 class="page-header"><?php echo __('Edit Authorized Name'); ?></h3>
	<div class="row">
		<div class="offset-md-2 col-md-8">
			<fieldset id="AuthorizedNameFormFieldset">
				<div class="row">
					<div class="col-md-12">
						<?php echo $this->Form->input('authorized_firstname', array(
							'placeholder' => 'First Name'
						)); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo $this->Form->input('authorized_lastname', array(
							'placeholder' => 'Last Name'
						)); ?>
					</div>
				</div>
			</fieldset>
		</div>
	</div>

	<div class="row">
		<div class="col-md-4 float-end">
			<?php echo $this->Form->button('Update', array(
				'class' => 'btn btn-primary',
				'type' => 'submit'
			)); ?>
			<?php echo $this->Form->end(); ?>
		</div>
		<div class="offset-md-2 col-md-4">
			<?php echo $this->Html->link(
				__('Delete'),
				array(
					'controller' => 'authorized_names',
					'action' => 'delete',
					'id' => $this->Form->value('AuthorizedName.authorized_names_id')
				),
				array(
					'class' => 'btn btn-danger'
				),
				__('Are you sure you want to delete %s %s?', $this->Form->value('AuthorizedName.authorized_firstname'), $this->Form->value('AuthorizedName.authorized_lastname'))
			); ?>
			<?php echo $this->Html->link('Cancel',
				array(
					'controller' => 'customers',
					'action' => 'account',
					'#' => 'authorized_names',
				),
				array(
					'class' => 'btn btn-warning',
				)
			); ?>
		</div>
	</div>
</div>
