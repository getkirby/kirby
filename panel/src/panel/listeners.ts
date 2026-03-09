import { isObject } from "@/helpers/object";

export type Listener = (...args: unknown[]) => unknown;

/**
 * Provides an event-listener system for Panel modules.
 * Each event can have at most one registered callback.
 *
 * @since 4.0.0
 */
export default function Listeners() {
	return {
		on: {} as Record<string, Listener>,

		/**
		 * Registers a single event listener
		 *
		 * @example
		 * panel.dialog.addEventListener("submit", (value) => {})
		 */
		addEventListener(event: string, callback: Listener): void {
			if (typeof callback !== "function") {
				return;
			}

			if (this.hasEventListener(event) === true) {
				console.warn(
					`Listener for "${event}" already exists and will be overwritten`
				);
			}

			this.on[event] = callback;
		},

		/**
		 * Registers multiple event listeners at once
		 *
		 * @example
		 * panel.dialog.addEventListeners({
		 *   submit: (value) => {},
		 *   close: () => {}
		 * })
		 */
		addEventListeners(listeners?: Record<string, Listener>): void {
			// ignore invalid listeners
			if (isObject(listeners) === false) {
				return;
			}

			for (const [event, callback] of Object.entries(listeners)) {
				this.addEventListener(event, callback);
			}
		},

		/**
		 * Calls the user-registered listener for the given event.
		 *
		 * @example
		 * panel.dialog.emit("submit", {})
		 */
		emit(event: string, ...args: unknown[]): unknown {
			if (this.hasEventListener(event) === true) {
				// Deliberately reads from this.on directly rather
				// than this.listeners(), as submodules like Modal inject
				// their own built-in handlers into listeners() which
				// themselves have to call emit() then
				return this.on[event](...args);
			}
		},

		/**
		 * Checks if a listener exists for the given event
		 *
		 * @example
		 * panel.dialog.hasEventListener("submit")
		 */
		hasEventListener(event: string): boolean {
			return typeof this.on[event] === "function";
		},

		/**
		 * Returns all user-registered listeners as a plain object.
		 * Panel modules like Modal and Drawer override this method
		 * to also inject built-in handlers, making the result
		 * suitable for binding to Vue components via v-on.
		 *
		 * Note: emit() always targets only user-registered listeners,
		 * regardless of any override. If a module injects its own
		 * handlers into listeners(), which could override user-defined
		 * listeners, make sure to explicitly als call emit() from the
		 * injected handler
		 *
		 * @example
		 * <k-dialog v-on="panel.dialog.listeners()" />
		 */
		listeners(): Record<string, Listener> {
			return this.on;
		},

		/**
		 * Removes the listener for a single event
		 *
		 * @example
		 * panel.dialog.removeEventListener("submit")
		 */
		removeEventListener(event: string): void {
			delete this.on[event];
		},

		/**
		 * Removes all registered listeners
		 *
		 * @example
		 * panel.dialog.removeEventListeners()
		 */
		removeEventListeners(): void {
			this.on = {};
		}
	};
}
