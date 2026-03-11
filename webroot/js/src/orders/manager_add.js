import Scale from '../scale';

function getVal(id) {
	var el = document.querySelector(id);
	return el ? el.value : '';
}

function setVal(id, value) {
	var el = document.querySelector(id);
	if (el) el.value = value;
	return el;
}

function markAutoChanged(el) {
	if (!el) return;
	var parent = el.closest('.mb-3') || el.parentElement;
	if (parent) parent.classList.add('auto-changed');
}

function checkTrackingRequirement() {
	var trackInput = document.querySelector('#OrderInboundTrackingNumber');
	if (!trackInput) return;
	if (getVal('#OrderCarrier') === 'none') {
		trackInput.removeAttribute('required');
	} else {
		trackInput.setAttribute('required', 'required');
	}
}

function checkForApo() {
	var apos = ['AA', 'AE', 'AP'];
	var select = document.querySelector('#OrderDeliveryAddressId');
	if (!select || !select.selectedOptions[0]) return false;
	var state = select.selectedOptions[0].textContent.split(',').pop().trim();
	return apos.includes(state);
}

function clearAddressError() {
	var btn = document.querySelector('#add-order');
	if (btn) {
		btn.disabled = false;
		btn.classList.remove('disabled');
	}
	var select = document.querySelector('#OrderDeliveryAddressId');
	if (select) {
		var group = select.closest('.mb-3') || select.closest('.form-group');
		if (group) group.classList.remove('error');
	}
	var err = document.querySelector('#AddressError');
	if (err) err.remove();
}

function checkWeight() {
	var limit = 1120;
	var poundsVal = getVal('#OrderWeightLb');
	var ouncesVal = getVal('#OrderWeightOz');
	var pounds = poundsVal.length > 0 ? poundsVal : 0;
	var ounces = ouncesVal.length > 0 ? ouncesVal : 0;
	var total = parseInt(pounds * 16, 10) + parseInt(ounces, 10);
	if (total >= limit) {
		toggleFedexOnly('on', true);
		return true;
	} else {
		toggleFedexOnly('off', false);
		return false;
	}
}

function checkSize(changedEl) {
	var lower = 108;
	var upper = 130;
	var fedexLower = 130;
	var fedexUpper = 165;
	var lengthVal = getVal('#OrderLength');
	var widthVal = getVal('#OrderWidth');
	var depthVal = getVal('#OrderDepth');
	var length = lengthVal.length > 0 ? lengthVal : 0;
	var width = widthVal.length > 0 ? widthVal : 0;
	var depth = depthVal.length > 0 ? depthVal : 0;

	if (changedEl) {
		var id = changedEl.id;
		switch (id) {
			case 'OrderLength': length = changedEl.value; break;
			case 'OrderWidth': width = changedEl.value; break;
			case 'OrderDepth': depth = changedEl.value; break;
		}
	}

	var elMc = document.querySelector('#OrderMailClass');
	var mc = elMc ? elMc.value : '';
	var total = parseInt(length, 10) + parseInt(width * 2, 10) + parseInt(depth * 2, 10);

	if (total > lower && total < upper) {
		if (mc === 'priority' && elMc) {
			elMc.value = 'parcel';
			markAutoChanged(elMc);
		}
	} else if (total > fedexLower && total < fedexUpper) {
		toggleFedexOnly('on', true);
		return true;
	} else {
		toggleFedexOnly('off', false);
		return false;
	}
}

function toggleFedexOnly(action, auto) {
	var elMc = document.querySelector('#OrderMailClass');
	if (!elMc) return;

	if (auto) markAutoChanged(elMc);

	if (action === 'on') {
		Array.from(elMc.options).forEach(function(opt) {
			opt.disabled = (opt.value !== 'fedex');
		});
		elMc.value = 'fedex';
		if (checkForApo() === true) {
			clearAddressError();
			setAddressError();
		}
	} else {
		clearAddressError();
		Array.from(elMc.options).forEach(function(opt) { opt.disabled = false; });
		if (elMc.value === 'fedex') {
			var defaultOpt = elMc.querySelector('option.customerDefault');
			if (defaultOpt) elMc.value = defaultOpt.value;
		}
	}
}

function setAddressError() {
	var btn = document.querySelector('#add-order');
	if (btn) {
		btn.disabled = true;
		btn.classList.add('disabled');
	}
	var select = document.querySelector('#OrderDeliveryAddressId');
	if (select) {
		var group = select.closest('.mb-3') || select.closest('.form-group');
		if (group) group.classList.add('error');
	}
	var form = document.querySelector('#OrderManagerAddForm') || document.querySelector('#ManagerAddForm');
	var customerId = '';
	if (form) {
		var action = form.getAttribute('action') || '';
		customerId = action.split('/').pop();
	}
	var span = document.createElement('span');
	span.className = 'form-text';
	span.id = 'AddressError';
	span.innerHTML = 'A US address is required for FedEx shipments. Please select one or add an address for this customer <a href="/manager/customers/' + customerId + '/address/add">here.</a>';
	if (select) select.parentNode.insertBefore(span, select.nextSibling);
}

function applyInsurance(insuranceText, elIns, insuranceDefault) {
	if (insuranceText.charAt(0) === '$') {
		var amount = parseFloat(insuranceText.substring(1).replace(',', '')).toFixed(2);
		if (elIns) {
			elIns.value = amount;
			markAutoChanged(elIns);
		}
	} else if (elIns) {
		elIns.value = insuranceDefault;
		markAutoChanged(elIns);
	}
}

document.addEventListener('DOMContentLoaded', function() {
	var managerAddForm = document.querySelector('#ManagerAddForm');
	if (!managerAddForm) return;

	var elIns = document.querySelector('#OrderInsuranceCoverage');
	var insuranceDefault = elIns ? elIns.value : '';
	var elMc = document.querySelector('#OrderMailClass');
	var customRequestId = document.querySelector('#CustomPackageRequestCustomOrdersId');

	// Mark current selected mail class as customer default
	if (elMc && elMc.selectedOptions[0]) {
		elMc.selectedOptions[0].classList.add('customerDefault');
	}

	// Customer selection → load addresses
	var customerSelect = managerAddForm.querySelector('#OrderCustomersId');
	if (customerSelect) {
		customerSelect.addEventListener('change', function() {
			var id = this.value;
			fetch('/manager/customers/' + id + '/addresses', { headers: { 'Accept': 'application/json' } })
				.then(function(r) { return r.json(); })
				.then(function(data) {
					var html = '';
					for (var key in data.addresses) {
						html += "<option value='" + key + "'>" + data.addresses[key] + "</option>";
					}
					['#OrderDefaultAddressId', '#OrderBillingAddressId'].forEach(function(sel) {
						var el = document.querySelector(sel);
						if (el) el.innerHTML = html;
					});
				})
				.catch(function() { alert('Unable to load Addresses from server.'); });

			fetch('/manager/customers/' + id + '/shippingAddresses', { headers: { 'Accept': 'application/json' } })
				.then(function(r) { return r.json(); })
				.then(function(data) {
					var html = '';
					for (var key in data.shippingAddresses) {
						html += "<option value='" + key + "'>" + data.shippingAddresses[key] + "</option>";
					}
					var el = document.querySelector('#OrderShippingAddressId');
					if (el) el.innerHTML = html;
				})
				.catch(function() { alert('Unable to load Shipping Addresses from server.'); });
		});
	}

	// Carrier change → toggle tracking requirement
	var carrierSelect = managerAddForm.querySelector('#OrderCarrier');
	if (carrierSelect) carrierSelect.addEventListener('change', checkTrackingRequirement);
	checkTrackingRequirement();

	// Inbound tracking number → auto-fill from custom requests table
	var trackInput = managerAddForm.querySelector('#OrderInboundTrackingNumber');
	if (trackInput) {
		trackInput.addEventListener('change', function() {
			var trackingId = this.value;
			document.querySelectorAll('.custom-requests tr').forEach(function(row) {
				var inboundLink = row.querySelector('.inbound a');
				if (!inboundLink) return;
				var requestId = inboundLink.textContent.trim();
				if (trackingId === requestId) {
					var mailclassCell = row.querySelector('.mailclass');
					var mailclass = mailclassCell ? mailclassCell.textContent.trim().toLowerCase() : '';
					if (elMc && elMc.value !== mailclass) {
						elMc.value = mailclass;
						markAutoChanged(elMc);
					}
					var insuranceCell = row.querySelector('.insurance');
					var insurance = insuranceCell ? insuranceCell.textContent.trim() : '';
					applyInsurance(insurance, elIns, insuranceDefault);
					if (customRequestId) customRequestId.value = row.id.substring(2);
				}
			});
		});
	}

	// Click mailclass link → set mail class
	managerAddForm.querySelectorAll('.mailclass a').forEach(function(link) {
		link.addEventListener('click', function(e) {
			e.preventDefault();
			var mailclass = this.textContent.trim().toLowerCase();
			if (elMc) {
				elMc.value = mailclass;
				markAutoChanged(elMc);
			}
		});
	});

	// Click insurance link → set insurance
	managerAddForm.querySelectorAll('.insurance a').forEach(function(link) {
		link.addEventListener('click', function(e) {
			e.preventDefault();
			applyInsurance(this.textContent.trim(), elIns, insuranceDefault);
		});
	});

	// Click inbound link → fill tracking + mailclass + insurance
	managerAddForm.querySelectorAll('.inbound a').forEach(function(link) {
		link.addEventListener('click', function(e) {
			e.preventDefault();
			var inbound = this.textContent.trim();
			var trackEl = setVal('#OrderInboundTrackingNumber', inbound);
			markAutoChanged(trackEl);

			var row = this.closest('tr');
			if (!row) return;

			var mailclassCell = row.querySelector('.mailclass');
			if (mailclassCell && elMc) {
				elMc.value = mailclassCell.textContent.trim().toLowerCase();
				markAutoChanged(elMc);
			}

			var insuranceCell = row.querySelector('.insurance');
			if (insuranceCell) applyInsurance(insuranceCell.textContent.trim(), elIns, insuranceDefault);
		});
	});

	// Click "Use" button → fill tracking + mailclass + insurance + custom request ID
	managerAddForm.querySelectorAll('.btn-use').forEach(function(btn) {
		btn.addEventListener('click', function(e) {
			e.preventDefault();
			var row = this.closest('tr');
			if (!row) return;

			var inboundCell = row.querySelector('.inbound');
			var inbound = inboundCell ? inboundCell.textContent.trim() : '';
			var trackEl = setVal('#OrderInboundTrackingNumber', inbound);
			markAutoChanged(trackEl);

			var mailclassCell = row.querySelector('.mailclass');
			if (mailclassCell && elMc) {
				elMc.value = mailclassCell.textContent.trim().toLowerCase();
				markAutoChanged(elMc);
			}

			var insuranceCell = row.querySelector('.insurance');
			if (insuranceCell) applyInsurance(insuranceCell.textContent.trim(), elIns, insuranceDefault);

			if (customRequestId) customRequestId.value = row.id.substring(2);
		});
	});

	// Dimension changes → check size/weight
	['#OrderLength', '#OrderWidth', '#OrderDepth'].forEach(function(sel) {
		var el = managerAddForm.querySelector(sel);
		if (el) {
			el.addEventListener('change', function() {
				if (checkSize(this) === false) checkWeight();
			});
		}
	});

	// Weight changes → check weight/size
	['#OrderWeightLb', '#OrderWeightOz'].forEach(function(sel) {
		var el = managerAddForm.querySelector(sel);
		if (el) {
			el.addEventListener('change', function() {
				if (checkWeight() === false) checkSize();
			});
		}
	});

	// Delivery address / mail class change → APO validation
	['#OrderDeliveryAddressId', '#OrderMailClass'].forEach(function(sel) {
		var el = managerAddForm.querySelector(sel);
		if (el) {
			el.addEventListener('change', function() {
				clearAddressError();
				if (getVal('#OrderMailClass') === 'fedex' && checkForApo() === true) {
					setAddressError();
				}
			});
		}
	});
});

// Scale integration
if (document.querySelector('#ManagerAddForm')) {
	var scaleReadTimerId;
	var getScaleWeight;
	var scaleId = localStorage.getItem('Settings.local.scale_id');

	var webCallback = function(response, error) {
		if (error) return console.error(error);
		var lbInput = document.querySelector('#OrderWeightLb');
		var ozInput = document.querySelector('#OrderWeightOz');
		if (lbInput) lbInput.value = response.pounds;
		if (ozInput) ozInput.value = response.ounces;
		var readScale = document.querySelector('#ReadScale');
		if (readScale) readScale.classList.add('auto-changed-link');
	};

	if (scaleId == 'legacy') {
		getScaleWeight = function() {
			var scale = new Scale();
			scale.read(webCallback);
		};
	} else {
		var webRequest = new XMLHttpRequest();
		var webUrl = 'https://' + scaleId + '.aposcales.autoploy.com:1880/scale/read';
		webRequest.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				webCallback(JSON.parse(this.responseText), false);
			}
		};
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

	document.addEventListener('DOMContentLoaded', function() {
		var scaleSwitch = document.querySelector('#ScaleToggle');
		if (!scaleSwitch) return;

		function scaleToggle(state) {
			state = state || 'Off';
			if (state === 'On') {
				scaleSwitch.textContent = 'On';
				scaleSwitch.classList.remove('btn-danger');
				scaleSwitch.classList.add('btn-success');
				startScaleReading();
			} else {
				scaleSwitch.textContent = 'Off';
				scaleSwitch.classList.remove('btn-success');
				scaleSwitch.classList.add('btn-danger');
				stopScaleReading();
			}
		}

		var globalScaleStatus = localStorage.getItem('Settings.local.scale_status');
		scaleToggle(globalScaleStatus);

		var readScaleBtn = document.querySelector('#ReadScale');
		if (readScaleBtn) {
			readScaleBtn.addEventListener('click', function(e) {
				e.preventDefault();
				getScaleWeight();
			});
		}

		scaleSwitch.addEventListener('click', function(e) {
			e.preventDefault();
			var newStatus = (scaleSwitch.textContent === 'On' ? 'Off' : 'On');
			scaleToggle(newStatus);
		});
	});
}
