import Modal, { defaults as modalDefaults } from "./modal.js";
import { isObject } from "@/helpers/object.js";
import { reactive, set } from "vue";

export const defaults = () => {
	return {
		...modalDefaults()
	};
};

/**
 * @since 4.0.0
 */
export default (panel) => {
	const parent = Modal(panel, "drawer", defaults());

	// shortcut to submit drawers
	panel.events.on("drawer.save", (e) => {
		e.preventDefault();
		panel.drawer.submit();
	});

	return reactive({
		...parent,

		get breadcrumb() {
			return this.history.milestones;
		},

		get icon() {
			return this.props.icon ?? "box";
		},

		listeners() {
			return {
				...parent.listeners.call(this),
				crumb: this.goTo.bind(this),
				tab: this.tab.bind(this)
			};
		},

		/**
		 * Opens drawer via JS object or loads it from the server
		 *
		 * @example
		 * panel.drawer.open('some/drawer');
		 *
		 * @example
		 * panel.drawer.open('some/drawer', () => {
		 *  // on submit
		 * });
		 *
		 * @example
		 * panel.drawer.open('some/drawer', {
		 *   query: {
		 *     template: 'some-template'
		 *   },
		 *   on: {
		 *     submit: () => {},
		 *     cancel: () => {}
		 *   }
		 * });
		 *
		 * @example
		 * panel.drawer.open({
		 *   component: 'k-forms-drawer',
		 *   props: {
		 *      fields: {}
		 *   },
		 *   on: {
		 *     submit: () => {},
		 *     cancel: () => {}
		 *   }
		 * });
		 *
		 * @param {String|Object} drawer
		 * @param {Object|Function} options
		 * @returns {Object}
		 */
		async open(drawer, options = {}) {
			// handle drawer object with url property
			if (isObject(drawer) && drawer.url) {
				options = drawer;
				drawer = drawer.url;
				delete options.url;
			}

			// prefix URLs
			if (typeof drawer === "string") {
				drawer = `/drawers/${drawer}`;
			}

			await parent.open.call(this, drawer, options);

			// open the provided or first tab
			this.tab(drawer.tab);

			// get the current state and add it to the history
			// (we need to fetch the state freshly as it is altered by `this.tab()`)
			const state = this.state();
			if (state?.id) {
				this.history.add(state, drawer.replace);
			}

			this.focus();

			return state;
		},

		tab(tab) {
			const tabs = this.props.tabs ?? {};
			tab ??= Object.keys(tabs ?? {})[0];

			if (!tab) {
				return false;
			}

			set(this.props, "fields", tabs[tab].fields);
			set(this.props, "tab", tab);

			this.emit("tab", tab);

			setTimeout(() => {
				this.focus();
			});
		}
	});
};
