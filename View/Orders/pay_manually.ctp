<h2>
	Pay for Order #<?php echo $order['Order']['orders_id']?>
</h2>

<h3>Order Summary</h3>
<div class="row">
	<div class="col-md-offset-1 col-md-10 col-lg-offset-2 col-lg-8">
		<div class="row">
			<div class="col-sm-10">
				<td>Order #<?php echo $order['Order']['orders_id']?></td>
			</div>
			<div class="col-sm-2">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-8"></div>
			<div class="col-sm-4">
				 <?php echo $this->Tracking->orderCharges($orderCharges); ?>
			</div>
		</div>
	</div>
</div>

<hr>
<h3>Payment Information</h3>
<div class="row">
	<div class="col-md-offset-1 col-md-10 col-lg-offset-2 col-lg-8">
		<?php
		echo $this->Form->create('Customer', array(
			'class' => 'with-checkbox',
			'inputDefaults' => array(
				'div' => array('class' => 'form-group'),
				'label' => array('class' => 'sr-only'),
				'class' => 'form-control',
					'between' => false,
					'before' => false,
					'after' => false,
			))
		);
		echo $this->element('forms/inputs/customer_payment_info');
		?>
		<div class="row">
			<div class="col-md-12">
				<div class="row">
				<?php
				echo $this->Form->input('save', array(
					'div' => array('class' => 'form-group col-sm-12'),
					'label' => array(
						'text' => 'Save and Use For Future Orders',
						'class' => 'control-label',
					),
					'type' => 'checkbox',
				));
				?>
				</div>
			</div>
		</div>
	</div>
</div>

<h3 class="page-header">Billing Address</h3>
<div class="row" id="PayManuallyAddress">
	<div class="col-md-offset-1 col-md-10 col-lg-offset-2 col-lg-8">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<?php echo $this->Form->input('Customer.customers_default_address_id', array(
						'div' => array('class' => 'form-group col-sm-6'),
						'placeholder' => 'First Name',
						'options' => $addresses,
						'empty' => array('custom' => 'Custom'),
						'selected' => $selected,
						'required' => false
					)); ?>
				</div>
			</div>
		</div>
		<fieldset id="AddressFormFieldset">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<?php echo $this->Form->input('Address.entry_firstname', array(
							'div' => array('class' => 'form-group col-sm-6'),
							'placeholder' => 'First Name',
						)); ?>
						<?php echo $this->Form->input('Address.entry_lastname', array(
							'div' => array('class' => 'form-group col-sm-6'),
							'placeholder' => 'Last Name',
						)); ?>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?php echo $this->Form->input('Address.entry_street_address', array(
						'placeholder' => 'Address',
					)); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?php echo $this->Form->input('Address.entry_suburb', array(
						'placeholder' => 'Apt / Building / etc.',
					)); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<?php echo $this->Form->input('Address.entry_city', array(
							'div' => array('class' => 'form-group col-sm-6'),
							'placeholder' => 'City',
						)); ?>
						<?php echo $this->Form->input('Address.entry_zone_id', array(
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
						<?php echo $this->Form->input('Address.entry_postcode', array(
							'div' => array('class' => 'form-group col-sm-6'),
							'placeholder' => 'Zip Code',
						)); ?>
						<?php echo $this->Form->input('Address.entry_country_id', array(
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
		<?= $this->Form->button('Pay for Order', [
			'type' => 'submit',
			'class' => 'btn btn-success btn-block',
			'id' => 'payment-btn',
		]) ?>
	</div>
</div>

<?php
echo $this->Form->end();
?>
<?php echo $this->element('js-import', ['js' => 'orders/pay_manually']); ?>
