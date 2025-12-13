<script>
export const props = {
	props: {
		/**
		 * Array of item definitions
		 */
		items: {
			type: Array,
			default: () => []
		},
		/**
		 * Additional layout-specific options
		 */
		options: Object,
		/**
		 * Whether items are in selecting mode
		 * @since 5.0.0
		 */
		selecting: Boolean,
		/**
		 * Array of selected items
		 * @since 6.0.0
		 */
		selected: {
			type: Array,
			default: () => []
		},
		/**
		 * @since 6.0.0
		 * @values "single", "multiple"
		 */
		selectmode: {
			type: String,
			default: "multiple"
		},
		/**
		 * Whether items are generally sortable.
		 * Each item can disable this individually.
		 */
		sortable: Boolean
	}
};

export default {
	mixins: [props]
};
</script>
