import Modal, { defaults as modalDefaults } from "./modal.js";
import History from "./history.js";
import { set } from "vue";

export const defaults = () => {
	return {
		...modalDefaults(),
		id: null
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
		/**
		 * Closes the drawer and goes back to the
		 * parent one if it has been stored
		 */
		async close() {
			if (this.isOpen === false) {
				return;
			}

			this.history.removeLast();

			// no more items in the history
			if (this.history.isEmpty() === true) {
				this.reset();
				this.isOpen = false;
				this.emit("close");
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
				tab: this.openTab.bind(this)
			};
		},

		async open(feature, options = {}) {
			await parent.open.call(this, feature, options);

			// open the provided or first tab
			this.openTab();

			// get the current state and add it to the list of parents
			const state = this.state();

			// add the drawer to the history
			if (feature.replace === true) {
				this.history.replace(-1, state);
			} else {
				this.history.add(state);
			}

			this.focus();

			return state;
		},

		openTab(tab = this.tab) {
			tab = tab ?? Object.keys(this.props.tabs)[0];

			if (!tab) {
				return false;
			}

			this.props.fields = this.props.tabs[tab].fields;
			this.props.tab = tab;
			this.emit("openTab", tab);
		}
	};
};
