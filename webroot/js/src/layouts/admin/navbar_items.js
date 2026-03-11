import * as bootstrap from 'bootstrap';

document.addEventListener('DOMContentLoaded', function() {
	loadSettings();

	document.querySelectorAll('.settings-dropdown .settings-btn').forEach(function(btn) {
		btn.addEventListener('click', function(e) { e.preventDefault(); });
	});

	document.querySelectorAll('.settings-dropdown #scale-status-on, .settings-dropdown #scale-status-off').forEach(function(radio) {
		radio.addEventListener('change', function() {
			saveSettings();
			loadSettings();
		});
	});

	document.querySelectorAll('.settings-dropdown [id^=scale-id-]').forEach(function(radio) {
		radio.addEventListener('change', function() {
			saveSettings();
			loadSettings();
		});
	});
});

function loadSettings() {
	settingInputs().forEach(function(item) {
		var input = document.getElementById(item.key);
		var value = localStorage.getItem(item.key);
		if (!value) {
			value = item.defaultValue;
			localStorage.setItem(item.key, value);
		}

		if (item.key == 'Settings.local.scale_status') {
			var onRadio = document.querySelector('#scale-status-on');
			var offRadio = document.querySelector('#scale-status-off');
			if (value === 'Off') {
				if (onRadio) onRadio.checked = false;
				if (offRadio) offRadio.checked = true;
			}
		} else if (item.key == 'Settings.local.scale_id') {
			document.querySelectorAll('[id^=scale-id-]').forEach(function(r) { r.checked = false; });
			var target = document.querySelector('#scale-id-' + value);
			if (target) target.checked = true;
		}

		if (input) {
			input.value = value;
			input.addEventListener('keyup', saveSettings);
		}
	});
}

function saveSettings(event) {
	if (event && event.keyCode == 13) {
		var toggle = document.querySelector('.settings-dropdown .dropdown-toggle');
		if (toggle) {
			var dropdown = bootstrap.Dropdown.getOrCreateInstance(toggle);
			dropdown.toggle();
		}
	}
	settingInputs().forEach(function(item) {
		var input = document.getElementById(item.key);
		if (!input) return;
		var checkedRadio = input.querySelector('input[type=radio]:checked');
		if (checkedRadio) {
			input.value = checkedRadio.value;
		}
		localStorage.setItem(item.key, input.value);
	});
}

function settingInputs() {
	return [
		{ key: 'Settings.local.printer_ip', defaultValue: '10.1.10.209' },
		{ key: 'Settings.local.scale_id', defaultValue: 'one' },
		{ key: 'Settings.local.scale_status', defaultValue: 'On' }
	];
}
