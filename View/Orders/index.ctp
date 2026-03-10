<div class="orders index">
	<h2><?php echo __('Orders'); ?></h2>
	<?php echo $this->element('search/orders', array('results' => $orders)); ?>
	<p>
	<?php echo $this->element('pagination'); ?>
</div>
