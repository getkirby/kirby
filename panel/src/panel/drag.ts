import { reactive } from "vue";
import State from "./state";

type DragState = {
	type?: string | null;
	data: Record<string, unknown>;
};

export function defaults(): DragState {
	return {
		type: null,
		data: {}
	};
}

/**
 * Tracks the current drag operation, including its type and payload
 *
 * @since 4.0.0
 */
export default function Drag() {
	const parent = State("drag", defaults());

	return reactive({
		...parent,

		/**
		 * Whether a drag operation is currently active
		 */
		get isDragging(): boolean {
			return this.type !== null;
		},

		/**
		 * Starts a drag operation with a type and optional payload
		 */
		start(type: string, data: Record<string, unknown>): void {
			this.set({ type, data });
		},

		/**
		 * Clears the drag state
		 */
		stop(): void {
			this.reset();
		}
	});
}
