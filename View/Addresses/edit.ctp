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
	<h3 class="page-header">Edit '<?php echo $addressName ?>'</h3>
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
	<div class="col-4 order-8 col-md-2">
		<?php echo $this->Form->button('Update', array(
			'class' => 'btn btn-primary float-end',
			'type' => 'submit'
		)); ?>
<?php echo $this->Form->end(); ?>
	</div>
	<div class="col-8 order-4 offset-md-2 col-md-6 order-md-2">
		<?php echo $this->Form->postLink(
			'Delete',
			array('controller' => 'addresses', 'action' => 'delete', 'id' => $addressId),
			array('class' => 'btn btn-danger', 'method' => 'delete'),
			'Are you sure you wish to delete this address?'
		); ?>
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
</div>
