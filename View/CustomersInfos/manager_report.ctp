<h2>Signup Report</h2>

<?php echo $this->element('Reports/signup_chart', ['signupChartData' => $results]); ?>
<?php echo $this->element('Reports/form', array(
	'type' => 'Customers',
)); ?>
<?php echo $this->element('Reports/results', array(
	'type' => 'Customers',
)); ?>
<?php echo $this->element('js-import', ['js' => 'reports/base']); ?>
