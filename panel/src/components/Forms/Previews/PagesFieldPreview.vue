<script>
import BubblesFieldPreview from "./BubblesFieldPreview.vue";

export default {
	extends: BubblesFieldPreview,
	inheritAttrs: false,
	class: "k-pages-field-preview",
	props: {
		html: {
			type: Boolean,
			default: true
		}
	}
};
</script>
