<?php
$labelClass = 'control-label sr-only';
?>
<div class="row">
	<div class="col-md-12">
		<div class="row">
		<?php
		echo $this->Form->input('cc_firstname', array(
			'div' => array('class' => 'form-group col-sm-6'),
			'placeholder' => 'First Name on Card',
			'label' => array(
				'class' => $labelClass,
			)
		));
		echo $this->Form->input('cc_lastname', array(
			'div' => array('class' => 'form-group col-sm-6'),
			'placeholder' => 'Last Name on Credit Card',
			'label' => array(
				'class' => $labelClass,
			)
		));
		?>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="row">
		<?php
		echo $this->Form->input('cc_number', array(
			'div' => array('class' => 'form-group col-sm-9'),
			'placeholder' => 'Card Number',
			'label' => array(
				'class' => $labelClass,
			),
			'maxlength' => '20',
		));
		echo $this->Form->input('cc_cvv', array(
			'div' => array('class' => 'form-group col-sm-3'),
			'placeholder' => 'CVV Code',
			'label' => array(
				'class' => $labelClass,
			),
			'type' => 'placeholder',
			'maxlength' => '4',
		));
		?>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="row">
		<div class="col-sm-4 text-right">
			<label class="form-label">Expiration:</label>
		</div>
		<?php
		echo $this->Form->input('cc_expires_month', array(
			'div' => array('class' => 'form-group col-sm-4'),
			'placeholder' => 'Expiration Month',
			'label' => array(
				'class' => $labelClass,
			),
			'type' => 'select',
			'options' => Configure::read('Form.months'),
		));
		echo $this->Form->input('cc_expires_year', array(
			'div' => array('class' => 'form-group col-sm-4'),
			'placeholder' => 'Expiration Year',
			'label' => array(
				'class' => $labelClass,
			),
			'type' => 'select',
			'default' => date_create('next year')->format('y'),
			'options' => Configure::read('Form.years'),
		));
		?>
		</div>
	</div>
</div>
<?php echo $this->element('js-import', ['js' => 'elements/forms/inputs/customer_payment_info']); ?>
