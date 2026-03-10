<!-- Static navbar -->
<div class="navbar navbar-default" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="fa fa-bars"></span>
			</button>
			<a class="navbar-brand" href="/">
				<?php echo $this->Html->image('apobox-logo.png', array('alt' => Configure::read('Defaults.short_name'))); ?>
			</a>
		</div>
		<div class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<?php echo $this->fetch('navbar-collapsing-list-items'); ?>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<?php echo $this->fetch('navbar-pull-right-list-items'); ?>
			</ul>
		</div><!--/.nav-collapse -->
	</div><!--/.container-fluid -->
</div>

<?php /*
	<?php echo $this->element('Layouts/social_networks'); ?>
	<?php echo $this->element('Layouts/search'); ?>
*/ ?>
