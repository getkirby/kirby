import { reactive } from "vue";
import State from "./state.js";

export const defaults = () => {
	return {
		isOpen: sessionStorage.getItem("kirby$activation$card") !== "true"
	};
};

/**
 * @since 4.0.0
 */
export default () => {
	const parent = State("activation", defaults());

	return reactive({
		...parent,

		close() {
			sessionStorage.setItem("kirby$activation$card", "true");
			this.isOpen = false;
		},

		open() {
			sessionStorage.removeItem("kirby$activation$card");
			this.isOpen = true;
		}
	});
};
