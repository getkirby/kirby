<script>
import BubblesFieldPreview from "./BubblesFieldPreview.vue";

export default {
	extends: BubblesFieldPreview,
	class: "k-files-field-preview",
	props: {
		html: {
			type: Boolean,
			default: true
		}
	},
	computed: {
		bubbles() {
			return this.value.map((file) => ({
				text: file.filename,
				link: file.link,
				image: file.image
			}));
		}
	}
};
</script>
