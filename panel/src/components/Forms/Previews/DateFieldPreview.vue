<script>
import TextFieldPreview from "./TextFieldPreview.vue";

export default {
	extends: TextFieldPreview,
	inheritAttrs: false,
	props: {
		value: String
	},
	class: "k-date-field-preview",
	computed: {
		text() {
			if (typeof this.value !== "string") {
				return "";
			}

			const dt = this.$library.dayjs(this.value);

			if (!dt) {
				return "";
			}

			let format = this.column?.display || this.field?.display || "YYYY-MM-DD";
			let time = this.column?.time?.display || this.field?.time?.display;

			if (time) {
				format += " " + time;
			}

			return dt.format(format);
		}
	}
};
</script>
