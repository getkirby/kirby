import { reactive } from "vue";
import State from "./state";

type ActivationState = {
	isOpen: boolean;
};

export function defaults(): ActivationState {
	return {
		isOpen: sessionStorage.getItem("kirby$activation$card") !== "true"
	};
}

/**
 * Tracks whether the Panel license activation banner is visible.
 * The initial state is read from sessionStorage so it
 * persists across page reloads.
 *
 * @since 4.0.0
 */
export default function Activation() {
	const parent = State("activation", defaults());

	return reactive({
		...parent,

		close(): void {
			sessionStorage.setItem("kirby$activation$card", "true");
			this.set({ isOpen: false });
		},

		open(): void {
			sessionStorage.removeItem("kirby$activation$card");
			this.set({ isOpen: true });
		}
	});
}
