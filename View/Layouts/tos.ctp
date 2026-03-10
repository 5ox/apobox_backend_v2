<!DOCTYPE html>
<html lang="en">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo Configure::read('Defaults.long_name'); ?> -
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
		echo $this->Html->meta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));

		if (Configure::read('CDN.enabled')) {
			$cssSources = array(
				'//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css',
				'global',
				'public',
			);
		} else {
			$cssSources = array(
				'bootstrap.min',
				'global',
				'public',
			);
		}
		echo $this->Html->css($cssSources);
	?>
	<?php echo $this->element('head/google-fonts'); ?>
</head>

<body>
	<div class="container">
		<main id="main" role="main">
			<?php echo $this->fetch('content'); ?>
		</main>
	</div> <!-- /container -->
</body>
</html>
