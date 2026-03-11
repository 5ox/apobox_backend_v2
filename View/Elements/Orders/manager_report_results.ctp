<div class="table-responsive">
	<table class="table table-bordered table-striped table-sm">
		<thead>
			<tr>
				<th class="active"><?php echo ucfirst($interval); ?></th>
				<th class="active"># Orders</th>
				<th class="active">Fee</th>
				<th class="active">Insurance</th>
				<th class="active">Shipping</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($results as $result): ?>
				<tr>
					<td>
						<?php echo $result['date_purchased']; ?>
					</td>
					<td>
						<?php echo $result['total']; ?>
					</td>
					<td>
						<?php echo $this->Report->formatPrice($result['ot_fee']); ?>
					</td>
					<td>
						<?php echo $this->Report->formatPrice($result['ot_insurance']); ?>
					</td>
					<td>
						<?php echo $this->Report->formatPrice($result['ot_shipping']); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<tbody>
	</table>
</div>
