<h2 class="page-header">
	Edit <?php echo ucwords(str_replace('_', ' ', $partial)); ?>
</h2>

<?php
switch($partial) {
	case 'default_addresses':
		echo $this->Html->link('Need to add an Address?', array('controller' => 'addresses', 'action' => 'add'));
		break;
}

if ($partial == 'payment_info') {
	echo $this->Form->create('Customer', array(
		'class' => 'offset-md-3 col-md-6',
		'inputDefaults' => array(
			'div' => array('class' => 'mb-3'),
			'label' => array('class' => 'visually-hidden'),
			'class' => 'form-control',
				'between' => false,
				'before' => false,
				'after' => false,
		))
	);
	echo $this->element('forms/inputs/customer_payment_info');
	echo $this->element('forms/inputs/customer_partial_btns');
} else {
	echo $this->Form->create('Customer');
	$defaults = array(
		'label' => array(
			'class' => 'col-sm-3 form-label',
		));
	foreach ($inputs as $fieldName => $options) {
		$options = Hash::merge($defaults, (array) $options);
		echo $this->Form->input($fieldName, $options);
	}
	echo $this->element('forms/inputs/customer_partial_btns');
}
echo $this->Form->end();
?>
