<script>
import ModelPickerField from "./ModelPickerField.vue";

/**
 * @displayName UsersField
 */
export default {
	extends: ModelPickerField,
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
