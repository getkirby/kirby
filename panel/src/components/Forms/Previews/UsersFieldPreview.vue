<script>
import TagsFieldPreview from "./TagsFieldPreview.vue";

export default {
	extends: TagsFieldPreview,
	class: "k-users-field-preview",
	props: {
		html: {
			type: Boolean,
			default: true
		}
	},
	computed: {
		tags() {
			return this.value.map((user) => {
				return {
					text: user.username ?? user.email,
					link: user.link,
					image: user.image
				};
			});
		}
	}
};
</script>
