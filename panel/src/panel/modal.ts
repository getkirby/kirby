import { reactive } from "vue";
import { isObject } from "@/helpers/object";
import Feature, {
	defaults as featureDefaults,
	type FeatureState
} from "./feature";
import History from "@/helpers/history";
import focus from "@/helpers/focus";
import { uuid } from "@/helpers/string";
import { wrap } from "@/helpers/array";
import { type Listener } from "./listeners";

export type ModalState = FeatureState & {
	id: string | null;
	props: Record<string, unknown> & { value: Record<string, unknown> };
};

type ModalSubmitResponse = {
	emit?: false;
	event?: string | string[];
	message?: string;
	redirect?: string | { url: string; options?: Record<string, unknown> };
	reload?: unknown;
	route?: string | { url: string; options?: Record<string, unknown> };
};

/**
 * Additional default values for modals
 */
export function defaults(): ModalState {
	return {
		...featureDefaults(),
		id: null,
		props: { value: {} }
	};
}

/**
 * A modal is a feature that can be opened and
 * closed and will be placed in the Panel by the matching
 * Modal component
 * @since 4.0.0
 */
export default function Modal<T extends ModalState>(
	panel: TODO,
	key: string,
	defaults: T
) {
	const parent = Feature(panel, key, defaults);

	return reactive({
		...parent,

		/**
		 * Dialogs and drawers can be cancelled. This could
		 * later be connected with a backend endpoint to
		 * run an action based on cancellation. Maybe delete an
		 * entry or something.
		 */
		async cancel(): Promise<void> {
			if (this.isOpen) {
				this.emit("cancel");
			}

			this.close();
		},

		/**
		 * Closes the modal and goes back to the
		 * parent one if it has been stored
		 *
		 * @param id - Which modal to close, true for all
		 */
		async close(id?: string | true): Promise<void> {
			if (this.isOpen === false) {
				return;
			}

			// Compare the modal id to avoid closing the wrong modal.
			// This is particularly useful in nested modals.
			if (id !== undefined && id !== true && id !== this.id) {
				return;
			}

			if (id === true) {
				this.history.clear();
			} else {
				this.history.removeLast();
			}

			// store the closed listener
			const closed = this.on.closed ?? (() => {});

			// history not empty, open previous modal
			if (this.history.isEmpty() === false) {
				const previous = this.history.last()!;
				this.open(previous);
				closed();
				return;
			}

			// no more items in the history,
			// all modals shall be closed
			this.isOpen = false;
			this.emit("close");
			this.reset();
			closed();

			if (panel.overlays().length === 0) {
				// unblock the overflow until we can use :has for this.
				document.documentElement.removeAttribute("data-overlay");
				document.documentElement.style.removeProperty("--scroll-top");
			}
		},

		/**
		 * Set the focus to the first focusable input
		 * or button in the modal. The input can also
		 * be set manually.
		 */
		focus(input?: string): void {
			focus(`.k-${this.key()}-portal`, input);
		},

		goTo(id: string): void {
			const state = this.history.goto(id);

			if (state !== undefined) {
				this.open(state);
			}
		},

		history: new History<T & { id: string }>(),

		/**
		 * Form drawers and dialogs can use this
		 * to update their value property and also
		 * fire an input event.
		 */
		input(value: Record<string, unknown>) {
			if (this.isOpen === false) {
				return;
			}

			this.props.value = value;
			this.emit("input", value);
		},

		isOpen: false,

		/**
		 * Define the default listeners
		 * for the State component
		 */
		listeners(): Record<string, Listener> {
			return {
				...this.on,
				cancel: this.cancel.bind(this),
				close: this.close.bind(this),
				input: this.input.bind(this),
				submit: this.submit.bind(this),
				success: this.success.bind(this)
			};
		},

		/**
		 * Customized setter for the active state, which
		 * will make sure to close unwanted notifications
		 * before a modal is opened. It also sets the
		 * isOpen state.
		 */
		async open(
			modal: string | URL | Partial<Prettify<T>>,
			options: Partial<Prettify<T>> | Listener = {}
		): Promise<Prettify<T>> {
			// close previous notifications from other
			// contexts, if the modal wasn't open so far
			if (this.isOpen === false) {
				panel.notification.close();
			}

			// open the modal feature via url or with a state object
			await parent.open.call(this, modal, options);

			// only mark this as open if a component has been defined
			if (this.component) {
				// block the overflow until we can use :has for this.
				document.documentElement.setAttribute("data-overlay", "true");
				document.documentElement.style.setProperty(
					"--scroll-top",
					window.scrollY + "px"
				);

				// mark the modal as open
				this.isOpen = true;
			}

			return this.state();
		},

		/**
		 * If the feature has a path, it can be reloaded
		 * with this method to replace the current modal
		 *
		 * @example
		 * panel.dialog.reload();
		 */
		async reload(options?: Partial<Prettify<T>>): Promise<Prettify<T> | false> {
			if (!this.path) {
				return false;
			}

			const url = this.url();

			await this.close();
			return this.open(url, options);
		},

		/**
		 * Sets a new active state for the modal
		 * This is done whenever the state is an object
		 * and not undefined or null
		 */
		set(state: Partial<Prettify<T>>): Prettify<T> {
			parent.set.call(this, state);

			// create a unique ID for the drawer if it does not have one
			this.id ??= uuid();

			return this.state();
		},

		/**
		 * Custom submitter for the dialog/drawer
		 * It will automatically close the modal
		 * if there's no submit listener or backend route.
		 */
		async submit(
			value: Record<string, unknown>,
			options: Partial<Prettify<T>> = {}
		): Promise<unknown> {
			if (this.isLoading === true) {
				return;
			}

			value ??= this.value;

			if (this.hasEventListener("submit")) {
				return this.emit("submit", value, options);
			}

			// close the drawer or dialog if there's no submitter
			// and no connection to the backend
			if (!this.path) {
				return this.close();
			}

			// send a request to the backend
			const response = await this.post(value, options);

			// the request failed and should have raised an error
			if (isObject(response) === false) {
				return response;
			}

			// Get details from the response object,
			// i.e. { dialog: { ... } }
			// pass it forward to the success handler
			// to react on elements in the response
			return this.success(response[this.key()] ?? {});
		},

		/**
		 * This is rebuilding the previous
		 * behaviors from the dialog mixin.
		 * Most of the response handling should
		 * be redone. But we keep it for compatibility
		 */
		success(success: ModalSubmitResponse | string): unknown {
			if (this.hasEventListener("success")) {
				return this.emit("success", success);
			}

			// close the modal
			this.close();

			if (typeof success === "string") {
				panel.notification.success(success);
				return;
			}

			// show a success message
			this.successNotification(success);

			// send custom events to the event bus
			this.successEvents(success);

			// redirect or reload
			if (success.route || success.redirect) {
				// handle any redirects
				this.successRedirect(success);
			} else {
				// reload the parent view to show changes
				panel.view.reload(success.reload);
			}

			return success;
		},

		/**
		 * Emit all events that might be in the response
		 */
		successEvents(state: ModalSubmitResponse): void {
			if (state.event) {
				// wrap single events to treat them all at once
				const events = wrap(state.event);

				// emit all defined events
				for (const event of events) {
					if (typeof event === "string") {
						panel.events.emit(event, state);
					}
				}
			}

			// emit a success event
			if (state.emit !== false) {
				panel.events.emit("success", state);
			}
		},

		/**
		 * Sends a success notification if the
		 * response contains a success message
		 */
		successNotification(state: ModalSubmitResponse): void {
			if (state.message) {
				panel.notification.success(state.message);
			}
		},

		/**
		 * Handles redirects in submit responses
		 */
		successRedirect(state: ModalSubmitResponse): unknown | false {
			const redirect = state.route ?? state.redirect;

			// no redirect
			if (!redirect) {
				return false;
			}

			if (typeof redirect === "string") {
				return panel.open(redirect);
			}

			return panel.open(redirect.url, redirect.options);
		},

		/**
		 * Quick access to the value prop.
		 * Drawers and dialogs might likely have forms
		 * so this seems useful.
		 */
		get value(): Record<string, unknown> {
			return this.props.value;
		}
	});
}
