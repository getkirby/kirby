<script>
import TagsFieldPreview from "./TagsFieldPreview.vue";

export default {
	extends: TagsFieldPreview,
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
