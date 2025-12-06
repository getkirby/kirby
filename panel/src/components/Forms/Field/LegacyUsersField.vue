<script>
import ModelsField from "./LegacyModelsField.vue";

/**
 * @deprecated 6.0.0 Use `k-users-field` instead
 */
export default {
	extends: ModelsField,
	type: "users",
	computed: {
		emptyProps() {
			return {
				icon: "users",
				text:
					this.empty ??
					(this.multiple && this.max !== 1
						? this.$t("field.users.empty")
						: this.$t("field.users.empty.single"))
			};
		}
	}
};
</script>
