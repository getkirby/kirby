import State from "./state.js";

export const defaults = () => {
	return {
		current:
			localStorage.getItem("kirby$theme") ??
			(window.matchMedia?.("(prefers-color-scheme: dark)").matches
				? "dark"
				: "light")
	};
};

/**
 * @since 5.0.0
 */
export default () => {
	const parent = State("theme", defaults());

	return {
		...parent,

		toggle() {
			if (this.current === "dark") {
				this.current = "light";
			} else {
				this.current = "dark";
			}

			localStorage.setItem("kirby$theme", this.current);
		}
	};
};
