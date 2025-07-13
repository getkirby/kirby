import { wrap } from "@/helpers/array.js";

/**
 * Ensures that even when a component prohibits to
 * inherit non-prop attributes, applied listeners
 * are still preserved and attached to the root element
 */
export default {
	data() {
		return {
			__listenersController: new AbortController()
		};
	},
	mounted() {
		// only if the component prohibits to inherit non-prop attributes
		// we need to manually attach the listeners to the root element
		if (this.$options.inheritAttrs === false) {
			for (const attr in this.$attrs) {
				// check if the attribute is an event listener
				if (attr.startsWith("on") === true) {
					// extract the event name
					const event = attr.slice(2).toLowerCase();

					// as there can be multiple listeners for the same event
					// ensure that we can work with an array of listeners
					const listeners = wrap(this.$attrs[attr]);

					// attach each listener to the root element
					for (const listener of listeners) {
						this.$el.addEventListener(event, listener, {
							// use an abort signal to ensure that all listeners
							// are removed when the component is unmounted
							signal: this.__listenersController.signal
						});
					}
				}
			}
		}
	},
	unmounted() {
		this.__listenersController.abort();
	}
};
