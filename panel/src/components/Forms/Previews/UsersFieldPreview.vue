<script>
import ModelsFieldPreview from "./ModelsFieldPreview.vue";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	extends: ModelsFieldPreview,
	class: "k-users-field-preview",
	endpoint: "items/users",
	methods: {
		tag(user) {
			return {
				text: user.username ?? user.email,
				link: user.link,
				image: user.image
			};
		}
	}
};
</script>
