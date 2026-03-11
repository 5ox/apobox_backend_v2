<h2>Customers</h2>
<div class="col-md-6">
	<?php echo $this->Form->create(false, array(
		'type' => 'get',
		'inputDefaults' =>  array(
			'between' => '<div>',
			'label' => array('class' => 'visually-hidden'),
		),
	)); ?>
	<?php echo $this->Form->input('q', array('Placeholder' => 'Search', 'value' => $search, 'autofocus')); ?>
</div>
<div class="col-md-2 offset-md-4">
	<?php echo $this->Form->button('Search', array('class' => 'btn btn-primary btn-block', 'type' => 'submit')); ?>
	<?php echo $this->Form->end(); ?>
</div>

<?php if (isset($results)): ?>
	<?php echo $this->element('search/customers', array('results' => $results)); ?>
	<?php echo $this->element('pagination'); ?>
<?php endif; ?>
