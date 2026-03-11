<?php echo $this->Form->create('Address', array(
	'class' => '',
	'inputDefaults' => array(
		'div' => array('class' => 'mb-3'),
		'label' => array('class' => 'visually-hidden'),
		'class' => 'form-control',
			'between' => false,
			'before' => false,
			'after' => false,
	))
); ?>
<h2>We need your shipping address!</h2>

<p class="lead">
In order to fulfill your orders, we need to know where to ship packages.
</p>

<div class="row" id="AccountIncompleteAddress">
	<div class="offset-md-2 col-md-8">
		<h3 class="page-header">Shipping Address</h3>
		<fieldset id="AddressFormFieldset">
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
							'div' => array('class' => 'mb-3 col-sm-6'),
							'placeholder' => 'Zip Code',
						)); ?>
						<?php echo $this->Form->input('entry_country_id', array(
							'div' => array('class' => 'mb-3 col-sm-6'),
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
	</div>
</div>

<div class="row">
	<div class="offset-sm-2 col-sm-10">
		<?php echo $this->Form->button('Submit Shipping Address', array(
			'class' => 'btn btn-primary',
			'type' => 'submit'
		)); ?>
	</div>
</div>
