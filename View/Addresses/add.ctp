<div class="addresses form">
	<?php echo $this->Form->create('Address', array(
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
	<h3 class="page-header">Add an Address</h3>
	<div class="row">
		<div class="col-md-offset-2 col-md-8">
			<fieldset id="AddressFormFieldset">
				<div class="row">
					<div class="col-md-12">
						<?php echo $this->Form->input('entry_company', array(
							'placeholder' => 'Company',
						)); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<?php echo $this->Form->input('entry_firstname', array(
								'div' => array('class' => 'form-group col-sm-6'),
								'placeholder' => 'First Name',
							)); ?>
							<?php echo $this->Form->input('entry_lastname', array(
								'div' => array('class' => 'form-group col-sm-6'),
								'placeholder' => 'Last Name',
							)); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo $this->Form->input('entry_street_address', array(
							'placeholder' => 'Address',
						)); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo $this->Form->input('entry_suburb', array(
							'placeholder' => 'Apt / Building / etc.',
						)); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<?php echo $this->Form->input('entry_city', array(
								'div' => array('class' => 'form-group col-sm-6'),
								'placeholder' => 'City',
							)); ?>
							<?php echo $this->Form->input('entry_zone_id', array(
								'div' => array('class' => 'form-group col-sm-6'),
								'type' => 'select',
								'empty' => 'State/Zone',
								'options' => $zones
							)); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<?php echo $this->Form->input('entry_postcode', array(
								'div' => array('class' => 'form-group col-sm-6'),
								'placeholder' => 'Zip Code',
							)); ?>
							<?php echo $this->Form->input('entry_country_id', array(
								'div' => array('class' => 'form-group col-sm-6'),
								'placeholder' => 'Country',
								'options' => array(
									'223' => 'USA'
								)
							)); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo $this->Form->input('entry_basename', array(
							'placeholder' => 'Base',
						)); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo $this->Form->input('make_this_my', array(
							'type' => 'select',
							'options' => array(
								'billing' => 'Make this my Billing Address.',
								'shipping' => 'Make this my Shipping Address.',
								'emergency' => 'Make this my Backup Shipping Address.',
							),
							'empty' => 'Don\'t make this a default address.'
						)); ?>
					</div>
				</div>
			</fieldset>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-4 col-xs-push-8 col-md-2">
			<?php echo $this->Form->button('Add Address', array(
				'class' => 'btn btn-primary pull-right',
				'type' => 'submit'
			)); ?>
		</div>
		<div class="col-xs-8 col-xs-pull-4 col-md-offset-2 col-md-6 col-md-pull-2">
			<?php echo $this->Html->link('Cancel',
				array(
					'controller' => 'customers',
					'action' => 'account',
					'#' => 'addresses',
				),
				array(
					'class' => 'btn btn-warning',
				)
			); ?>
		</div>
	</div>
	<?php echo $this->Form->end(); ?>
</div>
