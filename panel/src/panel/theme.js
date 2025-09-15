import { reactive } from "vue";
import State from "./state.js";

const media = window.matchMedia?.("(prefers-color-scheme: dark)");

export const defaults = () => {
	return {
		setting: localStorage.getItem("kirby$theme"),
		system: media?.matches ? "dark" : "light"
	};
};

/**
 * @since 5.0.0
 */
export default (panel) => {
	const parent = State("theme", defaults());

	const theme = reactive({
		...parent,

		get config() {
			return panel.config.theme;
		},

		get current() {
			const setting = this.setting ?? this.config;

			if (setting === "system") {
				return this.system;
			}

			return setting ?? this.system;
		},

		reset() {
			this.setting = null;
			localStorage.removeItem("kirby$theme");
		},

		set(theme) {
			this.setting = theme;
			localStorage.setItem("kirby$theme", theme);
		}
	});

	// watch the media query for changes
	// and update the system state
	media?.addEventListener("change", (event) => {
		theme.system = event.matches ? "dark" : "light";
	});

	return theme;
};
