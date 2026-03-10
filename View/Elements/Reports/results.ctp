<?php if (isset($results) && !empty($results)): ?>
	<?php echo $this->element($type . '/manager_report_results'); ?>
	<?php elseif (isset($results)): ?>
			<h3>No results found</h3>
<?php endif; ?>
