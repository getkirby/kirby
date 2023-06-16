import { clone } from "@/helpers/object.js";
import Modal, { defaults as modalDefaults } from "./modal.js";
import History from "./history.js";

export const defaults = () => {
	return {
		...modalDefaults(),
		id: null,
		tabId: null
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

			return state;
		},

		openTab(tabId = this.tabId) {
			tabId = tabId || Object.keys(this.tabs)[0];

			if (!tabId) {
				return false;
			}

			this.tabId = tabId;
			this.emit("openTab", tabId);
		},

		get tab() {
			return this.tabs[this.tabId] ?? null;
		},
		get tabs() {
			return this.props?.tabs ?? {};
		},
		get title() {
			return this.props.title;
		}
	};
};
