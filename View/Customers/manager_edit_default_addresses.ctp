<h2>
	Edit <?php echo $customer['Customer']['customers_firstname'] ?>
	<?php echo $customer['Customer']['customers_lastname'] ?>'s
	Default Addresses
</h2>
<hr>

<?php if (!empty($isManager)) {
	echo $this->Html->link('Need to add an address?', array(
		'controller' => 'addresses',
		'action' => 'add',
		'customerId' => $customer['Customer']['customers_id']
	));
} ?>

<?php
echo $this->Form->create('Customer');
echo $this->Form->hidden('customers_id', array('value' => $customer['Customer']['customers_id']));
echo $this->Form->input('customers_default_address_id', array(
	'label' => array(
		'text' => 'Billing Address',
		'class' => 'col-sm-3 form-label',
	),
));
echo $this->Form->input('customers_shipping_address_id', array(
	'label' => array(
		'text' => 'Shipping Address',
		'class' => 'col-sm-3 form-label',
	),
));
echo $this->Form->input('customers_emergency_address_id', array(
	'label' => array(
		'text' => 'Backup Shipping Address',
		'class' => 'col-sm-3 form-label',
	),
));
echo $this->Form->button('Update', array('type' => 'submit', 'class' => 'offset-sm-3 btn btn-primary'));
echo $this->Form->end();
?>
