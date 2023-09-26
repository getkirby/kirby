<script>
import BubblesFieldPreview from "./BubblesFieldPreview.vue";

export default {
	extends: BubblesFieldPreview,
	class: "k-object-field-preview",
	props: {
		value: [Array, Object]
	},
	computed: {
		bubbles() {
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
