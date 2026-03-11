<?php $this->extend('view_base'); ?>
<?php $this->append('extra-labels'); ?>
	<?php echo $this->Tracking->oversizeRateLabel($order); ?>
	<?php echo $this->Tracking->balloonRateLabel($order); ?>
	<?php echo $this->Tracking->nonMachinableLabel($order); ?>
	<?php echo $this->Tracking->customRequestLabel($order); ?>
	<?php echo $this->Tracking->repackLabel($order); ?>
<?php $this->end(); ?>
<?php $this->append('action-area'); ?>
	<div class="row">
		<div class="col-sm-8 offset-sm-4">
			<?php echo $this->Form->create('Order', array(
				'url' => array(
					'controller' => 'orders',
					'action' => 'update_status',
					'id' => $order['Order']['orders_id'],
				),
				'inputDefaults' => array(
					'label' => false,
					'between' => '<div class="col-sm-12">',
					'after' => '</div>',
				),
			)); ?>
				<?php echo $this->Form->input('orders_status', array(
					'type' => 'select',
					'label' => array('class' => 'col-sm-3', 'text' => 'Status:'),
					'between' => '<div class="col-sm-9">',
					'selected' => $order['Order']['orders_status'],
				)); ?>
				<?php echo $this->Form->input('status_history_comments', array(
					'type' => 'textarea',
					'rows' => '2',
					'placeholder' => 'Comments',
				)); ?>
				<?php echo $this->Form->input('usps_track_num', array(
					'placeholder' => 'Outbound tracking number',
					'value' => $order['Order']['usps_track_num'],
					'style' => 'display: none',
					'required' => false,
				)); ?>
				<?php echo $this->Form->input('notify_customer', array(
					'type' => 'checkbox',
					'label' => 'Notify Customer',
					'value' => '1',
					'hiddenField' => false,
				)); ?>
				<?php echo $this->Form->button('Update Status', array('class' => 'btn btn-warning btn-block')); ?>
				<?php echo $this->Html->link(
					'Email Customer',
					'mailto:' . $order['Order']['customers_email_address'],
					array('class' => 'btn btn-primary btn-block')
				); ?>
				<?php echo $this->Html->link(
					'Go to Customer',
					array(
						'controller' => 'customers',
						'action' => 'view',
						'id' => $order['Order']['customers_id'],
					),
					array('class' => 'btn btn-primary btn-block')
				); ?>
				<?php echo $this->fetch('action-buttons'); ?>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
	<div class="xml">
		<textarea class="label-xml">
			<?php echo $xml; ?>
		</textarea>
	</div>
<?php $this->end(); ?>
<?php echo $this->element('js-import', ['js' => 'orders/admin_view_base']); ?>
