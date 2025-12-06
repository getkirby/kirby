import { reactive } from "vue";

/**
 * @since 6.0.0
 */
export default (panel) => {
	return reactive({
		frames: new ResizeObserver((entries) => {
			for (const index in entries) {
				const item = entries[index];
				item.target.dispatchEvent(
					new CustomEvent("resize", {
						detail: {
							width: Math.round(item.contentRect.width / 50) * 50
						}
					})
				);
			}
		})
	});
};
