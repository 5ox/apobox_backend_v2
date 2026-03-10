<div class="footer navbar-fixed-bottom">
	<div class="container">
		<div class="navbar-brand">
			&copy;<?php echo date_create()->format('Y'); ?>
			<?php echo Configure::read('Defaults.long_name'); ?>
		</div>

		<?= $this->Html->link(
			'Terms of Service',
			'http://www.apobox.com/?page_id=3140',
			['target' => '_blank']
		); ?>

		<br class="visible-xs-block">

		<?= $this->Html->link(
			'Privacy Policy',
			'http://www.apobox.com/?page_id=3122',
			['target' => '_blank']
		); ?>
	</div>
</div>
