<p>
	<?php echo $customerName; ?>,
</p>

<p>
	If you recently requested to reset your password, please click the link below. If you cannot click
	the link you may copy and paste it into your browser.
</p>

<p>
	<?php echo $this->Html->link($url, $url); ?>
</p>

<p>
	If you did not request to reset your password, you do not need to click or copy and paste the link.
	You may still log in as normal and your password is safe.
</p>
