import Module from "./module.js";

export const defaults = () => {
	return {
		email: null,
		id: null,
		language: null,
		role: null,
		username: null
	};
};

export default () => {
	return Module("user", defaults());
};
