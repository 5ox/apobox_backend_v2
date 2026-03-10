<h2>You're almost finished!</h2>

<p><a href="<?php echo $almostFinishedUrl; ?>">Add your billing information to ensure fast delivery!</a></p>

<p>Your new U.S. address has been created and is ready for immediate use. Use your new address to let any company ship to your APO/FPO/DPO address.</p>

<p>Please use the exact address provided to prevent any delays in processing your package. Misaddressed packages or ones missing your APOID (Attn: Line) will be assessed an additional processing <a href="http://www.apobox.com/">fee</a>.</p>

<table class="twelve columns" style="border-collapse: collapse; border-spacing: 0; margin: 0 auto; padding: 0; text-align: left; vertical-align: top; width: 580px">
	<tr style="padding: 0; text-align: left; vertical-align: top" align="left">
		<td class="panel" style="-moz-hyphens: auto; -webkit-hyphens: auto; background: #f2f2f2; border-collapse: collapse !important; border: 1px solid #d9d9d9; color: #222222; font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 19px; margin: 0; padding: 10px; text-align: left; vertical-align: top; word-break: break-word; width:280px" align="left" bgcolor="#f2f2f2" valign="top">
			<h3>You Ship Packages Here:</h3>
			<address>
				<?php echo $firstName . ' ' . $lastName; ?><br>
				Attn: <?php echo $billingId; ?><br>
				1911 Western Ave<br>
				Plymouth, IN 46563
			</address>
		</td>
		<td>&nbsp;</td>
		<td class="panel" style="-moz-hyphens: auto; -webkit-hyphens: auto; background: #f2f2f2; border-collapse: collapse !important; border: 1px solid #d9d9d9; color: #222222; font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 19px; margin: 0; padding: 10px; text-align: left; vertical-align: top; word-break: break-word; width:280px" align="left" bgcolor="#f2f2f2" valign="top">
			<h3>We Forward Them Here:</h3>
			<address>
				<?php echo $firstName . ' ' . $lastName; ?><br>
				<?php echo h($address['entry_company']); ?>
				<?php if (!empty($address['entry_company'])): ?>
					<br>
				<?php endif; ?>
				<?php echo h($address['entry_street_address']); ?><br>
				<?php echo h($address['entry_suburb']); ?>
				<?php if (!empty($address['entry_suburb'])): ?>
					<br>
				<?php endif; ?>
				<?php echo h($address['entry_city']); ?>,
				<?php echo h($address['Zone']['zone_code']); ?>
				<?php if (!empty($address['entry_suburb'])): ?>
					,&nbsp;
				<?php endif; ?>
				<?php echo h($address['entry_postcode']); ?><br>
			</address>
		</td>
	</tr>
</table>

<h2>Shipping to your APO/FPO/DPO</h2>

<p>To ensure you get your package to your APO/FPO as fast as possible, please make sure the APO/FPO address on file is correct. Most package delays are due to incorrectly addressed packages.</p>

<p>You are responsible to ensure the incoming package to our warehouse matches all US Postal service shipping sizes, weight and shipping restrictions for your specific APO/FPO. Each address is different so check out your specific APO/FPO details at our <a href="http://www.apobox.com/">APO/FPO/DPO shipping restrictions link</a>.</p>
<ul>
	<li>
		<strong>Packages may not exceed 130 inches in combined length and girth.</strong><br />
		<img class="box-image" src="<?= $this->Html->assetUrl('box_length_width.gif', ['fullBase'	=> true, 'pathPrefix' => IMAGES_URL]) ?>" width="150" height="89" alt="APO size restriction" />
		<p class="package-detail"><strong>Maximum weight:</strong> 70 pounds.</p>
		<p class="package-detail"><strong>Maximum length and girth:</strong> 130 inches</p>
		<p class="package-detail"><strong>Length:</strong> longest side of the box</p>
		<p class="package-detail"><strong>Girth:</strong> measurement around the thickest part of the box</p>
	</li>
	<li>
		<strong>Oversize items:</strong> If your package exceeds this size or weight restrictions, we may attempt to repack it for an additional <a href="http://www.apobox.com/">repack fee</a>. You are responsible to ensure the item you order does not exceed 130 inches length width girth or is over 70 pounds.<br />
		Please note that flat screen TVs, car bumpers, custom exhaust systems, baby cribs and furniture are to large to send via the APO/FPO system and will be sent to your backup shipping address at your expense.
	</li>
	<li>
		<strong>Package Insurance:</strong> At signup, your default insurance coverage for each package is $50 for a minimal fee. You can change this amount at any time in your <a href="https://account.apobox.com">account</a>.
	</li>
	<li>
		<strong>Billing and Fees:</strong> Your credit card on file is only billed when a forwarding package arrives. The customer is responsible for all postage charges: shipping your package to your APO/FPO address or backup address, extra postal insurance fees, and our handling charges. See our <a href="https://www.apobox.com">rate pages</a> for the current rates and fees.
	</li>
</ul>

<p>We look forward to serving your shipping needs, contact us now if you have any questions.</p>

