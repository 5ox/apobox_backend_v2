<h2 class="page-header">
	Edit <?php echo $customer['Customer']['customers_firstname'] ?>
	<?php echo $customer['Customer']['customers_lastname'] ?>'s
	Information
</h2>
<hr>

<?php
echo $this->Form->create('Customer');
echo $this->Form->hidden('customers_id', array('value' => $customer['Customer']['customers_id']));
echo $this->Form->input('customers_firstname', array(
	'label' => array(
		'text' => 'First Name',
		'class' => 'col-sm-3 control-label',
	),
));
echo $this->Form->input('customers_lastname', array(
	'label' => array(
		'text' => 'Last Name',
		'class' => 'col-sm-3 control-label',
	),
));
echo $this->Form->input('customers_email_address', array(
	'label' => array(
		'text' => 'Email Address',
		'class' => 'col-sm-3 control-label',
	),
));
echo $this->Form->input('backup_email_address', array(
	'label' => array(
		'text' => 'Backup Email Address',
		'class' => 'col-sm-3 control-label',
	),
));
echo $this->Form->input('customers_telephone', array(
	'label' => array(
		'text' => 'Phone Number',
		'class' => 'col-sm-3 control-label',
	),
));
echo $this->Form->input('customers_fax', array(
	'label' => array(
		'text' => 'Cell Phone Number',
		'class' => 'col-sm-3 control-label',
	),
));
?>
<div class="row">
	<div class="col-sm-3 col-sm-offset-3">
		<?php echo $this->Form->input('invoicing_authorized', array(
			'type' => 'checkbox',
			'label' => 'Invoicing Authorized',
			'between' => null,
			'after' => null,
		));  ?>
	</div>
	<div class="col-sm-6">
		<?php echo $this->Form->input('billing_type', array(
			'type' => 'radio',
			'label' => array(
				'text' => null,
				'class' => 'with-radio',
			),
			'before' => 'Billing Type:&nbsp;&nbsp;&nbsp;',
			'between' => null,
			'after' => null,
			'class' => 'with-radio',
			'legend' => false,
			'separator' => '&nbsp;&nbsp;&nbsp;',
			'options' => array(
				'cc' => 'CC',
				'invoice' => 'Invoice',
			),
		));  ?>
	</div>
</div>
<?php
echo $this->element('forms/inputs/customer_partial_btns', array('partial' => 'customer_info', 'manager' => true));
echo $this->Form->end();
?>
