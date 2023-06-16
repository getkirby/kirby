import Modal, { defaults as modalDefaults } from "./modal.js";

export const defaults = () => {
	return {
		...modalDefaults(),
		parent: null,
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
			const crumbs = [];
			let parent = this;

			while (parent !== null) {
				crumbs.push(parent.props);
				parent = parent.parent;
			}

			return crumbs.reverse();
		},
		goTo(id) {
			let parent = this;

			while (parent !== null) {
				if (parent.props.id === id) {
					return this.openState(parent);
				}

				parent = parent.parent;
			}
		},
		get icon() {
			return this.props.icon ?? "box";
		},
		async open(feature, options = {}) {
			const parentDrawer = this.isOpen === true ? this.state() : null;
			await parent.open.call(this, feature, options);

			// add the parent to the drawer if it's not the same
			if (this.path !== parentDrawer?.path) {
				this.parent = parentDrawer;
			}

			// open the provided or first tab
			this.openTab();

			return this.state();
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
