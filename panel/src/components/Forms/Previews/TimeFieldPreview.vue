<script>
import DateFieldPreview from "./DateFieldPreview.vue";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
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
