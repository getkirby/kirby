import Modal, { defaults as modalDefaults } from "./modal.js";

export const defaults = () => {
	return {
		...modalDefaults(),
		// when drawers or dialogs are created with the
		// deprecated way of adding a dialog/drawer component
		// to a template, `legacy` is set to true in the open method
		// and the matching modal component will not load it.
		legacy: false,
		// Store for the Vue component reference
		// This will make it possible to hackishly
		// support directly rendered components
		ref: null
	};
};

export default (panel) => {
	// shortcut to submit dialogs
	panel.events.on("dialog.save", (e) => {
		e?.preventDefault?.();
		panel.dialog.submit();
	});

	const parent = Modal(panel, "dialog", defaults());

	return {
		...parent,

		/**
		 * Closes the modal
		 */
		async close() {
			// close legacy components
			// if it is still open
			if (this.ref) {
				this.ref.visible = false;
			}

			parent.close.call(this);
		},

		async open(feature, options = {}) {
			// check for legacy Vue components
			if (feature instanceof window.Vue) {
				return this.openComponent(feature);
			}

			return parent.open.call(this, feature, options);
		},

		/**
		 * Takes a legacy dialog component and
		 * opens it manually.
		 *
		 * @param {any} component
		 */
		async openComponent(component) {
			panel.deprecated(
				"Dialog components should no longer be used in your templates"
			);

			const state = await parent.open.call(this, {
				component: component.$options._componentTag,
				// don't render this in the modal
				// component. The Vue component already
				// takes over rendering.
				legacy: true,
				// Use a combination of attributes and props
				// to get everything that was passed to the component
				props: {
					...component.$attrs,
					...component.$props
				},
				// add a reference to the Vue component
				// This will make it possible to determine
				// its open state in the dialog or drawer components
				ref: component
			});

			const listeners = this.listeners();

			for (const listener in listeners) {
				component.$on(listener, listeners[listener]);
			}

			component.visible = true;

			return state;
		}
	};
};
