<?php echo $this->Form->create(false, ['type' => 'GET']); ?>
<div class="well">
	<div class="row">
		<div class="col-md-2 col-md-offset-1">
			<h4 class="radio-fg-title">Interval:</h4>
			<?php echo $this->Form->input('interval', array(
				'type' => 'radio',
				'label' => array(
					'tex' => null,
					'class' => 'with-radio',
				),
				'before' => null,
				'between' => null,
				'after' => null,
				'class' => 'with-radio',
				'legend' => false,
				'separator' => '<br>',
				'options' => $validIntervals,
			));  ?>
		</div>
		<div class="col-md-5">
			<h4>From:</h4>
			<?php echo $this->Form->dateTime('from_date', 'DMY', null, array(
				'class' => 'form-control with-datetime',
				'empty' => false,
				'separator' => '&nbsp;',
				'minYear' => '2006',
				'maxYear' => date('Y'),
			)); ?>
			<h4>To:</h4>
			<?php echo $this->Form->dateTime('to_date', 'DMY', null, array(
				'class' => 'form-control with-datetime',
				'empty' => false,
				'separator' => '&nbsp;',
				'minYear' => '2006',
				'maxYear' => date('Y'),
			)); ?>
		</div>
		<div class="col-md-3">
			<?php if ($type == 'Orders'): ?>
				<h4>Status:</h4>
				<?php echo $this->Form->select(
					'orders_status',
					$statusFilterOptions,
					array(
						'empty' => 'All Statuses',
						'class' => 'form-control'
					)
				); ?>
			<?php endif; ?>
			<h4>Sort:</h4>
			<?php echo $this->Form->select(
				'sort',
				$validSortFields,
				array(
					'empty' => false,
					'class' => 'form-control'
				)
			); ?>
			<?php echo $this->Form->select(
				'direction',
				array(
					'asc' => 'ASC',
					'desc' => 'DESC',
				),
				array(
					'empty' => false,
					'class' => 'form-control'
				)
			); ?>
		</div>
		<div class="col-md-1"></div>
	</div>
	<div class="row">
		<div class="col-md-2 col-md-offset-5"><br>
			<?php echo $this->Form->button('View Report', array(
				'class' => 'btn btn-primary btn-block col-sm-2',
				'type' => 'submit'
			)); ?>
		</div>
	</div>
</div>
<?php echo $this->Form->end(); ?>
