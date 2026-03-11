document.addEventListener('DOMContentLoaded', function() {
	var addressSelect = document.querySelector('#PayManuallyAddress #CustomerCustomersDefaultAddressId');
	if (!addressSelect) return;

	function handleAddressChange() {
		var fieldset = document.querySelector('#AddressFormFieldset');
		if (!fieldset) return;

		if (addressSelect.value === 'custom') {
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

	addressSelect.addEventListener('change', handleAddressChange);
	handleAddressChange();
});
