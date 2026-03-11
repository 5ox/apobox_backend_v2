<?php if (empty($results)): ?>
	<?php echo $this->element('search/no_results', array('type' => 'orders')); ?>
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

$customerId = isset($customerId) ? $customerId : null;
?>
<table class="table">
	<thead>
		<tr>
			<th><?php echo $this->Paginator->sort('orders_id', 'Order #'); ?></th>
			<th><?php echo $this->Paginator->sort('Customer.billing_id', 'To:'); ?></th>
			<th><?php echo $this->Paginator->sort('orders_status', 'Status'); ?></th>
			<th><?php echo $this->Paginator->sort('dimensions'); ?></th>
			<th><?php echo $this->Paginator->sort('weight_oz', 'Weight'); ?></th>
			<th><?php echo $this->Paginator->sort('mail_class', 'Class'); ?></th>
			<th><?php echo $this->Paginator->sort('inbound_tracking', 'Inbound'); ?></th>
			<th><?php echo $this->Paginator->sort('usps_track_num', 'Outbound'); ?></th>
			<?php if ($showComments): ?>
			<th><?php echo $this->Paginator->sort('comments', '<i class="fa fa-comment"></i>', array('escape' => false)); ?></th>
			<?php endif; ?>
			<th><?php echo $this->Paginator->sort('last_modified', 'Modified'); ?></th>
			<th><?php echo $this->Paginator->sort('date_purchased', 'Processed'); ?></th>
			<th><?php echo $this->Paginator->sort('OrderTotal.value', 'Total'); ?></th>
			<?php if (isset($userIsManager) && $userIsManager) : ?>
				<th class="actions"></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($results as $result): ?>
		<tr>
		<td>
			<?php echo $this->Html->link(
				h($result['Order']['orders_id']) . $this->Tracking->customRequestLabel($result, $customRequests),
				[
					'controller' => 'orders',
					'action' => 'view',
					'id' => h($result['Order']['orders_id'])
				],
				[
					'escapeTitle' => false,
				]
			); ?>
		</td>
		<td
			data-container="body"
			data-bs-toggle="popover"
			data-placement="bottom"
			data-html="true"
			data-content="<?php echo h($this->Tracking->deliveryAddress($result)); ?>"
		>
			<?php if (isset($userIsManager) && $userIsManager) : ?>
				<?php echo $this->Html->link($result['Customer']['billing_id'], array(
					'controller' => 'customers',
					'action' => 'view',
					'id' => $result['Customer']['customers_id'],
				)); ?>
			<?php else: ?>
				<?php echo $result['Customer']['billing_id']; ?>
			<?php endif; ?>
		</td>
		<td><?php echo $this->Tracking->statusLabel($result); ?></td>
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
		<?php if (isset($userIsManager) && $userIsManager) : ?>
			<td class="actions">
				<?php echo $this->Html->link(
					'<i class="fa fa-envelope"></i>',
					'mailto:' . $result['Customer']['customers_email_address'],
					array(
						'title' => 'Email Customer',
						'escape' => false,
					)
				); ?>
				<?php echo $this->Form->postLink('<i class="fa fa-times"></i>',
					array(
						'controller' => 'orders',
						'action' => 'delete',
						$result['Order']['orders_id'],
						$customerId
					),
					array(
						'title' => 'Delete Order',
						'escape' => false,
					),
					__('Are you sure you want to delete order %s?', $result['Order']['orders_id'])
				); ?>
				<?php if ($result['OrderStatus']['orders_status_id'] == 4): ?>
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
			<?php endif; ?>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?php endif; ?>
