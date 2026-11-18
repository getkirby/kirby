/**
 * Throttles the callback function
 *
 * @param callback - function to throttle
 * @param delay - delay in milliseconds
 *
 * @example
 * const throttled = throttle(myFunction, 100)
 * throttled() // myFunction() is called at most once every 100ms
 * throttled.flush() // runs a pending trailing call right away
 */
export default function <T extends unknown[], R>(
	callback: (this: unknown, ...args: T) => R,
	delay: number,
	options: { leading?: boolean; trailing?: boolean } = {
		leading: true,
		trailing: false
	}
): ((this: unknown, ...args: T) => void) & {
	cancel: () => void;
	flush: () => R | undefined;
} {
	let timer: ReturnType<typeof setTimeout> | undefined;
	let pending: (() => R) | undefined;

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

	// Add flush method to run the pending trailing call right away
	throttled.flush = () => {
		const call = options.trailing ? pending : undefined;
		throttled.cancel();
		return call?.();
	};

	return throttled;
}
