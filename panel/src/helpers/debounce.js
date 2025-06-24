/**
 * Debounces the callback function
 *
 * @param {Function} callback function to debounce
 * @param {int} delay delay in milliseconds
 * @param {object} options { leading: false, trailing: true }
 * @returns {Function}
 */
export default (
	callback,
	delay,
	options = { leading: false, trailing: true }
) => {
	let timer = null;
	let trailing = null;

	if (options.leading === false && options.trailing === false) {
		return () => null;
	}

	return function debounced(...args) {
		if (!timer && options.leading) {
			callback.apply(this, args);
		} else {
			trailing = args;
		}

		clearTimeout(timer);

		timer = setTimeout(() => {
			if (options.trailing && trailing) {
				callback.apply(this, trailing);
			}

			timer = null;
			trailing = null;
		}, delay);
	};
};
