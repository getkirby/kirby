export default {
	install(app) {
		const dir = (el, binding, vnode) => {
			if (vnode.context.disabled !== true) {
				el.dir = window.panel.language.direction;
			} else {
				el.dir = null;
			}
		};

		/**
		 * v-direction directive
		 * only applies `:dir="$direction"` if the
		 * component isn't disabled
		 */
		app.directive("direction", {
			bind: dir,
			update: dir
		});
	}
};
