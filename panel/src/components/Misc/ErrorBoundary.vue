<script>
import { h } from "vue";

/**
 * @internal
 */
export default {
	data() {
		return {
			error: null
		};
	},
	errorCaptured(error) {
		if (this.$panel.debug) {
			window.console.warn(error);
		}

		this.error = error;
		return false;
	},
	render() {
		if (this.error) {
			if (this.$slots.error) {
				return this.$slots.error()[0];
			}

			if (this.$slots.error) {
				return this.$slots.error({
					error: this.error
				});
			}

			return h(
				"k-box",
				{ attrs: { theme: "negative" } },
				this.error.message ?? this.error
			);
		}

		return this.$slots.default()[0];
	}
};
</script>
