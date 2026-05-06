/**
 * A simple event emitter with optional typed event map
 *
 * Use the `TEvents` generic to define the events and their payload types.
 * Event names become the keys, payload types become the values. Each event
 * carries a single argument. Without a type map the emitter accepts any string event and untyped args.
 *
 * @example
 * // untyped
 * const emitter = new Emitter();
 * emitter.on("change", (value) => console.log(value));
 *
 * // typed
 * type Events = { change: { from: string; to: string }; reset: undefined };
 * const emitter = new Emitter<Events>();
 * emitter.on("change", ({ from, to }) => console.log(from, to));
 * emitter.emit("change", { from: "a", to: "b" });
 */
export default class Emitter<
	TEvents extends Record<string, unknown> = Record<string, unknown>
> {
	#callbacks: Record<string, ((...args: unknown[]) => void)[]> = {};

	/**
	 * Call all registered listeners for that event.
	 *
	 * @example
	 * emitter.emit("change", { value: 42 });
	 * emitter.emit("change", someValue);
	 */
	emit<K extends keyof TEvents & string>(event: K, arg: TEvents[K]): this;
	emit(event: string, ...args: unknown[]): this;
	emit(event: string, ...args: unknown[]): this {
		const callbacks = this.#callbacks[event] ?? [];

		for (const callback of callbacks) {
			callback.apply(this, args);
		}

		return this;
	}

	/**
	 * Remove an event listener.
	 * If fn is omitted, all listeners for that event are removed.
	 * If event is omitted, all listeners for all events are removed.
	 *
	 * @example
	 * emitter.off("change", myHandler); // remove specific listener
	 * emitter.off("change");            // remove all listeners for "change"
	 * emitter.off();                    // remove all listeners
	 */
	off<K extends keyof TEvents & string>(
		event: K,
		fn?: (arg: TEvents[K]) => void
	): this;
	off(event?: string, fn?: (...args: unknown[]) => void): this;
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
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
	 * Add an event listener for the given event.
	 *
	 * @example
	 * emitter.on("change", (value) => console.log(value));
	 */
	on<K extends keyof TEvents & string>(
		event: K,
		fn: (arg: TEvents[K]) => void
	): this;
	on(event: string, fn: (...args: unknown[]) => void): this;
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	on(event: string, fn: (...args: any[]) => void): this {
		this.#callbacks[event] ??= [];
		this.#callbacks[event].push(fn);
		return this;
	}
}
