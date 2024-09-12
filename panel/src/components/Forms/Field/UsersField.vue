<script>
import ModelsField from "./ModelsField.vue";

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
