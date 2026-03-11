<?= $this->Form->create(false, [
	'type' => 'get',
	'inputDefaults' =>  [
		'between' => '<div>',
		'label' => ['class' => 'visually-hidden'],
	],
]); ?>

<div class="col-md-9">
	<p>
		<a tabindex="0"
			class="btn btn-sm btn-primary float-end"
			role="button"
			data-bs-toggle="popover"
			data-placement="left"
			data-html="true"
			title="Search Help"
			data-content="<?= $this->element('Admins/search_help') ?>">
			<i class="fa fa-question-circle" aria-hidden="true"></i>
		</a>
		<br>
	</p>
</div>

<div class="smart-search smart-search-hero">
	<div class="col-md-9">
		<?= $this->Form->input('q', [
			'placeholder' => 'Search for Orders or Customers or Scan',
			'between' => '<div class="col-md-9 offset-md-3">',
		]); ?>
	</div>
	<div class="col-md-3">
		<?= $this->Form->button('Search', ['type' => 'submit', 'class' => 'btn btn-primary']); ?>
	</div>
</div>
<?= $this->Form->end(); ?>
