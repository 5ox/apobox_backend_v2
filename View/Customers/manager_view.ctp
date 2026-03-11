<h1 class="page-header">
	<?php echo $customer['Customer']['name']?>'s Account
	<?php if (!$customer['Customer']['is_active']): ?>
		<span class="badge bg-danger">CLOSED:</span>
		<small><?php echo $closed; ?></small>
	<?php endif; ?>
	<?php if ($partialSignup): ?>
		<span class="badge text-bg-warning">INCOMPLETE</span>
	<?php endif; ?>
	<code class="float-end">
		<?php echo $customer['Customer']['billing_id'] ?>
	</code>
</h1>

<div class="row">
	<div class="col-md-4">
		<h2>
			Customer Information
			<?php echo $this->element('Customers/manager_link', [
				'text' => 'Update',
				'action' => 'edit_contact_info',
			]); ?>
		</h2>
		<dl class="row">
			<dt>First Name</dt><dd><?php echo $customer['Customer']['customers_firstname'] ?></dd>
			<dt>Last Name</dt><dd><?php echo $customer['Customer']['customers_lastname'] ?></dd>
			<dt>Email</dt><dd><?php echo $customer['Customer']['customers_email_address'] ?></dd>
			<dt>Backup Email</dt><dd><?php echo $customer['Customer']['backup_email_address'] ?></dd>
			<dt>Phone #</dt><dd><?php echo $customer['Customer']['customers_telephone'] ?></dd>
			<dt>Cell Phone #</dt><dd><?php echo $customer['Customer']['customers_fax'] ?></dd>
			<dt>Invoicing Authorized</dt><dd><?php echo $this->Tracking->yesNo($customer['Customer']['invoicing_authorized']); ?></dd>
			<dt>Billing Type</dt><dd><?php echo strtoupper($customer['Customer']['billing_type']) ?></dd>
		</dl>
	</div>
	<div class="col-md-4">
		<h2>
			Payment Information
			<?php echo $this->element('Customers/manager_link', [
				'text' => 'Update',
				'action' => 'edit_payment_info',
			]); ?>
		</h2>
		<dl class="row">
			<dt>First Name on Card</dt><dd><?php echo $customer['Customer']['cc_firstname'] ?></dd>
			<dt>Last Name on Card</dt><dd><?php echo $customer['Customer']['cc_lastname'] ?></dd>
			<dt>Card #</dt><dd><?php echo $customer['Customer']['cc_number'] ?></dd>
			<dt>Expiration</dt>
				<dd><?php echo $customer['Customer']['cc_expires_month'] ?> / 20<?php echo $customer['Customer']['cc_expires_year'] ?></dd>
		</dl>
	</div>
	<div class="col-md-4">
		<h2>
			Authorized Names
			<?php echo $this->element('Customers/manager_link', [
				'text' => __('Add'),
				'link' => [
					'controller' => 'authorized_names',
					'action' => 'add',
					'customerId' => $customer['Customer']['customers_id']
				]
			]); ?>
		</h2>
		<?php if (empty($customer['AuthorizedName'])): ?>
			<p>No authorized names created.</p>
		<?php else: ?>
			<ul class="authorized-names-list">
				<?php foreach($customer['AuthorizedName'] as $name): ?>
					<li>
						<?php echo $name['authorized_firstname']; ?>
						<?php echo $name['authorized_lastname']; ?>
						<?php if (!empty($isManager)): ?>
							|
							<?php echo $this->Html->link(
								__('Edit'),
								array(
									'controller' => 'authorized_names',
									'action' => 'edit',
									'id' => $name['authorized_names_id']
								)
							); ?> |
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
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php echo $this->element('Customers/admin_actions'); ?>
</div>

<div class="row">
	<div class="col-md-12">
		<hr>
		<h2>
			<small>
				Addresses &nbsp;
				<?= $this->Html->link('Add', [
					'controller' => 'addresses',
					'action' => 'add',
					'customerId' => $customer['Customer']['customers_id'],
				]) ?>
				|
				<?= $this->Html->link('Change Defaults', [
					'controller' => 'customers',
					'action' => 'edit_default_addresses',
					'id' => $customer['Customer']['customers_id'],
				]) ?>
			</small>
		</h2>
		<div class="row">
			<div class="col-md-6">
				<h3>Billing</h3>
				<?php
					echo $this->Tracking->fullAddress($customer['DefaultAddress']);
				?>
			</div>
			<div class="col-md-6">
				<h3>Shipping</h3>
				<?php
					echo $this->Tracking->fullAddress($customer['ShippingAddress']);
				?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<h3>Backup Shipping</h3>
				<?php
					echo $this->Tracking->fullAddress($customer['EmergencyAddress']);
				?>
			</div>
			<div class="col-md-6">
				<h3>Saved Addresses</h3>
				<ol>
				<?php foreach($customer['Address'] as $address): ?>
					<li>
						<?php echo $address['full'] ?>
					</li>
				<?php endforeach; ?>
				</ol>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12" id="customer-orders">
		<hr>
		<div class="row">
			<div class="col-md-6">
				<h2>Recent Orders</h2>
			</div>
			<div class="col-md-6 text-end">
					<?php echo $this->element('pagination'); ?>
			</div>
		</div>
		<?php echo $this->element('search/orders', array(
			'results' => $orders,
			'customerId' => $customer['Customer']['customers_id'],
		)) ?>
	</div>
</div>
<?php echo $this->element('js-import', ['js' => 'customers/manager_view']); ?>
