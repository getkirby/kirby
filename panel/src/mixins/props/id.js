import { useId } from "vue";

export default {
	props: {
		/**
		 * A unique ID
		 */
		id: {
			type: [Number, String],
			default: () => useId()
		}
	}
};
