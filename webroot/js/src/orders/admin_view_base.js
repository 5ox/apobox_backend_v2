var toggleOutboundTracking = function() {
	var statusSelect = document.querySelector('#OrderOrdersStatus');
	if (!statusSelect) return;
	var trackNum = document.querySelector('#OrderUspsTrackNum');
	if (!trackNum) return;

	if (statusSelect.value == '3') {
		showTrackingNumber();
	} else {
		trackNum.removeAttribute('required');
		trackNum.style.display = 'none';
	}
};

document.addEventListener('DOMContentLoaded', function() {
	toggleOutboundTracking();

	var statusSelect = document.querySelector('#OrderOrdersStatus');
	if (statusSelect) {
		statusSelect.addEventListener('change', toggleOutboundTracking);
	}

	var uspsBtn = document.querySelector('#usps');
	if (uspsBtn) {
		uspsBtn.addEventListener('click', function(e) {
			e.preventDefault();
			var print = true;
			var reprint = false;
			if (this.innerHTML === 'Printed') {
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
					this.innerHTML = 'Printed';
				}
				setupToShip();
			}
		});
	}

	var fedexBtn = document.querySelector('#fedex');
	if (fedexBtn) {
		fedexBtn.addEventListener('click', function(e) {
			e.preventDefault();
			var btn = this;
			var print = true;
			var reprint = false;
			var url = btn.getAttribute('href');
			if (btn.innerHTML === 'Printed') {
				print = confirm('Are you sure you want to reprint this label?');
				reprint = print;
				url = url + '/reprint';
			}
			if (print === true) {
				fetch(url)
					.then(function(response) { return response.text(); })
					.then(function(data) {
						if (data) {
							printLabel(data);
							btn.innerHTML = 'Printed';
							setupToShip();
						} else {
							btn.innerHTML = 'Printing Error';
						}
					})
					.catch(function() {
						btn.innerHTML = 'Printing Error';
					});
			}
		});
	}

	document.querySelectorAll('.zpl-btn').forEach(function(btn) {
		btn.addEventListener('click', function(e) {
			e.preventDefault();
			var url = this.getAttribute('href');
			fetch(url)
				.then(function(response) { return response.text(); })
				.then(function(data) {
					if (data) {
						printLabel(data);
						document.querySelectorAll('.zpl-btn').forEach(function(b) { b.innerHTML = 'Printed'; });
					} else {
						document.querySelectorAll('.zpl-btn').forEach(function(b) { b.innerHTML = 'Printing Error'; });
					}
				})
				.catch(function() {
					document.querySelectorAll('.zpl-btn').forEach(function(b) { b.innerHTML = 'Printing Error'; });
				});
		});
	});
});

function setupToShip() {
	var statusSelect = document.querySelector('#OrderOrdersStatus');
	if (statusSelect) {
		statusSelect.value = '3';
		statusSelect.classList.add('auto-changed');
	}
	var notifyCheckbox = document.querySelector('#OrderNotifyCustomer');
	if (notifyCheckbox) {
		notifyCheckbox.checked = true;
		var parent = notifyCheckbox.parentElement;
		if (parent) parent.classList.add('auto-changed');
	}
	showTrackingNumber();
}

function showTrackingNumber() {
	var trackNum = document.querySelector('#OrderUspsTrackNum');
	if (trackNum) {
		trackNum.style.display = '';
		trackNum.setAttribute('required', 'required');
		trackNum.focus();
	}
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

		postData(url, data).then(function(response) {
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
