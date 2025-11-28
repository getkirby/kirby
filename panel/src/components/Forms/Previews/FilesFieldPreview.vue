<script>
import ModelsFieldPreview from "./ModelsFieldPreview.vue";

export default {
	extends: ModelsFieldPreview,
	class: "k-files-field-preview",
	endpoint: "items/files",
	methods: {
		tag(file) {
			return {
				text: file.filename,
				link: file.link,
				image: file.image
			};
		}
	}
};
</script>
