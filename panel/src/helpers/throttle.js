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

	return function (...args) {
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
	};
};
