<?php if (!empty($js) && Configure::read('Javascript.autoBuild')): ?>
	<?php if (!is_array($js)) { $js = [$js]; } ?>
	<?php $this->append('script'); ?><script>
		<?php foreach ($js as $import): ?>
			System.import('/js/src/<?php echo  $import; ?>');
		<?php endforeach; ?>
	</script><?php $this->end(); ?>
<?php endif; ?>
