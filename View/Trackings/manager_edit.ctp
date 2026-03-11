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
					<div class="col-md-9 offset-md-3">
						<h2>Tracking ID: <code><?php echo $this->request->data['Tracking']['tracking_id']; ?></code></h2>
						<?php echo $this->Form->input('tracking_id', array('type' => 'hidden')); ?>
					</div>
				</div>
				<div class="col-md-3">
					<?php echo $this->Form->button('Update', array('type' => 'submit', 'class' => 'btn btn-primary')); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-9">
		<div class="row">
			<div class="col-md-9 offset-md-3">
				<h4>Comments or Exceptions</h4>
			</div>
		</div>
		<div class="row">
			<div class="col-md-9 offset-md-3">
				<div class="col-md-12">
					<div id="TrackingCommentsContainer">
						<?php echo $this->Form->input('comments', array('type' => 'textarea', 'placeholder' => 'Comments')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo $this->Form->end(); ?>
<?php $this->append('script') ?>
<?php $this->end(); ?>
