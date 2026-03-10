import $ from 'jquery';

var toggleOutboundTracking = function() {
	if ($('#OrderOrdersStatus').val() == '3') {
		showTrackingNumber();
	} else {
		$('#OrderUspsTrackNum').removeAttr('required').hide();
	}
};

$(document).ready(function() {
	toggleOutboundTracking();
	$('#OrderOrdersStatus').on('change', function() {toggleOutboundTracking();});

	$('#usps').on('click', function(e) {
		e.preventDefault();
		var print = true;
		var reprint = false;
		if ($(this).html() === 'Printed') {
			print = confirm('Are you sure you want to reprint this label?');
			reprint = print;
		}
		if (print === true) {
			var label = document.querySelector('.label-xml');
			if (reprint === true) {
				var promptYes = label.innerHTML.replace('Prompt="No"', 'Prompt="Yes"');
				label.innerHTML = promptYes;
			}
			label.select();
			document.designMode = 'on';
			var success = document.execCommand('copy');
			document.designMode = 'off';
			if (success) {
				$(this).html('Printed');
			}
			setupToShip();
		}
	});

	$('#fedex').on('click', function(e) {
		e.preventDefault();
		var print = true;
		var reprint = false;
		var url = $(this).attr('href');
		if ($(this).html() === 'Printed') {
			print = confirm('Are you sure you want to reprint this label?');
			reprint = print;
			url = url + '/reprint';
		}
		if (print === true) {
			$.ajax({
				url: url
			})
			.done(function(data) {
				if (data) {
					printLabel(data);
					$('#fedex').html('Printed');
					setupToShip();
				} else {
					$('#fedex').html('Printing Error');
				}
			})
			.fail(function(xhr) {
				$('#fedex').html('Printing Error');
			});
		}
	});

	$('.zpl-btn').on('click', function(e) {
		e.preventDefault();
		var url = $(this).attr('href');
		$.ajax({
			url: url
		})
			.done(function(data) {
				if (data) {
					printLabel(data);
					$('.zpl-btn').html('Printed');
				} else {
					$('.zpl-btn').html('Printing Error');
				}
			})
			.fail(function(xhr) {
				$('.zpl-btn').html('Printing Error');
			});
	});
});

function setupToShip() {
	$('#OrderOrdersStatus').val(3).addClass('auto-changed');
	$('#OrderNotifyCustomer').prop( "checked", true ).parent().addClass('auto-changed');
	showTrackingNumber();
}

function showTrackingNumber() {
	$('#OrderUspsTrackNum').show().attr('required', 'required').focus();
}

/**
 * Print labels using IP printers listed below.
 */
function printLabel(data) {
	var printers = [
		{
			"name": "GX420t",
			"ip": localStorage.getItem('Settings.local.printer_ip'),
		},
	];

	var callback = function(response, error) {
		if (error) {
			return console.error(error);
		}
		console.log('Read response...');
		console.log(response);
		return !error;
	};

	async function postData(url = '', data = {}) {
		const response = await fetch(url, {
			method: 'POST',
			mode: 'no-cors',
			cache: 'no-cache',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: data
		});
		return response;
	}

	var printCallbackURL = function(device) {
		var url = "http://" + device.ip + "/pstprnt";

		postData(url, data).then(response => {
			callback(response);
		});

		return true;
	};

	var printerFinder = function(printer) {
		if (printer.ip) {
			return printCallbackURL(printer);
		} else {
			console.log('Printer must have property: ip');
		}
	};

	return printers.forEach(printerFinder);
}
