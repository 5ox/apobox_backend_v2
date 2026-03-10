import $ from 'jquery';

$(document).ready(function() {
	loadSettings();
	$('.settings-btn', '.settings-dropdown').on('click', function(e) {
		e.preventDefault();
	});

	$('#scale-status-on, #scale-status-off', '.settings-dropdown').on('change', function(e) {
		saveSettings();
		loadSettings();
	});
	$('[id^=scale-id-]', '.settings-dropdown').on('change', function(e) {
		saveSettings();
		loadSettings();
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
			if (value === 'Off') {
				$('#scale-status-on').prop('checked', false);
				$('#scale-status-off').prop('checked', true);
			}
		} else if (item.key == 'Settings.local.scale_id') {
			$('[id^=scale-id-]').prop('checked', false);
			$('#scale-id-' + value).prop('checked', true);
		}

		$(input).val(value);
		$(input).on('keyup', saveSettings);
	});
}

function saveSettings(event) {
	if (event && event.keyCode == 13) {
		$('.dropdown-toggle', '.settings-dropdown').dropdown('toggle');
	}
	settingInputs().forEach(function(item) {
		var input = document.getElementById(item.key);
		if ($(input).find(':radio').length > 0) {
			input.value = $(input).find(':radio:checked').val();
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
