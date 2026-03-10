import LocalDevice from './local-device';

export default class Scale extends LocalDevice {
	constructor() {
		super();

		/**
		 * Character encodings used by scale.
		 */
		this.chars = {
			etx: String.fromCharCode(0x03),
			cr: String.fromCharCode(0x0D),
			lf: String.fromCharCode(0x0A),
		};

		/**
		 * Scale commands.
		 */
		this.commands = {
			'read': "W" + this.chars.cr,
			'status': "S" + this.chars.cr,
			'reset': "Z" + this.chars.cr,
			'hires': "H" + this.chars.cr,
			'units': "U" + this.chars.cr,
			'raw': "M" + this.chars.cr,
		};

		/**
		 * Regex patterns for response data.
		 */
		this.responses = {
			'read': /(\d+\.\d+)/g, // Returns decimal lb or kg weight, units and scale status
		};

/*
		var template = {
			//status: this.chars.lf + 'hhh' + this.chars.cr + this.chars.etx
			status: this.chars.lf + '.+' + this.chars.cr + this.chars.etx
		};

		this.responses = {
			//'read': this.chars.lf + 'xxxx.xxuu' + this.chars.cr + template.status, // Returns decimal lb or kg weight, units and scale status
			//'read': this.chars.lf + '\s*(\d+\.\d+)lb' + this.chars.cr + template.status, // Returns decimal lb or kg weight, units and scale status
			'read': /(\d+\.\d+)/g, // Returns decimal lb or kg weight, units and scale status
			'read2': this.chars.lf + 'xx lb<sp>xx.x oz' + this.chars.cr + template.status, // Returns lb-oz weight, units and scale status
			'status': template.status, // Returns scale status
			'reset': template.status, // Scale is zeroed, returns status
			'hires': this.chars.lf + 'xxxx.xxxuu' + this.chars.cr + template.status, // Returns decimal lb or kg weight in 10X format with units and scale status
			'hires2': this.chars.lf + 'xx lb<sp>xx.xx oz' + this.chars.cr + template.status, // Returns lb-oz weight in 10X format with units and scale status
			'units': this.chars.lf + ' uu ' + this.chars.cr + template.status, // Changes unit of measure and returns new unit and status
			'raw': this.chars.lf + 'xxxxxxxMM' + this.chars.cr + template.status, // Returns normalized raw counts and status
			'error': this.chars.lf + '?' + this.chars.cr + this.chars.etx, // Unrecognized command
		};
*/

		/**
		 * The command used to read the scale as listed in this.commands.
		 */
		this.readCommand = 'read';

		/**
		 * The format used to parse the scale response as listed in
		 * this.responses.
		 */
		this.readResponseFormat = 'read';

		/**
		 * The maximum number of times to attempt to read the scale result
		 * before timing out.
		 */
		this.maxReadAttempts = 25;

		/**
		 * The pause between each read attempt in milliseconds.
		 */
		this.readAttemptInterval = 500;

		console.log('Scale JS loaded');
	}

	/**
	 * Request a reading from the scale and capture the response.
	 *
	 * @param {function(response, error)} callback The read response handler.
	 */
	read(callback) {
		console.log('Requesting scale reading');
		var Scale = this;
		var iterations = 0;
		var resultCallback = function(response, error) {
			console.log('Inside resultCallback');
			console.log('Current iteration: ' + iterations);
			if (response && response.result) {
				callback(Scale.parseResult(response.result));
			} else if (iterations < Scale.maxReadAttempts) {
				iterations++;
				setTimeout(function() {
					Scale.message({readResult: true}, resultCallback);
				}, Scale.readAttemptInterval);
			} else {
				console.warn('Could not get scale reading after ' + Scale.maxReadAttempts + ' attempts, giving up.');
			}
		};
		var requestCallback = function(response, error) {
			console.log('Inside requestCallback');
			if (!response || !response.received) {
				console.error('Read error! Response was...');
				console.log(response);
				if (error) {
					console.error(error);
				}
				callback(response, error);
				return;
			}

			Scale.message({readResult: true}, resultCallback);
		};
		return this.message({message: this.commands[this.readCommand]}, requestCallback);
	}

	/**
	 * Parse the raw scale output into pounds and ounces.
	 *
	 * @param {string} The scale output
	 */
	parseResult(result) {
		console.log('Parsing scale result...');
		var result = result.match(this.responses[this.readResponseFormat]);
		var pounds = Math.floor(result[0]);
		var ounces = Math.ceil((result[0] - pounds)*16);
		if (ounces == 16) {
			pounds++;
			ounces = 0;
		}

		return {
			pounds: pounds,
			ounces: ounces
		};
	}
}
