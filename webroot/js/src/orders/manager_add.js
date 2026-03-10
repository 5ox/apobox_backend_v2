import $ from 'jquery';
import Scale from '../scale';

/**
 * checkTrackingRequirement
 *
 * @return void
 */
function checkTrackingRequirement() {
	if ($('#OrderCarrier').val() === 'none') {
		$("#OrderInboundTrackingNumber").removeAttr('required');
	} else {
		$("#OrderInboundTrackingNumber").attr('required', 'required');
	}
}

/**
 * Checks for a US address in the selected delivery address.
 *
 * @return bool true if the the selected address is an AA, AE, or AP
 */
function checkForApo() {
	var apos = ['AA', 'AE', 'AP'];
	var state = $.trim($('#OrderDeliveryAddressId option:selected').text().split(',').pop());
	return ($.inArray(state, apos) !== -1);
}

/**
 * Clears any delivery address errors and enables the submit button
 *
 * @return void
 */
function clearAddressError() {
		$('#add-order').prop('disabled', false).removeClass('disabled');
		$('#OrderDeliveryAddressId').closest('.form-group').removeClass('error');
		$('#AddressError').remove();
}

/**
 * If the total weight is over 70 lbs, set the mail class to `fedex`
 *
 * @return bool true if fedex only
 */
function checkWeight() {
	var limit = 1120;
	var pounds = $('#OrderWeightLb').val().length > 0 ? $('#OrderWeightLb').val() : 0;
	var ounces = $('#OrderWeightOz').val().length > 0 ? $('#OrderWeightOz').val() : 0;
	var total = parseInt(pounds * 16, 10) + parseInt(ounces, 10);
	var elMc = $('#OrderMailClass');
	if (total >= limit) {
		toggleFedexOnly('on', true);
		return true;
	} else {
		toggleFedexOnly('off', false);
		return false;
	}
}

/**
 * Sets mail class to `parcel` if package Length + (Width x 2) + (Height x 2)
 * is 108" or greater but under 130". Set it to `fedex` if over 130" using
 * Length + (Width x 2) + (Height x 2).
 *
 * @return bool true if fedex only
 */
function checkSize() {
	var lower = 108;
	var upper = 130;
	var fedexLower = 130;
	var fedexUpper = 165;
	var length = $('#OrderLength').val().length > 0 ? $('#OrderLength').val() : 0;
	var width = $('#OrderWidth').val().length > 0 ? $('#OrderWidth').val() : 0;
	var depth = $('#OrderDepth').val().length > 0 ? $('#OrderDepth').val() : 0;
	var elMc = $('#OrderMailClass');
	var mc = elMc.val();
	var id = $(this).attr('id');
	switch (id) {
		case 'OrderLength':
					length = $(this).val();
					break;
		case 'OrderWidth':
					width = $(this).val();
					break;
		case 'OrderDepth':
					depth = $(this).val();
					break;
	}
	var total = parseInt(length, 10) + parseInt(width * 2, 10) + parseInt(depth * 2, 10);
	if (total > lower && total < upper) {
		if (mc === 'priority') {
			$('#OrderMailClass').val('parcel').parents('div:eq(1)').addClass('auto-changed');
		}
	} else if (total > fedexLower && total < fedexUpper) {
		toggleFedexOnly('on', true);
		return true;
	} else {
		toggleFedexOnly('off', false);
		return false;
	}
}

/**
 * Toggles the mail class between FedEx only and the USPS mail classes, and
 * checks for a valid US address if switching to FedEx.
 *
 * @param string $action either `on` or `off`
 * @param bool $auto True to fire an auto-change notification
 * @return void
 */
function toggleFedexOnly(action, auto) {
	var elMc = $('#OrderMailClass');
	if (auto) {
		elMc.parents('div:eq(1)').addClass('auto-changed');
	}
	if (action === 'on') {
		elMc.find('option[value!="fedex"]').attr('disabled', true);
		elMc.val(elMc.find('option[value="fedex"]').val());
		if (checkForApo() === true) {
			clearAddressError();
			setAddressError();
		}
	} else {
		clearAddressError();
		elMc.find('option').removeAttr('disabled');
		if (elMc.find('option[value="fedex"]:selected').length) {
			elMc.val(elMc.find('option.customerDefault').val());
		}
	}
}

/**
 * Sets a delivery address error and disables the submit button
 *
 * @return void
 */
function setAddressError() {
		$('#add-order').prop('disabled', true).addClass('disabled');
		$('#OrderDeliveryAddressId').closest('.form-group').addClass('error');
		var customerId = $('#OrderManagerAddForm', '#ManagerAddForm').attr('action').split('/').pop();
		$('<span class="help-block" id="AddressError">A US address is required for FedEx shipments. Please select one or add an address for this customer <a href="/manager/customers/' + customerId + '/address/add">here.</a></span>').insertAfter('#OrderDeliveryAddressId');
}

$(document).ready(function() {
	var managerAddForm = $('#ManagerAddForm');
	var insuranceDefault = $('#OrderInsuranceCoverage').val();
	$('#OrderMailClass').find('option:selected').addClass('customerDefault');

	$('#OrderCustomersId', managerAddForm).change(function() {
		var id = $(this).val();
		$.ajax({
			dataType: "json",
			url: '/manager/customers/:id/addresses'.replace(':id', id),
			aysnc: false,
			success: function( data ) {
				var options = [];
				$.each(data.addresses, function(key, val) {
					options.push( "<option value='" + key + "'>" + val + "</option>" );
				});
				$("#OrderDefaultAddressId, #OrderBillingAddressId").html(options.join( "" ));
			},
			error: function(jqXHR, textStatus, error) {
				alert('Unable to load Addresses from server.');
			},
		});
		$.ajax({
			dataType: "json",
			url: '/manager/customers/:id/shippingAddresses'.replace(':id', id),
			aysnc: false,
			success: function( data ) {
				var options = [];
				$.each(data.shippingAddresses, function(key, val) {
					options.push( "<option value='" + key + "'>" + val + "</option>" );
				});
				$("#OrderShippingAddressId").html(options.join( "" ));
			},
			error: function(jqXHR, textStatus, error) {
				alert('Unable to load Shipping Addresses from server.');
			},
		});
	});

	$('#OrderCarrier', managerAddForm).change(function() {checkTrackingRequirement();});
	checkTrackingRequirement();

	var elMc = $('#OrderMailClass');
	var elIns = $('#OrderInsuranceCoverage');
	var customRequestId = $('#CustomPackageRequestCustomOrdersId');

	$('#OrderInboundTrackingNumber', managerAddForm).change(function() {
		var trackingId = $(this).val();
		$('.custom-requests tr').each(function() {
			var requestId = $(this).find('.inbound a').text().trim();
			if (trackingId === requestId) {
				var mailclass = $(this).children('.mailclass').text().trim().toLowerCase();
				if (elMc.find(':selected').val() !== mailclass) {
					elMc.val(mailclass).parents('div:eq(1)').addClass('auto-changed');
				}
				var insurance = $(this).children('.insurance').text().trim();
				if (insurance.charAt(0) === '$') {
					var amount = insurance.substring(1);
					amount = parseFloat(amount.replace(',','')).toFixed(2);
					elIns.val(amount).parents('div:eq(1)').addClass('auto-changed');
				} else {
					elIns.val(insuranceDefault).parents('div:eq(1)').addClass('auto-changed');
				}
				var customId = $(this).attr('id').substring(2);
				customRequestId.val(customId);
			}
		});
	});

	$('.mailclass a', managerAddForm).on('click', function(e) {
		e.preventDefault();
		var mailclass = $(this).text().trim().toLowerCase();
		elMc.val(mailclass).parents('div:eq(1)').addClass('auto-changed');
	});

	$('.insurance a', managerAddForm).on('click', function(e) {
		e.preventDefault();
		var insurance = $(this).text().trim();
		if (insurance.charAt(0) === '$') {
			var amount = insurance.substring(1);
			amount = parseFloat(amount.replace(',','')).toFixed(2);
			elIns.val(amount).parents('div:eq(1)').addClass('auto-changed');
		} else {
			elIns.val(insuranceDefault).parents('div:eq(1)').addClass('auto-changed');
		}
	});

	$('.inbound a', managerAddForm).on('click', function(e) {
		e.preventDefault();
		var el = $(this);
		var inbound = $(this).text().trim();
		$('#OrderInboundTrackingNumber').val(inbound).parents('div:eq(1)').addClass('auto-changed');
		var mailclass = el.parent().nextAll('.mailclass').text().trim().toLowerCase();
		elMc.val(mailclass).parents('div:eq(1)').addClass('auto-changed');

		var insurance = el.parent().nextAll('.insurance').text().trim();
		if (insurance.charAt(0) === '$') {
			var amount = insurance.substring(1);
			amount = parseFloat(amount.replace(',','')).toFixed(2);
			elIns.val(amount).parents('div:eq(1)').addClass('auto-changed');
		} else {
			elIns.val(insuranceDefault).parents('div:eq(1)').addClass('auto-changed');
		}
	});

	$('.btn-use', managerAddForm).on('click', function(e) {
		e.preventDefault();
		var el = $(this);
		var inbound = el.parent().prevAll('.inbound').text().trim();
		$('#OrderInboundTrackingNumber').val(inbound).parents('div:eq(1)').addClass('auto-changed');
		var mailclass = el.parent().prevAll('.mailclass').text().trim().toLowerCase();
		elMc.val(mailclass).parents('div:eq(1)').addClass('auto-changed');

		var insurance = el.parent().prevAll('.insurance').text().trim();
		if (insurance.charAt(0) === '$') {
			var amount = insurance.substring(1);
			amount = parseFloat(amount.replace(',','')).toFixed(2);
			elIns.val(amount).parents('div:eq(1)').addClass('auto-changed');
		} else {
			elIns.val(insuranceDefault).parents('div:eq(1)').addClass('auto-changed');
		}

		var customId = el.parent().parent().attr('id').substring(2);
		customRequestId.val(customId);
	});

	$('#OrderLength, #OrderWidth, #OrderDepth', managerAddForm).change(function() {
		if (checkSize() === false) {
			checkWeight();
		}
	});

	$('#OrderWeightLb, #OrderWeightOz', managerAddForm).change(function() {
		if (checkWeight() === false) {
			checkSize();
		}
	});

	// if the delivery address is millitary (AA, AE, or AP) and the selected mail
	// class is `fedex` display an error.
	$('#OrderDeliveryAddressId, #OrderMailClass', managerAddForm).change(function() {
		clearAddressError();
		if ($('#OrderMailClass').val() === 'fedex' && checkForApo() === true) {
			setAddressError();
		}
	});
});

if ($('#ManagerAddForm').length > 0) {
	var scaleReadTimerId;
	var getScaleWeight;
	var scaleId = localStorage.getItem('Settings.local.scale_id');
	var webCallback = function(response, error) {
		if (error) {
			return console.error(error);
		}

		$('#OrderWeightLb').val(response.pounds);
		$('#OrderWeightOz').val(response.ounces);
		$('#ReadScale').addClass('auto-changed-link');
	};

	if (scaleId == 'legacy') {
		getScaleWeight = function(poundsInput, ouncesInput) {
			var scale = new Scale();
			scale.read(webCallback);
		}
	} else {
		var webRequest = new XMLHttpRequest();
		var webUrl = 'https://' + scaleId + '.aposcales.autoploy.com:1880/scale/read';
		webRequest.onreadystatechange=function() {
			if (this.readyState == 4 && this.status == 200) {
				webCallback(JSON.parse(this.responseText), false);
			}
		}
		getScaleWeight = function() {
			webRequest.open('GET', webUrl);
			webRequest.send();
		};
	}

	function startScaleReading() {
		scaleReadTimerId = window.setInterval(getScaleWeight, 1000);
	}
	function stopScaleReading() {
		clearInterval(scaleReadTimerId);
	}

	$(document).ready(function() {
		var scaleSwitch = $('#ScaleToggle');
		function scaleToggle(state = 'Off') {
			if (state === 'On') {
				scaleSwitch.html('On').removeClass('btn-danger').addClass('btn-success');
				startScaleReading();
			} else {
				scaleSwitch.html('Off').removeClass('btn-success').addClass('btn-danger');
				stopScaleReading();
			}
		}

		var globalScaleStatus = localStorage.getItem('Settings.local.scale_status');
		scaleToggle(globalScaleStatus);

		$('#ReadScale').on('click', function(e) {
			e.preventDefault();
			getScaleWeight();
		});

		scaleSwitch.on('click', function(e) {
			e.preventDefault();
			var newStatus = (scaleSwitch.html() === 'On' ? 'Off' : 'On');
			scaleToggle(newStatus);
		});
	});
}
