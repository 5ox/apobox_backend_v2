<h2>Customer Report</h2>

<div class="row">
	<div class="col-md-6">
		<?php echo $this->element('Reports/demo_form'); ?>
	</div>
	<div class="col-md-6">
		<?php echo $this->element('Reports/demo_chart', [
			'title' => $reportFields[$options['field']] . ' (top ' . $options['limit'] . ')',
			'demoChartData' => $data,
		]); ?>
	</div>
</div>
<?php echo $this->element('Customers/demo_report_results'); ?>
<?php echo $this->element('js-import', ['js' => 'reports/base']); ?>
