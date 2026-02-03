import { reactive } from "vue";
import State from "./state.js";

export const defaults = () => {
	return {
		code: null,
		default: false,
		direction: "ltr",
		hasCustomDomain: false,
		name: null,
		rules: null
	};
};

/**
 * @since 4.0.0
 */
export default () => {
	const parent = State("language", defaults());

	return reactive({
		...parent,

		get isDefault() {
			return this.default;
		}
	});
};
