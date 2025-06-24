<script>
import TagsFieldPreview from "./TagsFieldPreview.vue";

export default {
	extends: TagsFieldPreview,
	class: "k-files-field-preview",
	props: {
		html: {
			type: Boolean,
			default: true
		}
	},
	computed: {
		tags() {
			return this.value.map((file) => ({
				text: file.filename,
				link: file.link,
				image: file.image
			}));
		}
	}
};
</script>
