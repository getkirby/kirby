<script>
import BubblesFieldPreview from "./BubblesFieldPreview.vue";

export default {
	inheritAttrs: false,
	extends: BubblesFieldPreview,
	class: "k-files-field-preview",
	computed: {
		bubbles() {
			return this.value.map((file) => {
				return {
					text: file.filename,
					link: file.link,
					image: file.image
				};
			});
		}
	}
};
</script>
