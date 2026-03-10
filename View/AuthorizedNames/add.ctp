<div class="authorizedNames form">
	<?php echo $this->Form->create('AuthorizedName', array(
		'class' => '',
		'inputDefaults' => array(
			'div' => array('class' => 'form-group'),
			'label' => array('class' => 'sr-only'),
			'class' => 'form-control',
			'between' => false,
			'before' => false,
			'after' => false,
		)
	)); ?>
	<h3 class="page-header"><?php echo __('Add Authorized Name'); ?></h3>
	<div class="row">
		<div class="col-md-offset-2 col-md-8">
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
		<div class="col-xs-7 col-xs-push-5 col-md-6">
			<?php echo $this->Form->button('Add Authorized Name', array(
				'class' => 'btn btn-primary pull-right',
				'type' => 'submit'
			)); ?>
		</div>
		<div class="col-xs-5 col-xs-pull-7 col-md-offset-6 col-md-2 col-md-pull-6">
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
