<h2 class="page-header">
	Edit <?php echo $customer['Customer']['customers_firstname'] ?>
	<?php echo $customer['Customer']['customers_lastname'] ?>'s
	Payment Info
</h2>

<?php
	echo $this->Form->create('Customer', array(
		'class' => 'col-md-offset-3 col-md-6 with-checkbox',
		'inputDefaults' => array(
			'div' => array('class' => 'form-group'),
			'label' => array('class' => 'sr-only'),
			'class' => 'form-control',
				'between' => false,
				'before' => false,
				'after' => false,
		))
	);
	echo $this->Form->hidden('customers_id', array('value' => $customer['Customer']['customers_id']));
	echo $this->element('forms/inputs/customer_payment_info');
	echo $this->element('forms/inputs/customer_partial_btns', array('partial' => 'payment_info', 'manager' => true));
	echo $this->Form->end();
?>
