function updateTotal() {
	var sum = 0;
	var form = document.querySelector('#ChargeForm');
	if (!form) return;
	form.querySelectorAll('input[name*="][value]"]').forEach(function(input) {
		sum += Number(input.value) || 0;
	});
	var totalEl = document.querySelector('.total-value');
	if (totalEl) totalEl.textContent = sum.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
	var form = document.querySelector('#ChargeForm');
	if (!form) return;

	form.querySelectorAll('input[name*="][value]"]').forEach(function(input) {
		input.addEventListener('change', updateTotal);
		input.addEventListener('keyup', updateTotal);
	});
	updateTotal();

	form.querySelectorAll('input[type=checkbox][name*="data[checkbox][Order"]').forEach(function(checkbox) {
		checkbox.addEventListener('change', function() {
			var input = this.parentElement.querySelector('input[name*="][value]"]');
			if (input) {
				input.value = this.checked ? input.dataset.valueChecked : input.dataset.valueUnchecked;
			}
			updateTotal();
		});
	});
	updateTotal();

	form.querySelectorAll('.rates-btn').forEach(function(btn) {
		btn.addEventListener('click', function() {
			this.textContent = 'Please wait...';
		});
	});

	form.querySelectorAll('.postage-rate').forEach(function(link) {
		link.addEventListener('click', function(e) {
			e.preventDefault();
			var rate = this.textContent.substring(1);
			var shippingInput = document.querySelector('#OrderShippingValue');
			if (shippingInput) shippingInput.value = rate;
			updateTotal();
		});
	});
});
