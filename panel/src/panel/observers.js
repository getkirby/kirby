import { reactive } from "vue";

/**
 * @since 6.0.0
 */
export default () => {
	return reactive({
		frames: new ResizeObserver((entries) => {
			for (const item of entries) {
				item.target.dispatchEvent(
					new CustomEvent("resize", {
						detail: {
							width: Math.round(item.contentRect.width / 50) * 50,
							height: Math.round(item.contentRect.height / 50) * 50
						}
					})
				);
			}
		})
	});
};
