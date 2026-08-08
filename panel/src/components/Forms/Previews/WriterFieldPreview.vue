<script>
import HtmlFieldPreview from "./HtmlFieldPreview.vue";

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
			return this.sanitized;
		}
	},
	watch: {
		value: {
			immediate: true,
			async handler(value) {
				const html = await this.$helper.string.sanitizeHTML(value);

				// a newer value may have superseded this one while
				// the writer schema was still being loaded
				if (value === this.value) {
					this.sanitized = html;
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
