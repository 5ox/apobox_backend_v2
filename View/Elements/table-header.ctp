<?php if (!empty($tableHeader)): ?>
	<?php foreach ($tableHeader as $column => $label): ?>
		<th>
			<?php if (empty($this->request->paging) || !empty($noTableHeader)): ?>
				<?= $label ?>
			<?php else: ?>
				<?php if (is_numeric($column)) {
					$column = $label;
					$label = null;
				} ?>
				<?php echo $this->Paginator->sort($column, $label); ?>
			<?php endif; ?>
		</th>
	<?php endforeach; ?>
<?php endif; ?>
