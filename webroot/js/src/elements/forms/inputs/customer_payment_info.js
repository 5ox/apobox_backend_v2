import $ from 'jquery';
import validate from 'jquery-validation';
import 'jquery-validation/dist/additional-methods';

$('#CustomerEditPartialForm,#CustomerPayManuallyForm,#CustomerManagerEditPaymentInfoForm').validate({
	rules: {
		// simple rule, converted to {required:true}
		name: "required",
		// compound rule
		'data[Customer][cc_number]': {
			required: true,
			creditcard: true
		},
		'data[Customer][cc_cvv]': {
			required: true,
			rangelength: [3, 4],
			digits: true
		}
	},
	messages: {
		'data[Customer][cc_number]': {
			creditcard: "This doesn't look like a valid credit card."
		},
		'data[Customer][cc_cvv]': {
			rangelength: "A CVV Code is 3-4 digits.",
			digits: "A CVV code may only consist of digits."
		}
	},
	errorElement: 'span',
	errorClass: 'help-block',
	highlight: function (element) {
		$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
	},
	success: function (element) {
		$(element).closest('.form-group').removeClass('has-error').addClass('has-success');
	}
});

$(document).ready(function() {
	$('#payment-btn').on('click', function(e) {
		if ($('#CustomerPayManuallyForm').valid()) {
			$(this).addClass('disabled').html('Please wait for payment processing...');
		}
	});
});
