<div class="orders form" id="ManagerAddForm">
	<?php echo $this->Form->create('Order'); ?>
	<h3>Add New Order</h3>
	<?php echo $this->element('Orders/customer'); ?>
	<?php echo $this->element('CustomPackageRequests/list', array(
		'customerId' => $customer['Customer']['customers_id']
	)); ?>
	<?php echo $this->element('Orders/form'); ?>
	<div class="row">
		<div class="offset-sm-2 col-sm-10">
			<?php echo $this->Form->button('Add Order', array(
				'class' => 'btn btn-primary float-end',
				'type' => 'submit',
				'id' => 'add-order',
			)); ?>
		</div>
	</div>
	<?php echo $this->Form->end(); ?>
</div>
<?php echo $this->element('js-import', ['js' => 'orders/manager_add']); ?>
