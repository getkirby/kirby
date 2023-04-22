import Module from "./module.js";

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
	return Module("system", defaults());
};
