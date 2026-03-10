<h3>APO Box Reports</h3>
<div class="row main-row">
	<div class="col-sm-12">
		<div class="main-report">
			<div class="row">
				<div class="col-sm-12">
					<?php echo $this->element('Reports/sales_chart'); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<?php echo $this->element('Reports/quick_report', array(
						'controller' => 'orders',
						'interval' => 'day',
						'from' => strtotime('30 days ago'),
						'to' => strtotime('today'),
						'title' => 'Daily',
					)); ?> |
					<?php echo $this->element('Reports/quick_report', array(
						'controller' => 'orders',
						'interval' => 'week',
						'from' => strtotime('monday 8 weeks ago'),
						'to' => strtotime('sunday this week'),
						'title' => 'Weekly',
					)); ?> |
					<?php echo $this->element('Reports/quick_report', array(
						'controller' => 'orders',
						'interval' => 'month',
						'from' => strtotime('first day of 12 months ago'),
						'to' => strtotime('last day of this month'),
						'title' => 'Monthly',
					)); ?> |
					<?php echo $this->element('Reports/quick_report', array(
						'controller' => 'orders',
						'interval' => 'year',
						'from' => strtotime('jan 1, 2006'),
						'to' => strtotime('last day of this year'),
						'title' => 'Yearly',
					)); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row main-row">
	<div class="col-sm-6">
		<div class="row">
			<div class="col-sm-12">
				<?php echo $this->element('Reports/signup_chart'); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<?php echo $this->element('Reports/quick_report', array(
					'controller' => 'customers',
					'interval' => 'day',
					'from' => strtotime('30 days ago'),
					'to' => strtotime('today'),
					'title' => 'Daily',
				)); ?> |
				<?php echo $this->element('Reports/quick_report', array(
					'controller' => 'customers',
					'interval' => 'week',
					'from' => strtotime('monday 8 weeks ago'),
					'to' => strtotime('sunday this week'),
					'title' => 'Weekly',
				)); ?> |
				<?php echo $this->element('Reports/quick_report', array(
					'controller' => 'customers',
					'interval' => 'month',
					'from' => strtotime('first day of 12 months ago'),
					'to' => strtotime('last day of this month'),
					'title' => 'Monthly',
				)); ?> |
				<?php echo $this->element('Reports/quick_report', array(
					'controller' => 'customers',
					'interval' => 'year',
					'from' => strtotime('jan 1, 2006'),
					'to' => strtotime('last day of this year'),
					'title' => 'Yearly',
				)); ?>
			</div>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="row">
			<div class="col-sm-12">
				<?php echo $this->element('Reports/demo_chart', [
					'title' => 'Top 5 Shipping Postal Codes',
				]); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<?php echo $this->Html->link('More Customer Data', [
					'controller' => 'customers',
					'action' => 'demographics_report',
				]); ?>
			</div>
		</div>
	</div>
</div>
<div class="row main-row">
	<div class="col-sm-6">
		<?php echo $this->element('Reports/status_counts'); ?>
	</div>
</div>
<?php echo $this->element('js-import', ['js' => 'reports/base']); ?>
