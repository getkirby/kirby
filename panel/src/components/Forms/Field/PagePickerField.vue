<script>
import ModelPickerField from "./ModelPickerField.vue";

/**
 * @displayName PagesField
 */
export default {
	extends: ModelPickerField,
	type: "pages",
	computed: {
		emptyProps() {
			return {
				icon: "page",
				text:
					this.empty ??
					(this.multiple && this.max !== 1
						? this.$t("field.pages.empty")
						: this.$t("field.pages.empty.single"))
			};
		}
	}
};
</script>
