/**
 *
 */

var connectionId, path, options;

/**
 * Chrome Serial Default options.
 *
 * Options that are actual Chrome defaults are commented out. Options that are
 * default to this app and not user modifiable are uncommented.
 */
var defaultOptions = {
	//persistent: false,
	//name: 'Scale reader',
	//bufferSize: 4096,
	//bitrate: 9600,
	//dataBits: 'eight',
	//parityBit: 'no',
	//stopBits: 'one',
	ctsFlowControl: true, //false,
	//receiveTimeout: 0,
	//sendTimeout: 0
};

var queuedResponse = '';
var readyResponse = '';
var resultHex = '';
var resultCharCodes = '';

var processAppMessage = function(request, sender, sendResponse) {
	console.log('Receiving web app communication');
	if (request.ping) {
		console.log('Responding to ping request');
		sendResponse(response(true));
	}
	if (request.print) {
		console.log('Passing request print data to usb');
		handlePrintRequest(request.print, request.printers);
		sendResponse(response(true, {received: request.print}));
	}
	if (request.message) {
		console.log('Passing request message to serial');
		handleRequest(request.message);
		sendResponse(response(true, {received: request.message}));
	}
	if (request.readResult) {
		console.log('Checking if a serial result has been received');
		if (readyResponse.length) {
			console.log('Response available, so sending it');
			sendResponse(response(true, {result: readyResponse}));
			readyResponse = '';
		} else {
			console.log('No response yet');
		}
	}
}

var processSerialResponse = function(info) {
	console.log('Receiving serial response');
	if (info.connectionId !== connectionId) {
		chrome.serial.flush(connectionId, function(){});
		console.log('Expecting connection ID ' + connectionId + ', got ' + info.connectionId);
		return;
	}
	if (!info.data) {
		console.log('No data, returning.');
		return;
	}

	storeResponse(convertArrayBufferToString(info.data));
}

var processSerialError = function(info) {
	console.log('A serial connection error has occurred...');
	console.log(info);
	if (info && info.error == 'frame_error') {
		chrome.serial.disconnect(info.connectionId, function() {});
		connectionId = null;
	}
}

chrome.runtime.onMessageExternal.addListener(processAppMessage);
chrome.serial.onReceive.addListener(processSerialResponse);
chrome.serial.onReceiveError.addListener(processSerialError);


/**
 * Store the serial response to a global queuedResponse object.
 *
 * The responses typically come in one character at a time. Store them to
 * queuedResponse until we get an ETX (end of transmission) character.
 *
 * @param {string} string The scale response.
 */
function storeResponse(string) {
	for (var i=0; i<string.length; i++) {
		queuedResponse += string.charCodeAt(i);
		if (string.charCodeAt(i) === 3) {
			console.log('Final response');
			readyResponse = queuedResponse;
			console.log(queuedResponse);
			queuedResponse = '';
			resultHex = '';
			resultCharCodes = '';
			return;
		}
    }
}

/**
 * Send the request and log the response to console.
 *
 * @param {string} message The message to send.
 */
function handleRequest(message) {
	var requestCallback = function(sendInfo) {
		console.log('Inside requestCallback');
		console.log(message);
		console.log(sendInfo);
	};

	return send(message, requestCallback);
}

/**
 * Send the print request and log the response to console.
 *
 * @param {string} data The raw print data.
 * @param {array} printers The printers to print to.
 */
function handlePrintRequest(data, printers) {
	var requestCallback = function(sendInfo) {
		console.log('Inside print requestCallback');
		console.log(data);
		console.log(sendInfo);
	};

	return print(data, printers, requestCallback);
}

/**
 * Configure the serial port.
 *
 * @param {function(port, options)} callback The connect method.
 */
function configure(callback) {
	console.log('Configuring serial connection');
	chrome.storage.local.get(null, function(options) {
		console.log(options.port);
		if (options.bitrate) defaultOptions.bitrate = parseInt(options.bitrate);
		if (options.dataBits) defaultOptions.dataBits = options.dataBits;
		if (options.parityBit) defaultOptions.parityBit = options.parityBit;
		if (options.stopBits) defaultOptions.stopBits = options.stopBits;
		callback(options.port, defaultOptions);
	});
}

/**
 * Connect to the serial port.
 *
 * @param {function(connectionId)} callback The callback to perform after connect.
 */
function connect(callback) {
	var connectCall = function(path, options) {
		console.log('Connecting to serial port ' + path + ' with:');
		console.log(options);
		chrome.serial.connect(
			path,
			options,
			function(connectionInfo) {
				connectionId = connectionInfo.connectionId;
				console.log('Connection ID: ' + connectionId);
				callback(connectionId);
			}
		);
	};

	if (!path || !options) {
		return configure(connectCall);
	}

	return connectCall(path, options);
}

/**
 * Detect if a connection already exists and use it if possible. Otherwise
 * initiate setup of a new connection.
 *
 * @param {function(connectionId)} callback The function to run after finding connection.
 */
function findConnection(callback) {
	/**
	 * @param connections array of ConnectionInfo
	 */
	var connectionsCallback = function(connections) {
		if (!connections.length) {
			return connect(callback);
		}

		for (var i=0; i<connections.length; i++) {
			console.log('Closing old connection ' + connections[i].connectionId);
			chrome.serial.disconnect(connections[i].connectionId, function() {});
		}

		return connect(callback);
	};
	return chrome.serial.getConnections(connectionsCallback);
}

/**
 * Send message to serial port.
 *
 * @param {string} message The message to send.
 * @param {function(sendInfo)} The callback to process after sending.
 */
function send(message, callback) {
	var sendCall = function(connectionId) {
		console.log('Sending message to serial port');
		//TODO: use below line to see if connection is lost and regrab it
		chrome.serial.getControlSignals(connectionId, function(signals){console.log(signals)});
		return chrome.serial.send(
			connectionId,
			convertStringToArrayBuffer(message),
			callback
		);
	};
	if (!connectionId) {
		return findConnection(sendCall);
	}

	sendCall(connectionId);
}

/**
 * Print to USB connected printers matching vendorId and productId.
 *
 * @param {string} data The data to print.
 * @param {array} printers The printers to print to.
 * @param {function(sendInfo)} The callback to process after sending.
 */
function print(data, printers, callback) {
	var printCallbackURL = function(device) {
		var xhttp = new XMLHttpRequest();
		var url = "http://" + device.ip + "/pstprnt";
        var method = 'POST';

        xhttp.onload = function() {
            callback(xhttp.responseText);
        };
        xhttp.onerror = function() {
            callback('HTTP Request sent. CORS prevents reading any response.');
        };
        xhttp.open(method, url, true);
        if (method == 'POST') {
            xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        }
        xhttp.send(data);
		return true;
	};

	var printCallback = function(device) {
		console.log('Printing to ' + device.handle);
		var transferInfo = {
			direction: "out",
			endpoint: 1,
			data: convertStringToArrayBuffer(data)
		};
		return chrome.usb.claimInterface(device, 0, function() {
			if (chrome.runtime.lastError) {
				console.error(chrome.runtime.lastError);
				callback('Failed to claim the printer interface.');
				return;
			}
			chrome.usb.bulkTransfer(device, transferInfo, function(transferResult) {
				console.log("Send data", transferResult);
				chrome.usb.releaseInterface(device, 0, function() {
					if (chrome.runtime.lastError) {
						console.error(chrome.runtime.lastError);
					}
				});
				callback(transferResult);
			});
		});
	};

	var findCallback = function(devices) {
		if (Array.isArray(devices) && devices.length) {
			console.log(devices);
			return devices.forEach(printCallback);
		}
		return null;
	};

	var printerFinder = function(printer) {
		if (printer.ip) {
			return printCallbackURL(printer);
		} else {
			return chrome.usb.findDevices({
				"vendorId": printer.vendorId,
				"productId": printer.productId
			}, findCallback);
		}
	};

	return printers.forEach(printerFinder);
}

/**
 * Format and return an appropriate response for the web app to process.
 *
 * @param {bool} success True if call was successful.
 * @param {object} response The response object.
 */
function response(success, response) {
	console.log('Formatting response');
	response = response || {};
	response.success = success || false;

	console.log(response);
    return response;
}

function convertStringToArrayBuffer(str) {
	console.log('Text encoding...');

	var encodedString = unescape(encodeURIComponent(str));
	var bufView = new Uint8Array(encodedString.length);
	for (var i=0; i<encodedString.length; i++) {
		bufView[i]=encodedString.charCodeAt(i);
	}

	var result;
	result = bufView.buffer;
	console.log('Result:');
	console.log(result);
	return result;
}

function convertArrayBufferToString(buf) {
	console.log('Text decoding...');
	return new TextDecoder('ascii').decode(buf);
}
