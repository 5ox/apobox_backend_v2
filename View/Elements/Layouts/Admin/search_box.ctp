<?= $this->Form->create(false, [
	'type' => 'get',
	'inputDefaults' =>  [
		'between' => '<div>',
		'label' => ['class' => 'sr-only'],
	],
]); ?>

<div class="col-md-9">
	<p>
		<a tabindex="0"
			class="btn btn-xs btn-primary pull-right"
			role="button"
			data-toggle="popover"
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
			'between' => '<div class="col-md-9 col-md-offset-3">',
		]); ?>
	</div>
	<div class="col-md-3">
		<?= $this->Form->button('Search', ['type' => 'submit', 'class' => 'btn btn-primary']); ?>
	</div>
</div>
<?= $this->Form->end(); ?>
