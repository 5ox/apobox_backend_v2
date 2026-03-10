<div class="table-responsive">
	<table class="table table-bordered table-striped table-condensed">
		<thead>
			<tr>
				<th><?php echo ucfirst($interval); ?></th>
				<th>Count</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($results as $result): ?>
				<tr>
					<td>
						<?php echo $result['date']; ?>
					</td>
					<td>
						<?php echo $result['total']; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<tbody>
	</table>
</div>
