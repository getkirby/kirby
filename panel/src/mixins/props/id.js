import { useUid } from "@/helpers/useUid.js";

export default {
	props: {
		/**
		 * A unique ID
		 */
		id: {
			type: [Number, String],
			default: () => useUid()
		}
	}
};
