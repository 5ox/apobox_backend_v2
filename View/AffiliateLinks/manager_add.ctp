<div class="affiliateLinks form">
	<div class="actions float-end">
		<?php echo $this->Html->link(__('List Affliate Links'),
			array(
				'action' => 'index'
			),
			array(
				'class' => 'btn btn-primary'
			)
		); ?>
	</div>
	<?php echo $this->Form->create('AffiliateLink'); ?>
	<fieldset>
			<legend><?php echo __('Manager Add Affiliate Link'); ?></legend>
		<?php
			echo $this->Form->input('title');
			echo $this->Form->input('url');
			echo $this->Form->input('enabled', [
				'default' => true,
			]);
		?>
	</fieldset>
	<?php echo $this->Form->button(__('Submit'), array(
		'type' => 'submit',
		'class' => 'btn btn-primary center-block',
	)); ?>
	<?php echo $this->Form->end(); ?>
</div>
