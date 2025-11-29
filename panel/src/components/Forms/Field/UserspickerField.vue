<script>
import ModelspickerField from "./ModelspickerField.vue";

/**
 * @displayName UsersField
 */
export default {
	extends: ModelspickerField,
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
