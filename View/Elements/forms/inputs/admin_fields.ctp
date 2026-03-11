<?php
echo $this->Form->input('email');
echo $this->Form->input('password', array(
	'required' => false,
	'label' => array(
		'class' => 'col-sm-3 form-label',
		'text' => 'New Password',
	),
));
echo $this->Form->input('confirm_new_password', array(
	'type' => 'password',
	'required' => false
));
echo $this->Form->input('role', array(
	'type' => 'select',
	'options' => array(
		'manager' => 'Manager',
		'employee' => 'Employee',
		'api' => 'API',
	)
));
echo $this->Form->input('token', array(
	'label' => array(
		'class' => 'col-sm-3 form-label',
		'text' => 'Token (API only)',
	),
	'required' => false
));
?>
