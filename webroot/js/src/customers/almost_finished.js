import $ from 'jquery';

$('#CustomerCustomersDefaultAddressId', '#AlmostFinishedAddress').change(function() {
	if ($(this).val() === 'new') {
		$('.not-required', '#AddressFormFieldset').prop('required', true).removeClass('not-required');
		$('#AddressFormFieldset').show(750);
	} else {
		$('#AddressFormFieldset').hide(750);
		$('[required]', '#AddressFormFieldset').addClass('not-required').prop('required', false);
	}
});

$(document).ready(function() {
	$('#CustomerCustomersDefaultAddressId', '#AlmostFinishedAddress').change();
});
