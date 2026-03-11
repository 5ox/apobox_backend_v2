<?php if (isset($customer['Customer']['billing_id'])): ?>
<div class="row bg-light p-2 rounded">
	<div class="col-sm-6">
		<address>
			<h4>
				<strong><?php echo $customer['Customer']['customers_firstname']; ?> <?php echo $customer['Customer']['customers_lastname']; ?></strong><br>
				<?php echo $this->Html->link(
					$customer['Customer']['customers_email_address'],
					'mailto:' . $customer['Customer']['customers_email_address']
				); ?>
			</h4>
		</address>
	</div>
	<div class="col-sm-6">
		<h2><code class="float-end">
			<?php echo $this->Html->link($customer['Customer']['billing_id'], array(
				'controller' => 'customers',
				'action' => 'view',
				'id' => $customer['Customer']['customers_id']
			)); ?>
			</code></h2>
	</div>
</div>
<?php else: ?>
<hr>
<?php endif; ?>
