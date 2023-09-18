<script>
import ModelsField from "./ModelsField.vue";

export default {
	extends: ModelsField,
	type: "pages",
	computed: {
		emptyProps() {
			return {
				icon: "page",
				text: this.empty ?? this.$t("field.pages.empty")
			};
		}
	}
};
</script>
