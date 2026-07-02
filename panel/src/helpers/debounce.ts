/**
 * Debounces the callback function
 *
 * @param callback - function to debounce
 * @param delay - delay in milliseconds
 *
 * @example
 * const debounced = debounce(myFunction, 300)
 * debounced() // myFunction() is called 300ms after the last invocation
 */
export default function <T extends unknown[]>(
	callback: (...args: T) => void,
	delay: number,
	options: { leading?: boolean; trailing?: boolean } = {
		leading: false,
		trailing: true
	}
): (...args: T) => void {
	let timer: ReturnType<typeof setTimeout> | undefined;
	let trailing: T | undefined;

	if (options.leading === false && options.trailing === false) {
		return () => null;
	}

	return function debounced(this: unknown, ...args: T) {
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

			timer = undefined;
			trailing = undefined;
		}, delay);
	};
}
