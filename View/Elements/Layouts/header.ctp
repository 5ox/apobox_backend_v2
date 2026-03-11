<!-- Static navbar -->
<nav class="navbar navbar-expand-md navbar-light" role="navigation">
	<div class="container">
		<a class="navbar-brand" href="/">
			<?php echo $this->Html->image('apobox-logo.png', array('alt' => Configure::read('Defaults.short_name'))); ?>
		</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarMain">
			<ul class="navbar-nav me-auto">
				<?php echo $this->fetch('navbar-collapsing-list-items'); ?>
			</ul>
			<ul class="navbar-nav ms-auto">
				<?php echo $this->fetch('navbar-float-end-list-items'); ?>
			</ul>
		</div>
	</div>
</nav>

<?php /*
	<?php echo $this->element('Layouts/social_networks'); ?>
	<?php echo $this->element('Layouts/search'); ?>
*/ ?>
