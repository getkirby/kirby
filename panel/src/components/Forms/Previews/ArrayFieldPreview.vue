<script>
import TagsFieldPreview from "./TagsFieldPreview.vue";

export default {
	extends: TagsFieldPreview,
	inheritAttrs: false,
	class: "k-array-field-preview",
	computed: {
		tags() {
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
