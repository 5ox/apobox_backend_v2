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
	<h3 class="page-header"><?php echo __('Add Authorized Name'); ?></h3>
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
		<div class="col-7 order-5 col-md-6">
			<?php echo $this->Form->button('Add Authorized Name', array(
				'class' => 'btn btn-primary float-end',
				'type' => 'submit'
			)); ?>
		</div>
		<div class="col-5 order-7 offset-md-6 col-md-2 order-md-6">
			<?php echo $this->Html->link(
				'Cancel',
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
	<?php echo $this->Form->end(); ?>
</div>
