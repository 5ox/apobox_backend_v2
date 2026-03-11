<?php echo $this->Form->create(false, array(
	'type' => 'get',
	'id' => 'quick-order',
	'url' => array(
		'controller' => 'customers',
		'action' => 'quick_order',
	),
	'inputDefaults' =>  array(
		'between' => '<div>',
		'label' => array('class' => 'visually-hidden'),
	),
)); ?>
<div class="smart-search smart-search-hero">
	<div class="col-md-9">
		<?php echo $this->Form->input('q', array(
			'placeholder' => 'Quick Order Entry by Customer ID',
			'between' => '<div class="col-md-9 offset-md-3">',
			'id' => 'quick-order-q',
			'autofocus',
		)); ?>
		<div class="col-md-9 offset-md-3">
		</div>
	</div>
	<div class="col-md-3">
		<?php echo $this->Form->button('Add', array('type' => 'submit', 'class' => 'btn btn-primary')); ?>
	</div>
</div>
<?php echo $this->Form->end(); ?>
