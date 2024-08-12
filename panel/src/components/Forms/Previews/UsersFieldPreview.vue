<script>
import TagsFieldPreview from "./TagsFieldPreview.vue";

export default {
	extends: TagsFieldPreview,
	class: "k-users-field-preview",
	computed: {
		bubble() {
			return this.value.map((user) => {
				return {
					text: user.username,
					link: user.link,
					image: user.image
				};
			});
		}
	}
};
</script>
