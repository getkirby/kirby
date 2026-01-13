/**
 * Ensures that even when a component prohibits to
 * inherit non-prop attributes, all `data-` attributes
 * are still applied
 */
export default {
	mounted() {
		// only if the component prohibits to inherit non-prop attributes
		if (this.$options.inheritAttrs === false) {
			for (const attr in this.$attrs) {
				if (attr.startsWith("data-") === true) {
					this.$el.setAttribute(attr, this.$attrs[attr]);
				}
			}
		}
	}
};
