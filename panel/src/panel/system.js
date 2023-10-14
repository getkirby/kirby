import State from "./state.js";

export const defaults = () => {
	return {
		ascii: {},
		csrf: null,
		isLocal: null,
		locales: {},
		slugs: [],
		title: null
	};
};

/**
 * @since 4.0.0
 */
export default () => {
	return State("system", defaults());
};
