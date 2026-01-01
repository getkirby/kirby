import { reactive } from "vue";

/**
 * @since 5.3.0
 */
export default (panel) => {
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
};
