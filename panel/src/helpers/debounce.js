/**
 * Debounces the callback function
 *
 * @param {Function} callback function to debounce
 * @param {int} delay delay in milliseconds
 * @returns {Function}
 */
export default (callback, delay) => {
	let timer = null;

	return (...args) => {
		clearTimeout(timer);
		timer = setTimeout(() => callback.apply(this, args), delay);
	};
};
