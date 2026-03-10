<div class="container login-container">
	<?php
	echo $this->Form->create('Customer', array(
		'class' => 'form-signin',
		'inputDefaults' => array(
			'between' => false,
			'before' => false,
			'after' => false,
		)
	));
	?>
		<h2 class="form-signin-heading">Please sign in</h2>
		<?php echo $this->Form->input('customers_email_address', array(
			'label' => false,
			'class' => 'form-control login-email',
			'placeholder' => 'Email Address',
			'type' => 'email',
			'required',
			'autofocus'
		)); ?>
		<?php echo $this->Form->input('customers_password', array(
			'label' => false,
			'class' => 'form-control login-password',
			'placeholder' => 'Password',
			'type' => 'password',
			'required',
		)); ?>
		<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
	<?php echo $this->Form->end(); ?>
	<?php
		echo $this->Html->link('Forgot Password?', array(
			'controller' => 'customers',
			'action' => 'forgot_password'
		));
	?>
</div>
