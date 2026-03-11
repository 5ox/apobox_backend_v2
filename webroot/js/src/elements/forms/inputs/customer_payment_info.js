/**
 * Luhn algorithm for credit card validation.
 */
function isValidCreditCard(number) {
	var cleaned = number.replace(/[\s-]/g, '');
	if (!/^\d+$/.test(cleaned) || cleaned.length < 13 || cleaned.length > 19) return false;

	var sum = 0;
	var alternate = false;
	for (var i = cleaned.length - 1; i >= 0; i--) {
		var n = parseInt(cleaned[i], 10);
		if (alternate) {
			n *= 2;
			if (n > 9) n -= 9;
		}
		sum += n;
		alternate = !alternate;
	}
	return sum % 10 === 0;
}

function setupPaymentValidation() {
	var forms = document.querySelectorAll('#CustomerEditPartialForm, #CustomerPayManuallyForm, #CustomerManagerEditPaymentInfoForm');

	forms.forEach(function(form) {
		form.addEventListener('submit', function(e) {
			var ccInput = form.querySelector('[name="data[Customer][cc_number]"]');
			var cvvInput = form.querySelector('[name="data[Customer][cc_cvv]"]');
			var isValid = true;

			// Clear previous errors
			form.querySelectorAll('.form-text.text-danger').forEach(function(el) { el.remove(); });
			form.querySelectorAll('.mb-3.has-error').forEach(function(el) { el.classList.remove('has-error'); });

			// Validate credit card
			if (ccInput) {
				if (!ccInput.value.trim()) {
					showError(ccInput, 'This field is required.');
					isValid = false;
				} else if (!isValidCreditCard(ccInput.value)) {
					showError(ccInput, "This doesn't look like a valid credit card.");
					isValid = false;
				}
			}

			// Validate CVV
			if (cvvInput) {
				var cvv = cvvInput.value.trim();
				if (!cvv) {
					showError(cvvInput, 'This field is required.');
					isValid = false;
				} else if (!/^\d{3,4}$/.test(cvv)) {
					showError(cvvInput, 'A CVV Code is 3-4 digits.');
					isValid = false;
				}
			}

			if (!isValid) e.preventDefault();
		});
	});

	// Payment button loading state
	var payBtn = document.querySelector('#payment-btn');
	if (payBtn) {
		payBtn.addEventListener('click', function() {
			var form = document.querySelector('#CustomerPayManuallyForm');
			if (form && form.checkValidity()) {
				this.classList.add('disabled');
				this.textContent = 'Please wait for payment processing...';
			}
		});
	}
}

function showError(input, message) {
	var group = input.closest('.mb-3') || input.closest('.form-group');
	if (group) group.classList.add('has-error');
	var span = document.createElement('span');
	span.className = 'form-text text-danger';
	span.textContent = message;
	input.parentNode.insertBefore(span, input.nextSibling);
}

document.addEventListener('DOMContentLoaded', setupPaymentValidation);
