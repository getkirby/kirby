<script>
import HtmlFieldPreview from "./HtmlFieldPreview.vue";
import html from "@/panel/html";

export default {
	extends: HtmlFieldPreview,
	class: "k-writer-field-preview",
	data() {
		return {
			sanitized: ""
		};
	},
	computed: {
		html() {
			return html(this.sanitized);
		}
	},
	watch: {
		value: {
			immediate: true,
			async handler(value) {
				const sanitized = await this.$helper.string.sanitizeHTML(value);

				// a newer value may have superseded this one while
				// the writer schema was still being loaded
				if (value === this.value) {
					this.sanitized = sanitized;
				}
			}
		}
	}
};
</script>

<style>
.k-writer-field-preview .k-text {
	overflow-x: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}
</style>
