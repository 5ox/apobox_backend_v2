<?php
/**
 * Default public page layout.
 */

if (empty($keywordsForLayout)) {
	$keywordsForLayout = __('default, keywords');
}
if (empty($descriptionForLayout)) {
	$descriptionForLayout = __('Default description.');
}
if (!isset($socialMetaTags)) {
	$socialMetaTags = array();
}

?><!DOCTYPE html>
<html lang="en">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo Configure::read('Defaults.long_name'); ?> -
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
		echo $this->Html->meta(array('name' => 'keywords', 'content' => $keywordsForLayout));
		echo $this->Html->meta(array('name' => 'description', 'content' => $descriptionForLayout));
		echo $this->Html->meta(array('name' => 'canonical', 'content' => Router::url(null, true)));
		echo $this->Html->meta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));

		echo $this->element('Layouts/icons');
		echo $this->element('Layouts/social_meta_tags', array(
			'socialMetaTags' => $socialMetaTags,
			'description_for_layout' => $descriptionForLayout,
			'title_for_layout' => $title_for_layout
		));

		echo $this->element('Layouts/css', ['extra' => ['admin']]);
		echo $this->element('head/google-fonts');
	?>

	<?php
		echo $this->fetch('meta');
		echo $this->fetch('css');
	?>
</head>

<body class="<?php echo $this->action ?>">

	<?php echo $this->element('Layouts/Admin/navbar_items'); ?>
	<?php echo $this->element('Layouts/header'); ?>

	<div class="container">

		<main id="main" role="main">
			<?php echo $this->element('Layouts/breadcrumbs', array(
				'currentPage' => $title_for_layout
			)); ?>
			<?php echo $this->Flash->render(); ?>
			<?php echo $this->Flash->render('auth'); ?>
			<?php echo $this->fetch('content'); ?>
		</main>

	</div> <!-- /container -->

	<?php echo $this->element('Layouts/footer'); ?>

	<?php
		echo $this->element('Layouts/javascript');
		echo $this->fetch('script');
		echo $this->Js->writeBuffer(array('onDomReady' => false, 'safe' => false));
		echo $this->element('Layouts/footer_scripts');
	?>
</body>
</html>
