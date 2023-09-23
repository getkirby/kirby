import { autofocus, disabled, id, name, required } from "@/mixins/props.js";

export const props = {
	mixins: [autofocus, disabled, id, name, required]
};

export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"],
	methods: {
		/**
		 * Focuses the input element
		 * @public
		 */
		focus() {
			this.$el.focus();
		}
	}
};
