<script>
import HtmlFieldPreview from "./HtmlFieldPreview.vue";
import html from "@/panel/html";

export default {
	extends: HtmlFieldPreview,
	class: "k-writer-field-preview",
	computed: {
		html() {
			return html(this.$helper.string.sanitizeHTML(this.value));
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
