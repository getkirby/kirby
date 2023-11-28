<script>
import ModelsField from "./ModelsField.vue";

export default {
	extends: ModelsField,
	type: "users",
	computed: {
		emptyProps() {
			return {
				icon: "users",
				text: this.empty ?? this.$t("field.users.empty")
			};
		}
	}
};
</script>
