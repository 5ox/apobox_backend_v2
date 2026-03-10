<ul style="margin-top:5px;margin-bottom:0;">
	<li>Dimensions: <?php echo $this->Tracking->packageDimensions($order) ?></li>
	<li>Weight: <?php echo $this->Tracking->weight($order); ?></li>
	<li>Class: <?php echo h($order['Order']['mail_class']); ?></li>
	<li>Type: <?php echo h($order['Order']['package_type']); ?></li>
	<li>Insurance: $<?php echo h($order['Order']['insurance_coverage']); ?></li>
	<?php if (!empty($order['Order']['customs_description'])): ?>
		<li>Customs Description: <?php echo h($order['Order']['customs_description']); ?></li>
	<?php endif ?>
	<li>Processed: <?php echo $this->Tracking->formatDatetime($order['Order']['date_purchased'], 'customer'); ?></li>
</ul>
