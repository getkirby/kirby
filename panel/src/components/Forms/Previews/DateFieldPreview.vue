<script>
import TextFieldPreview from "./TextFieldPreview.vue";

export default {
	extends: TextFieldPreview,
	props: {
		value: String
	},
	class: "k-date-field-preview",
	computed: {
		display() {
			return this.column.display ?? this.field.display;
		},
		format() {
			let format = this.display ?? "YYYY-MM-DD";

			if (this.time?.display) {
				format += " " + this.time.display;
			}

			return format;
		},
		parsed() {
			return this.$library.dayjs(this.value);
		},
		text() {
			if (this.parsed.isValid() === false) {
				return this.value;
			}

			return this.parsed?.format(this.format);
		},
		time() {
			return this.column.time ?? this.field.time;
		}
	}
};
</script>
