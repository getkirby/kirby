export default class Emitter {
	#callbacks: Record<string, ((...args: any[]) => void)[]> = {};

	/**
	 * Call all registered listeners for that event
	 */
	emit(event: string, ...args: unknown[]): this {
		const callbacks = this.#callbacks[event] ?? [];

		for (const callback of callbacks) {
			callback.apply(this, args);
		}

		return this;
	}

	/**
	 * Remove event listener for given event.
	 * If fn is not provided, all event listeners for that event will be removed.
	 * If neither is provided, all event listeners will be removed.
	 */
	off(event?: string, fn?: (...args: any[]) => void): this {
		if (event === undefined) {
			this.#callbacks = {};
			return this;
		}

		// event listeners for the given event
		const callbacks = this.#callbacks[event];

		if (callbacks) {
			if (fn) {
				// remove specific handler
				this.#callbacks[event] = callbacks.filter((cb) => cb !== fn);
			} else {
				// remove all handlers
				delete this.#callbacks[event];
			}
		}

		return this;
	}

	/**
	 * Add an event listener for given event
	 */
	on(event: string, fn: (...args: any[]) => void): this {
		this.#callbacks[event] ??= [];
		this.#callbacks[event].push(fn);
		return this;
	}
}
