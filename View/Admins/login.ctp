<div class="container login-container">
	<?php if (Configure::read('OAuth2.legacyLogin')): ?>
		<?php
		echo $this->Form->create('Admin', array(
			'class' => 'form-signin',
			'inputDefaults' => array(
				'between' => false,
				'before' => false,
				'after' => false,
			)
		));
		?>
		<h2 class="form-signin-heading">Please sign in</h2>
			<?php echo $this->Form->input('email', array(
				'label' => false,
				'class' => 'form-control login-email',
				'placeholder' => 'Email Address',
				'type' => 'email',
				'required',
				'autofocus'
			)); ?>
			<?php echo $this->Form->input('password', array(
				'label' => false,
				'class' => 'form-control login-password',
				'placeholder' => 'Password',
				'type' => 'password',
				'required',
			)); ?>
			<button class="btn btn-lg btn-warning btn-block" type="submit">Sign in</button>
		<?php echo $this->Form->end(); ?>
		<br>
	<?php endif; ?>
	<?php if (Configure::check('OAuth2.Google')): ?>
		<?= $this->Html->link('Sign in via Google',
			[
				'controller' => 'admins',
				'action' => 'login_google',
			],
			[
				'class' => 'btn btn-lg btn-warning btn-block',
			]
		) ?>
	<?php endif; ?>
</div>
