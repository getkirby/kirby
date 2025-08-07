/**
 * Throttles the callback function
 *
 * @param {Function} callback function to debounce
 * @param {int} delay delay in milliseconds
 * @param {object} options { leading: true, trailing: false }
 * @returns {Function}
 */
export default (
	callback,
	delay,
	options = { leading: true, trailing: false }
) => {
	let timer = null;
	let last = null;
	let trailing = null;

	function throttled(...args) {
		if (timer) {
			last = this;
			trailing = args;
			return;
		}

		if (options.leading) {
			callback.call(this, ...args);
		} else {
			last = this;
			trailing = args;
		}

		const cooled = () => {
			if (options.trailing && trailing) {
				callback.call(last, ...trailing);

				last = null;
				trailing = null;
				timer = setTimeout(cooled, delay);
			} else {
				timer = null;
			}
		};

		timer = setTimeout(cooled, delay);
	}

	// Add cancel method to clear the timeout
	throttled.cancel = () => {
		if (timer) {
			clearTimeout(timer);
			timer = null;
			last = null;
			trailing = null;
		}
	};

	return throttled;
};
