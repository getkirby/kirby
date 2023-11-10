export default {
	props: {
		/**
		 * An array of option objects
		 * @value { value, text, info }
		 */
		options: {
			default: () => [],
			type: Array
		}
	}
};
