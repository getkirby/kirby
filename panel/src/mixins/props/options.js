export default {
	props: {
		/**
		 * An array of option objects `{ value, text, info }`
		 */
		options: {
			default: () => [],
			type: Array
		}
	}
};
