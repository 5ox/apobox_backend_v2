<div class="orders form">
<?php echo $this->Form->create('Order'); ?>
	<fieldset>
		<legend><?php echo __('Manager Edit Order'); ?></legend>
	<?php
		echo $this->Form->input('orders_id');
		echo $this->Form->input('customers_id');
		echo $this->Form->input('customers_name');
		echo $this->Form->input('customers_company');
		echo $this->Form->input('customers_street_address');
		echo $this->Form->input('customers_suburb');
		echo $this->Form->input('customers_city');
		echo $this->Form->input('customers_postcode');
		echo $this->Form->input('customers_state');
		echo $this->Form->input('customers_country');
		echo $this->Form->input('customers_telephone');
		echo $this->Form->input('customers_email_address');
		echo $this->Form->input('customers_address_format_id');
		echo $this->Form->input('delivery_name');
		echo $this->Form->input('delivery_company');
		echo $this->Form->input('delivery_street_address');
		echo $this->Form->input('delivery_suburb');
		echo $this->Form->input('delivery_city');
		echo $this->Form->input('delivery_postcode');
		echo $this->Form->input('delivery_state');
		echo $this->Form->input('delivery_country');
		echo $this->Form->input('delivery_address_format_id');
		echo $this->Form->input('billing_name');
		echo $this->Form->input('billing_company');
		echo $this->Form->input('billing_street_address');
		echo $this->Form->input('billing_suburb');
		echo $this->Form->input('billing_city');
		echo $this->Form->input('billing_postcode');
		echo $this->Form->input('billing_state');
		echo $this->Form->input('billing_country');
		echo $this->Form->input('billing_address_format_id');
		echo $this->Form->input('payment_method');
		echo $this->Form->input('cc_type');
		echo $this->Form->input('cc_owner');
		echo $this->Form->input('cc_number');
		echo $this->Form->input('cc_expires');
		echo $this->Form->input('comments');
		echo $this->Form->input('last_modified');
		echo $this->Form->input('date_purchased');
		echo $this->Form->input('turnaround_sec');
		echo $this->Form->input('orders_status');
		echo $this->Form->input('orders_date_finished');
		echo $this->Form->input('amazon_track_num');
		echo $this->Form->input('ups_track_num');
		echo $this->Form->input('usps_track_num');
		echo $this->Form->input('usps_track_num_in');
		echo $this->Form->input('fedex_track_num');
		echo $this->Form->input('fedex_freight_track_num');
		echo $this->Form->input('dhl_track_num');
		echo $this->Form->input('currency');
		echo $this->Form->input('currency_value');
		echo $this->Form->input('shipping_tax');
		echo $this->Form->input('billing_status');
		echo $this->Form->input('qbi_imported');
		echo $this->Form->input('width');
		echo $this->Form->input('length');
		echo $this->Form->input('depth');
		echo $this->Form->input('weight_oz');
		echo $this->Form->input('mail_class');
		echo $this->Form->input('package_type');
		echo $this->Form->input('NonMachinable');
		echo $this->Form->input('OversizeRate');
		echo $this->Form->input('BalloonRate');
		echo $this->Form->input('package_flow');
		echo $this->Form->input('shipped_from');
		echo $this->Form->input('insurance_coverage');
		echo $this->Form->input('warehouse');
		echo $this->Form->input('postage_id');
		echo $this->Form->input('trans_id');
		echo $this->Form->input('moved_to_invoice');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Order.orders_id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Order.orders_id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Orders'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Customer'), array('controller' => 'customers', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Order Statuses'), array('controller' => 'order_statuses', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Order Status'), array('controller' => 'order_statuses', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Order Status Histories'), array('controller' => 'order_status_histories', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Order Status History'), array('controller' => 'order_status_histories', 'action' => 'add')); ?> </li>
	</ul>
</div>
