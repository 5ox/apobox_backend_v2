<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<link href="../../../../webroot/css/ink.css" rel="stylesheet">
	<link href="../../../../webroot/css/email.css" rel="stylesheet">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width"/>
	<title><?php echo $title_for_layout; ?></title>
</head>
<body>
<table class="body">
		<tr>
			<td class="center page" align="center" valign="top">
				<center>

					<table class="row header">
						<tr>
							<td class="center" align="center">
								<center>

									<table class="container">
										<tr>
											<td class="wrapper last">

												<table class="twelve columns">
													<tr>
														<td class="six sub-columns">
															<img src="<!--<?php echo $this->Html->assetUrl('apobox-logo.png', array('fullBase'	 => true,	'pathPrefix' => IMAGES_URL)); ?>-->">
														</td>
														<td class="six sub-columns last" style="text-align:right; vertical-align:middle;">
															<!--<?php if ($this->fetch('status')): ?>-->
															<h6 style="text-align:right"><!--<?php echo $this->fetch('status'); ?>--></h6>
															<!--<?php endif; ?>-->
														</td>
														<td class="expander"></td>
													</tr>
												</table>

											</td>
										</tr>
									</table>

								</center>
							</td>
						</tr>
					</table>

					<table class="container">
						<tr>
							<td>

								<table class="row">
									<tr>
										<td class="wrapper last">

											<table class="twelve columns">
												<tr>
													<td>
													<?php if (isset($firstName) && isset($lastName)): ?>
													<h3><?php echo $firstName; ?> <?php echo $lastName; ?>,</h3>
													<?php endif; ?>

													<!--<?php if ($this->fetch('lead')): ?>-->
													<p class="lead"><!--<?php echo $this->fetch('lead'); ?>--></p>
													<!--<?php endif; ?>-->

													<!--<?php if ($this->fetch('shipped')): ?>-->
													<table class="button success">
														<tr>
															<td>
																<a href="<!--<?php echo $this->fetch('shipped'); ?>-->">View Order #<?php echo $orderId; ?></a>
															</td>
														</tr>
													</table>
													<!--<?php endif; ?>-->

													<!--<?php if ($this->fetch('update')): ?>-->
													<table class="button info">
														<tr>
															<td>
																<a href="<!--<?php echo $this->fetch('update'); ?>-->">View Order #<?php echo $orderId; ?></a>
															</td>
														</tr>
													</table>
													<!--<?php endif; ?>-->

													<!--<?php if ($this->fetch('tracking')): ?>-->
													<p class="lead"><!--<?php echo $this->fetch('tracking'); ?>--></p>
													<!--<?php endif; ?>-->

													<!--<?php if ($this->fetch('package-data')): ?>-->
													<br>
													<p class="lead">Package Data:</p>
													<!--<?php echo $this->fetch('package-data'); ?>-->
													<!--<?php endif; ?>-->

													<!--<?php echo $this->fetch('content'); ?>-->
													</td>
													<td class="expander"></td>
												</tr>
											</table>

										</td>
									</tr>
								</table>

								<!--<?php if($this->fetch('comments')): ?>-->
								<table class="row callout">
									<tr>
										<td class="wrapper last">
											<p class="lead">Additional Comments:</p>
											<table class="twelve columns">
												<tr>
													<td class="panel">
														<p><!--<?php echo $this->fetch('comments'); ?>--></p>
													</td>
													<td class="expander"></td>
												</tr>
											</table>

										</td>
									</tr>
								</table>
								<br>
								<!--<?php endif; ?>-->

								<table class="row footer">
									<tr>
										<td class="wrapper last">

											<table class="twelve columns">
												<tr>
													<td>

														<p>
															To update or change your account settings, visit your
															<!--<?php echo $this->Html->link('account page', 'https://account.apobox.com/'); ?>-->.
														</p>

														<p>
															We look forward to serving your shipping needs, contact us if you have any questions.
														</p>

														<p class="signature">
															The APO Box team
														</p>

														<p>
															For deals, updates and specials follow us on <a href="https://twitter.com/apobox">Twitter</a> or like us on <a href="https://www.facebook.com/ApoBoxShipping">Facebook</a>.
														</p>

														<!--<?= $this->element('Email/affiliate_links') ?>-->
													</td>
													<td class="expander"></td>
												</tr>
											</table>

										</td>
									</tr>
								</table>

								<table class="row">
									<tr>
										<td class="wrapper last">

											<table class="twelve columns">
												<tr>
													<td align="center">
														<center>
															<p class="fine-print">You are receiving this email because you have an account with this email address at apobox.com.</p>
															<p style="text-align:center;"><a href="http://www.apobox.com/?page_id=3140">Terms</a> | <a href="http://www.apobox.com/?page_id=3122">Privacy</a></p>
														</center>
													</td>
													<td class="expander"></td>
												</tr>
											</table>

										</td>
									</tr>
								</table>

							</td>
						</tr>
					</table>

				</center>
			</td>
		</tr>
	</table>
</body>
</html>

