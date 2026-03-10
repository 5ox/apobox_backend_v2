<h2>Order Report</h2>

<?php echo $this->element('Reports/sales_chart', ['salesChartData' => $results]); ?>
<?php echo $this->element('Reports/form', array(
	'type' => 'Orders',
)); ?>
<?php echo $this->element('Reports/results', array(
	'type' => 'Orders',
)); ?>
<?php echo $this->element('js-import', ['js' => 'reports/base']); ?>
