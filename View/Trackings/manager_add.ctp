<div class="row">
	<div class="col-md-12">
		<?php echo $this->Html->link('Recent Scans', array('action' => 'search')); ?>
	</div>
</div>
<?php echo $this->Form->create('Tracking', array(
	'inputDefaults' =>  array(
		'between' => '<div>',
		'label' => array('class' => 'visually-hidden'),
	),
)); ?>
<div class="row">
	<div class="trackings add col-md-12">
		<div class="smart-search smart-search-hero">
			<div class="row">
				<div class="col-md-9">
					<?php echo $this->Form->input('tracking_id', array(
						'placeholder' => 'Scan in Tracking ID',
						'between' => '<div class="col-md-9 offset-md-3">',
						'type' => 'text',
						'autofocus'
					)); ?>
				</div>
				<div class="col-md-3">
					<?php echo $this->Form->button('Save', array('type' => 'submit', 'class' => 'btn btn-primary')); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-9">
		<div class="row">
			<div class="col-md-9 offset-md-3">
				<div class="btn-group" data-bs-toggle="buttons">
					<label class="btn btn-primary btn-push">
						<input type="checkbox" name="add_exception" id="TrackingAddException" value="1" autocomplete="off">Add Exception
					</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-9 offset-md-3">
				<div class="col-md-12">
					<div id="TrackingCommentsContainer" style="display: none;">
						<?php echo $this->Form->input('comments', array('type' => 'textarea', 'placeholder' => 'Comments')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo $this->Form->end(); ?>
<?php echo $this->element('js-import', ['js' => 'trackings/manager_add']); ?>
