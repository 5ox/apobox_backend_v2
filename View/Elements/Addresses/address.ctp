<?php if (!empty($address['address_book_id'])): ?>
	<address class="lead">
		<?php echo h($address['entry_firstname']); ?>
		<?php echo h($address['entry_lastname']); ?><br>
		<?php if (!empty($address['entry_company'])): ?>
			<?php echo h($address['entry_company']); ?><br>
		<?php endif; ?>
		<?php echo h($address['entry_street_address']); ?><br>
		<?php if (!empty($address['entry_suburb'])): ?>
			<?php echo h($address['entry_suburb']); ?><br>
		<?php endif; ?>
		<?php if (!empty($address['entry_city'])): ?>
			<?php echo h($address['entry_city']); ?>,
		<?php endif; ?>
		<?php if (isset($address['Zone'])): ?>
			<?php echo h($address['Zone']['zone_code']); ?>
		<?php endif; ?>
		<?php echo h($this->Tracking->zip($address['entry_postcode'])); ?><br>
		<?php if (empty($hideBase) && isset($address['entry_basename'])): ?>
			<?php echo h($address['entry_basename']); ?>
		<?php endif; ?>
	</address>
<?php endif; ?>
