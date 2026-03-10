<?php
$url = Router::url(array(
	'controller' => 'orders',
	'action' => 'pay_manually',
	'id' => $orderId,
	'manager' => false,
	'employee' => false,
	'api' => false,
), array(
	'full' => true,
));
?>

<?php $this->start('lead'); ?>
Your order has failed automatic payment.
<?php $this->end(); ?>

<p>
	Your credit card on file has failed. Please click or paste the link below into your browser to pay for your shipment.
</p>

<p>
	<?php echo $this->Html->link($url, $url); ?>
</p>

<p>
	 We will retry with the payment information
	on file within 24 hours.
</p>

<?php if (!empty($comments)): ?>
	<?php $this->append('comments'); ?>
	<p><?php echo $comments; ?></p>
	<?php $this->end(); ?>
<?php endif; ?>
