import State from "./state.js";

export const defaults = () => {
	return {
		entries: [],
		isOpen: false
	};
};

export default () => {
	const parent = State("menu", defaults());
	const media = window.matchMedia("(max-width: 40rem)");

	return {
		...parent,

		/**
		 * Collapses the sidebar menu
		 * @public
		 */
		close() {
			this.isOpen = false;

			if (media.matches) {
				document.body.style.overflow = null;
				document.removeEventListener("click", this.onBackground);
			} else {
				localStorage.removeItem("kirby$menu");
			}
		},

		/**
		 * Closes the mobile menu when clicking outside of it
		 * @internal
		 * @param {Event} event
		 */
		onBackground(event) {
			const menu = document.querySelector(".k-panel-menu");
			const toggle = document.querySelector(".k-panel-menu-proxy");

			if (
				toggle.contains(event.target) === false &&
				menu.contains(event.target) === false
			) {
				this.close();
			}
		},

		/**
		 * Handles change from mobile to desktop and vice versa
		 * @internal
		 */
		onResize() {
			// when resizing to mobile, make sure menu starts closed
			if (media.matches) {
				return this.close();
			}

			// only restore collapse/expanded state when not mobile
			if (localStorage.getItem("kirby$menu") !== null) {
				this.isOpen = true;
			} else {
				this.isOpen = false;
			}
		},

		/**
		 * Expands the sidebar menu
		 * @public
		 */
		open() {
			this.isOpen = true;

			if (media.matches) {
				document.body.style.overflow = "hidden";
				document.addEventListener("click", this.onBackground);
			} else {
				localStorage.setItem("kirby$menu", true);
			}
		},

		/**
		 * Sets a new state by retrieving etnries
		 *
		 * @param {Array} entries
		 */
		set(entries) {
			media.addEventListener("change", this.onResize.bind(this));
			return parent.set.call(this, { entries });
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
	};
};
