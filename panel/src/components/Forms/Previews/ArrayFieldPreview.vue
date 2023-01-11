<script>
import BubblesFieldPreview from "./BubblesFieldPreview.vue";

export default {
	extends: BubblesFieldPreview,
	inheritAttrs: false,
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
