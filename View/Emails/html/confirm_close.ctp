<p>
	<?php echo $customerName; ?>,
</p>

<p>
	If you recently requested to close your account, please click the link below. If you cannot click
	the link you may copy and paste it into your browser. The link will be valid until tomorrow.
</p>

<p>
	Closing your account will immediately deactivate your APO Box address and remove your credit card information from our system. We will no longer forward packages on your behalf.
</p>

<p>
	<strong>Important:</strong> You must be logged into your APO Box account to close your account. Upon clicking or pasting the following link, you will be automatically logged out and will not be able to access your account.
<p>
	<?php echo $this->Html->link($url, $url); ?>
</p>

<p>
	If you did not request to close your account, you do not need to click or copy and paste the link.
	You may still log in and your account will continue to function normally.
</p>
