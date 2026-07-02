import { reactive } from "vue";
import { isObject } from "@/helpers/object";

/**
 * Creates a reactive state object for a particular part
 * of the Panel, e.g. system or translation.
 * State is the base for Feature and Modal.
 *
 * The inheritance cascade is:
 * State -> Feature -> Modal
 *
 * @since 4.0.0
 *
 * @param key - Identifies this state in backend responses
 * @param defaults - Initial values; also defines which keys are tracked
 */
export default function State<T extends Record<string, unknown>>(
	key: string,
	defaults: T = {} as T
) {
	return reactive({
		/**
		 * Defaults must be spread into the reactive object
		 * so Vue tracks each property from the start
		 */
		...defaults,

		/**
		 * Identifies this state object in
		 * global Panel state backend responses
		 */
		key(): string {
			return key;
		},

		/**
		 * Returns the default values,
		 * used by `reset()` and `state()`
		 * as the source of truth for all known keys
		 */
		defaults(): T {
			return defaults;
		},

		/**
		 * Restores the default state
		 */
		reset(): void {
			this.set(this.defaults());
		},

		/**
		 * Merges the given partial state with the defaults
		 * and applies it as new state
		 */
		set(state: Partial<Prettify<T>>): Prettify<T> {
			if (isObject(state) === false) {
				throw new Error(`Invalid ${this.key()} state`);
			}

			const defaults = this.defaults();

			// merge the new state with the defaults
			// to always get a full object with all props
			for (const prop in defaults) {
				this[prop] = state[prop] ?? defaults[prop];
			}

			return this.state();
		},

		/**
		 * Returns the current reactive state,
		 * limited to keys defined in defaults
		 */
		state(): Prettify<T> {
			const state = {} as T;
			const self = this as unknown as T;
			const defaults = this.defaults();

			// Build a plain object with the current value of each default prop,
			// falling back to the default if the current value is nullish
			for (const prop of Object.keys(defaults) as Array<keyof T>) {
				state[prop] = self[prop] ?? defaults[prop];
			}

			return state;
		}
	});
}
