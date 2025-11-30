<script>
import ModelsField from "./ModelsField.vue";

/**
 * @displayName LegacyPagesField
 * @deprecated 6.0.0 Use `k-pages-field` instead
 */
export default {
	extends: ModelsField,
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