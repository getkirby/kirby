<script>
import { h, resolveComponent } from "vue";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
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
				return this.$slots.error({
					error: this.error
				});
			}

			return h(resolveComponent("k-box"), {
				text: this.error.message ?? String(this.error),
				theme: "negative"
			});
		}

		return this.$slots.default?.()[0];
	}
};
</script>
