import { reactive } from "vue";
import State from "./state.js";

export const defaults = () => {
	return {
		items: [],
		hover: false,
		isOpen: false
	};
};

/**
 * @since 4.0.0
 */
export default (panel) => {
	const parent = State("menu", defaults());
	const media = window.matchMedia?.("(max-width: 60rem)");
	const menu = reactive({
		...parent,

		/**
		 * Closes the mobile menu when clicking outside of it
		 * @internal
		 * @param {Event} event
		 */
		blur(event) {
			const menu = document.querySelector(".k-panel-menu");

			if (!menu || media.matches === false) {
				return false;
			}

			const toggle = document.querySelector(".k-panel-menu-proxy");

			if (
				toggle.contains(event.target) === false &&
				menu.contains(event.target) === false
			) {
				this.close();
			}
		},

		/**
		 * Collapses the sidebar menu
		 * @public
		 */
		close() {
			this.isOpen = false;

			if (media.matches === false) {
				localStorage.setItem("kirby$menu", true);
			}
		},

		/**
		 * Closes the mobile menu when escape key is pressed
		 * @internal
		 * @param {Event} event
		 */
		escape() {
			if (media.matches === false) {
				return false;
			}

			this.close();
		},

		/**
		 * Expands the sidebar menu
		 * @public
		 */
		open() {
			this.isOpen = true;

			if (media.matches === false) {
				localStorage.removeItem("kirby$menu");
			}
		},

		/**
		 * Handles change from mobile to desktop and vice versa
		 * @internal
		 */
		resize() {
			// when resizing to mobile, make sure menu starts closed
			if (media.matches) {
				return this.close();
			}

			// only restore collapse/expanded state when not mobile
			if (localStorage.getItem("kirby$menu") !== null) {
				this.isOpen = false;
			} else {
				this.isOpen = true;
			}
		},

		/**
		 * Sets a new state by retrieving entries
		 *
		 * @param {Array} entries
		 */
		set(items) {
			this.items = items;
			this.resize();
			return this.state();
		},

		/**
		 * Toggles the sidebar menu collapse state
		 * @public
		 */
		toggle() {
			if (this.isOpen) {
				this.close();
			} else {
				this.open();
			}
		}
	});

	// escape key event
	panel.events.on("keydown.esc", menu.escape.bind(menu));

	// outside click event
	panel.events.on("click", menu.blur.bind(menu));

	// only register the resize event once
	media?.addEventListener("change", menu.resize.bind(menu));

	return menu;
};
