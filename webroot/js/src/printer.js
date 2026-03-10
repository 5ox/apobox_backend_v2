import LocalDevice from './local-device';

export default class Printer extends LocalDevice {
	/**
	 * Send data to the printer.
	 *
	 * @param string data The data to print.
	 * @param {function(response, error)} callback The read response handler.
	 */
	print(data, callback) {
		var printers = [
			/*
			{
				"name": "LP-2844",
				"vendorId": 2655,
				"productId": 9,
			},
			{
				"name": "TLP-2844Z",
				"vendorId": 2655,
				"productId": 39,
			},
			{
				"name": "TLP-2844Z",
				"vendorId": 2655,
				"productId": 40,
			},
			*/
			{
				"name": "GX420t",
				"ip": localStorage.getItem('Settings.local.printer_ip'),
			},
		];

		console.log('Sending data to printer');
		var Printer = this;
		var requestCallback = function(response, error) {
			console.log('Inside Printer requestCallback');
			if (!response || !response.received) {
				console.error('Print error! Response was...');
				console.log(response);
				if (error) {
					console.error(error);
					console.error(chrome.runtime.lastError.message);
				}
			}

			return callback(response, error);
		};
		return this.message({print: data, printers: printers}, requestCallback);
	}
}
