<script>
import BubblesFieldPreview from "./BubblesFieldPreview.vue";

export default {
	extends: BubblesFieldPreview,
	inheritAttrs: false,
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
