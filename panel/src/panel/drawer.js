import Island, { defaults as islandDefaults } from "./island.js";

export const defaults = () => {
	return {
		...islandDefaults(),
		parent: null,
		tabId: null
	};
};

export default (panel) => {
	const parent = Island(panel, "drawer", defaults());

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
		async close() {
			await parent.close.call(this);
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

			// add the parent to the drawer
			this.parent = parentDrawer;

			// open the first tab
			this.openTab();

			return this.state();
		},
		openTab(tabId) {
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
