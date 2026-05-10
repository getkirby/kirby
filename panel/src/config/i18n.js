export default {
	install(app) {
		const dir = (el, binding) => {
			if (binding.instance.disabled !== true) {
				el.dir = window.panel.language.direction;
			} else {
				el.removeAttribute("dir");
			}
		};

		/**
		 * v-direction directive
		 * only applies `:dir="$direction"` if the
		 * component isn't disabled
		 */
		app.directive("direction", {
			beforeMount: dir,
			updated: dir
		});
	}
};
