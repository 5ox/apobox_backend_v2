<div class="admins index">
	<?php if (empty($paidManually)): ?>
		<p>No orders have been paid manually.</p>
	<?php else: ?>
		<?= $this->element('tables/order-status', ['results' => $paidManually]); ?>
	<?php endif; ?>
	<?php if (empty($inWarehouse)): ?>
		<p>No orders marked as "warehouse".</p>
	<?php else: ?>
		<?= $this->element('tables/order-status', ['results' => $inWarehouse]); ?>
	<?php endif; ?>
	<hr>
	<?= $this->element('Layouts/Admin/menu', ['orderStatuses' => $orderStatuses]); ?>
</div>
<div class="row">
	<?= $this->element('Layouts/Admin/search_box'); ?>
</div>
<div class="row">
	<?= $this->element('Layouts/Admin/order_box'); ?>
</div>
