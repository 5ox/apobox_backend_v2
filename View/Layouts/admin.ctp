<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>
		<?php echo __('EU'); ?> -
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
		echo $this->element('Layouts/icons');
		echo $this->element('Layouts/css', ['extra' => ['admin']]);
		echo $this->element('head/google-fonts');
	?>

	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<?php
		echo $this->fetch('meta');
		echo $this->fetch('css');
	?>
</head>

<body>

	<?php echo $this->element('Layouts/Admin/navbar_items'); ?>
	<?php echo $this->element('Layouts/header'); ?>
	<?php echo $this->element('Layouts/Admin/breadcrumbs', compact('breadcrumbs')); ?>

	<div class="container">
		<?php echo $this->Flash->render(); ?>
		<?php echo $this->Flash->render('auth'); ?>
		<?php echo $this->fetch('content'); ?>
	</div>

	<?php //echo $this->element('Layouts/footer'); ?>

	<?php
		echo $this->element('Layouts/javascript', ['extra' => ['admin']]);
		echo $this->fetch('script');
	?>
	<?php echo $this->Js->writeBuffer(); ?>
</body>
</html>
