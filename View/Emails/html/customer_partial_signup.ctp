<?php
$url = Router::url(array(
	'controller' => 'addresses',
	'action' => 'add',
	'manager' => false,
	'employee' => false,
	'api' => false,
), array(
	'full' => true,
));
?>

<?php $this->start('lead'); ?>
<h2><?php echo $customer['Customer']['customers_fullname']; ?>,</h2>
You APO Box registration is incomplete.
<?php $this->end(); ?>

<p>
	There are no addresses saved with your account.
	To complete your registration, please click or paste the
	link below into your browser to add an address.
<p>
	<?php echo $this->Html->link($url, $url); ?>
</p>

<?php if (!empty($comments)): ?>
	<?php $this->append('comments'); ?>
	<p><?php echo $comments; ?></p>
	<?php $this->end(); ?>
<?php endif; ?>
