<script>
import TextFieldPreview from "./TextFieldPreview.vue";

export default {
	extends: TextFieldPreview,
	inheritAttrs: false,
	props: {
		value: String
	},
	class: "k-time-field-preview",
	computed: {
		text() {
			const dt = this.$library.dayjs.iso(this.value, "time");
			return dt?.format(this.field.display) || "";
		}
	}
};
</script>
