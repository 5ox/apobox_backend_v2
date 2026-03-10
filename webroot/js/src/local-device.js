export default class LocalDevice {
	constructor() {
		/**
		 * The id of the chrome app this code will communicate with.
		 *
		 * After an app is installed, this id can be retreived from
		 * chrome://extensions. Take note that this id will be unique for
		 * manually installed (unpacked) extensions.
		 */
		this.chromeAppExtensionId = "efcdipocffpmeiaoepcopheefghpgfgk";
		if (window.chromeAppExtensionId !== undefined) {
			this.chromeAppExtensionId = window.chromeAppExtensionId;
		}

		/**
		 * The listening state of the chrome app.
		 *
		 * This is checked and set before the first message is sent.
		 */
		this.appListening = false;
	}

	/**
	 * Send a message to the chrome app.
	 *
	 * @param {object} message The message object to send to the app.
	 * @param {function(response, error)} callback The chrome app response handler.
	 * @param {string} error The error string to pass through callbacks.
	 */
	message(message, callback, error) {
		if (error) {
			return callback(null, error);
		}

		var Device = this;
		var pingCallback = function(response, error) {
			if (response) {
				Device._sendMessage(message, callback, 'Sending message after successful ping failed.');
			} else {
				callback(response, error);
			}
		}

		if (this.appListening) {
			Device._sendMessage(message, callback, 'Sending message when appListening=true failed.');
		} else {
			this.ping(pingCallback);
		}
	}

	/**
	 * Ping the chrome app to ensure it is listening.
	 *
	 * @param {function(response)} callback A callback to run after ping.
	 * @param {string} error The error string to pass through callbacks.
	 */
	ping(callback, error) {
		var Device = this;
		var pingCallback = function(response, error) {
			if (response) {
				Device._setListening();
			}
			callback(response, error);
		};

		this._sendMessage(
			{ping: true},
			pingCallback,
			'The Chrome App is not listening. Check that it is installed.'
		);
	}

	/**
	 * Send message to the chrome app.
	 *
	 * @param {string} message The message to send.
	 * @param {function(response, error)} callback The chrome app response handler.
	 * @param {string} errorMessage The error message to return if send fails.
	 */
	_sendMessage(message, callback, errorMessage) {
		errorMessage = errorMessage || null;
		var Device = this;

		console.log('  Sending message...');
		console.log(message);
		chrome.runtime.sendMessage(
			this.chromeAppExtensionId,
			message,
			function(response) {
				console.log('  Received response...');
				if (!response || !response.success) {
					errorMessage = errorMessage + "\n" + chrome.runtime.lastError.message;
					return Device._handleError(callback, errorMessage);
				}
				callback(response);
			}
		);
	}

	/**
	 * Set the appListening flag to true.
	 */
	_setListening() {
		this.appListening = true;
	}

	/**
	 * Handle error processing.
	 *
	 * @param {function(object, error)} callback The chrome app response handler.
	 * @param {string} message The error message.
	 */
	_handleError(callback, message) {
		console.error(message);
		callback(null, message);
		return false;
	}
}
