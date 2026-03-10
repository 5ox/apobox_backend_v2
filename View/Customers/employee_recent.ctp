<h2><?php echo $customerName ?>'s Recent Orders</h2>

<table class="table">
	<thead>
		<tr>
			<th><?php echo $this->Paginator->sort('orders_id', 'Order #'); ?></th>
			<th><?php echo $this->Paginator->sort('orders_status', 'Status'); ?></th>
			<th><?php echo $this->Paginator->sort('delivery_address'); ?></th>
			<th><?php echo $this->Paginator->sort('dimensions'); ?></th>
			<th><?php echo $this->Paginator->sort('weight_oz'); ?></th>
			<th><?php echo $this->Paginator->sort('mail_class'); ?></th>
			<th><?php echo $this->Paginator->sort('inbound_tracking'); ?></th>
			<th><?php echo $this->Paginator->sort('usps_track_num_in'); ?></th>
			<th><?php echo $this->Paginator->sort('comments'); ?></th>
			<th><?php echo $this->Paginator->sort('last_modified'); ?></th>
			<th><?php echo $this->Paginator->sort('date_purchased'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($orders as $order): ?>
	<tr>
		<td>
			<?php
			echo $this->Html->link(h($order['Order']['orders_id']),array(
				'controller' => 'orders',
				'action' => 'view',
				'id' => h($order['Order']['orders_id'])
			));
			?>
			&nbsp;
		</td>
		<td><?php echo h($order['OrderStatus']['orders_status_name']); ?></td>
		<td><?php echo h($order['Order']['delivery_address']); ?>&nbsp;</td>
		<td><?php echo h($order['Order']['dimensions']); ?>&nbsp;</td>
		<td><?php echo h($order['Order']['weight_oz']); ?>&nbsp;</td>
		<td><?php echo h($order['Order']['mail_class']); ?>&nbsp;</td>
		<td><?php echo h($order['Order']['inbound_tracking']); ?>&nbsp;</td>
		<td><?php echo h($order['Order']['usps_track_num']); ?>&nbsp;</td>
		<td><?php echo h($order['Order']['comments']); ?>&nbsp;</td>
		<td><?php echo h($order['Order']['last_modified']); ?>&nbsp;</td>
		<td><?php echo h($order['Order']['date_purchased']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $order['Order']['orders_id']), array(), __('Are you sure you want to delete # %s?', $order['Order']['orders_id'])); ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>
