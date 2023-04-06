import Module from "./module.js";

export const defaults = () => {
	return {
		code: null,
		default: false,
		direction: "ltr",
		name: null,
		rules: []
	};
};

export default () => {
	const parent = Module("language", defaults());

	return {
		...parent,
		get isDefault() {
			return this.default;
		}
	};
};
