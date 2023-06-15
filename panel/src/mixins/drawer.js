/**
 * The Drawer mixin is intended for all components
 * that extend <k-drawer> It forwards the methods to
 * the <k-drawer> ref. Extending <k-drawer> directly
 * can lead to breaking methods when the methods are not
 * wired correctly to the right elements and refs.
 */
export default {
	props: {
		icon: String,
		id: String,
		options: {
			type: Array
		},
		tabs: {
			default: () => {},
			type: [Array, Object]
		},
		title: String,
		visible: {
			default: false,
			type: Boolean
		}
	}
};
