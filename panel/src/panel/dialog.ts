import Modal, { defaults as modalDefaults, type ModalState } from "./modal";
import { isObject } from "@/helpers/object";
import { reactive } from "vue";
import { type Listener } from "./listeners";

export type DialogState = ModalState & {};

export function defaults(): DialogState {
	return {
		...modalDefaults()
	};
}

// Options used when opening a dialog
export type DialogOptions = DialogState & {
	replace?: boolean;
	url?: string;
};

/**
 * @since 4.0.0
 */
export default function Dialog(panel: TODO) {
	// shortcut to submit dialogs
	panel.events.on("dialog.save", (e: Event) => {
		e?.preventDefault?.();
		panel.dialog.submit();
	});

	const parent = Modal(panel, "dialog", defaults());

	return reactive({
		...parent,

		/**
		 * Opens dialog via JS object or loads it from the server
		 *
		 * @example
		 * panel.dialog.open('some/dialog');
		 *
		 * @example
		 * panel.dialog.open('some/dialog', () => {
		 *  // on submit
		 * });
		 *
		 * @example
		 * panel.dialog.open('some/dialog', {
		 *   query: {
		 *     template: 'some-template'
		 *   },
		 *   on: {
		 *     submit: () => {},
		 *     cancel: () => {}
		 *   }
		 * });
		 *
		 * @example
		 * panel.dialog.open({
		 *   component: 'k-remove-dialog',
		 *   props: {
		 *      text: 'Do you really want to delete this?'
		 *   },
		 *   on: {
		 *     submit: () => {},
		 *     cancel: () => {}
		 *   }
		 * });
		 */
		async open(
			dialog: string | URL | Partial<Prettify<DialogOptions>>,
			options: Partial<Prettify<DialogOptions>> | Listener = {}
		): Promise<Prettify<DialogState>> {
			// extract replace before dialog is transformed
			const replace = isObject(dialog) ? dialog.replace : undefined;

			// handle drawer object with url property
			if (isObject(dialog) && dialog.url) {
				options = dialog;
				dialog = dialog.url;
				delete options.url;
			}

			// prefix URLs
			if (typeof dialog === "string") {
				dialog = `/dialogs/${dialog}`;
			}

			const state = await parent.open.call(this, dialog, options);

			// add it to the history
			if (state?.id) {
				const milestone = state as DialogState & { id: string };
				this.history.add(milestone, replace);
			}

			return state;
		}
	});
}
