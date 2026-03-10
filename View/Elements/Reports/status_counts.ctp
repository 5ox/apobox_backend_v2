<div class="well">
	<h3>Current Order Statuses:</h3>
	<?php foreach ($statusCounts as $status): ?>
		<div class="row">
			<div class="col-sm-6">
			<h4><?php echo key($status); ?></h4>
			</div>
			<div class="col-sm-3">
				<h4><span class="label label-info"><?php echo $status[key($status)]; ?></span></h4>
			</div>
		</div>
	<?php endforeach; ?>
</div>
