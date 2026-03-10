<h2>Orders</h2>
<div class="col-md-6">
	<?php echo $this->Form->create(false, array(
		'type' => 'get',
		'inputDefaults' =>  array(
			'between' => '<div>',
			'label' => array('class' => 'sr-only'),
		),
	)); ?>
	<?php echo $this->Form->input('q', array('Placeholder' => 'Search', 'value' => $search, 'autofocus')); ?>
</div>
<div class="col-md-2">
	<?php echo $this->Form->input('from_the_past', array(
		'type' => 'select',
		'options' => Configure::read('Search.date.options'),
		'value' => $fromThePast,
	)); ?>
</div>
<?php if (!empty($statusFilterOptions)): ?>
<div class="col-md-2">
	<?php echo $this->Form->select(
		'showStatus',
		$statusFilterOptions,
		array('value' => $showStatus, 'empty' => 'All Statuses', 'class' => 'form-control')
	); ?>
</div>
<?php endif; ?>
<div class="col-md-2">
	<?php echo $this->Form->button('Search', array('class' => 'btn btn-primary btn-block', 'type' => 'submit')); ?>
	<?php echo $this->Form->end(); ?>
</div>

<?php if (isset($results)): ?>
<?php echo $this->element('search/orders', array('results' => $results)); ?>
<?php echo $this->element('pagination'); ?>
<?php endif; ?>
