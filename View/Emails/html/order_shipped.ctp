<?php echo $this->element('Email/order_common'); ?>

<?php $this->start('lead'); ?>
Your order has shipped!
<?php $this->end(); ?>

<?php $this->start('tracking'); ?>
<br>
<?php if (!empty($outboundTracking)): ?>
Outbound tracking #: <strong><?php echo $this->Html->link($outboundTracking, $trackingUrl . $outboundTracking); ?></strong>
<?php endif; ?>

<?php if (!empty($inboundTracking)): ?>
<br>Inbound tracking #: <strong><?php echo $inboundTracking ?></strong>
<?php endif; ?>
<?php $this->end(); ?>
