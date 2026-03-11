<?php if (!empty($isManager) || !empty($isEmployee)): ?>
	<?php if ($this->request->action === 'manager_add' || $this->request->action === 'employee_add'): ?>
		<?= $this->element('Orders/customer', ['customer' => $customer]); ?>
		<div class="row">
			<div class="col-md-12">
				<?= $this->Form->input('customers_id', [
					'type' => 'hidden',
					'value' => $customer['Customer']['customers_id'],
				]); ?>
			</div>
		</div>
	<?php endif; ?>
	<div class="row">
		<div class="col-md-12">
			<?= $this->Form->input('package_status', [
				'type' => 'select',
				'label' => [
					'text' => 'Package Status',
					'class' => 'col-sm-4 form-label',
				],
			]); ?>
			<?= $this->Form->input('orders_id', [
				'type' => 'text',
				'default' => 0,
				'label' => [
					'text' => 'Order ID',
					'class' => 'col-sm-4 form-label',
				],
			]); ?>
		</div>
	</div>
<?php endif; ?>
