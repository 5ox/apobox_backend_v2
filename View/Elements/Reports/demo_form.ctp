<?php echo $this->Form->create(false, ['type' => 'GET']); ?>
<div class="bg-light p-3 rounded">
	<div class="row">
		<div class="col-md-4 offset-md-1">
			<h4 class="radio-fg-title">Field:</h4>
			<?php echo $this->Form->input('field', [
				'type' => 'radio',
				'label' => [
					'tex' => null,
					'class' => 'with-radio',
				],
				'before' => null,
				'between' => null,
				'after' => null,
				'class' => 'with-radio',
				'legend' => false,
				'separator' => '<br>',
				'options' => $reportFields,
				'default' => $options['field'],
			]);  ?>
		</div>
		<div class="col-md-6">
			<h4>From:</h4>
			<?php echo $this->Form->dateTime('from_date', 'DMY', null, [
				'class' => 'form-control with-datetime',
				'empty' => false,
				'separator' => '&nbsp;',
				'minYear' => '2006',
				'maxYear' => date('Y'),
				'default' => $options['from_date'],
			]); ?>
			<h4>To:</h4>
			<?php echo $this->Form->dateTime('to_date', 'DMY', null, [
				'class' => 'form-control with-datetime',
				'empty' => false,
				'separator' => '&nbsp;',
				'minYear' => '2006',
				'maxYear' => date('Y'),
				'default' => $options['to_date'],
			]); ?>
			<h4>Limit:</h4>
			<?php echo $this->Form->input(
				'limit',
				[
					'label' => false,
					'class' => 'form-control',
					'default' => $options['limit'],
				]
			); ?>
		</div>
		<div class="col-md-1"></div>
	</div>
	<div class="row">
		<div class="col-md-10 offset-md-1"><br>
			<?php echo $this->Form->button('View Report', [
				'class' => 'btn btn-primary btn-block col-sm-2',
				'type' => 'submit'
			]); ?>
		</div>
	</div>
</div>
<?php echo $this->Form->end(); ?>

