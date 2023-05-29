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

export default () => {
	return State("system", defaults());
};
