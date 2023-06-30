import State from "./state.js";

export const defaults = () => {
	return {
		email: null,
		id: null,
		language: null,
		role: null,
		theme: window.matchMedia("(prefers-color-scheme: dark)").matches
			? "dark"
			: "light",
		username: null
	};
};

/**
 * @since 4.0.0
 */
export default () => {
	return State("user", defaults());
};
