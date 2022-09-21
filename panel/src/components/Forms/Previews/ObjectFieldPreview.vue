<script>
import BubblesFieldPreview from "./BubblesFieldPreview.vue";

export default {
	extends: BubblesFieldPreview,
	inheritAttrs: false,
	class: "k-object-field-preview",
	props: {
		value: [Array, Object]
	},
	computed: {
		bubbles() {
			return [
				{
					text: "{ ... }"
				}
			];
		}
	}
};
</script>
