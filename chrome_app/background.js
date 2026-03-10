chrome.app.runtime.onLaunched.addListener(function() {
	console.log('Launching');
	setDefaultOptions();
});

chrome.runtime.onSuspend.addListener(function() {
});

chrome.runtime.onInstalled.addListener(function() {
	console.log('Installed');
	setDefaultOptions();
});

function createWindow() {
	console.log('Creating window');
	chrome.app.window.create('window.html', {
		'outerBounds': {
			'width': 300,
			'height': 220
		}
	});
}

function setDefaultOptions() {
	chrome.storage.local.get(null, function(options) {
		console.log('Got storage options');
		console.log(options);
		var newOptions = {};
		//TODO: consolidate with the scale defaults and set at top somewhere.
		if (!options.bitrate) newOptions.bitrate = 9600;
		if (!options.dataBits) newOptions.dataBits = 'seven';
		if (!options.parityBit) newOptions.parityBit = 'even';
		if (!options.stopBits) newOptions.stopBits = 'one';
		if (Object.keys(newOptions).length) {
			console.log('Saving new options');
			console.log(newOptions);
			chrome.storage.local.set(newOptions, createWindow);
		} else {
			createWindow();
		}
	});
}
