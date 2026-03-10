import $ from 'jquery';

$('#CustomerCustomersDefaultAddressId', '#PayManuallyAddress').change(function() {
	if ($(this).val() === 'custom') {
		$('.not-required', '#AddressFormFieldset').prop('required', true).removeClass('not-required');
		$('#AddressFormFieldset').show(750);
	} else {
		$('#AddressFormFieldset').hide(750);
		$('[required]', '#AddressFormFieldset').addClass('not-required').prop('required', false);
	}
});

$(document).ready(function() {
	$('#CustomerCustomersDefaultAddressId', '#PayManuallyAddress').change();
});
