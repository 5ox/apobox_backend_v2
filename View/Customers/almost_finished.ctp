<?php echo $this->Form->create('Address', array(
	'class' => '',
	'inputDefaults' => array(
		'div' => array('class' => 'form-group'),
		'label' => array('class' => 'sr-only'),
		'class' => 'form-control',
			'between' => false,
			'before' => false,
			'after' => false,
	))
); ?>
<h2>You're almost finished!</h2>

<p class="lead">
All that is left to do it to fill out your billing information to ensure your
packages are delivered to you as quickly as possible.
</p>

<div class="row" id="AlmostFinishedAddress">
	<div class="col-md-offset-2 col-md-8">
		<h3 class="page-header">Billing Address</h3>
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<?php echo $this->Form->input('Customer.customers_default_address_id', array(
						'div' => array('class' => 'form-group col-sm-6'),
						'placeholder' => 'First Name',
						'options' => $addresses,
						'empty' => array('new' => 'Create a new address'),
						'required' => false
					)); ?>
				</div>
			</div>
		</div>
		<fieldset id="AddressFormFieldset">
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
		</fieldset>

		<fieldset>
			<h3 class="page-header">Payment Information</h3>
			<?php echo $this->Form->hidden('Customer.customers_id', array(
				'value' => $customer['customers_id']
			)); ?>
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<?php echo $this->Form->input('Customer.cc_firstname', array(
							'div' => array('class' => 'form-group col-sm-6'),
							'placeholder' => 'First Name'
						)); ?>
						<?php echo $this->Form->input('Customer.cc_lastname', array(
							'div' => array('class' => 'form-group col-sm-6'),
							'placeholder' => 'Last Name'
						)); ?>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<?php echo $this->Form->input('Customer.cc_number', array(
							'div' => array('class' => 'form-group col-sm-4'),
							'placeholder' => 'Credit Card Number',
						)); ?>
						<?php echo $this->Form->input('Customer.cc_expires_month', array(
							'div' => array('class' => 'form-group col-sm-3'),
							'placeholder' => 'Month',
							'type' => 'select',
							'options' => Configure::read('Form.months')
						)); ?>
						<?php echo $this->Form->input('Customer.cc_expires_year', array(
							'div' => array('class' => 'form-group col-sm-2'),
							'placeholder' => 'Year',
							'type' => 'select',
							'options' => Configure::read('Form.years')
						)); ?>
						<?php echo $this->Form->input('Customer.cc_cvv', array(
							'div' => array('class' => 'form-group col-sm-3'),
							'placeholder' => 'Verification Code',
							'type' => 'text'
						)); ?>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<?php echo $this->Form->input('CustomersInfo.source_id', array(
							'div' => array('class' => 'form-group col-sm-5'),
							'type' => 'select',
							'label' => false,
							'options' => Configure::read('Customers.sources')
						)); ?>
				</div>
			</div>
		</fieldset>
	</div>
</div>

<div class="row">
	<div class="col-sm-offset-2 col-sm-10">
		<?php echo $this->Form->button('Add Billing Info', array(
			'class' => 'btn btn-primary',
			'type' => 'submit'
		)); ?>
	</div>
</div>
<?php echo $this->element('js-import', ['js' => 'customers/almost_finished']); ?>
