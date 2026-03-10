<?php if (empty($results)): ?>
	<?php echo $this->element('search/no_results', array('type' => 'customers')); ?>
<?php else: ?>

<table class="table">
	<thead>
			<tr>
			<th><?php echo $this->Paginator->sort('billing_id', 'Billing ID'); ?></th>
			<th><?php echo $this->Paginator->sort('customers_firstname', 'First Name'); ?></th>
			<th><?php echo $this->Paginator->sort('customers_lastname', 'Last Name'); ?></th>
			<th><?php echo $this->Paginator->sort('customers_email_address', 'Email'); ?></th>
			<th><?php echo $this->Paginator->sort('backup_email_address', 'Backup Email'); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($results as $result): ?>
		<tr>
			<td>
				<?php echo $this->Html->link(h($result['Customer']['billing_id']), array(
					'controller' => 'customers',
					'action' => 'view',
					'id' => $result['Customer']['customers_id'],
				)) ?>
				<?php if (!$result['Customer']['is_active']): ?>
					<span class="label label-danger">CLOSED</span>
				<?php endif; ?>
				<?php if ($result['Customer']['customers_default_address_id'] == null): ?>
					<span class="label label-warning">INCOMPLETE</span>
				<?php endif; ?>
			</td>
			<td><?php echo h($result['Customer']['customers_firstname']) ?></td>
			<td><?php echo h($result['Customer']['customers_lastname']) ?></td>
			<td>
				<?php echo $this->Html->link(
					h($result['Customer']['customers_email_address']),
					'mailto:' . h($result['Customer']['customers_email_address']));
				?>
			</td>
			<td>
				<?php echo $this->Html->link(
					h($result['Customer']['backup_email_address']),
					'mailto:' . h($result['Customer']['backup_email_address']));
				?>
			</td>
			<td class="actions">
				<?php echo $this->Html->link(
					'<i class="fa fa-eye"></i>',
					array(
						'controller' => 'customers',
						'action' => 'view',
						'id' => $result['Customer']['customers_id']
					), array(
						'escape' => false,
						'title' => 'View Customer',
				)); ?>
				<?php echo $this->Html->link(
					'<i class="fa fa-envelope"></i>',
					'mailto:' . $result['Customer']['customers_email_address'],
					array('escape' => false)
				); ?>
				<?php echo $this->Html->link(
					'<i class="fa fa-clock-o"></i>',
					array(
						'controller' => 'customers',
						'action' => 'recent',
						'id' => $result['Customer']['customers_id']
					), array(
						'escape' => false,
						'title' => 'Recent Orders',
				)); ?>
				<?php if (!empty($isManager)) {
					echo $this->Html->link(
						'<i class="fa fa-cart-plus"></i>',
						array(
							'controller' => 'custom_package_requests',
							'action' => 'add',
							'customerId' => $result['Customer']['customers_id'],
						),
						array(
							'escape' => false,
							'title' => 'New Custom Package Request',
						)
					);
				} ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?php endif; ?>
