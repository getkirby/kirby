/**
 * Debounces the callback function
 */
export default (
	/** Callback function to debounce */
	fn: () => void,
	/** Miliseconds to debounce the fn calls */
	delay: number
): (() => void) => {
	let timer = null;
	return function (...args) {
		clearTimeout(timer);
		timer = setTimeout(() => fn.apply(this, args), delay);
	};
};
