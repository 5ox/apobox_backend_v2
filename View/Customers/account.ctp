<div class="customers account">
	<div class="row">
		<div class="col-sm-12">
			<?php if (!empty($requests)): ?>
			<h3>My Pending Requests</h3>
			<?php echo $this->element('CustomPackageRequests/list'); ?>
			<?php endif; ?>

			<?php if (!empty($awaitingPayments)): ?>
				<h3>My Orders Awaiting Payment</h3>
				<div class="row">
					<div class="col-md-6">
					<p>
						The orders listed below were unable to be automatically paid for with the payment information
						we had on file when the order was received. These orders will need to be paid manually before
						they can be shipped.
					<p>
					</div>
					<div class="col-md-6">
					</div>
				</div>
				<div class="table-responsive">
					<table class="table table-condensed">
						<thead>
							<tr>
								<th></th>
								<th>Order Number</th>
								<th>Total</th>
								<th>Inbound Tracking #</th>
								<th>Dimensions</th>
								<th>Weight</th>
								<th>Date Received</th>
							</tr>
						<thead>
						<tbody>
							<?php foreach ($awaitingPayments as $order): ?>
							<tr>
								<td>
									<?php echo $this->Html->link(
										'Pay',
										array(
											'controller' => 'orders',
											'action' => 'pay_manually',
											'id' => $order['Order']['orders_id']
										),
										array('class' => 'btn btn-primary btn-sm btn-block')
									); ?>
								</td>
								<td>
									<?php
									echo $this->Html->link($order['Order']['orders_id'], array(
										'controller' => 'orders',
										'action' => 'view',
										'id' => $order['Order']['orders_id']
									));
									?>
								</td>
								<td><?php echo $this->Tracking->orderTotal($order); ?></td>
								<td><?php echo $this->Tracking->inbound($order); ?></td>
								<td><?php echo $order['Order']['dimensions']; ?></td>
								<td><?php echo $this->Tracking->weight($order, 'lb'); ?></td>
								<td>
									<?php echo $this->Tracking->datePurchased($order, 'customer') ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

			<h3>
				My Orders
				<?php if ($showViewAllLink): ?>
				<small>
				<?php echo $this->Html->link(
					'View All',
					array(
						'controller' => 'orders',
						'action' => 'index',
					)
				); ?>
				</small>
				<?php endif; ?>
			</h3>
			<?php if (empty($orders)): ?>
			<p>You have no orders at this time.</p>
			<?php else: ?>
			<div class="table-responsive">
				<table class="table table-condensed table-striped">
					<thead>
						<tr>
							<th>Order Number</th>
							<th>Outbound Tracking #</th>
							<th>Inbound Tracking #</th>
							<th>Order Status</th>
							<th>Postage Class</th>
							<th>Date Shipped</th>
							<th>Date Processed</th>
							<th>Order Total</th>
						</tr>
					<thead>
					<tbody>
						<?php foreach ($orders as $order): ?>
						<tr>
							<td>
								<?php echo $this->Html->link(
									$order['Order']['orders_id']
										.	$this->Tracking->customRequestLabel($order),
									[
										'controller' => 'orders',
										'action' => 'view',
										'id' => $order['Order']['orders_id']
									],
									['escapeTitle' => false]
								); ?>
							</td>
							<td><?php echo $this->Tracking->outbound($order); ?></td>
							<td><?php echo $this->Tracking->inbound($order); ?></td>
							<td><?php echo $this->Tracking->statusLabel($order); ?></td>
							<td><?php echo $order['Order']['mail_class'] ?></td>
							<td><?php echo $this->Tracking->dateShipped($order, 'customer') ?></td>
							<td><?php echo $this->Tracking->datePurchased($order, 'customer') ?></td>
							<td><?php echo $this->Tracking->orderTotal($order); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div role="tabpanel">
				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation" class="active">
						<a href="#account" aria-controls="account" role="tab" data-toggle="tab">My Account</a>
					</li>
					<li role="presentation">
						<a href="#my-info" aria-controls="home" role="tab" data-toggle="tab">My Info</a>
					</li>
					<li role="presentation">
						<a href="#authorized_names" aria-controls="authorized_names" role="tab" data-toggle="tab">Authorized Names</a>
					</li>
					<li role="presentation">
						<a href="#addresses" aria-controls="addresses" role="tab" data-toggle="tab">
							Addresses
						</a>
					</li>
					<li role="presentation">
						<a href="#payment" aria-controls="home" role="tab" data-toggle="tab">Payment</a>
					</li>
					<li role="presentation">
						<a href="#shipping" aria-controls="home" role="tab" data-toggle="tab">Shipping</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="account">
			<div class="row">
				<div class="col-sm-4">
					<h3>You Ship Packages Here:</h3>
					<div class="well well-small well-address">
						<address class="lead">
							<?php echo $this->Tracking->apoBoxAddress($customer); ?><br>
						</address>
					</div>
				</div>
				<div class="col-sm-4">
					<h3>We Forward Them Here:</h3>
					<div class="well well-small well-address">
						<?php echo $this->element('Addresses/address', [
							'address' => $customer['ShippingAddress'],
							'hideBase' => true,
						]); ?>
					</div>
				</div>
				<div class="col-sm-4">
				</div>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane" id="my-info">
			<div class="row">
				<div class="col-sm-7">
					<h3>
						My Information
						<small>
							<?php echo $this->Html->link(__('Edit'), array(
								'action' => 'edit_partial',
								'partial' => 'my_info',
							)); ?>
						</small>
					</h3>
					<dl class="dl-horizontal">
						<dt><?php echo __('Billing ID'); ?></dt>
						<dd>
							<?php echo h($customer['Customer']['billing_id']); ?>
							&nbsp;
						</dd>
						<dt><?php echo __('First Name'); ?></dt>
						<dd>
							<?php echo h($customer['Customer']['customers_firstname']); ?>
							&nbsp;
						</dd>
						<dt><?php echo __('Last Name'); ?></dt>
						<dd>
							<?php echo h($customer['Customer']['customers_lastname']); ?>
							&nbsp;
						</dd>
						<dt><?php echo __('Email Address'); ?></dt>
						<dd>
							<?php echo h($customer['Customer']['customers_email_address']); ?>
							&nbsp;
						</dd>
						<dt><?php echo __('Backup Email'); ?></dt>
						<dd>
							<?php echo h($customer['Customer']['backup_email_address']); ?>
							&nbsp;
						</dd>
						<dt><?php echo __('Telephone'); ?></dt>
						<dd>
							<?php echo h($customer['Customer']['customers_telephone']); ?>
							&nbsp;
						</dd>
						<dt><?php echo __('Cell Phone'); ?></dt>
						<dd>
							<?php echo h($customer['Customer']['customers_fax']); ?>
							&nbsp;
						</dd>
						<dt><?php echo __('Password'); ?></dt>
						<dd>
							<?php echo $this->Html->link(
								'Change',
								array('controller' => 'customers', 'action' => 'change_password')
							); ?>
						</dd>
					</dl>
				</div>
				<?php echo $this->element('Customers/close_account_email'); ?>
			</div>

			<div class="row">
				<div class="col-sm-12">
					<?php echo $this->Html->link('Close Account',
						array(
							'controller' => 'customers',
							'action' => 'confirm_close',
							'customerId' => $customer['Customer']['customers_id'],
							'hash' => sha1(date('Y-m-d') . $customer['Customer']['customers_id']),
						),
						array(
							'class' => 'btn btn-sm btn-danger btn-close',
						)
					); ?>
				</div>
			</div>

		</div>





		<div role="tabpanel" class="tab-pane" id="authorized_names">
			<div class="row">
				<div class="col-sm-12">
					<h3>
						Authorized Names
						<small>
							<?php echo $this->Html->link(__('Add'), array(
								'controller' => 'authorized_names',
								'action' => 'add'
							)); ?>
						</small>
					</h3>
					<?php if (!empty($customer['AuthorizedName'])): ?>
						<div class="table-responsive">
							<table class="table table-condensed table-striped">
								<thead>
									<tr>
										<th><?php echo __('Name'); ?></th>
										<th><?php echo __('Actions'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($customer['AuthorizedName'] as $name): ?>
										<tr id="authorized-name-<?php echo $name['authorized_names_id']; ?>">
											<td>
												<?php echo $name['authorized_firstname']; ?>
												<?php echo $name['authorized_lastname']; ?>
											</td>
											<td>
												<?php echo $this->Html->link(__('Edit'), array(
													'controller' => 'authorized_names',
													'action' => 'edit',
													'id' => $name['authorized_names_id']
												)); ?> |
												<?php echo $this->Html->link(
													__('Delete'),
													array(
														'controller' => 'authorized_names',
														'action' => 'delete',
														'id' => $name['authorized_names_id']
													),
													array(
														'confirm' => __('Are you sure you want to delete %s %s?',
															$name['authorized_firstname'],
															$name['authorized_lastname']
														)
													)
												); ?>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>

					<?php else: ?>
						<p>You haven't created any authorized names.</p>
						<p>
							<?php
								echo $this->Html->link(__('New Authorized Name'), array(
									'controller' => 'authorized_names',
									'action' => 'add'
								));
							?>
						</p>
					<?php endif; ?>

				</div>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane" id="addresses">
			<div class="row">
				<div class="col-sm-6">
					<?php echo $this->element('Addresses/address-box', [
						'address' => $customer['DefaultAddress'],
						'label' => 'Billing Address',
						'tooltip' => 'defaultAddress',
					]); ?>
				</div>
				<div class="col-sm-6">
					<?php echo $this->element('Addresses/address-box', [
						'address' => $customer['ShippingAddress'],
						'label' => 'Shipping Address',
						'tooltip' => 'shippingAddress',
					]); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<?php echo $this->element('Addresses/address-box', [
						'address' => $customer['EmergencyAddress'],
						'label' => 'Backup Shipping Address',
						'tooltip' => 'emergencyAddress',
					]); ?>
				</div>
				<div class="col-sm-6">
					<h3>All Addresses</h3>
					<address>
						<?php if (!empty($customer['Address'])): ?>
							<ol>
							<?php foreach ($customer['Address'] as $address): ?>
								<li>
									<?php
										echo $this->Html->link($address['full'], array(
											'controller' => 'addresses',
											'action' => 'edit',
											'id' => $address['address_book_id'],
										));
									?>
								</li>
							<?php endforeach; ?>
							</ol>
							<?php
								echo $this->Html->link(__('New Address'), array(
									'controller' => 'addresses',
									'action' => 'add'
								));
							?>
						<?php endif; ?>
					</address>
				</div>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane" id="payment">
			<div class="col-sm-12">
				<h3>
					Payment Information
					<small>
						<?php echo $this->Html->link(__('Update'), array(
							'action' => 'edit_partial',
							'partial' => 'payment_info',
						)); ?>
					</small>
				</h3>
				<dl class="dl-horizontal">
					<dt><?php echo __('Name on Card'); ?></dt>
					<dd>
						<?php echo h($customer['Customer']['cc_firstname']); ?>
						<?php echo h($customer['Customer']['cc_lastname']); ?>
						&nbsp;
					</dd>
					<dt><?php echo __('Card Number'); ?></dt>
					<dd>
						<?php echo h($customer['Customer']['cc_number']); ?>
						&nbsp;
					</dd>
					<dt><?php echo __('Expires'); ?></dt>
					<dd>
						<?php echo h($customer['Customer']['cc_expires_month']); ?>
						/&nbsp;20<?php echo h($customer['Customer']['cc_expires_year']); ?>
						&nbsp;
					</dd>
				</dl>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane" id="shipping">
			<div class="col-sm-12">
				<h3>
					Shipping
					<small>
						<?php echo $this->Html->link(__('Edit'), array(
							'action' => 'edit_partial',
							'partial' => 'shipping',
						)); ?>
					</small>
				</h3>
				<dl class="dl-horizontal">
					<dt><?php echo __('Insurance Amount'); ?></dt>
					<dd>
						<?php echo h($this->Number->currency($customer['Customer']['insurance_amount'])); ?>
						&nbsp;
					</dd>
					<dt><?php echo __('Insurance Fee'); ?></dt>
					<dd>
						<?php echo h($this->Number->currency($insuranceFee)); ?>
						&nbsp;
					</dd>
					<dt><?php echo __('Default Postal Type'); ?></dt>
					<dd>
						<?php echo Configure::read('PostalClasses.' . $customer['Customer']['default_postal_type']); ?>
						&nbsp;
					</dd>
				</dl>
			</div>
		</div>
	</div> <!-- end tab-content -->
</div>
<?php echo $this->element('js-import', ['js' => 'customers/account']); ?>
