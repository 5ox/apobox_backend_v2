<h3><?php echo $label; ?>
	<small>
		<span
			data-bs-toggle="tooltip"
			data-placement="bottom"
			title="<?php echo Configure::read('Tooltip.' . $tooltip); ?>"
		>
			?
		</span>
		<?php echo $this->Html->link(__('Change'), array(
			'action' => 'edit_partial',
			'partial' => 'addresses',
		)); ?>
		|
		<?php echo $this->element('Addresses/add-edit-link', [
			'address' => $address
		]); ?>
	</small>
</h3>
<div class="bg-light p-3 rounded well-address plus">
	<?php echo $this->element('Addresses/address', [
		'address' => $address
	]); ?>
</div>

