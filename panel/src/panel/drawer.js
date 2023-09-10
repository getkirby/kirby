import Modal, { defaults as modalDefaults } from "./modal.js";
import { set } from "vue";

export const defaults = () => {
	return {
		...modalDefaults()
	};
};

export default (panel) => {
	const parent = Modal(panel, "drawer", defaults());

	// shortcut to submit drawers
	panel.events.on("drawer.save", (e) => {
		e.preventDefault();
		panel.drawer.submit();
	});

	return {
		...parent,
		get breadcrumb() {
			return this.history.milestones;
		},

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
		 *   submit: () => {},
		 *   cancel: () => {}
		 * });
		 *
		 * @example
		 * panel.drawer.open({
		 *   component: 'k-forms-drawer',
		 *   props: {
		 *      fields: {}
		 *   },
		 *   submit: () => {},
		 *   cancel: () => {}
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

			const state = await parent.open.call(this, drawer, options);

			// open the provided or first tab
			this.tab(drawer.tab);

			this.focus();

			return state;
		},

		tab(tab) {
			const tabs = this.props.tabs ?? {};
			tab = tab ?? Object.keys(tabs ?? {})[0];

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
	};
};
