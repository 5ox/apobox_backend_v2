function init() {
	var portSelect = document.getElementById('port');
	var callback = function(ports) {
		for(var i = 0; i < ports.length; i++) {
			if (i === 0) {
				var defaultPort = ports[i];
				chrome.storage.local.get('port', function(options) {
					if (!options.port) {
						chrome.storage.local.set({port: defaultPort.path});
					}
				});
			}

			var opt = document.createElement("option");
			opt.value = ports[i].path;
			opt.innerHTML = ports[i].path;
			portSelect.appendChild(opt);
		}
	};
	chrome.serial.getDevices(callback);

	var ids = ['port', 'bitrate', 'dataBits', 'parityBit', 'stopBits'];
	var inputs = [];
	for (var i = 0; i < ids.length; i++) {
		document.getElementById(ids[i]).addEventListener("change", function(event) {
			var element = event.srcElement;
			var value = element.options[element.selectedIndex].value;
			var obj = {};
			obj[element.id] = value;
			chrome.storage.local.set(obj, function() { console.log(obj); });
		});
	}
}

window.setTimeout(init, 10);
