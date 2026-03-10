<?php
$url = Router::url(array(
	'controller' => 'orders',
	'action' => 'pay_manually',
	'id' => $order['Order']['orders_id'],
	'manager' => false,
	'employee' => false,
	'api' => false,
), array(
	'full' => true,
));
?>

<?php $this->start('lead'); ?>
<h2><?php echo $order['Customer']['customers_fullname']; ?>,</h2>
You have a package that is awaiting payment.
<?php $this->end(); ?>

<p>
	Payment for your APO Box order #<strong><?php echo $order['Order']['orders_id']; ?></strong>
	could not be completed. Please click or paste the
	link below into your browser to complete your payment.
<p>
	<?php echo $this->Html->link($url, $url); ?>
</p>

<?php if (!empty($comments)): ?>
	<?php $this->append('comments'); ?>
	<p><?php echo $comments; ?></p>
	<?php $this->end(); ?>
<?php endif; ?>
