import type { ComponentOptions } from "vue";
import { wrap } from "@/helpers/array";

/**
 * Ensures that even when a component prohibits to
 * inherit non-prop attributes, applied listeners
 * are still preserved and attached to the root element
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	mounted() {
		if (this.$options.inheritAttrs !== false) {
			return;
		}

		for (const attr in this.$attrs) {
			// check if the attribute is an event listener
			if (attr.startsWith("on") === false) {
				continue;
			}

			// the controller is created on demand,
			// only very few of all components this mixin
			// is applied to ever receives a listener
			this.__listeners ??= new AbortController();

			// extract the event name
			const event = attr.slice(2).toLowerCase();

			for (const listener of wrap(this.$attrs[attr])) {
				// attach each listener to the root element
				this.$el.addEventListener(event, listener, {
					// use an abort signal to ensure that all listeners
					// are removed when the component is unmounted
					signal: this.__listeners.signal
				});
			}
		}
	},
	unmounted() {
		this.__listeners?.abort();
	}
} satisfies ComponentOptions;
