<?php if (!empty($data)): ?>
	<div class="table-responsive">
		<table class="table table-bordered table-striped table-condensed">
			<thead>
				<tr>
					<th><?php echo $reportFields[$options['field']]; ?></th>
					<th>Count</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data as $result): ?>
					<tr>
						<td>
							<?php echo $result['name']; ?>
						</td>
						<td>
							<?php echo $result['y']; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<tbody>
		</table>
	</div>
<?php else: ?>
	<h3>No results found</h3>
<?php endif; ?>
