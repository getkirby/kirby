import State from "./state.js";

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
	return State("user", defaults());
};
