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
		/**
		 * @internal
		 */
		current: {
			default: true,
			type: Boolean
		},
		/**
		 * The default icon for the drawer header
		 */
		icon: String,
		/**
		 * Width of the drawer
		 * @values "tiny", "small", "default", "large", "huge"
		 */
		size: {
			default: "default",
			type: String
		},
		/**
		 * A unique ID for the drawer
		 */
		id: String,
		/**
		 * Option buttons for the drawer header
		 */
		options: {
			type: Array
		},
		/**
		 * The default title for the drawer header
		 */
		title: String,
		/**
		 * @internal
		 */
		visible: {
			default: false,
			type: Boolean
		}
	}
};
