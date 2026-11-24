import { markRaw } from "vue";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default function Observers() {
	return {
		// ResizeObserver is a native browser object and must not be
		// turned into a reactive proxy, otherwise its methods would be
		// called with the wrong `this` (illegal invocation).
		resize: markRaw(
			new ResizeObserver((entries) => {
				for (const index in entries) {
					const item = entries[index];
					item.target.dispatchEvent(
						new CustomEvent("resize", {
							detail: {
								width: item.contentRect.width,
								height: item.contentRect.height
							}
						})
					);
				}
			})
		)
	};
}
