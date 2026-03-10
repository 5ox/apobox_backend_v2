<div class="row">
	<div class="col-md-12">
		<fieldset id="CustomPackageRequest">
			<?php echo $this->element('Orders/manager_form_parts'); ?>
			<div class="row">
				<div class="col-md-6">
					<?php echo $this->Form->input('carrier', array(
						'type' => 'select',
						'label' => array(
							'text' => 'Carrier and Tracking Number',
							'class' => 'col-md-8 control-label',
						),
						'empty' => 'Carrier',
						'options' => array(
							'ups' => 'UPS',
							'fedex' => 'FedEx',
							'usps' => 'USPS',
							'dhl' => 'DHL',
							'amazon' => 'Amazon',
							'none' => 'None',
						),
						'between' => '<div class="col-sm-4">',
						'after' => '</div>',
					)); ?>
				</div>
				<div class="col-md-6">
					<?php echo $this->Form->input('inbound_tracking_number', array(
						'type' => 'text',
						'label' => array(
							'text' => 'Inbound Tracking Number',
							'class' => 'control-label sr-only',
						),
						'between' => '<div class="col-sm-8">',
						'after' => '</div>',
						'autofocus',
					)); ?>
					<?php echo $this->Form->input('CustomPackageRequest.custom_orders_id', array(
						'type' => 'hidden',
						'value' => null,
					)); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<label class="col-sm-4 control-label">
							Dimensions
						</label>
						<div class="col-sm-6">
							<div class="row">
								<div class="col-sm-4">
								<?php echo $this->Form->input('length', array(
									'type' => 'text',
									'placeholder' => 'Length',
									'label' => array(
										'class' => 'control-label sr-only',
									),
									'between' => '<div class="col-sm-12"><div class="input-group">',
									'after' => '<div class="input-group-addon">in.</div></div></div>',
								)); ?>
								</div>
								<div class="col-sm-4">
								<?php echo $this->Form->input('width', array(
									'type' => 'text',
									'placeholder' => 'Width',
									'label' => array(
										'class' => 'control-label sr-only',
									),
									'between' => '<div class="col-sm-12"><div class="input-group">',
									'after' => '<div class="input-group-addon">in.</div></div></div>',
								)); ?>
								</div>
								<div class="col-sm-4">
								<?php echo $this->Form->input('depth', array(
									'type' => 'text',
									'placeholder' => 'Depth',
									'label' => array(
										'class' => 'control-label sr-only',
									),
									'between' => '<div class="col-sm-12"><div class="input-group">',
									'after' => '<div class="input-group-addon">in.</div></div></div>',
								)); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<label class="col-sm-4 control-label">
							Weight
						</label>
						<div class="col-sm-6">
							<div class="row">
								<div class="col-sm-4">
									<?php echo $this->Form->input('weight_lb', array(
										'type' => 'text',
										'placeholder' => 'Pounds',
										'label' => array(
											'class' => 'control-label sr-only',
										),
										'between' => '<div class="col-sm-12"><div class="input-group">',
										'after' => '<div class="input-group-addon">lb</div></div></div>',
									)); ?>
								</div>
								<div class="col-sm-4">
									<?php echo $this->Form->input('weight_oz', array(
										'type' => 'text',
										'placeholder' => 'Ounces',
										'label' => array(
											'class' => 'control-label sr-only',
										),
										'between' => '<div class="col-sm-12"><div class="input-group">',
										'after' => '<div class="input-group-addon">oz</div></div></div>',
									)); ?>
								</div>
								<div class="col-sm-2">
									<?php echo $this->Html->link('Read', '#', array(
										'id' => 'ReadScale',
										'class' => 'btn',
									)); ?>
								</div>
								<div class="col-sm-2">
									<?php echo $this->Html->link('On', '#', array(
										'id' => 'ScaleToggle',
										'class' => 'btn btn-success',
									)); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?php echo $this->Form->input('customs_description', array(
						'type' => 'text',
						'label' => array(
							'text' => 'Customs Description',
							'class' => 'col-sm-4 control-label',
						),
						'value' => Configure::read('Orders.defaultCustomsDescription'),
					)); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?php echo $this->Form->input('customers_address_id', array(
						'type' => 'select',
						'label' => array(
							'text' => 'Customer Address',
							'class' => 'col-sm-4 control-label',
						),
						'options' => $customersAddresses,
						'value' => $customer['Customer']['customers_default_address_id'],
					)); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?php echo $this->Form->input('delivery_address_id', array(
						'type' => 'select',
						'label' => array(
							'text' => 'Delivery Address',
							'class' => 'col-sm-4 control-label',
						),
						'options' => $customersAddresses,
						'value' => $customer['Customer']['customers_shipping_address_id'],
					)); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?php echo $this->Form->input('billing_address_id', array(
						'type' => 'select',
						'label' => array(
							'text' => 'Billing Address',
							'class' => 'col-sm-4 control-label',
						),
						'options' => $customersAddresses,
						'value' => $customer['Customer']['customers_default_address_id'],
					)); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?php echo $this->Form->input('mail_class', array(
						'type' => 'select',
						'default' => 'priority',
						'label' => array(
							'text' => 'Mail Class',
							'class' => 'col-sm-4 control-label',
						),
						'options' => array(
							'priority' => 'Priority',
							'parcel' => 'Parcel Post',
							'fedex' => 'FedEx Ground',
						)
					)); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?php echo $this->Form->input('package_type', array(
						'type' => 'hidden',
						'value' => 'rectparcel',
					)); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?php echo $this->Form->input('insurance_coverage', array(
						'label' => array(
							'text' => 'Insurance Coverage',
							'class' => 'col-sm-4 control-label',
						),
					)); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?php echo $this->Form->input('comments', array(
						'type' => 'textarea',
						'label' => array(
							'text' => 'Comments',
							'class' => 'col-sm-4 control-label',
						),
					)); ?>
				</div>
			</div>
		</fieldset>
	</div>
</div>
