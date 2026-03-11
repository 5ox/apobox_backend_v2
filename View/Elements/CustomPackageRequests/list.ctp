<?php
if (empty($requests)) {
	return '';
}

$tableHeader = array(
	'tracking_id' => 'Tracking #',
	'package_status' => 'Order Status',
	'package_repack' => 'Repackage',
	'mail_class' => 'Postage Class',
	'insurance_coverage' => 'Insurance',
	'order_add_date' => 'Date Requested',
);

if (!empty($isEmployee)) {
	$tableHeader = $tableHeader + array(
		'Insurance',
	);
}
if (!empty($isManager) || !empty($isEmployee)) {
	$tableHeader = $tableHeader + array(
		'Actions',
	);
}

$customerId = isset($customerId) && (!empty($isManager) || !empty($isEmployee)) ? $customerId : null;
$showName = !isset($customerId) && (!empty($isManager) || !empty($isEmployee)) ? true : false;

if ($showName) {
	$tableHeader = ['Customer'] + $tableHeader;
}
?>

<?php if ($customerId): ?>
	<div class="row bg-light p-2 rounded">
	<h4>Custom Package Requests</h4>
<?php endif; ?>

<div class="table-responsive">
	<table class="table table-sm custom-requests">
		<thead>
			<tr>
				<?= $this->element('table-header', array(
					'tableHeader' => $tableHeader
				)); ?>
			</tr>
		<thead>
		<tbody>
			<?php foreach ($requests as $request): ?>
			<tr class="request-id" id="r-<?php echo $request['CustomPackageRequest']['custom_orders_id']; ?>">
				<?php if ($showName): ?>
					<td>
						<?php echo $request['Customer']['billing_id']; ?>
						<?php echo $request['Customer']['customers_firstname']; ?>
						<?php echo $request['Customer']['customers_lastname']; ?>
					</td>
				<?php endif; ?>
				<td class="inbound">
					<?php if ($customerId): ?>
						<a href="#">
					<?php endif; ?>
					<?php echo $this->Tracking->requestInbound($request); ?>
					<?php if ($customerId): ?>
						</a>
					<?php endif; ?>
				</td>
				<td><?php echo $this->Tracking->requestLabel($request); ?></td>
				<td><?php echo Inflector::humanize($request['CustomPackageRequest']['package_repack']); ?></td>
				<td class="mailclass">
					<?php if ($customerId): ?>
						<a href="#">
					<?php endif; ?>
					<?php echo Inflector::humanize($request['CustomPackageRequest']['mail_class']); ?>
					<?php if ($customerId): ?>
						</a>
					<?php endif; ?>
				</td>
				<?php if (!empty($isManager) || !empty($isEmployee)): ?>
					<td class="insurance">
						<?php if ($customerId): ?>
							<a href="#">
						<?php endif; ?>
						<?php echo !empty($request['CustomPackageRequest']['insurance_coverage']) ? $this->Number->currency($request['CustomPackageRequest']['insurance_coverage']) : '<small>Default</small>'; ?>
						<?php if ($customerId): ?>
							</a>
						<?php endif; ?>
					</td>
				<?php else: ?>
					<td><?php echo $this->Tracking->insuranceCoverage($request); ?></td>
				<?php endif; ?>
				<td><?php echo $this->Tracking->dateRequested($request, 'customer'); ?></td>
				<?php if (!empty($isManager) || !empty($isEmployee) || !empty($u)): ?>
					<td>
						<?php if ($customerId): ?>
							<?php echo $this->Html->link('Use', '#', array(
								'class' => 'btn btn-sm btn-success btn-use'
							)); ?>
						<?php endif; ?>
						<?php if (!empty($isManager) || !empty($isEmployee) || $u['customers_id'] == $request['CustomPackageRequest']['customers_id']): ?>
							<?php echo $this->Tracking->requestEdit($request, $customerId); ?>
						<?php endif; ?>
					</td>
				<?php endif; ?>
			</tr>
				<?php if ((!empty($isManager) || !empty($isEmployee)) && !empty($request['CustomPackageRequest']['instructions'])): ?>
					<tr>
						<td colspan="7">
							<small>
								<span class="badge text-bg-warning">Note:</span>
								<?php echo !empty($request['CustomPackageRequest']['instructions']) ? h($request['CustomPackageRequest']['instructions']) : 'None'; ?>
							</small>
						</td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php if ($customerId): ?>
	</div>
<?php endif; ?>
