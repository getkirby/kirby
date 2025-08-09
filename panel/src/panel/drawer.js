import Modal, { defaults as modalDefaults } from "./modal.js";
import History from "./history.js";
import { reactive, set } from "vue";
import { uuid } from "@/helpers/string.js";

export const defaults = () => {
	return {
		...modalDefaults(),
		id: null
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

		/**
		 * Closes the drawer and goes back to the
		 * parent one if it has been stored
		 * @param {String|true} id Which drawer to close, true for all
		 */
		async close(id) {
			if (this.isOpen === false) {
				return;
			}

			// Compare the drawer id to avoid closing
			// the wrong drawer. This is particularly useful
			// in nested drawers.
			if (id !== undefined && id !== true && id !== this.id) {
				return;
			}

			if (id === true) {
				this.history.clear();
			} else {
				this.history.removeLast();
			}

			// no more items in the history
			if (this.history.isEmpty() === true) {
				parent.close.call(this);
				return;
			}

			return this.open(this.history.last());
		},

		goTo(id) {
			const state = this.history.goto(id);

			if (state !== undefined) {
				this.open(state);
			}
		},

		history: History(),

		get icon() {
			return this.props.icon ?? "box";
		},

		input(value) {
			// make sure that value is reactive
			set(this.props, "value", value);

			this.emit("input", this.props.value);
		},

		listeners() {
			return {
				...this.on,
				cancel: this.cancel.bind(this),
				close: this.close.bind(this),
				crumb: this.goTo.bind(this),
				input: this.input.bind(this),
				submit: this.submit.bind(this),
				success: this.success.bind(this),
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
			// prefix URLs
			if (typeof drawer === "string") {
				drawer = `/drawers/${drawer}`;
			}

			await parent.open.call(this, drawer, options);

			// open the provided or first tab
			this.tab(drawer.tab);

			// get the current state and add it to the list of parents
			const state = this.state();

			// add the drawer to the history
			if (drawer.replace === true) {
				this.history.replace(-1, state);
			} else {
				this.history.add(state);
			}

			this.focus();

			return state;
		},

		/**
		 * Sets a new active state for the feature
		 * This is done whenever the state is an object
		 * and not undefined or null
		 *
		 * @param {Object} state
		 */
		set(state) {
			parent.set.call(this, state);

			// create a unique ID for the drawer if it does not have one
			this.id ??= uuid();

			return this.state();
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
