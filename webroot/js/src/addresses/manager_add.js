document.addEventListener('DOMContentLoaded', function() {
	hideAllZonesExceptOne('United States');

	var countrySelect = document.querySelector('#AddressEntryCountryId');
	if (countrySelect) {
		countrySelect.addEventListener('change', filterByCountry);
	}
});

function filterByCountry() {
	var countrySelect = document.querySelector('#AddressEntryCountryId');
	var country = countrySelect.selectedOptions[0].textContent;
	hideAllZonesExceptOne(country);
}

function hideAllZonesExceptOne(country) {
	var zoneSelect = document.querySelector('#AddressEntryZoneId');
	if (!zoneSelect) return;
	zoneSelect.querySelectorAll('optgroup').forEach(function(group) {
		group.style.display = 'none';
	});
	var match = zoneSelect.querySelector('optgroup[label="' + country + '"]');
	if (match) match.style.display = '';
}
