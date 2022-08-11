<script>
import BubblesFieldPreview from "./BubblesFieldPreview.vue";

export default {
	inheritAttrs: false,
	extends: BubblesFieldPreview,
	class: "k-array-field-preview",
	computed: {
		bubbles() {
			return [
				{
					text:
						this.value.length === 1
							? `1 ${this.$t("entry")}`
							: `${this.value.length} ${this.$t("entries")}`
				}
			];
		}
	}
};
</script>
