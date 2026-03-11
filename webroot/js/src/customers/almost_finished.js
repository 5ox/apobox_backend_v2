document.addEventListener('DOMContentLoaded', function() {
	var container = document.querySelector('#AlmostFinishedAddress');
	if (!container) return;
	var select = container.querySelector('#CustomerCustomersDefaultAddressId');
	if (!select) return;

	select.addEventListener('change', toggleAddressForm);
	toggleAddressForm.call(select);
});

function toggleAddressForm() {
	var fieldset = document.querySelector('#AddressFormFieldset');
	if (!fieldset) return;

	if (this.value === 'new') {
		fieldset.querySelectorAll('.not-required').forEach(function(el) {
			el.required = true;
			el.classList.remove('not-required');
		});
		fieldset.style.display = '';
	} else {
		fieldset.style.display = 'none';
		fieldset.querySelectorAll('[required]').forEach(function(el) {
			el.classList.add('not-required');
			el.required = false;
		});
	}
}
