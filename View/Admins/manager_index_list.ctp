<div class="admins index">
	<div class="actions pull-right">
		<?php echo $this->Html->link(__('Add New Admin'),
			array(
				'action' => 'add'
			),
			array(
				'class' => 'btn btn-primary',
			)
		); ?>
	</div>
	<h2><?php echo __('Admins'); ?></h2>
	<table cellpadding="0" cellspacing="0" class="table">
		<thead>
			<tr>
				<th><?php echo $this->Paginator->sort('id'); ?></th>
				<th><?php echo $this->Paginator->sort('email'); ?></th>
				<th><?php echo $this->Paginator->sort('role'); ?></th>
				<th><?php echo $this->Paginator->sort('token'); ?></th>
				<th><?php echo $this->Paginator->sort('modified'); ?></th>
				<th class="actions"><?php echo __('Actions'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($admins as $admin): ?>
			<tr>
				<td><?php echo h($admin['Admin']['id']); ?>&nbsp;</td>
				<td><?php echo h($admin['Admin']['email']); ?>&nbsp;</td>
				<td><?php echo h($admin['Admin']['role']); ?>&nbsp;</td>
				<td><?php echo h($admin['Admin']['token']); ?>&nbsp;</td>
				<td><?php echo h($admin['Admin']['modified']); ?>&nbsp;</td>
				<td class="actions">
					<?php echo $this->Html->link(__('Edit'),
						array(
							'action' => 'edit',
							$admin['Admin']['id']
						),
						array(
							'class' => 'btn btn-primary',
						)
					); ?>
					<?php echo $this->Form->postLink(__('Delete'),
						array(
							'action' => 'delete',
							$admin['Admin']['id']
						),
						array(
							'class' => 'btn btn-danger',
						),
						__('Are you sure you want to delete # %s?', $admin['Admin']['id'])
					); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php echo $this->element('pagination'); ?>
</div>
