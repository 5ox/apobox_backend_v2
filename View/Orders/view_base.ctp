<div class="orders view">
<div class="row">
	<div class="col-md-8">
		<h2><?php echo __('Order #'); echo h($order['Order']['orders_id']); ?></h2>
	</div>
	<div class="col-md-4">
		<h2>Status: <?php echo $this->Tracking->statusLabel($order); ?></h2>
	</div>
</div>
<hr>
<div class="row">
	<div class="col-md-12 lead">
		<?php echo $this->fetch('extra-labels'); ?>
	</div>
</div>
<div class="row">
	<div class="col-sm-6 col-sm-push-6">
	<?php echo $this->fetch('action-area'); ?>
	</div>
	<div class="col-sm-6 col-sm-pull-6">
		<div class="lead">
			Updated <?php echo $this->Tracking->lastUpdated($order); ?>
		</div>
		<div class="lead">
			Inbound: <?php echo $this->Tracking->inbound($order); ?>
		</div>
		<div class="lead">
			Outbound: <?php echo $this->Tracking->outbound($order); ?>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-4">
		<div>
			Dimensions: <?php echo $this->Tracking->packageDimensions($order) ?>
		</div>
		<div>
			Weight: <?php echo $this->Tracking->weight($order); ?>
		</div>
	</div>
	<div class="col-md-4">
		<div>
			Class: <?php echo h($order['Order']['mail_class']); ?>
		</div>
		<div>
			Type: <?php echo h($order['Order']['package_type']); ?>
		</div>
	</div>
	<div class="col-md-4">
		<div>
			Insurance: $<?php echo h($order['Order']['insurance_coverage']); ?>
		</div>
		<?php if (!empty($order['Order']['customs_description'])): ?>
			<div>
				Customs Description: <?php echo h($order['Order']['customs_description']); ?>
			</div>
		<?php endif ?>
		<div>
			Processed: <?php echo $this->Tracking->formatDatetime($order['Order']['date_purchased'], 'customer'); ?>
		</div>
		<?php if (!empty($creator)): ?>
			<div>
				Created By: <?php echo h($creator); ?>
			</div>
		<?php endif ?>
	</div>
</div>
<div class="row">
	<div class="col-md-4">
		<h3>From:</h3>
		<div class="well well-small lead">
			<?php echo $this->Tracking->apoBoxAddress($order); ?><br>
		</div>
	</div>
	<div class="col-md-4">
		<h3>To:</h3>
		<div class="well well-small lead">
			<?php echo $this->Tracking->deliveryAddress($order); ?>
		</div>
	</div>
	<div class="col-md-4">
		<?php if (!empty($order['Order']['comments']) && $order['Order']['comments'] != 'NULL'): ?>
			<h3>Comments</h3>
			<?php echo h($order['Order']['comments']); ?>
		<?php endif; ?>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<h3>Billing:</h3>
		<hr>
		<div class="row">
			<div class="col-md-4">
				<h4>Address:</h4>
				<div class="lead">
					<?php echo $this->Tracking->billingAddress($order); ?>
				</div>
			</div>
			<div class="col-md-4">
				<h4>Card:</h4>
				<div class="lead">
					<?php echo $this->Tracking->paymentInfo($order); ?>
				</div>
			</div>
			<div class="col-md-4">
				<h4>Charges:</h4>
				<div class="lead">
					<?php echo $this->Tracking->orderCharges($orderCharges); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
	<h3>Status History</h3>
	<?php if (empty($statusHistories)): ?>
		<p>No status history is available for this order.</p>
	<?php else: ?>
	<table class="table table-responsive order-status-history-table">
		<thead>
			<tr>
				<th>Time</th>
				<th>Status</th>
				<th>Comments</th>
				<th>Notified</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($statusHistories as $statusHistory): ?>
			<tr>
				<td><?php echo $this->Tracking->formatDatetime($statusHistory['OrderStatusHistory']['date_added'], 'customer') ?></td>
				<td><?php echo $this->Tracking->statusLabel($statusHistory) ?></td>
				<td><?php echo $statusHistory['OrderStatusHistory']['comments'] ?></td>
				<td><?php echo $this->Tracking->checkmark($statusHistory['OrderStatusHistory']['customer_notified']) ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
	</div>
</div>
