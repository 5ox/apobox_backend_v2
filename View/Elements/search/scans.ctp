<?php if (empty($results)): ?>
	<?php echo $this->element('search/no_results', array('type' => 'scans')); ?>
<?php else: ?>

<table class="table table-condensed table-striped">
	<thead>
		<tr>
			<th><?php echo $this->Paginator->sort('tracking_id'); ?></th>
			<th><?php echo $this->Paginator->sort('warehouse'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th><?php echo $this->Paginator->sort('comments'); ?></th>
			<th><?php echo $this->Paginator->sort('shipped'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
		</tr>
	<thead>
	<tbody>
		<?php foreach ($results as $result): ?>
		<tr>
			<td><?php echo h($result['Tracking']['tracking_id']); ?>&nbsp;</td>
			<td><?php echo h($result['Tracking']['warehouse']); ?>&nbsp;</td>
			<td><?php echo h($result['Tracking']['created']); ?>&nbsp;</td>
			<td><?php echo h($result['Tracking']['modified']); ?>&nbsp;</td>
			<td><?php echo h($result['Tracking']['comments']); ?>&nbsp;</td>
			<td><?php echo h($result['Tracking']['shipped']); ?>&nbsp;</td>
			<td class="actions">
				<?php if (!empty($isManager)): ?>
					<?php echo $this->Html->link('Edit', array(
						'controller' => 'trackings',
						'action' => 'edit',
						$result['Tracking']['tracking_id'],
					)); ?>
					|
					<?php echo $this->Form->postLink(
						__('Delete'),
						array('action' => 'delete', $result['Tracking']['tracking_id']),
						array(), __('Are you sure you want to delete # %s?', $result['Tracking']['tracking_id'])
					); ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php endif; ?>
