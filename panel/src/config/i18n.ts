import type { App, DirectiveBinding } from "vue";

export default {
	install(app: App) {
		const dir = (el: HTMLElement, binding: DirectiveBinding) => {
			const instance = binding.instance as { disabled?: boolean } | null;

			if (instance?.disabled !== true) {
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
