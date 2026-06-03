import type { App, DirectiveBinding } from "vue";
import { escapeHTML } from "@/helpers/string";
import { HtmlString } from "@/panel/html";

export default {
	install(app: App) {
		const render = (el: HTMLElement, binding: DirectiveBinding) => {
			if (binding.value instanceof HtmlString) {
				el.innerHTML = binding.value.toString();
				return;
			}

			if (binding.value === null || binding.value === undefined) {
				el.innerHTML = "";
				return;
			}

			el.innerHTML = escapeHTML(binding.value);
		};

		/**
		 * v-safe-html directive
		 *
		 * Safe-by-default replacement for `v-html`: plain strings are
		 * HTML-escaped at the render site, `HtmlString` instances
		 * (flagged as trusted by the backend) are written through
		 * unchanged. The bound value can be a `String`, an
		 * `HtmlString`, `null` or `undefined`.
		 */
		app.directive("safe-html", {
			mounted: render,
			updated: (el, binding) => {
				if (binding.value === binding.oldValue) {
					return;
				}

				render(el, binding);
			}
		});
	}
};
