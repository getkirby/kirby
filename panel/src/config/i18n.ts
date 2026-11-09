import type { App, DirectiveBinding } from "vue";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
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
		 *
		 * @deprecated 6.0.0 Use the `dir` and `lang` props of the field instead
		 */
		app.directive("direction", {
			beforeMount: (el: HTMLElement, binding: DirectiveBinding) => {
				window.panel.deprecated(
					"`v-direction`: the directive has been deprecated. Use the `dir` and `lang` props of the field instead."
				);

				dir(el, binding);
			},
			updated: dir
		});
	}
};
