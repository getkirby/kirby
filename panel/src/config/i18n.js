export default {
	install(app) {
		/**
		 * v-direction directive
		 * only applies `:dir="$direction"` if the
		 * component isn't disabled
		 */
		app.directive("direction", {
			inserted(el, binding, vnode) {
				if (vnode.context.disabled !== true) {
					el.dir = window.panel.translation.direction;
				} else {
					el.dir = null;
				}
			}
		});
	}
};
