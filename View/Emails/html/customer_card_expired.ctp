<?php
$url = Router::url(array(
	'controller' => 'customers',
	'action' => 'edit_partial',
	'partial' => 'payment_info',
	'manager' => false,
	'employee' => false,
	'api' => false,
), array(
	'full' => true,
));
?>

<?php $this->start('lead'); ?>
<h2><?php echo $customer['Customer']['customers_fullname']; ?>,</h2>
Your credit card has expired.
<?php $this->end(); ?>

<p>
	To avoid any interruption of service, please click or paste the
	link below into your browser to update your payment information.
<p>
	<?php echo $this->Html->link($url, $url); ?>
</p>

<?php if (!empty($comments)): ?>
	<?php $this->append('comments'); ?>
	<p><?php echo $comments; ?></p>
	<?php $this->end(); ?>
<?php endif; ?>
