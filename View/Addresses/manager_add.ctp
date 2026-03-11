<div class="addresses form">
	<?php echo $this->Form->create('Address', array(
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
	<h3 class="page-header">Add an Address</h3>
	<div class="row">
		<div class="offset-md-2 col-md-8">
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
								'div' => array('class' => 'mb-3 col-sm-6'),
								'placeholder' => 'First Name',
							)); ?>
							<?php echo $this->Form->input('entry_lastname', array(
								'div' => array('class' => 'mb-3 col-sm-6'),
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
								'div' => array('class' => 'mb-3 col-sm-6'),
								'placeholder' => 'City',
							)); ?>
							<?php echo $this->Form->input('entry_zone_id', array(
								'div' => array('class' => 'mb-3 col-sm-6'),
								'empty' => 'State/Zone',
								'options' => $zones,
							)); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<?php echo $this->Form->input('entry_postcode', array(
								'div' => array('class' => 'mb-3 col-sm-6'),
								'placeholder' => 'Zip Code',
							)); ?>
							<?php echo $this->Form->input('entry_country_id', array(
								'div' => array('class' => 'mb-3 col-sm-6'),
								'placeholder' => 'Country',
								'selected' => 223,
								'options' => $countries,
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
	<div class="offset-sm-2 col-sm-10">
		<?php echo $this->Form->button('Add Address', array(
			'class' => 'btn btn-primary float-end',
			'type' => 'submit'
		)); ?>
	</div>
</div>
<?php echo $this->Form->end(); ?>
</div>
<?php echo $this->element('js-import', ['js' => 'addresses/manager_add']); ?>
