<?php if (empty($results)): ?>
<p>No orders were found.</p>
<?php else: ?>
<?php
$showComments = true;
$comments = Hash::extract($results, '{n}.Order.comments');
array_walk($comments, function(&$comment) {
	$comment = (string)$comment;
});
$comments = array_flip($comments);
unset($comments['NULL']);
if (empty($comments)) {
	$showComments = false;
}
?>
<h2><?php echo $this->Tracking->statusLabel($results[0]); ?></h2>
<table class="table">
	<thead>
		<tr>
			<th>Order #</th>
			<th>To:</th>
			<th>Dimensions</th>
			<th>Weight</th>
			<th>Class</th>
			<th>Inbound</th>
			<th>Outbound</th>
			<?php if ($showComments): ?>
			<th><i class="fa fa-comment"></i></th>
			<?php endif; ?>
			<th>Modified</th>
			<th>Processed</th>
			<th>Total</th>
			<th class="actions"></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($results as $result): ?>
		<tr>
		<td>
			<?php
			echo $this->Html->link(h($result['Order']['orders_id']),array(
				'controller' => 'orders',
				'action' => 'view',
				'id' => h($result['Order']['orders_id'])
			));
			?>
		</td>
		<td
			data-container="body"
			data-bs-toggle="popover"
			data-placement="bottom"
			data-html="true"
			data-content="<?php echo h($this->Tracking->deliveryAddress($result)); ?>"
		>
			<?php echo $this->Html->link($result['Customer']['billing_id'], array(
				'controller' => 'customers',
				'action' => 'view',
				'id' => $result['Customer']['customers_id'],
			)); ?>
		</td>
		<td><?php echo $this->Tracking->packageDimensions($result); ?></td>
		<td><?php echo $this->Tracking->weight($result); ?></td>
		<td><?php echo h($result['Order']['mail_class']); ?></td>
		<td
			data-container="body"
			data-bs-toggle="popover"
			data-placement="bottom"
			data-html="true"
			data-content="<?php echo h($result['Order']['inbound_tracking']); ?>"
		>
			<?php echo $this->Tracking->inboundTrackingLink(
				$result,
				$this->Text->tail(h($result['Order']['inbound_tracking']), 10)
			); ?>
		</td>
		<td
			data-container="body"
			data-bs-toggle="popover"
			data-placement="bottom"
			data-html="true"
			data-content="<?php echo h($result['Order']['usps_track_num']); ?>"
		>
			<?php echo $this->Tracking->outbound(
				$this->Text->tail(h($result['Order']['usps_track_num']), 10),
				$result
			); ?>
		</td>
		<?php if ($showComments): ?>
		<td>
			<?php if (!empty($result['Order']['comments']) && $result['Order']['comments'] != 'NULL'): ?>
			<i
				class="fa fa-comment"
				data-container="body"
				data-bs-toggle="popover"
				data-placement="bottom"
				data-html="true"
				data-content="<?php echo h($result['Order']['comments']) ?>"
			></i>
			<?php endif; ?>
		</td>
		<?php endif; ?>
		<td><?php echo $this->Tracking->lastUpdated($result); ?></td>
		<td><?php echo $this->Tracking->datePurchased($result); ?></td>
		<td><?php echo $this->Tracking->orderTotal($result); ?></td>
		<td class="actions">
			<?php echo $this->Html->link(
				'<i class="fa fa-envelope"></i>',
				'mailto:' . $result['Customer']['customers_email_address'],
				array(
					'title' => 'Email Customer',
					'escape' => false,
				)
			); ?>
			<?php if ($result['OrderStatus']['orders_status_id'] == 4 && !empty($isManager)): ?>
				<?php echo $this->Form->postLink(
					'<i class="fa fa-truck"></i>',
					array(
						'controller' => 'orders',
						'action' => 'mark_as_shipped',
						'id' => $result['Order']['orders_id']
					),
					array(
					'title' => 'Mark as Shipped',
						'escape' => false,
					)
				); ?>
			</td>
			<?php endif; ?>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?php endif; ?>
