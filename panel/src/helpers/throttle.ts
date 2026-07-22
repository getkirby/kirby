/**
 * Throttles the callback function
 *
 * @param callback - function to throttle
 * @param delay - delay in milliseconds
 *
 * @example
 * const throttled = throttle(myFunction, 100)
 * throttled() // myFunction() is called at most once every 100ms
 */
export default function <T extends unknown[]>(
	callback: (this: unknown, ...args: T) => void,
	delay: number,
	options: { leading?: boolean; trailing?: boolean } = {
		leading: true,
		trailing: false
	}
): ((this: unknown, ...args: T) => void) & { cancel: () => void } {
	let timer: ReturnType<typeof setTimeout> | undefined;
	let pending: (() => void) | undefined;

	function throttled(this: unknown, ...args: T) {
		pending = () => callback.call(this, ...args);

		if (timer) {
			return;
		}

		if (options.leading) {
			pending();
			pending = undefined;
		}

		const cooled = () => {
			if (options.trailing && pending) {
				pending();
				pending = undefined;
				timer = setTimeout(cooled, delay);
			} else {
				timer = undefined;
			}
		};

		timer = setTimeout(cooled, delay);
	}

	// Add cancel method to clear the timeout
	throttled.cancel = () => {
		if (timer) {
			clearTimeout(timer);
			timer = undefined;
			pending = undefined;
		}
	};

	return throttled;
}
