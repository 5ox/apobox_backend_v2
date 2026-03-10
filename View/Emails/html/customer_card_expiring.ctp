<?php $this->start('lead'); ?>
Your credit card expires soon.
<?php $this->end(); ?>

<p>
	Please make sure to update your payment information before your credit card
	expires next month.
</p>

<?php if (!empty($comments)): ?>
	<?php $this->append('comments'); ?>
	<p><?php echo $comments; ?></p>
	<?php $this->end(); ?>
<?php endif; ?>
