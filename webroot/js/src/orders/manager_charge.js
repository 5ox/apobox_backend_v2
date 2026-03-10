import $ from 'jquery';

var updateTotal = function() {
	var sum = 0;
	$('input[name*="][value]"]', '#ChargeForm').each(function() {
		sum += Number($(this).val()) || 0;
	});
	$('.total-value').text(sum.toFixed(2));
};
$(document).ready(function() {
	$('input[name*="][value]"]', '#ChargeForm').on('change keyup', function() {updateTotal()});
	updateTotal();
	$('input[type=checkbox][name*="data[checkbox][Order"]', '#ChargeForm').on('change', function() {
		let input = $(this).parent().find('input[name*="][value]"]');
		if ($(this).is(':checked')) {
			input.val(input.data('value-checked'));
		} else {
			input.val(input.data('value-unchecked'));
		}
		updateTotal();
	});
	updateTotal();

	$('.rates-btn', '#ChargeForm').on('click', function() {
		$(this).text('Please wait...');
	});
	$('.postage-rate', '#ChargeForm').on('click', function(e) {
		e.preventDefault();
		var rate = $(this).text().substring(1);
		$('#OrderShippingValue').val(rate);
		updateTotal();
	});
});
