import { reactive } from "vue";
import Feature, { defaults, type FeatureState } from "./feature";
import { type Listener } from "./listeners";

type DropdownState = FeatureState & {};

/**
 * @since 4.0.0
 */
export default function Dropdown(panel: TODO) {
	const parent = Feature(panel, "dropdown", defaults() as DropdownState);

	return reactive({
		...parent,

		close(): void {
			this.emit("close");
			this.reset();
		},

		open(
			dropdown: string | URL | Partial<Prettify<DropdownState>>,
			options: Partial<Prettify<DropdownState>> | Listener = {}
		): Promise<Prettify<DropdownState>> {
			// prefix URLs
			if (typeof dropdown === "string") {
				dropdown = `/dropdowns/${dropdown}`;
			}

			return parent.open.call(this, dropdown, options);
		},

		/**
		 * @deprecated 4.0.0
		 */
		openAsync(
			dropdown: string,
			options: Partial<Prettify<DropdownState>> | Listener = {}
		): (ready: (items: unknown[]) => void) => Promise<void> {
			// panel.deprecated(
			// 	"`panel.dropdown`: opening via $dropdown won't return an async closure in future versions."
			// );

			return async (ready: (items: unknown[]) => void) => {
				await this.open(dropdown, options);

				// load all options from the dropdown
				const items = this.options();

				// react to empty dropdowns
				if (items.length === 0) {
					throw Error(`The dropdown is empty`);
				}

				ready(items);
			};
		},

		options(): unknown[] {
			// return an empty array for invalid/non-existing options
			if (Array.isArray(this.props.options) === false) {
				return [];
			}

			return this.props.options;
		},

		set(state: Partial<Prettify<DropdownState>>): Prettify<DropdownState> {
			// deprecated dropdown responses only return the options
			// @ts-expect-error normalize malformed response
			if (state.options) {
				// panel.deprecated(
				// 	"`panel.dropdown`: responses should return the full state object. Only returning the options has been deprecated and will be removed in a future version."
				// );

				state.props = {
					// @ts-expect-error normalize malformed response
					options: state.options
				};
			}

			return parent.set.call(this, state);
		}
	});
}
