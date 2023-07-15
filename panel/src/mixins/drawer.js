import { props as Header } from "@/components/Drawers/Elements/Header.vue";

/**
 * The Drawer mixin is intended for all components
 * that extend <k-drawer> It forwards the methods to
 * the <k-drawer> ref. Extending <k-drawer> directly
 * can lead to breaking methods when the methods are not
 * wired correctly to the right elements and refs.
 */
export default {
	mixins: [Header],
	props: {
		disabled: {
			default: false,
			type: Boolean
		},
		expand: {
			default: false,
			type: Boolean
		},
		icon: String,
		id: String,
		options: {
			type: Array
		},
		title: String,
		visible: {
			default: false,
			type: Boolean
		}
	}
};
