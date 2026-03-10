import $ from 'jquery';

$(document).ready(function() {
	hideAllZonesExceptOne('United States');

	$('#AddressEntryCountryId').change(function() {
		filterByCountry();
	});
});

function filterByCountry() {
	var country = $('#AddressEntryCountryId option:selected').text();
	hideAllZonesExceptOne(country);
}

function hideAllZonesExceptOne(country) {
	$('#AddressEntryZoneId optgroup').hide();
	$('#AddressEntryZoneId optgroup[label="' + country + '"]').show();
}
