<h3>Custom Package Requests</h3>
<div class="col-md-5">
	<?= $this->Form->create(false, [
		'type' => 'get',
		'inputDefaults' =>  [
			'between' => '<div>',
			'label' => ['class' => 'visually-hidden'],
		],
	]); ?>
	<?= $this->Form->input('q', ['Placeholder' => 'Search', 'value' => $search, 'autofocus']); ?>
</div>
<div class="col-md-2">
	<?= $this->Form->input('from_the_past', [
		'type' => 'select',
		'options' => Configure::read('Search.date.options'),
		'selected' => $fromThePast,
	]); ?>
</div>
<?php if (!empty($statusFilterOptions)): ?>
<div class="col-md-2">
	<?= $this->Form->select(
		'showStatus',
		$statusFilterOptions,
		['value' => $showStatus, 'empty' => 'All Statuses', 'class' => 'form-control']
	); ?>
</div>
<?php endif; ?>
<div class="col-md-3">
	<?= $this->Form->button('Search', ['class' => 'btn btn-primary', 'type' => 'submit']); ?>
	<?= $this->Form->end(); ?>
</div>

<?php if (isset($requests)): ?>
	<?= $this->element('CustomPackageRequests/list'); ?>
	<?= $this->element('pagination'); ?>
<?php endif; ?>
