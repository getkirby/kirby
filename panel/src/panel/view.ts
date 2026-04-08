import { reactive } from "vue";
import Feature, {
	defaults as featureDefaults,
	type FeatureState
} from "./feature";

type ViewState = FeatureState & {
	breadcrumb: {
		link: string;
		label: string;
		icon?: string;
	}[];
	breadcrumbLabel: string | null;
	icon: string | null;
	id: string | null;
	link: string | null;
	search: string;
	title: string | null;
};

export function defaults(): ViewState {
	return {
		...featureDefaults(),
		breadcrumb: [],
		breadcrumbLabel: null,
		icon: null,
		id: null,
		link: null,
		search: "pages",
		title: null
	};
}

/**
 * @since 4.0.0
 */
export default function View(panel: TODO) {
	const parent = Feature(panel, "view", defaults());

	return reactive({
		...parent,

		/**
		 * Load a view from the server and
		 * cancel any previous request
		 */
		async load(
			url: string | URL,
			options: Partial<Prettify<ViewState>> & {
				silent?: boolean;
			} = {}
		): Promise<Prettify<ViewState>> {
			// cancel any previous request
			this.abortController?.abort();

			return parent.load.call(this, url, options);
		},

		/**
		 * Setting the active view state
		 * will also change the document title
		 * and the browser URL
		 */
		set(state: Partial<Prettify<ViewState>>): Prettify<ViewState> {
			// reuse the parent state setter, but with
			// the view bound as this
			const result = parent.set.call(this, state);

			// change the document title
			panel.title = this.title;

			// get the current url
			const url = this.url().toString();

			// change the browser location and reset the scroll
			// position if the path changed
			if (window.location.toString() !== url) {
				window.history.pushState(null, "", url);
				window.scrollTo(0, 0);
			}

			return result;
		},

		/**
		 * Submitting view form values is not implemented yet
		 */
		async submit(): Promise<never> {
			throw new Error("Not yet implemented");
		}
	});
}
