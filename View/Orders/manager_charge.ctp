<?php $label = (!$invoiceCustomer ? 'Charge' : 'Invoice'); ?>
<div class="row">
	<div class="col-md-12">
		<h2>
		<?php echo __($label . ' %s for Order #%d',
			$order['Customer']['customers_firstname'] . ' ' . $order['Customer']['customers_lastname'],
			$order['Order']['orders_id']);
		?>
		</h2>
	</div>
</div>

<?php if (!$allowCharge['allow']): ?>
<div class="row">
	<div class="col-md-12">
		<div class="alert alert-warning" role="alert">
			<p><?php echo $allowCharge['message']; ?></p>
		</div>
	</div>
</div>
<?php endif; ?>

<?php echo $this->Form->create('Order'); ?>
<?php echo $this->Form->input('Order.orders_id'); ?>
<div class="row" id="ChargeForm">
	<div class="col-sm-6 offset-sm-3 col-md-6 offset-md-3">
		<div class="row">
			<div class="col-sm-6">Postage</div>
			<div class="col-sm-6">
				<?php echo $this->Form->input('OrderShipping.value', array(
					'label' => false,
					'step' => '0.01',
					'after' => '</div>',
					'between' => '<div class="input-group"><div class="input-group-text">$</div>',
				)); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">APO Box Fee</div>
			<div class="col-sm-6">
				<?php echo $this->Form->input('OrderFee.value', array(
					'label' => false,
					'step' => '0.01',
					'after' => '</div>',
					'between' => '<div class="input-group"><div class="input-group-text">$</div>',
				)); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">Insurance Fee</div>
			<div class="col-sm-6">
				<?php echo $this->Form->input('OrderInsurance.value', array(
					'label' => false,
					'step' => '0.01',
					'after' => '</div>',
					'between' => '<div class="input-group"><div class="input-group-text">$</div>',
				)); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">Storage Fee</div>
			<div class="col-sm-6">
				<?php echo $this->Form->input('OrderStorage.value', array(
					'label' => false,
					'step' => '0.01',
					'after' => '</div>',
					'between' => '<div class="input-group"><div class="input-group-text">$</div>',
				)); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">Repack Fee</div>
			<div class="col-sm-6">
				<?php echo $this->Form->input('OrderRepack.value', array(
					'label' => false,
					'step' => '0.01',
					'after' => '</div>',
					'between' => '<div class="input-group"><div class="input-group-text">$</div>',
				)); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<?= $this->Form->checkbox('checkbox.OrderBattery') ?>Inspection Fee
				<?php echo $this->Form->input('OrderBattery.value', array(
					'type' => 'hidden',
					'default' => '0.00',
					'data-value-unchecked' => '0.00',
					'data-value-checked' => $feeRates['battery'],
				)); ?>
			</div>
			<div class="col-sm-6">
				<?= $this->Form->checkbox('checkbox.OrderReturn') ?>Return
				<?php echo $this->Form->input('OrderReturn.value', array(
					'type' => 'hidden',
					'default' => '0.00',
					'data-value-unchecked' => '0.00',
					'data-value-checked' => $feeRates['return'],
				)); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<?= $this->Form->checkbox('checkbox.OrderMisaddressed') ?>Misaddressed Fee
				<?php echo $this->Form->input('OrderMisaddressed.value', array(
					'type' => 'hidden',
					'default' => '0.00',
					'data-value-unchecked' => '0.00',
					'data-value-checked' => $feeRates['misaddressed'],
				)); ?>
			</div>
			<div class="col-sm-6">
				<?= $this->Form->checkbox('checkbox.OrderShipToUS') ?>Ship to US Address
				<?php echo $this->Form->input('OrderShipToUS.value', array(
					'type' => 'hidden',
					'default' => '0.00',
					'data-value-unchecked' => '0.00',
					'data-value-checked' => $feeRates['shipToUS'],
				)); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-6"><h3>Total</h3></div>
			<div class="col-6">
				<h3>$<span class="total-value"><?php echo number_format($order['OrderTotal']['value'], 2) ?></span></h3>
			</div>
		</div>
		<?php echo $this->Form->button(
			'Get Postage Rates',
			array(
				'class' => 'btn btn-lg btn-secondary btn-block rates-btn',
				'name' => 'submit',
				'value' => 'postage'
			)
		); ?>
		<?php if (isset($rates)): ?>
			<?php echo $this->element('Orders/manager_postal_rates', array('rates' => $rates)); ?>
		<?php endif; ?>
		<?php if ($allowCharge['allow']): ?>
			<?php echo $this->Form->button(
				$label . ' $<span class="total-value">' . number_format($order['OrderTotal']['value'], 2) . '</span>',
				array('class' => 'btn btn-lg btn-primary btn-block', 'name' => 'submit', 'value' => 'charge')
			); ?>
			<?php echo $this->Form->button(
				'Save',
				array('class' => 'btn btn-lg btn-secondary btn-block', 'name' => 'submit', 'value' => 'update')
			); ?>
		<?php endif; ?>
	</div>
</div>
<?php echo $this->Form->end(); ?>
<?php echo $this->element('js-import', ['js' => 'orders/manager_charge']); ?>
