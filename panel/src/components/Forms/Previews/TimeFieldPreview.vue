<script>
import DateFieldPreview from "./DateFieldPreview.vue";

export default {
	extends: DateFieldPreview,
	class: "k-time-field-preview",
	computed: {
		format() {
			return this.display ?? "HH:mm";
		},
		parsed() {
			return this.$library.dayjs.iso(this.value, "time");
		},
		text() {
			return this.parsed?.format(this.format);
		}
	}
};
</script>
