<div class="affiliateLinks index">
	<div class="actions float-end">
		<?php echo $this->Html->link(__('Add New Affiliate Link'),
			array(
				'action' => 'add'
			),
			array(
				'class' => 'btn btn-primary',
			)
		); ?>
	</div>
	<h2><?php echo __('Affiliate Links'); ?></h2>
	<table class="table rable-responsive">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('title'); ?></th>
			<th><?php echo $this->Paginator->sort('url'); ?></th>
			<th><?php echo $this->Paginator->sort('enabled'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($affiliateLinks as $affiliateLink): ?>
	<tr>
		<td><?php echo h($affiliateLink['AffiliateLink']['id']); ?>&nbsp;</td>
		<td><?php echo h($affiliateLink['AffiliateLink']['title']); ?>&nbsp;</td>
		<td><?php echo h($affiliateLink['AffiliateLink']['url']); ?>&nbsp;</td>
		<td><?php echo $affiliateLink['AffiliateLink']['enabled'] == '1' ? __('Yes') : __('No'); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Edit'),
				array(
					'action' => 'edit',
					$affiliateLink['AffiliateLink']['id']
				),
				[
					'class' => 'btn btn-primary',
				]
			); ?>
			<?php echo $this->Form->postLink(__('Delete'),
				array(
					'action' => 'delete',
					$affiliateLink['AffiliateLink']['id']
				),
				array(
					'class' => 'btn btn-danger',
					'confirm' => __('Are you sure you want to delete # %s?', $affiliateLink['AffiliateLink']['id'])
				)
			); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
		'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<?= $this->Element('pagination') ?>
</div>
