import { reactive } from "vue";
import { isUrl } from "@/helpers/url";
import listeners, { type Listener } from "./listeners";
import State from "./state";

export type FeatureState = {
	// the feature component
	component: string | null;
	isLoading: boolean;
	// event listeners
	on: Record<string, Listener>;
	// relative path to this feature
	path: string | null;
	// all props for the feature component
	props: Record<string, unknown>;
	// the query parameters from the latest request
	query: Record<string, unknown>;
	// referrer can be used to redirect properly in handlers
	referrer: string | null;
	// timestamp from the backend to force refresh the reactive state
	timestamp: number | null;
};

/**
 * Default state for all features
 */
export function defaults(): FeatureState {
	return {
		component: null,
		isLoading: false,
		on: {},
		path: null,
		props: {},
		query: {},
		referrer: null,
		timestamp: null,
	};
}

/**
 * Feature objects isolate functionality and state of Panel features
 * like drawers, dialogs, notifications and views
 * @since 4.0.0
 *
 * @param panel - The Panel singleton
 * @param key - Identifies this state in backend responses
 * @param defaults - Initial values; also defines which keys are tracked
 */
export default function Feature<T extends FeatureState>(panel: TODO, key: string, defaults: T) {
	const parent = State(key, defaults);

	return reactive({
		/**
		 * Feature inherits all the state methods
		 * and reactive defaults are also merged
		 * through them
		 */
		...parent,
		...listeners(),
		abortController: undefined as AbortController | undefined,

		/**
		 * Sends a get request to the backend route for this feature
		 * @since 5.1.0
		 */
		async get(
			url: string | URL,
			options?: Partial<Prettify<T>>,
		): Promise<Record<string, unknown> | false> {
			this.isLoading = true;

			try {
				return await panel.get(url, options);
			} catch (error) {
				panel.error(error);
			} finally {
				this.isLoading = false;
			}

			return false;
		},

		/**
		 * Loads a feature from the backend and opens it afterwards
		 *
		 * @example
		 * panel.view.load("/some/view");
		 *
		 * @example
		 * panel.view.load("/some/view", () => {
		 *   // submit
		 * });
		 *
		 * @example
		 * panel.view.load("/some/view", {
		 *   query: {
		 *     search: "Find me"
		 *   }
		 * });
		 */
		async load(
			url: string | URL,
			options: Partial<Prettify<T>> & { silent?: boolean } = {},
		): Promise<Prettify<T>> {
			// each feature can have its own loading state
			// the panel.open method also triggers the global loading
			// state for the entire panel. This adds fine-grained control
			// over appropriate spinners.
			if (options.silent !== true) {
				this.isLoading = true;
			}

			// create a new abort controller
			this.abortController = new AbortController();

			// the global open method is used to make sure
			// that a response can also trigger other features.
			// For example, a dialog request could also open a drawer
			// or a notification by sending the matching object
			await panel.open(url, {
				...options,
				signal: this.abortController.signal,
			});

			// stop the feature loader
			this.isLoading = false;

			// add additional listeners from the options
			this.addEventListeners(options.on);

			// return the final state
			return this.state();
		},

		/**
		 * Opens the feature either by URL or by passing a state object
		 *
		 * @example
		 * panel.dialog.open({
		 *   component: "k-page-view",
		 *	 props: {},
		 *   on: {
		 *     submit: () => {}
		 * 	 }
		 * });
		 *
		 * See load for more examples
		 */
		async open(
			feature: string | URL | Partial<Prettify<T>>,
			options: Partial<Prettify<T>> | Listener = {},
		): Promise<Prettify<T>> {
			const listeners: Record<string, unknown> | undefined =
				typeof options === "function" ? { submit: options } : options.on;
			const state: Partial<T> =
				typeof options === "function" ? ({ on: listeners } as Partial<T>) : options;

			// the feature needs to be loaded first
			// before it can be opened. This will route
			// the request through panel.open
			if (isUrl(feature) === true) {
				return this.load(feature, state);
			}

			// set the new state
			this.set(feature);

			// add additional listeners from the options
			this.addEventListeners(listeners);

			// trigger optional open listeners
			this.emit("open", feature, state);

			// return the final state
			return this.state();
		},

		/**
		 * Sends a post request to the backend route for this feature
		 */
		async post(
			value: Record<string, unknown>,
			options: Partial<Prettify<T>> = {},
		): Promise<Record<string, unknown> | false> {
			if (!this.path) {
				throw new Error(`The ${this.key()} cannot be posted`);
			}

			// start the loader
			this.isLoading = true;

			// if no value has been passed to the submit method,
			// take the value object from the props
			value ??= (this.props?.value ?? {}) as Record<string, unknown>;

			try {
				return await panel.post(this.path, value, options);
			} catch (error) {
				panel.error(error);
			} finally {
				// stop the loader
				this.isLoading = false;
			}

			return false;
		},

		/**
		 * Reloads the properties for the feature to refresh its state
		 */
		async refresh(options: Partial<T> & { url?: string | URL } = {}): Promise<T | undefined> {
			const url = options.url ?? this.url();
			const response = await this.get(url, options);

			if (response === false) {
				return;
			}

			const state = response["$" + this.key()] as FeatureState | undefined;

			// the state cannot be updated
			if (!state || state.component !== this.component) {
				return;
			}

			this.props = state.props;

			return this.state();
		},

		/**
		 * If the feature has a path, it can be reloaded
		 * with this method to replace its state
		 *
		 * @example
		 * panel.view.reload();
		 */
		async reload(options?: Partial<Prettify<T>>): Promise<Prettify<T> | false> {
			if (!this.path) {
				return false;
			}

			return this.open(this.url(), options);
		},

		/**
		 * Sets a new active state for the feature
		 */
		set(state: Partial<Prettify<T>>): Prettify<T> {
			parent.set.call(this, state);

			// reset the event listeners
			this.removeEventListeners();

			// register new listeners
			this.addEventListeners(state.on ?? {});

			return this.state();
		},

		/**
		 * Creates a full URL object for the current path
		 */
		url(): URL {
			return panel.url(this.path, this.query);
		},
	});
}
