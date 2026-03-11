<?php $this->extend('view_base'); ?>
<?php $this->append('extra-labels'); ?>
	<?php echo $this->Tracking->customRequestLabel($order); ?>
	<?php echo $this->Tracking->repackLabel($order); ?>
<?php $this->end(); ?>
<?php $this->append('action-area'); ?>
	<div class="row">
		<div class="col-sm-8 offset-sm-4">
		<?php if($order['Order']['orders_status'] == 2) {
			echo $this->Html->link(
				'Pay for Order',
				array(
					'controller' => 'orders',
					'action' => 'pay_manually',
					'id' => $order['Order']['orders_id'],
				),
				array('class' => 'btn btn-success btn-block')
			);
		} ?>
		</div>
	</div>
<?php $this->end() ?>
