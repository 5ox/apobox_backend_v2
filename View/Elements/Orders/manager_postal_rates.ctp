<div class="well well-sm">
	<dl class="dl-horizontal">
	<?php foreach($rates as $key => $rate): ?>
		<dt>
			<?php echo $this->Html->link($this->Number->currency($rate['Rate'], 'USD'), '#', array(
				'class' => 'postage-rate',
				'id' => 'rate-' . $key,
				'title' => isset($rate['MailClass']) ? $rate['MailClass'] : $rate['MailService'],
			)); ?>
		</dt>
		<dd><?php echo htmlspecialchars_decode($rate['MailService']); ?></dd>
	<?php endforeach; ?>
	</dl>
</div>
