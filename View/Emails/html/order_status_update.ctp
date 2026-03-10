<?php echo $this->element('Email/order_common'); ?>

<?php $this->start('lead'); ?>
Your order has been updated. The status is now "<?php echo ucfirst($status); ?>".
<?php $this->end(); ?>
