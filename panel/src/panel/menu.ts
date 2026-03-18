import { reactive } from "vue";
import State from "./state";

// TODO: replace with proper menu entry/button type
type MenuItems = Array<Record<string, unknown> | "-">;
type MenuState = {
	hover: boolean;
	isOpen: boolean;
	items: MenuItems;
};

export function defaults(): MenuState {
	return {
		hover: false,
		isOpen: false,
		items: []
	};
}

/**
 * Manages the Panel sidebar navigation menu, including open/close state,
 * mobile vs. desktop behavior, and state persistence via localStorage
 *
 * @since 4.0.0
 */
export default function Menu(panel: {
	events: { on: (event: string, handler: EventListener) => void };
}) {
	const parent = State("menu", defaults());
	const media = window.matchMedia("(max-width: 60rem)");
	const menu = reactive({
		...parent,

		/**
		 * Closes the mobile menu when clicking outside of it
		 * @internal
		 */
		blur(event: Event): void {
			const menu = document.querySelector(".k-panel-menu");

			if (!menu || media.matches === false) {
				return;
			}

			const selector = ".k-panel-menu-proxy";
			const toggle = document.querySelector(selector) as HTMLElement;
			const target = event.target as Node;

			if (
				toggle.contains(target) === false &&
				menu.contains(target) === false
			) {
				this.close();
			}
		},

		/**
		 * Collapses the sidebar menu
		 */
		close(): void {
			this.isOpen = false;

			if (media.matches === false) {
				localStorage.setItem("kirby$menu", "true");
			}
		},

		/**
		 * Closes the mobile menu when escape key is pressed
		 * @internal
		 */
		escape(): void {
			if (media.matches === false) {
				return;
			}

			this.close();
		},

		/**
		 * Expands the sidebar menu
		 */
		open(): void {
			this.isOpen = true;

			if (media.matches === false) {
				localStorage.removeItem("kirby$menu");
			}
		},

		/**
		 * Handles change from mobile to desktop and vice versa
		 * @internal
		 */
		resize(): void {
			// when resizing to mobile, make sure menu starts closed
			if (media.matches) {
				return this.close();
			}

			// on desktop, restore the last collapsed/expanded state
			this.isOpen = localStorage.getItem("kirby$menu") === null;
		},

		/**
		 * Sets the menu items and restores the open/closed state
		 */
		set(items: MenuItems): MenuState {
			this.items = items;
			this.resize();
			return this.state();
		},

		/**
		 * Toggles the sidebar menu collapse state
		 */
		toggle(): void {
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
	media.addEventListener("change", menu.resize.bind(menu));

	return menu;
}
