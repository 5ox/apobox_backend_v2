<h2>Forgot Password</h2>

<?php
echo $this->Form->create('Customer');
echo $this->Form->input('new_password', array('type' => 'password'));
echo $this->Form->input('password_confirm', array('type' => 'password'));
?>
<div class="row">
	<div class="col-sm-8 col-offset-sm-4">
		<?php
		echo $this->Form->button('Submit', array('type' => 'submit', 'class' => 'btn btn-primary pull-right'));
		?>
	</div>
</div>
<?php
echo $this->Form->end();
?>
