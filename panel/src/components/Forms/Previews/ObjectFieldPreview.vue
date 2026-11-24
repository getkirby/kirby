<script>
import TagsFieldPreview from "./TagsFieldPreview.vue";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	extends: TagsFieldPreview,
	class: "k-object-field-preview",
	props: {
		value: [Array, Object]
	},
	computed: {
		tags() {
			if (!this.value) {
				return [];
			}

			return [
				{
					text: "{ ... }"
				}
			];
		}
	}
};
</script>
