import State from "./state.js";

export const defaults = () => {
	return {
		setting: localStorage.getItem("kirby$theme")
	};
};

/**
 * @since 5.0.0
 */
export default () => {
	const parent = State("theme", defaults());

	return {
		...parent,

		get current() {
			return this.setting ?? this.system;
		},

		reset() {
			this.setting = null;
			localStorage.removeItem("kirby$theme");
		},

		set(theme) {
			this.setting = theme;
			localStorage.setItem("kirby$theme", theme);
		},

		get system() {
			return window.matchMedia?.("(prefers-color-scheme: dark)").matches
				? "dark"
				: "light";
		}
	};
};
