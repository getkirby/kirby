/**
 * Throttles the callback function
 *
 * @param {Function} callback function to debounce
 * @param {int} delay delay in milliseconds
 * @param {object} options { leading: true, trailing: false}
 * @returns {Function}
 */
export default (
	callback,
	delay,
	options = { leading: true, trailing: false }
) => {
	let timer = null;
	let last = null;
	let trailingArgs = null;

	return function (...args) {
		// called within cooldown period
		if (timer) {
			last = this; // update context
			trailingArgs = args; // save for later
			return;
		}

		if (options.leading) {
			// if leading, call the 1st instance
			callback.call(this, ...args);
		} else {
			// else it's trailing: update context & save args for later
			last = this;
			trailingArgs = args;
		}

		const coolDownPeriodComplete = () => {
			// if trailing and the trailing args exist
			if (options.trailing && trailingArgs) {
				// invoke the instance with stored context "last"
				callback.call(last, ...trailingArgs);

				//reset the status of last and trailing arguments
				last = null;
				trailingArgs = null;

				// clear the timeout
				timer = setTimeout(coolDownPeriodComplete, delay);
			} else {
				// reset timer
				timer = null;
			}
		};

		timer = setTimeout(coolDownPeriodComplete, delay);
	};
};
