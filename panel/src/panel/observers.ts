import { reactive } from "vue";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default function Observers() {
	return reactive({
		resize: new ResizeObserver((entries) => {
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
	});
}
